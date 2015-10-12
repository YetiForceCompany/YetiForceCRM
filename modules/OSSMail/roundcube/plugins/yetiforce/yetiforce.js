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
			var popupInstance = crm.Vtiger_Popup_Js.getInstance();
			crm.show(params, function (data) {
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
		var popupInstance = crm.Vtiger_Popup_Js.getInstance();
		crm.show(params, function (data) {
			var responseData = JSON.parse(data);
			var length = Object.keys(responseData).length;
			for (var id in responseData) {
				getMail(mailField, module, id, length);
			}
		});
	});

	//Loading list of modules with templates mail
	if ($(crm.document).find('#activeMailTemplates').val() == 1) {
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
					jQuery('[name="_subject"]').val(oldSubject + data.result['subject']);
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
	}
});

function getCrmWindow() {
	if (opener !== null) {
		return opener.parent;
	} else if (typeof parent.app == "object") {
		return parent;
	}
	return false;
}

function getMail(mailField, module, record, length) {
	var params = {
		module: 'OSSMail',
		action: 'getContactMail',
		mod: module,
		ids: record
	};
	window.crm.AppConnector.request(params).then(
			function (response) {
				var resp = response['result'];
				var loadFirstMail = false;
				var exits_emails = $('#' + mailField).val();
				if (exits_emails != '' && exits_emails.charAt(exits_emails.length - 1) != ',') {
					exits_emails = exits_emails + ',';
				}

				if (resp.length == 0) {
					var notify_params = {
						text: window.crm.app.vtranslate('NoFindEmailInRecord'),
						animation: 'show'
					};
					window.crm.Vtiger_Helper_Js.showPnotify(notify_params);
				}
				if (resp.length > 1 && length == 1) {
					var params = {
						module: 'OSSMail',
						view: 'selectEmail',
						resp: resp
					};
					window.crm.AppConnector.request(params).then(
							function (response) {
								var data = {}
								data.cb = function (mondal) {
									mondal.find('button.btn-success').click(function (e) {
										var mail = resp[0].name + ' <' + mondal.find('[name=selectedFields]:checked').val() + '>';
										$('#' + mailField).val(exits_emails + mail);
										window.crm.app.hideModalWindow();
									});
								};
								data.data = response;
								window.crm.app.showModalWindow(data);
							}
					);
				} else if (resp.length > 1 && length > 1) {
					loadFirstMail = true;
				}
				if (resp.length == 1 || loadFirstMail) {
					var mail = resp[0].name + ' <' + resp[0].email + '>';
					$('#' + mailField).val(exits_emails + mail);
				}
			}
	);
}
