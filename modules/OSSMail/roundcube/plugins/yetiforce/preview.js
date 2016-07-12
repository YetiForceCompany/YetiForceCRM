/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
window.rcmail && rcmail.addEventListener('init', function (evt) {
	window.crm = getCrmWindow();
	loadActionBar();
	rcmail.env.message_commands.push('yetiforce.importICS');
	rcmail.register_command('yetiforce.importICS', function (ics, element, e) {
		window.crm.AppConnector.request({
			async: true,
			dataType: 'json',
			data: {
				module: 'Calendar',
				action: 'ImportICS',
				ics: ics
			}
		}).then(function (response) {
			window.crm.Vtiger_Helper_Js.showPnotify({
				text: response['result'],
				type: 'info',
				animation: 'show'
			});
			$(element).closest('.icalattachments').remove();
		})
	}, true);
}
);
function loadActionBar() {
	var content = $('#ytActionBarContent');
	var params = {
		module: 'OSSMail',
		view: 'MailActionBar',
		uid: rcmail.env.uid,
		folder: rcmail.env.mailbox,
		rcId: rcmail.env.user_id
	};
	window.crm.AppConnector.request(params).then(function (response) {
		content.find('.ytHeader').html(response);
		$('#messagecontent').css('top', (content.outerHeight() + $('#messageheader').outerHeight()) + 'px');
		registerEvents(content);
	});
}
function registerEvents(content) {
	registerAddRecord(content);
	registerAddReletedRecord(content);
	registerSelectRecord(content);
	registerRemoveRecord(content);
	registerImportMail(content);

	var block = content.find('.ytHeader > .data');
	content.find('.hideBtn').click(function () {
		var button = $(this);
		var icon = button.find('.glyphicon');

		if (button.data('type') == '0') {
			button.data('type', '1');
			icon.removeClass("glyphicon-chevron-up").addClass("glyphicon-chevron-down");
		} else {
			button.data('type', '0');
			icon.removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
		}
		block.toggle();
		$(window).trigger("resize");
	});
}
function registerImportMail(content) {
	content.find('.importMail').click(function (e) {
		window.crm.Vtiger_Helper_Js.showPnotify({
			text: window.crm.app.vtranslate('StartedDownloadingEmail'),
			type: 'info'
		});
		var params = {
			module: 'OSSMailScanner',
			action: 'ImportMail',
			params: {
				uid: rcmail.env.uid,
				folder: rcmail.env.mailbox,
				rcId: rcmail.env.user_id
			}
		};
		window.crm.AppConnector.request(params).then(function (data) {
			loadActionBar();
			window.crm.Vtiger_Helper_Js.showPnotify({
				text: window.crm.app.vtranslate('AddFindEmailInRecord'),
				type: 'success'
			});
		})
	});
}
function registerRemoveRecord(content) {
	content.find('button.removeRecord').click(function (e) {
		var row = $(e.currentTarget).closest('.rowReletedRecord');
		removeRecord(row.data('id'));
	});
}
function registerSelectRecord(content) {
	var id = content.find('#mailActionBarID').val();
	content.find('button.selectRecord').click(function (e) {
		var sourceFieldElement = jQuery('input[name="tempField"]');
		var relParams = {
			mailId: id
		};
		if ($(this).data('type') == 0) {
			var module = $(this).closest('.col').find('.module').val();
		} else {
			var module = $(this).data('module');
			relParams.crmid = $(this).closest('.rowReletedRecord').data('id');
			relParams.mod = $(this).closest('.rowReletedRecord').data('module');
			relParams.newModule = module;
		}
		var PopupParams = {
			module: module,
			src_module: module,
			src_field: 'tempField',
			src_record: '',
			url: rcmail.env.site_URL + 'index.php?'
		};
		showPopup(PopupParams, sourceFieldElement, relParams);
	});
}
function registerAddReletedRecord(content) {
	var id = content.find('#mailActionBarID').val();
	content.find('button.addReletedRecord').click(function (e) {
		var targetElement = $(e.currentTarget);
		var row = targetElement.closest('.rowReletedRecord');
		var params = {sourceModule: row.data('module')};
		showQuickCreateForm(targetElement.data('module'), row.data('id'), params);
	});
}
function registerAddRecord(content) {
	var id = content.find('#mailActionBarID').val();
	content.find('button.addRecord').click(function (e) {
		var col = $(e.currentTarget).closest('.col');
		showQuickCreateForm(col.find('.module').val(), id);
	});
}
function removeRecord(crmid) {
	var id = $('#mailActionBarID').val();
	var params = {}
	params.data = {
		module: 'OSSMail',
		action: 'executeActions',
		mode: 'removeRelated',
		params: {
			mailId: id,
			crmid: crmid
		}
	}
	params.async = false;
	params.dataType = 'json';
	window.crm.AppConnector.request(params).then(function (data) {
		var response = data['result'];
		if (response['success']) {
			var notifyParams = {
				text: response['data'],
				type: 'info',
				animation: 'show'
			};
		} else {
			var notifyParams = {
				text: response['data'],
				animation: 'show'
			};
		}
		window.crm.Vtiger_Helper_Js.showPnotify(notifyParams);
		loadActionBar();
	});
}
function showPopup(params, sourceFieldElement, actionsParams) {
	actionsParams['newModule'] = params['module'];
	var prePopupOpenEvent = jQuery.Event(window.crm.Vtiger_Edit_Js.preReferencePopUpOpenEvent);
	sourceFieldElement.trigger(prePopupOpenEvent);
	var data = {};
	show(params, function (data) {
		var responseData = JSON.parse(data);
		for (var id in responseData) {
			var data = {
				name: responseData[id].name,
				id: id
			}
			sourceFieldElement.val(data.id);
		}
		actionsParams['newCrmId'] = data.id;
		var params = {}
		params.data = {
			module: 'OSSMail',
			action: 'executeActions',
			mode: 'addRelated',
			params: actionsParams
		}
		params.async = false;
		params.dataType = 'json';
		window.crm.AppConnector.request(params).then(function (data) {
			var response = data['result'];
			if (response['success']) {
				var notifyParams = {
					text: response['data'],
					type: 'info',
					animation: 'show'
				};
			} else {
				var notifyParams = {
					text: response['data'],
					animation: 'show'
				};
			}
			window.crm.Vtiger_Helper_Js.showPnotify(notifyParams);
			loadActionBar();
		});
	});
}
function showQuickCreateForm(moduleName, record, params) {
	var content = $('#ytActionBarContent');
	if (params == undefined) {
		var params = {};
	}
	var relatedParams = {};
	if (params['sourceModule']) {
		var sourceModule = params['sourceModule'];
	} else {
		var sourceModule = 'OSSMailView';
	}
	var postShown = function (data) {
		var index, queryParam, queryParamComponents;
		$('<input type="hidden" name="sourceModule" value="' + sourceModule + '" />').appendTo(data);
		$('<input type="hidden" name="sourceRecord" value="' + record + '" />').appendTo(data);
		$('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);
	}
	var links = JSON.parse(content.find('#modulesLevel0').val());
	var process = JSON.parse(content.find('#modulesLevel1').val());
	var subprocess = JSON.parse(content.find('#modulesLevel2').val());

	if ($.inArray(sourceModule, links) >= 0) {
		relatedParams['link'] = record;
	}
	if ($.inArray(sourceModule, process) >= 0) {
		relatedParams['process'] = record;
	}
	if ($.inArray(sourceModule, subprocess) >= 0) {
		relatedParams['subprocess'] = record;
	}
	if (moduleName == 'Leads') {
		relatedParams['company'] = rcmail.env.fromName;
	}
	if (moduleName == 'Leads' || moduleName == 'Contacts') {
		relatedParams['lastname'] = rcmail.env.fromName;
	}
	if (moduleName == 'Project') {
		relatedParams['projectname'] = rcmail.env.subject;
	}
	if (moduleName == 'HelpDesk') {
		relatedParams['ticket_title'] = rcmail.env.subject;
	}
	if (moduleName == 'Products') {
		relatedParams['productname'] = rcmail.env.subject;
	}
	if (moduleName == 'Services') {
		relatedParams['servicename'] = rcmail.env.subject;
	}
	relatedParams['email'] = rcmail.env.fromMail;
	relatedParams['email1'] = rcmail.env.fromMail;
	relatedParams['description'] = $('#messagebody').text();
	//relatedParams['related_to'] = record;
	var postQuickCreate = function (data) {
		loadActionBar();
	}

	relatedParams['sourceModule'] = sourceModule;
	relatedParams['sourceRecord'] = record;
	relatedParams['relationOperation'] = true;
	var quickCreateParams = {
		callbackFunction: postQuickCreate,
		callbackPostShown: postShown,
		data: relatedParams,
		noCache: true
	};
	var headerInstance = new window.crm.Vtiger_Header_Js();
	headerInstance.quickCreateModule(moduleName, quickCreateParams);
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
function getCrmWindow() {
	if (opener !== null) {
		return opener.parent;
	} else if (typeof parent.app == "object") {
		return parent;
	} else if (typeof parent.parent.app == "object") {
		return parent.parent;
	}
	return false;
}