/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
$("#roundcube_interface").load(function () {
	var inframe = $('#roundcube_interface').contents();
	var crm_path = getAbsolutePath();
	load_all_widgets(inframe, crm_path);
	load_ical_attachments(inframe, crm_path);
});
function load_ical_attachments(inframe, crm_path) {
	$(inframe.find('#messagecontent .icalattachments a')).click(function () {
		var params = {};
		params.data = {module: 'Calendar', action: 'ImportICS', ics: $(this).attr('class')}
		params.async = true;
		params.dataType = 'json';
		$(this).closest('.icalattachments').remove();
		AppConnector.request(params).then(
				function (response) {
					var notify_params = {
						text: response['result'],
						type: 'info',
						animation: 'show'
					};
					Vtiger_Helper_Js.showPnotify(notify_params);
				}
		);
	});
}
function load_all_widgets(inframe, crm_path) {
	if (!inframe) {
		var inframe = $('#roundcube_interface').contents();
	}
	if (!crm_path) {
		var crm_path = getAbsolutePath();
	}
	var params = {};
	var gcrm = {};
	if (!inframe.find('.oss-header').text() == '') {
		var progressIndicatorElement = '';
		var fromEmailString = inframe.find('.from .rcmContactAddress').attr("href");
		if (!!fromEmailString) {
			var fromEmail = fromEmailString.split(':');
			params['from_email'] = fromEmail[1];
		}
		params['from_name'] = inframe.find('.from .rcmContactAddress').text();
		params['title'] = inframe.find('.subject').text().replace("Subject: ", "");
		params['body'] = inframe.find('#messagebody').text();
		gcrm['uid'] = params['uid'] = inframe.find('#message-oss-parameters-uid').text();
		gcrm['folder'] = params['folder'] = inframe.find('#message-oss-parameters-folder').text();
		gcrm['username'] = params['username'] = inframe.find('#message-oss-parameters-username').text();
		load_connection(crm_path, gcrm, params, inframe, progressIndicatorElement);
	}
	$(inframe.find('#messagelist')).click(function () {
		$(inframe.find('#messagecontframe')).load(function () {
			var inframe2 = $(inframe.find('#messagecontframe')).contents();
			var fromEmailString = inframe2.find('.from .rcmContactAddress').attr("href");
			if (!!fromEmailString) {
				var fromEmail = fromEmailString.split(':');
				params['from_email'] = fromEmail[1];
			}
			params['from_name'] = inframe2.find('.from .rcmContactAddress').text();
			params['title'] = inframe2.find('.subject').text().replace("Subject: ", "");
			params['body'] = inframe2.find('#messagebody').text();
			gcrm['uid'] = params['uid'] = inframe2.find('#message-oss-parameters-uid').text();
			gcrm['folder'] = params['folder'] = inframe2.find('#message-oss-parameters-folder').text();
			gcrm['username'] = params['username'] = inframe2.find('#message-oss-parameters-username').text();
			load_connection(crm_path, gcrm, params, inframe2, progressIndicatorElement);
		});
	});

}
function load_connection(crm_path, gcrm, params, inframe, progressIndicatorElement) {
	var getConfig = jQuery.ajax({
		type: "GET",
		async: true,
		url: crm_path + 'index.php',
		data: {module: 'OSSMail', action: 'getConfig'}
	});
	jqxhr = get_crm_id(gcrm);
	jqxhr.done(function (data) {
		params['crmid'] = data.result;
		var related_records = find_crm_detail(crm_path, 'all', params);
		getConfig.done(function (cfg) {
			related_records.done(function (fcd) {
				if (data.result !== 'false') {
					var crmid = 0;
					if (data.result.id !== undefined) {
						crmid = data.result.id;
					}
					if (data.result['0_created_Email'] !== undefined) {
						crmid = data.result['0_created_Email']['created_Email'];
					}
					if (crmid === 0) {
						load_oss_bar_no_mail(inframe, params);
					} else {
						params['crmid'] = crmid;
						load_oss_bar(inframe, params['crmid'], cfg.result, fcd.result);
						load_action(inframe, params);
					}
				}
			});
		});
	});
}

function load_action(inframe, params) {
	$(inframe.find('#message-oss-header a.link')).click(function () {
		var url = $(this).attr('href');
		window.location.href = url;
	});
	$(inframe.find('#message-oss-header .oss-add-Vendors')).click(function () {
		loadQuickCreateForm('Vendors', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-Accounts')).click(function () {
		loadQuickCreateForm('Accounts', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-Contacts')).click(function () {
		loadQuickCreateForm('Contacts', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-Leads')).click(function () {
		loadQuickCreateForm('Leads', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-Project')).click(function () {
		loadQuickCreateForm('Project', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-ServiceContracts')).click(function () {
		loadQuickCreateForm('ServiceContracts', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-HelpDesk')).click(function () {
		if ($(this).attr('data-module') && $(this).attr('data-module') != 'HelpDesk') {
			params['sourceModule'] = $(this).attr('data-module');
			params['crmid'] = $(this).attr('data-crmid');
		}
		loadQuickCreateForm('HelpDesk', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-remove-relation')).click(function () {
		var removeParams = {
			mailId: params['crmid'],
			crmid: $(this).attr('data-crmid')
		};
		executeActions('removeRelated', removeParams);
		load_all_widgets();
	});
	$(inframe.find('#message-oss-header .oss-Related')).click(function () {
		var relParams = {mailId: params['crmid']};
		if ($(this).attr('data-crmid')) {
			relParams.crmid = $(this).attr('data-crmid');
		}
		var rmodule = $(this).attr('data-module');
		var sourceFieldElement = jQuery('input[name="temp_field"]');
		var PopupParams = {
			'module': rmodule,
			'src_module': rmodule,
			'src_field': sourceFieldElement.attr('name'),
			'src_record': '',
			'url': getAbsolutePath() + '/index.php?'
		};
		showPopup(PopupParams, sourceFieldElement, relParams, inframe, true);
	});
	$(inframe.find('#message-oss-header .oss-add-modcomments')).click(function () {
		params['rcrmid'] = $(this).attr('data-crmid');
		params['no_rel'] = true;
		loadQuickCreateForm('ModComments', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-events')).click(function () {
		params['sourceModule'] = $(this).attr('data-module');
		params['crmid'] = $(this).attr('data-crmid');
		loadQuickCreateForm('Calendar', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-task')).click(function () {
		params['sourceModule'] = $(this).attr('data-module');
		params['crmid'] = $(this).attr('data-crmid');
		params['mode'] = 'task';
		loadQuickCreateForm('Calendar', params, inframe);
	});
	$(inframe.find('#message-oss-header .oss-add-products')).click(function () {
		var module = 'Products';
		var relParams = {
			mailId: params['crmid'],
			newModule: 'Products',
			mod: $(this).attr('data-module'),
			crmid: $(this).attr('data-crmid'),
		};
		var sourceFieldElement = jQuery('input[name="temp_field"]');
		var PopupParams = {
			'module': module,
			'src_module': module,
			'src_field': sourceFieldElement.attr('name'),
			'src_record': '',
			'url': getAbsolutePath() + '/index.php?'
		};
		showPopup(PopupParams, sourceFieldElement, relParams, inframe, true);
	});
	$(inframe.find('#message-oss-header .oss-add-services')).click(function () {
		var module = 'Services';
		var relParams = {
			mailId: params['crmid'],
			newModule: 'Products',
			mod: $(this).attr('data-module'),
			crmid: $(this).attr('data-crmid'),
		};
		var sourceFieldElement = jQuery('input[name="temp_field"]');
		var PopupParams = {
			'module': module,
			'src_module': module,
			'src_field': sourceFieldElement.attr('name'),
			'src_record': '',
			'url': getAbsolutePath() + '/index.php?'
		};
		showPopup(PopupParams, sourceFieldElement, relParams, inframe, true);
	});
	$(inframe.find('#moreheaderstoggle .oss-reload-bar')).click(function () {
		load_all_widgets();
	});
	$(inframe.find('#moreheaderstoggle .oss-close-bar')).click(function () {
		var header = inframe.find('#message-oss-header');
		var messageheader = inframe.find('#messageheader');
		var height = messageheader.height()
		if (header.data('show') == true) {
			header.show().data('show', false);
			inframe.find('#messagecontent').css('top', (height + 80) + 'px');
			inframe.find('#moreheaderstoggle .oss-close-bar').html('<img src="' + window.images_path + 'upArrowSmall.png">');
		} else {
			header.hide().data('show', true);
			inframe.find('#messagecontent').css('top', (height) + 'px');
			inframe.find('#moreheaderstoggle .oss-close-bar').html('<img src="' + window.images_path + 'downArrowSmall.png">');
		}
	});
	$(inframe.find('#moreheaderstoggle .oss-email-link')).click(function () {
		if (params['crmid']) {
			window.location.href = getAbsolutePath() + 'index.php?module=OSSMailView&view=Detail&record=' + params['crmid'];
		} else {
			alert(app.vtranslate('NoCrmRecord'));
		}
	});
}
function executeActions(action_name, param) {
	var resp = {};
	var Connectorparams = {}
	Connectorparams.data = {module: 'OSSMail', action: 'executeActions', action_name: action_name, params: param}
	Connectorparams.async = false;
	Connectorparams.dataType = 'json';
	AppConnector.request(Connectorparams).then(
			function (data) {
				var response = data['result'];
				if (response['success']) {
					var notify_params = {
						text: response['data'],
						type: 'info',
						animation: 'show'
					};
				} else {
					var notify_params = {
						text: response['data'],
						animation: 'show'
					};
				}
				Vtiger_Helper_Js.showPnotify(notify_params);
			}
	);
}
function load_oss_bar_no_mail(inframe, params) {
	inframe.find('#message-oss-header').html('<div class="no_records">' + app.vtranslate('JS_MAIL_NOT_FOUND_IN_DB') + ' <a class="import_mail">' + app.vtranslate('JS_MANUAL_IMPORT') + '</a><div>');
	inframe.find('#messagecontent').css('top', (inframe.find('.oss-header').outerHeight() + inframe.find('#messageheader').outerHeight() + 1) + 'px');
	$(inframe.find('#message-oss-header .import_mail')).click(function () {
		Vtiger_Helper_Js.showPnotify({text: app.vtranslate('StartedDownloadingEmail'), type: 'info'});
		import_mail(params).then(function (data) {
			load_all_widgets(inframe);
			Vtiger_Helper_Js.showPnotify({text: app.vtranslate('AddFindEmailInRecord'), type: 'success'});
		});
	});
}
function import_mail(params) {
	var aDeferred = jQuery.Deferred();
	var requestParams = {};
	requestParams.data = {module: 'OSSMailScanner', action: 'ImportMail', params: params};

	AppConnector.request(requestParams).then(
			function (data) {
				aDeferred.resolve(data);
			},
			function (error) {
				aDeferred.reject();
			}
	)
	return aDeferred.promise();

}
function load_oss_bar(inframe, crmid, config, related_records) {
	//var crm_path = getBaseUrl(host);
	var crm_path = getAbsolutePath();
	var msheader = inframe.find('#messageheader');
	var params = {};
	var fromEmailString = msheader.find('.from .rcmContactAddress').attr("href");
	if (!!fromEmailString) {
		var fromEmail = fromEmailString.split(':');
		params['from_email'] = fromEmail[1];
	}
	params['from_name'] = msheader.find('.from .rcmContactAddress').text();
	params['title'] = msheader.find('.subject').text();
	params['body'] = inframe.find('#messagebody').text();
	params['uid'] = inframe.find('#message-oss-parameters-uid').text();
	params['folder'] = inframe.find('#message-oss-parameters-folder').text();
	params['username'] = inframe.find('#message-oss-parameters-username').text();
	var module_permissions = get_module_permissions(crm_path);
	var show_Marketing = false;
	var show_Projekty = false;
	var show_HelpDesk = false;
	var show_ServiceContracts = false;
	var Marketing_icon = '';
	var Marketing_text = '';
	var Sprzedaz_text = '';
	var Sprzedaz_icon = '';
	var Projekty_text = '';
	var Projekty_icon = '';
	var HelpDesk_text = '';
	var HelpDesk_icon = '';
	var images_path = crm_path + 'layouts/basic/skins/images/';
	window.images_path = images_path;
	var Projekty_thead_td = '';
	var Projekty_tbody_td = '';
	var tab_html = {};
	var html_1 = '';
	var html_2 = '';
	var html_3 = '';
	var html_4 = '';
	if (related_records) {
		////////  Marketing ///////////
		if (related_records['Accounts']) {
			for (var i = 0; i < related_records['Accounts']['rows'].length; i++) {
				var row = {};
				row = related_records['Accounts']['rows'][i];
				Marketing_text += '<div class="oss-border-top"><a href="' + crm_path + 'index.php?module=' + row['module'] + '&view=Detail&record=' + row['crmid'] + '" title="' + row['label'] + '" class="btn link">' + row['label'] + '</a><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Accounts') + '" class="oss-add-Accounts btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Accounts') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Related To Accounts') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Accounts') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Remove relation') + '" class="oss-remove-relation btn small-icon"><img src="' + images_path + 'no.png" alt="' + app.vtranslate('Remove relation') + '"></a></span></div>';
				Marketing_text += '<div>' + load_icons('Accounts', row['crmid'], module_permissions, images_path) + '</div>';
			}
		}
		if (related_records['Contacts']) {
			for (var i = 0; i < related_records['Contacts']['rows'].length; i++) {
				var row = {};
				row = related_records['Contacts']['rows'][i];
				Marketing_text += '<div class="oss-border-top"><a href="' + crm_path + 'index.php?module=' + row['module'] + '&view=Detail&record=' + row['crmid'] + '" title="' + row['label'] + '" class="btn link">' + row['label'] + '</a><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Contacts') + '" class="oss-add-Contacts btn  small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Contacts') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Related To Contacts') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Contacts') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Remove relation') + '" class="oss-remove-relation btn small-icon"><img src="' + images_path + 'no.png" alt="' + app.vtranslate('Remove relation') + '"></a></span></div>';
				Marketing_text += '<div>' + load_icons('Contacts', row['crmid'], module_permissions, images_path) + '</div>';
			}
		}
		if (related_records['Leads']) {
			for (var i = 0; i < related_records['Leads']['rows'].length; i++) {
				var row = {};
				row = related_records['Leads']['rows'][i];
				Marketing_text += '<div class="oss-border-top"><a href="' + crm_path + 'index.php?module=' + row['module'] + '&view=Detail&record=' + row['crmid'] + '" title="' + row['label'] + '" class="btn link">' + row['label'] + '</a><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Leads') + '" class="oss-add-Leads btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Leads') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Related To Leads') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Leads') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Remove relation') + '" class="oss-remove-relation btn small-icon"><img src="' + images_path + 'no.png" alt="' + app.vtranslate('Remove relation') + '"></a></span></div>';
				Marketing_text += '<div>' + load_icons('Leads', row['crmid'], module_permissions, images_path) + '</div>';
			}
		}
		if (related_records['Vendors']) {
			for (var i = 0; i < related_records['Vendors']['rows'].length; i++) {
				var row = {};
				row = related_records['Vendors']['rows'][i];
				Marketing_text += '<div class="oss-border-top"><a href="' + crm_path + 'index.php?module=' + row['module'] + '&view=Detail&record=' + row['crmid'] + '" title="' + row['label'] + '" class="btn link">' + row['label'] + '</a><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Vendors') + '" class="oss-add-Vendors btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Vendors') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Related To Vendors') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Vendors') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Remove relation') + '" class="oss-remove-relation btn small-icon"><img src="' + images_path + 'no.png" alt="' + app.vtranslate('Remove relation') + '"></a></span></div>';
			}
		}
		if (Marketing_text == '') {
			show_Marketing = true;
		}

		////////  Campaigns ///////////
		if (related_records['Campaigns']) {
			for (var i = 0; i < related_records['Campaigns']['rows'].length; i++) {
				var row = {};
				row = related_records['Campaigns']['rows'][i];
				Sprzedaz_text += '<div class="oss-border-top"><a href="' + crm_path + 'index.php?module=' + row['module'] + '&view=Detail&record=' + row['crmid'] + '" title="' + app.vtranslate('Campaigns') + ': ' + row['label'] + '" class="btn link">' + row['label'] + '</a></div>';
			}
		}
		//////// Project ////////////
		if (!related_records['Project']) {
			show_Projekty = true;
		} else {
			for (var i = 0; i < related_records['Project']['rows'].length; i++) {
				var row = {};
				row = related_records['Project']['rows'][i];
				Projekty_text += '<div class="oss-border-top"><a href="' + crm_path + 'index.php?module=' + row['module'] + '&view=Detail&record=' + row['crmid'] + '" title="' + row['label'] + '" class="btn link">' + row['label'] + '</a><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Project') + '" class="oss-add-Project btn  small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Project') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Related To Project') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Project') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Remove relation') + '" class="oss-remove-relation btn small-icon"><img src="' + images_path + 'no.png" alt="' + app.vtranslate('Remove relation') + '"></a></span></div>';
				Projekty_text += '<div>' + load_icons('Project', row['crmid'], module_permissions, images_path) + '</div>';
			}
		}
		//////// HelpDesk ////////////
		if (!related_records['HelpDesk']) {
			show_HelpDesk = true;
		} else {
			for (var i = 0; i < related_records['HelpDesk']['rows'].length; i++) {
				var row = {};
				row = related_records['HelpDesk']['rows'][i];
				HelpDesk_text += '<div class="oss-border-top"><a href="' + crm_path + 'index.php?module=' + row['module'] + '&view=Detail&record=' + row['crmid'] + '" title="' + row['label'] + '" class="btn link">' + row['label'] + '</a><span class="pull-right"><a href="#" title="' + app.vtranslate('Add HelpDesk') + '" class="oss-add-HelpDesk btn  small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add HelpDesk') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Related To HelpDesk') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To HelpDesk') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Remove relation') + '" class="oss-remove-relation btn small-icon"><img src="' + images_path + 'no.png" alt="' + app.vtranslate('Remove relation') + '"></a></span></div>';
				HelpDesk_text += '<div>' + load_icons('HelpDesk', row['crmid'], module_permissions, images_path) + '</div>';
			}

		}
		//////// Accounts  ServiceContracts ////////////
		if (related_records['ServiceContracts']) {
			for (var i = 0; i < related_records['ServiceContracts']['rows'].length; i++) {
				row = related_records['ServiceContracts']['rows'][i];
				HelpDesk_text += '<div class="oss-border-top"><a href="' + crm_path + 'index.php?module=' + row['module'] + '&view=Detail&record=' + row['crmid'] + '" title="' + app.vtranslate('ServiceContracts') + ': ' + row['label'] + '" class="btn link">' + row['label'] + '</a><span class="pull-right"><a href="#" title="' + app.vtranslate('Add ServiceContracts') + '" class="oss-add-ServiceContracts btn  small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add ServiceContracts') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Related To ServiceContracts') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To ServiceContracts') + '"></a><a data-crmid="' + row['crmid'] + '" data-module="' + row['module'] + '" href="#" title="' + app.vtranslate('Remove relation') + '" class="oss-remove-relation btn small-icon"><img src="' + images_path + 'no.png" alt="' + app.vtranslate('Remove relation') + '"></a></span></div>';
			}
		} else {
			show_ServiceContracts = true;
		}
	} else {
		var show_Marketing = true;
		var show_Projekty = true;
		var show_HelpDesk = true;
		var show_ServiceContracts = true;
	}
	if (show_Marketing) {
		Marketing_text += '<div><span class="vtop inline-block">' + app.vtranslate('Add or related to Leads') + '</span><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Leads') + '" class="oss-add-Leads btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Leads') + '"></a><a data-module="Leads" href="#" title="' + app.vtranslate('Related To Leads') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Leads') + '"></a></span></div>';
		Marketing_text += '<div><span class="vtop inline-block">' + app.vtranslate('Add or related to Contacts') + '</span><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Contacts') + '" class="oss-add-Contacts btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Contacts') + '"></a><a data-module="Contacts" href="#" title="' + app.vtranslate('Related To Contacts') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Contacts') + '"></a></span></div>';
		Marketing_text += '<div><span class="vtop inline-block">' + app.vtranslate('Add or related to Accounts') + '</span><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Accounts') + '" class="oss-add-Accounts btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Accounts') + '"></a><a data-module="Accounts" href="#" title="' + app.vtranslate('Related To Accounts') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Accounts') + '"></a></span></div>';
		Marketing_text += '<div><span class="vtop inline-block">' + app.vtranslate('Add or related to Vendors') + '</span><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Vendors') + '" class="oss-add-Vendors btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Vendors') + '"></a><a data-module="Vendors" href="#" title="' + app.vtranslate('Related To Vendors') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Vendors') + '"></a></span></div>';
	}
	if (show_Projekty) {
		Projekty_text += '<span class="vtop inline-block">' + app.vtranslate('Add or related to Project') + '</span><span class="pull-right"><a href="#" title="' + app.vtranslate('Add Project') + '" class="oss-add-Project btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add Project') + '"></a><a data-module="Project" href="#" title="' + app.vtranslate('Related To Project') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To Project') + '"></a></span>';
	}
	if (show_HelpDesk) {
		HelpDesk_text += '<div><span class="vtop inline-block">' + app.vtranslate('Add or related to HelpDesk') + '</span><span class="pull-right"><a href="#" title="' + app.vtranslate('Add HelpDesk') + '" class="oss-add-HelpDesk btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add HelpDesk') + '"></a><a data-module="HelpDesk" href="#" title="' + app.vtranslate('Related To HelpDesk') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To HelpDesk') + '"></a></span></div>';
	}
	if (show_ServiceContracts) {
		HelpDesk_text += '<div><span class="vtop inline-block">' + app.vtranslate('Add or related to ServiceContracts') + '</span><span class="pull-right"><a href="#" title="' + app.vtranslate('Add ServiceContracts') + '" class="oss-add-ServiceContracts btn small-icon"><img src="' + images_path + 'btnColorAdd.png" alt="' + app.vtranslate('Add ServiceContracts') + '"></a><a data-module="ServiceContracts" href="#" title="' + app.vtranslate('Related To ServiceContracts') + '" class="oss-Related btn small-icon"><img src="' + images_path + 'search.png" alt="' + app.vtranslate('Related To ServiceContracts') + '"></a></span></div>';
	}
	if (module_permissions['Accounts'] || module_permissions['Contacts'] || module_permissions['Leads'] || module_permissions['Vendors']) {
		html_1 += '<td>' + app.vtranslate('Marketing') + '</td>';
		html_2 += '<td class="Marketing">' + Marketing_text + '</td>';
	}
	if (module_permissions['Campaigns']) {
		html_1 += '<td>' + app.vtranslate('MPotentials') + '</td>';
		html_2 += '<td class="Potentials">' + Sprzedaz_text + '</td>';
	}
	if (module_permissions['Project']) {
		html_1 += '<td>' + app.vtranslate('MProject') + '</td>';
		html_2 += '<td class="Project">' + Projekty_text + '</td>';
	}
	if (module_permissions['HelpDesk']) {
		html_1 += '<td>' + app.vtranslate('MHelpDesk') + '</td>';
		html_2 += '<td class="HelpDesk">' + HelpDesk_text + '</td>';
	}

	inframe.find('#message-oss-header').html(
			'<table class=""><thead><tr>' + html_1 + '</tr></thead><tbody><tr class="text-body" >' + html_2 + '</tr></tbody></table>'
			);
	inframe.find('#moreheaderstoggle').html(
			'<a title="' + app.vtranslate('Preview email in CRM') + '" href="#" class="oss-email-link btn small-icon"><img src="' + images_path + 'Emails.png" ></a><a title="' + app.vtranslate('Reload action bar') + '" href="#" class="oss-reload-bar btn small-icon"><img src="' + crm_path + '/layouts/basic/modules/OSSMail/icons/Reload.png" ></a><a title="X" href="#" class="oss-close-bar btn small-icon"><img src="' + images_path + 'upArrowSmall.png"></a>'
			);
	inframe.find('#messagecontent').css('top', (inframe.find('.oss-header').outerHeight() + inframe.find('#messageheader').outerHeight() + 1) + 'px');
}
function load_icons(module, id, module_permissions, images_path) {
	var return_text = '';
	if (module_permissions['Calendar']) {
		return_text += '<a data-crmid="' + id + '" data-module="' + module + '" href="#" title="' + app.vtranslate('Add Events') + '" class="oss-add-events btn"><img src="' + images_path + 'Calendar.png" alt="' + app.vtranslate('Add Events') + '"></a><a  data-crmid="' + id + '" data-module="' + module + '" href="#" title="' + app.vtranslate('Add Task') + '" class="oss-add-task btn"><img src="' + images_path + 'Tasks.png" alt="' + app.vtranslate('Add Task') + '"></a>';
	}
	if (module_permissions['ModComments']) {
		return_text += '<a data-crmid="' + id + '" data-module="' + module + '" href="#" title="' + app.vtranslate('Add ModComments') + '" class="oss-add-modcomments btn"><img src="' + images_path + 'ModComments.png" alt="' + app.vtranslate('Add ModComments') + '"></a>';
	}
	if (module == 'Accounts' || module == 'Contacts' || module == 'Leads') {
		if (module_permissions['Products']) {
			return_text += '<a data-crmid="' + id + '" data-module="' + module + '" href="#" title="' + app.vtranslate('Add Products') + '" class="oss-add-products btn"><img src="' + images_path + 'Products.png" alt="' + app.vtranslate('Add Products') + '"></a>';
		}
	}
	if (module == 'HelpDesk' || module == 'Project') {
		if (module_permissions['HelpDesk']) {
			return_text += '<a data-crmid="' + id + '" data-module="' + module + '" href="#" title="' + app.vtranslate('Add HelpDesk') + '" class="oss-add-HelpDesk btn"><img src="' + images_path + 'HelpDesk.png" alt="' + app.vtranslate('Add HelpDesk') + '"></a>';
		}
	}
	if (module == 'Accounts' || module == 'Contacts' || module == 'Leads' || module == 'HelpDesk') {
		if (module_permissions['Services']) {
			return_text += '<a data-crmid="' + id + '" data-module="' + module + '" href="#" title="' + app.vtranslate('Add Services') + '" class="oss-add-services btn"><img src="' + images_path + 'Services.png" alt="' + app.vtranslate('Add Services') + '"></a>';
		}
	}
	return return_text;
}
function get_module_permissions(crm_path) {
	var resp;
	jQuery.ajax({
		type: "Post",
		async: false,
		dataType: "json",
		url: crm_path + "index.php?module=OSSMail&action=GetPermissions",
		data: {View: 'EditView'},
		success: function (response) {
			resp = response['result'];
		},
		failure: function (msg) {
			resp = false;
		}
	});
	return resp;
}
function getAbsolutePath() {
	return jQuery('#site_URL').val();
}
function oss_get_config() {
	var params = {};
	var resp = {};
	params.data = {module: 'OSSMail', action: 'getConfig'}
	params.async = false;
	params.dataType = 'json';
	AppConnector.request(params).then(
			function (response) {
				resp = response['result'];
			}
	);
	return resp;
}
function find_crm_detail(crm_path, _metod, _param) {
	var resp = jQuery.ajax({
		type: "Post",
		async: true,
		dataType: "json",
		url: crm_path + "index.php?module=OSSMail&action=findCrmDetail",
		data: {metod: _metod, params: _param}
	});
	return resp;
}
function showPopup(params, sourceFieldElement, actionsParams, inframe, reload_widget) {
	var prePopupOpenEvent = jQuery.Event(Vtiger_Edit_Js.preReferencePopUpOpenEvent);
	sourceFieldElement.trigger(prePopupOpenEvent);
	var data = {};
	var popupInstance = Vtiger_Popup_Js.getInstance();
	show(params, function (data) {
		var responseData = JSON.parse(data);
		for (var id in responseData) {
			var data = {
				'name': responseData[id].name,
				'id': id
			}
			sourceFieldElement.val(data.id);
		}
		actionsParams['newModule'] = params['module'];
		actionsParams['newCrmId'] = data.id;
		if (reload_widget) {
			executeActions('addRelated', actionsParams);
			load_all_widgets();
		}
	});
}
function show(urlOrParams, cb, windowName, eventName, onLoadCb) {
	var thisInstance = Vtiger_Popup_Js.getInstance();
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
	var urlString = (typeof urlOrParams == 'string') ? urlOrParams : jQuery.param(urlOrParams);
	var url = urlOrParams['url'] + urlString;
	var popupWinRef = window.open(url, windowName, 'width=800,height=650,resizable=0,scrollbars=1');
	if (typeof thisInstance.destroy == 'function') {
		thisInstance.destroy();
	}
	jQuery.initWindowMsg();
	if (typeof cb != 'undefined') {
		thisInstance.retrieveSelectedRecords(cb, eventName);
	}
	if (typeof onLoadCb == 'function') {
		jQuery.windowMsg('Vtiger.OnPopupWindowLoad.Event', function (data) {
			onLoadCb(data);
		})
	}
	return popupWinRef;
}
function get_crm_id(param) {
	var crm_path = getAbsolutePath();
	var jqxhr = jQuery.ajax({
		type: "GET",
		async: true,
		url: crm_path + 'index.php',
		data: {module: 'OSSMail', action: 'getCrmId', params: param}
	});
	return jqxhr;
}
function loadQuickCreateForm(moduleName, params, inframe) {
	var quickCreateParams = {};
	var relatedParams = {};
	if (params['crmid']) {
		if (params['sourceModule']) {
			var sourceModule = params['sourceModule'];
		} else {
			var sourceModule = 'OSSMailView';
		}
		var preQuickCreateSave = function (data) {
			var index, queryParam, queryParamComponents;
			if (params['mode'] == 'task') {
				data.find('a[data-tab-name="Task"]').trigger('click');
			}
			if (params['no_rel'] != 'true') {
				jQuery('<input type="hidden" name="sourceModule" value="' + sourceModule + '" />').appendTo(data);
				jQuery('<input type="hidden" name="sourceRecord" value="' + params['crmid'] + '" />').appendTo(data);
				jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);
			}
		}
		var links = ['Leads', 'Contacts', 'Vendors', 'Accounts'];
		var process = ['Campaigns', 'HelpDesk', 'Project', 'ServiceContracts'];
		if ($.inArray(params['sourceModule'], links) >= 0) {
			relatedParams['link'] = params['crmid'];
		}
		if ($.inArray(params['sourceModule'], process) >= 0) {
			relatedParams['process'] = params['crmid'];
		}
	}
	var postQuickCreateSave = function (data) {
		load_all_widgets();
	}
	var quickcreateUrl = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
	relatedParams['email'] = params['from_email'];
	relatedParams['projectname'] = params['title'];
	relatedParams['ticket_title'] = params['title'];
	relatedParams['description'] = params['body'];
	relatedParams['related_to'] = params['rcrmid'];
	relatedParams['productname'] = params['title'];
	relatedParams['servicename'] = params['title'];
	quickCreateParams['callbackFunction'] = postQuickCreateSave;
	if (params['crmid']) {
		if (params['no_rel'] != 'true') {
			relatedParams['sourceModule'] = sourceModule;
			relatedParams['sourceRecord'] = params['crmid'];
			relatedParams['relationOperation'] = true;
			quickCreateParams['callbackPostShown'] = preQuickCreateSave;
		}
	}
	quickCreateParams['data'] = relatedParams;
	quickCreateParams['noCache'] = true;
	var progress = jQuery.progressIndicator();
	var headerInstance = new Vtiger_Header_Js();
	headerInstance.getQuickCreateForm(quickcreateUrl, moduleName, quickCreateParams).then(function (data) {
		headerInstance.handleQuickCreateData(data, quickCreateParams);
		progress.progressIndicator({'mode': 'hide'});
	});
}
function oss_add_task(params) {
	Vtiger_Header_Js.getInstance().quickCreateModule('Calendar');
	$("#globalmodal").ready(function () {
		setTimeout(function () {
			jQuery('a[data-tab-name="Task"]').trigger('click');
			jQuery('#Calendar_editView_fieldName_subject').val(params['title']);
			jQuery('<input type="hidden" name="parent_id" value="' + params['crmid'] + '">').appendTo('#quickCreate');
			jQuery('<input type="hidden" name="sourceModule" value="' + params['sourceModule'] + '" />').appendTo('#quickCreate');
			jQuery('<input type="hidden" name="sourceRecord" value="' + params['crmid'] + '" />').appendTo('#quickCreate');
			jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo('#quickCreate');
		}, 1000);
	});
}
