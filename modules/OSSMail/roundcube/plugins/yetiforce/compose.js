/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

window.rcmail && rcmail.addEventListener('init', function (evt) {
	var crm = window.crm = getCrmWindow();
	var crmPath = rcmail.env.site_URL + 'index.php?';

	rcmail.env.compose_commands.push('yetiforce.addFilesToMail');
	rcmail.env.compose_commands.push('yetiforce.addFilesFromCRM');

	// Document selection
	rcmail.register_command('yetiforce.addFilesToMail', function (data) {
		var ts = new Date().getTime(),
				frame_name = 'rcmupload' + ts,
				frame = rcmail.async_upload_form_frame(frame_name);
		data._uploadid = ts;
		jQuery.ajax({
			url: "?_task=mail&_action=plugin.yetiforce.addFilesToMail&_id=" + rcmail.env.compose_id,
			type: "POST",
			data: data,
			success: function (data) {
				var doc = frame[0].contentWindow.document;
				var body = $('html', doc);
				body.html(data);
			}
		});
	}, true);

	// Add a document to an email crm
	rcmail.register_command('yetiforce.addFilesFromCRM', function (data) {
		if (crm != false) {
			var params = {
				module: 'Documents',
				src_module: 'Documents',
				multi_select: true,
				url: crmPath
			};
			var sourceFieldElement = $(this);
			var prePopupOpenEvent = jQuery.Event(crm.Vtiger_Edit_Js.preReferencePopUpOpenEvent);
			sourceFieldElement.trigger(prePopupOpenEvent);
			var data = {};
			show(params, function (data) {
				var responseData = JSON.parse(data);
				var ids = [];
				for (var id in responseData) {
					ids.push(id);
				}
				rcmail.command('yetiforce.addFilesToMail', {ids: ids, _uploadid: new Date().getTime()});
			});
		}
	}, true);

	// Selection of email with popup
	$('#composeheaders #oss_btn_bar .oss_btn').click(function () {
		var mailField = $(this).attr('data-input');
		var module = $(this).attr('data-module');
		var params = {
			module: module,
			src_module: 'OSSMail',
			multi_select: true,
			url: crmPath
		};
		show(params, function (data) {
			var responseData = JSON.parse(data);
			var length = Object.keys(responseData).length;
			for (var id in responseData) {
				getMailFromCRM(mailField, module, id, length);
			}
		});
	});
	//Loading list of modules with templates mail
	jQuery.ajax({
		type: 'Get',
		url: crmPath + 'module=OSSMailTemplates&action=GetTemplates',
		async: false,
		success: function (data) {
			var modules = [];
			var tmp = [];
			$.each(data.result, function (index, value) {
				jQuery('#vtmodulemenulink').removeClass('disabled');
				jQuery('#tplmenulink').removeClass('disabled');
				tmp.push({name: value.module, label: value.moduleName});
				jQuery('#tplmenu #texttplsmenu').append('<li class="' + value.module + '"><a href="#" data-module="' + value.module + '" data-tplid="' + value.id + '" class="active">' + value.name + '</a></li>');
			});

			$.each(tmp, function (index, value) {
				if (jQuery.inArray(value.name, modules) == -1) {
					jQuery('#vtmodulemenu .toolbarmenu').append('<li class="' + value.name + '"><a href="#" data-module="' + value.name + '" class="active">' + value.label + '</a></li>');
					modules.push(value.name);
				}
			});

		}
	});

	// Limit the list of templates
	jQuery('#vtmodulemenu li a').on('click', function () {
		var selectModule = jQuery(this).data('module');
		if (selectModule == undefined) {
			jQuery('#tplmenu li').show();
		} else {
			jQuery('#tplmenu li.' + selectModule).show();
			jQuery('#tplmenu li').not("." + selectModule).hide();
		}
	});

	if (rcmail.env.crmModule != undefined) {
		jQuery('#vtmodulemenu li.' + rcmail.env.crmModule + ' a').trigger("click");
	}

	// Loading a template mail
	jQuery('#tplmenu  li a').on('click', function () {
		var id = jQuery(this).data('tplid');
		var recordId = rcmail.env.crmRecord,
				module = rcmail.env.crmModule,
				view = rcmail.env.crmView;
		if (view == 'List') {
			var chElement = jQuery(crm.document).find('.listViewEntriesCheckBox')[0];
			recordId = jQuery(chElement).val();
		}
		jQuery.ajax({
			type: 'Get',
			url: crmPath + 'module=OSSMailTemplates&action=GetTpl',
			data: {
				id: id,
				record_id: recordId,
				select_module: module
			},
			success: function (data) {
				var oldSubject = jQuery('[name="_subject"]').val();
				var html = jQuery("<div/>").html(data.result['content']).html();
				jQuery('[name="_subject"]').val(oldSubject + ' ' + data.result['subject']);
				if (window.tinyMCE && (ed = tinyMCE.get(rcmail.env.composebody))) {
					var oldBody = tinyMCE.activeEditor.getContent();
					tinymce.activeEditor.setContent(html + oldBody);
				} else {
					var oldBody = jQuery('#composebody').val();
					jQuery('#composebody').val(html + oldBody);
				}
				if (data.result.hasOwnProperty("attachments")) {
					rcmail.command('yetiforce.addFilesToMail', data.result.attachments);
				}
			}
		});
	});
});

function getCrmWindow() {
	if (opener !== null) {
		return opener.parent;
	} else if (typeof parent.app == "object") {
		return parent;
	}
	return false;
}

function getMailFromCRM(mailField, module, record, length) {
	if (length > 1) {
		length = 1;
	} else {
		length = 0;
	}
	window.crm.Vtiger_Index_Js.getEmailFromRecord(record, module, length).then(function (data) {
		if (data == '') {
			var notifyParams = {
				text: window.crm.app.vtranslate('NoFindEmailInRecord'),
				animation: 'show'
			};
			window.crm.Vtiger_Helper_Js.showPnotify(notifyParams);
		} else {
			var emails = $('#' + mailField).val();
			if (emails != '' && emails.charAt(emails.length - 1) != ',') {
				emails = emails + ',';
			}
			$('#' + mailField).val(emails + data);
		}
	});
}
function show(urlOrParams, cb, windowName, eventName, onLoadCb) {
	var thisInstance = window.crm.Vtiger_Popup_Js.getInstance();
	if (typeof urlOrParams == 'undefined') {
		urlOrParams = {};
	}
	if (typeof urlOrParams == 'object' && (typeof urlOrParams['view'] == "undefined")) {
		urlOrParams['view'] = 'Popup';
	}
	if (typeof eventName == 'undefined') {
		eventName = 'postSelection' + Math.floor(Math.random() * 10000);
	}
	if (typeof windowName == 'undefined') {
		windowName = 'test';
	}
	if (typeof urlOrParams == 'object') {
		urlOrParams['triggerEventName'] = eventName;
	} else {
		urlOrParams += '&triggerEventName=' + eventName;
	}
	var urlString = (typeof urlOrParams == 'string') ? urlOrParams : window.crm.jQuery.param(urlOrParams);
	var url = urlOrParams['url'] + urlString;
	var popupWinRef = window.crm.window.open(url, windowName, 'width=800,height=650,resizable=0,scrollbars=1');
	if (typeof thisInstance.destroy == 'function') {
		thisInstance.destroy();
	}
	window.crm.jQuery.initWindowMsg();
	if (typeof cb != 'undefined') {
		thisInstance.retrieveSelectedRecords(cb, eventName);
	}
	if (typeof onLoadCb == 'function') {
		window.crm.jQuery.windowMsg('Vtiger.OnPopupWindowLoad.Event', function (data) {
			onLoadCb(data);
		})
	}
	return popupWinRef;
}
