/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Settings_OSSMailScanner_Index_Js", {}, {
	registerColorField: function (field) {
		let params = {};
		params.tags = true;
		params.templateSelection = function (object) {
			let selectedId = object.id,
				tabValue = selectedId.split("@"),
				state = object.text;
			if (!tabValue[0]) {
				state = $('<span class="domain">' + object.text + '</span>');
			}
			return state;
		}
		App.Fields.Picklist.showSelect2ElementView(field, params);
	},
	registerEditFolders: function (container) {
		const self = this;
		container.find('.editFolders').on('click', function () {
			const url = 'index.php?module=OSSMailScanner&parent=Settings&view=Folders' + '&record=' + $(this).data('user'),
				progressIndicatorElement = jQuery.progressIndicator({
					message: app.vtranslate('LBL_LOADING_LIST_OF_FOLDERS'),
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
			app.showModalWindow("", url, function (data) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				let recurrenceTree = new App.Components.Tree.Basic();
				data.find('[name="saveButton"]').on('click', function (e) {
					const selectedFolders = self.getSelectedFolders(recurrenceTree.treeInstance);
					AppConnector.request({
						module: 'OSSMailScanner',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateFolders',
						user: data.find('.modal-body').data('user'),
						folders: selectedFolders
					}).done(function (data) {
						let response = data['result'],
							emptyFoldersAlert = $('.js-empty-folders-alert'),
							messageType = 'info';
						if (!response['success']) {
							messageType = 'error';
						}
						Vtiger_Helper_Js.showPnotify({
							text: response['message'],
							type: messageType
						});
						if (Object.keys(selectedFolders).length) {
							emptyFoldersAlert.addClass('d-none');
						} else {
							emptyFoldersAlert.removeClass('d-none');
						}
						app.hideModalWindow();
					});
				});
			});
		});
	},
	getSelectedFolders(treeInstance) {
		let folders = {};
		for (let value of treeInstance.jstree('get_selected', true)) {
			if (!Array.isArray(folders[value.original.db_type])) {
				folders[value.original.db_type] = [];
			}
			if (value.original.db_id !== undefined) {
				folders[value.original.db_type].push(value.original.db_id);
			}
		}
		return folders;
	},
	registerEvents: function () {
		const thisIstance = this,
			container = jQuery('.contentsDiv');
		thisIstance.registerColorField($('#exceptions select'));
		thisIstance.registerEditFolders(container);
		$('#exceptions select').on('select2:select', function (e) {
			let value = e.params.data.id;
			if (!!thisIstance.domainValidateToExceptions(value) || !!thisIstance.email_validate(value)) {
				thisIstance.saveWidgetConfig(jQuery(this).attr('name'), jQuery(this).val().join(), 'exceptions');
				thisIstance.registerColorField(jQuery(this));
			} else {
				jQuery(this).find("option[value='" + value + "']").remove();
				jQuery(this).trigger('change');
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_mail_error'),
					type: 'error',
				});
			}
		}).on('select2:unselect', function () {
			thisIstance.saveWidgetConfig(jQuery(this).attr('name'), jQuery(this).val(), 'exceptions');
		});

		$('#status').on('change', function () {
			$('#confirm').attr('disabled', !this.checked);
		});

		jQuery('.conftabChangeTicketStatus').on('click', function () {
			if ($(this).data('active') == '1') {
				return false;
			}
			$('.conftabChangeTicketStatus').data('active', 0);
			$(this).data('active', 1);
			AppConnector.request({
				async: true,
				data: {
					module: 'OSSMailScanner',
					action: "SaveRcConfig",
					ct: "emailsearch",
					type: "changeTicketStatus",
					vale: $(this).val()
				}
			}).done(
				function (data) {
					if (data.success) {
						Vtiger_Helper_Js.showPnotify({
							text: data.result.data,
							type: 'info',
						});
					}
				},
				function (data, err) {
				}
			);
		});

		jQuery('.delate_accont').on('click', function () {
			if (window.confirm(app.vtranslate('whether_remove_an_identity'))) {
				const userId = jQuery(this).data('user-id');
				AppConnector.request({
					data: {module: 'OSSMailScanner', action: "AccontRemove", id: userId},
					async: true
				}).done(
					function (data) {
						Vtiger_Helper_Js.showPnotify({
							text: data.result.data,
							type: 'info',
						});
						jQuery('#row_account_' + userId).hide();
					}
				);
			}
		});
		jQuery('.identities_del').on('click', function () {
			const button = this;
			if (window.confirm(app.vtranslate('whether_remove_an_identity'))) {
				AppConnector.request({
					data: {module: 'OSSMailScanner', action: "IdentitiesDel", id: jQuery(this).data('id')},
					async: true
				}).done(
					function () {
						Vtiger_Helper_Js.showPnotify({
							text: app.vtranslate('removed_identity'),
							type: 'info',
						});
						jQuery(button).parent().parent().remove();
					},
					function (data, err) {

					}
				);
			}
		});

		jQuery('.expand-hide').on('click', function () {
			let userId = jQuery(this).data('user-id'),
				tr = jQuery('tr[data-user-id="' + userId + '"]');

			if ('none' == tr.css('display')) {
				tr.show();
			} else {
				tr.hide();
			}

		});
		$(".alert").alert();
		jQuery("select[id^='function_list_']").on('change', function () {
			thisIstance.saveActions(jQuery(this).data('user-id'), jQuery(this).val());
		});
		jQuery("select[id^='user_list_']").on('change', function () {
			thisIstance.saveCRMuser(jQuery(this).data('user'), jQuery(this).val());
		});
		jQuery("#email_search").on('change', function () {
			thisIstance.saveEmailSearchList(jQuery('#email_search').val());
		});
		jQuery('#tab_email_view_widget_limit').on('blur', function () {
			thisIstance.saveWidgetConfig('widget_limit', jQuery(this).val(), 'email_list');
		});
		jQuery('#tab_email_view_open_window').on('change', function () {
			thisIstance.saveWidgetConfig('target', jQuery(this).val(), 'email_list');
		});
		jQuery('[name="email_to_notify"]').on('blur', function () {
			let value = jQuery(this).val();
			if (!!thisIstance.email_validate(value)) {
				thisIstance.saveWidgetConfig('email', value, 'cron');
			} else {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_mail_error'),
					type: 'error',
				});
			}
		});
		jQuery('[name="time_to_notify"]').on('blur', function () {
			let value = jQuery(this).val();
			if (!!thisIstance.number_validate(value)) {
				thisIstance.saveWidgetConfig('time', jQuery(this).val(), 'cron');
			} else {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_time_error'),
					type: 'error',
				});
			}
		});
	},
	saveActions: function (userid, vale) {
		AppConnector.request({
			'module': 'OSSMailScanner',
			'action': "SaveActions",
			'userid': userid,
			'vale': vale
		}).done(function (data) {
			let response = data['result'];
			if (response['success']) {
				Vtiger_Helper_Js.showPnotify({
					text: response['data'],
					type: 'info',
				});
			} else {
				Vtiger_Helper_Js.showPnotify({
					text: response['data'],
				});
			}
		});
	},
	saveCRMuser: function (userid, value) {
		AppConnector.request({
			'module': 'OSSMailScanner',
			'action': "SaveCRMuser",
			'userid': userid,
			'value': value
		}).done(function (data) {
			let response = data['result'];
			if (response['success']) {
				Vtiger_Helper_Js.showPnotify({
					text: response['data'],
					type: 'info',
				});
			} else {
				Vtiger_Helper_Js.showPnotify({
					text: response['data'],
				});
			}
		});
	},
	isEmpty: function (val) {
		if (!!val) {
			return val;
		}

		return '';
	},
	saveEmailSearchList: function (vale) {
		AppConnector.request({
			'module': 'OSSMailScanner',
			'action': "SaveEmailSearchList",
			'vale': vale
		}).done(function (data) {
			let response = data['result'];
			if (response['success']) {
				Vtiger_Helper_Js.showPnotify({
					text: response['data'],
					type: 'info',
				});
			} else {
				Vtiger_Helper_Js.showPnotify({
					text: response['data'],
				});
			}
		});
	},
	domainValidateToExceptions: function (src) {
		let regex = /^@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,63}$/;
		return regex.test(src);
	},
	email_validate: function (src) {
		let regex = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,63}$/;
		return regex.test(src);
	},
	number_validate: function (value) {
		let valid = !/^\s*$/.test(value) && !isNaN(value);
		return valid;
	},
	saveWidgetConfig: function (name, value, type) {
		AppConnector.request({
			'module': 'OSSMailScanner',
			'action': "SaveWidgetConfig",
			'conf_type': type,
			'name': name,
			'value': value
		}).done(function (data) {
			let response = data['result'];
			if (response['success']) {
				Vtiger_Helper_Js.showPnotify({
					text: response['data'],
					type: 'info',
				});
			} else {
				Vtiger_Helper_Js.showPnotify({
					text: response['data'],
				});
			}
		});
	}
});
