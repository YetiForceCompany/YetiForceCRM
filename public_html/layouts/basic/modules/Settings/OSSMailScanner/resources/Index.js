/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Settings_OSSMailScanner_Index_Js", {}, {
	registerColorField: function (field) {
		var params = {};
		params.tags = true;
		params.templateSelection = function (object) {
			var selectedId = object.id;
			var tabValue = selectedId.split("@");
			var state = object.text;
			if (!tabValue[0]) {
				state = $('<span class="domain">' + object.text + '</span>');
			}
			return state;
		}
		app.showSelect2ElementView(field, params);
	},
	registerEditFolders: function (container) {
		container.find('.editFolders').on('click', function () {
			var url = 'index.php?module=OSSMailScanner&parent=Settings&view=Folders' + '&record=' + $(this).data('user');
			var progressIndicatorElement = jQuery.progressIndicator({
				message: app.vtranslate('LBL_LOADING_LIST_OF_FOLDERS'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			app.showModalWindow("", url, function (data) {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				app.showScrollBar(data.find('.modal-body'), {
					height: app.getScreenHeight(70) + 'px'
				});
				data.find('[name="saveButton"]').click(function (e) {
					var folder = {};
					data.find('select').each(function () {
						var select = $(this);
						var val = select.val();
						if (val == null) {
							val = [];
						}
						folder[select.attr('name')] = val;
					})
					var params = {
						module: 'OSSMailScanner',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateFolders',
						user: data.find('.modal-body').data('user'),
						folders: folder
					}
					AppConnector.request(params).then(function (data) {
						var response = data['result'];
						if (response['success']) {
							var params = {
								text: response['message'],
								type: 'info',
								animation: 'show'
							};
							Vtiger_Helper_Js.showPnotify(params);
						} else {
							var params = {
								text: response['message'],
								animation: 'show'
							};
							Vtiger_Helper_Js.showPnotify(params);
						}
						app.hideModalWindow();
					});
				});
			});
		});
	},
	registerEvents: function () {
		var thisIstance = this;
		var container = jQuery('.contentsDiv');
		thisIstance.registerColorField($('#exceptions select'));
		thisIstance.registerEditFolders(container);
		$('#exceptions select').on('select2:select', function (e) {
			var value = e.params.data.id;
			if (!!thisIstance.domainValidateToExceptions(value) || !!thisIstance.email_validate(value)) {
				thisIstance.saveWidgetConfig(jQuery(this).attr('name'), jQuery(this).val().join(), 'exceptions');
				thisIstance.registerColorField(jQuery(this));
			} else {
				jQuery(this).find("option[value='" + value + "']").remove();
				jQuery(this).trigger('change');
				var params = {
					text: app.vtranslate('JS_mail_error'),
					type: 'error',
					animation: 'show'
				};
				Vtiger_Helper_Js.showPnotify(params);
			}
		}).on('select2:unselect', function () {
			thisIstance.saveWidgetConfig(jQuery(this).attr('name'), jQuery(this).val(), 'exceptions');
		});

		$('#status').change(function () {
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
			}).then(
					function (data) {
						if (data.success) {
							var params = {
								text: data.result.data,
								type: 'info',
								animation: 'show'
							}
							Vtiger_Helper_Js.showPnotify(params);
						}
					},
					function (data, err) {
					}
			);
		});

		jQuery('.delate_accont').on('click', function () {
			var button = this;
			if (window.confirm(app.vtranslate('whether_remove_an_identity'))) {
				var ajaxParams = {};
				var userid = jQuery(this).data('user-id');
				ajaxParams.data = {module: 'OSSMailScanner', action: "AccontRemove", id: userid};
				ajaxParams.async = true;
				AppConnector.request(ajaxParams).then(
						function (data) {
							var params = {
								text: data.result.data,
								type: 'info',
								animation: 'show'
							};
							Vtiger_Helper_Js.showPnotify(params);
							jQuery('#row_account_' + userid).hide();
						},
						function (data, err) {

						}
				);
			}
		});
		jQuery('.identities_del').on('click', function () {
			var button = this;
			if (window.confirm(app.vtranslate('whether_remove_an_identity'))) {
				var ajaxParams = {};
				ajaxParams.data = {module: 'OSSMailScanner', action: "IdentitiesDel", id: jQuery(this).data('id')};
				ajaxParams.async = true;

				AppConnector.request(ajaxParams).then(
						function (data) {
							var params = {
								text: app.vtranslate('removed_identity'),
								type: 'info',
								animation: 'show'
							};

							Vtiger_Helper_Js.showPnotify(params);
							jQuery(button).parent().parent().remove();
						},
						function (data, err) {

						}
				);
			}
		});

		jQuery('.expand-hide').on('click', function () {
			var userId = jQuery(this).data('user-id');
			var tr = jQuery('tr[data-user-id="' + userId + '"]');

			if ('none' == tr.css('display')) {
				tr.show();
			} else {
				tr.hide();
			}

		});
		$(".alert").alert();
		jQuery("select[id^='function_list_']").change(function () {
			thisIstance.saveActions(jQuery(this).data('user-id'), jQuery(this).val());
		});
		jQuery("select[id^='user_list_']").change(function () {
			thisIstance.saveCRMuser(jQuery(this).data('user'), jQuery(this).val());
		});
		jQuery("#email_search").change(function () {
			thisIstance.saveEmailSearchList(jQuery('#email_search').val());
		});
		jQuery('#tab_email_view_widget_limit').on('blur', function () {
			thisIstance.saveWidgetConfig('widget_limit', jQuery(this).val(), 'email_list');
		});
		jQuery('#tab_email_view_open_window').on('change', function () {
			thisIstance.saveWidgetConfig('target', jQuery(this).val(), 'email_list');
		});
		jQuery('[name="email_to_notify"]').on('blur', function () {
			var value = jQuery(this).val();
			if (!!thisIstance.email_validate(value)) {
				thisIstance.saveWidgetConfig('email', value, 'cron');
			} else {
				var params = {
					text: app.vtranslate('JS_mail_error'),
					type: 'error',
					animation: 'show'
				};

				Vtiger_Helper_Js.showPnotify(params);
			}
		});
		jQuery('[name="time_to_notify"]').on('blur', function () {
			var value = jQuery(this).val();
			if (!!thisIstance.number_validate(value)) {
				thisIstance.saveWidgetConfig('time', jQuery(this).val(), 'cron');
			} else {
				var params = {
					text: app.vtranslate('JS_time_error'),
					type: 'error',
					animation: 'show'
				};

				Vtiger_Helper_Js.showPnotify(params);
			}
		});
	},
	saveActions: function (userid, vale) {
		var params = {
			'module': 'OSSMailScanner',
			'action': "SaveActions",
			'userid': userid,
			'vale': vale
		}
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					if (response['success']) {
						var params = {
							text: response['data'],
							type: 'info',
							animation: 'show'
						};
						Vtiger_Helper_Js.showPnotify(params);
					} else {
						var params = {
							text: response['data'],
							animation: 'show'
						};
						Vtiger_Helper_Js.showPnotify(params);
					}
				},
				function (data, err) {

				}
		);
	},
	saveCRMuser: function (userid, value) {
		var params = {
			'module': 'OSSMailScanner',
			'action': "SaveCRMuser",
			'userid': userid,
			'value': value
		}
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					if (response['success']) {
						var params = {
							text: response['data'],
							type: 'info',
							animation: 'show'
						};
						Vtiger_Helper_Js.showPnotify(params);
					} else {
						var params = {
							text: response['data'],
							animation: 'show'
						};
						Vtiger_Helper_Js.showPnotify(params);
					}
				},
				function (data, err) {

				}
		);
	},
	isEmpty: function (val) {
		if (!!val) {
			return val;
		}

		return '';
	},
	saveEmailSearchList: function (vale) {
		var params = {
			'module': 'OSSMailScanner',
			'action': "saveEmailSearchList",
			'vale': vale
		}
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					if (response['success']) {
						var params = {
							text: response['data'],
							type: 'info',
							animation: 'show'
						};
						Vtiger_Helper_Js.showPnotify(params);
					} else {
						var params = {
							text: response['data'],
							animation: 'show'
						};
						Vtiger_Helper_Js.showPnotify(params);
					}
				},
				function (data, err) {

				}
		);
	},
	domainValidateToExceptions: function (src) {
		var regex = /^@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,63}$/;
		return regex.test(src);
	},
	email_validate: function (src) {
		var regex = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,63}$/;
		return regex.test(src);
	},
	number_validate: function (value) {
		var valid = !/^\s*$/.test(value) && !isNaN(value);
		return valid;
	},
	saveWidgetConfig: function (name, value, type) {
		var params = {
			'module': 'OSSMailScanner',
			'action': "SaveWidgetConfig",
			'conf_type': type,
			'name': name,
			'value': value
		}
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					if (response['success']) {
						var params = {
							text: response['data'],
							type: 'info',
							animation: 'show'
						};
						Vtiger_Helper_Js.showPnotify(params);
					} else {
						var params = {
							text: response['data'],
							animation: 'show'
						};
						Vtiger_Helper_Js.showPnotify(params);
					}
				},
				function (data, err) {

				}
		);
	}
});
