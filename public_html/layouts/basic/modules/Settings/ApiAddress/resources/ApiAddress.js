/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_ApiAddress_Configuration_Js',
	{},
	{
		registerSave: function(content) {
			content.find('.saveGlobal').on('click', event => {
				const defaultProvider = $('[name="default_provider"]:checked');
				let elements = {
					global: {
						min_length: $('[name="min_length"]').val(),
						result_num: $('[name="result_num"]').val(),
						default_provider: defaultProvider.length ? defaultProvider.val() : 0
					}
				};
				$('[name="active"]').each((i, e) => {
					elements[e.dataset.type] = { active: e.checked ? 1 : 0 };
				});
				AppConnector.request({
					data: {
						module: 'ApiAddress',
						parent: 'Settings',
						action: 'SaveConfig',
						elements: elements
					},
					async: false,
					dataType: 'json'
				})
					.done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data['result']['message'],
							type: 'success'
						});
					})
					.fail(function() {
						Vtiger_Helper_Js.showPnotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerValidate: function(elements) {
			var thisInstance = this;
			var status = true;
			for (var i in elements) {
				if (i == 'min_length' || i == 'result_num') {
					if (!thisInstance.registerValidatemin_length(elements[i])) {
						return false;
					}
				}

				if (i == 'key') {
					if (!thisInstance.registerValidatekey(elements.key, elements.api_name)) {
						return false;
					}
				}
			}
			return status;
		},
		registerValidatemin_length: function(val) {
			var filter = /^\d+$/;

			if (!filter.test(val) || (1 == val || 0 == val)) {
				var par = {
					text: app.vtranslate('JS_WRONG_NUMBER'),
					type: 'error'
				};
				Vtiger_Helper_Js.showPnotify(par);
				return false;
			}
			return true;
		},
		registerValidatekey: function(val, apiName) {
			var status = true;

			if ('opencage_data' == apiName) {
				var test = 'https://api.opencagedata.com/geocode/v1/json?query=test&pretty=1&key=' + val;
				jQuery.ajax({
					url: test,
					async: false,
					complete: function(data) {
						if (data.status == 403) {
							var parametry = {
								text: app.vtranslate('Invalid API key'),
								type: 'error'
							};
							Vtiger_Helper_Js.showPnotify(parametry);
							status = false;
						}
					}
				});
			} else {
				return true;
			}

			return status;
		},
		registerConfigModal(container) {
			container.find('.js-show-config-modal').on('click', e => {
				const providerName = e.currentTarget.dataset.provider;
				app.showModalWindow(
					null,
					`index.php?module=ApiAddress&parent=Settings&view=ApiConfigModal&provider=${providerName}`,
					modalContainer => {
						modalContainer.find('.js-modal__save').on('click', _ => {
							let elements = {};
							let customField = modalContainer.find('.js-custom-field');
							customField.each((i, e) => {
								elements[$(e).attr('name')] = e.value;
							});
							elements = { [providerName]: elements };
							AppConnector.request({
								data: {
									module: 'ApiAddress',
									parent: 'Settings',
									action: 'SaveConfig',
									elements: elements
								},
								async: false,
								dataType: 'json'
							})
								.done(function(data) {
									Vtiger_Helper_Js.showPnotify({
										text: data['result']['message'],
										type: 'success'
									});
									window.location.reload();
								})
								.fail(function() {
									Vtiger_Helper_Js.showPnotify({
										text: app.vtranslate('JS_ERROR'),
										type: 'error'
									});
								});
						});
					}
				);
			});
		},
		registerEvents: function() {
			var content = $('.contentsDiv');
			var configTable = $('.js-config-table');
			this.registerConfigModal(configTable);
			this.registerSave(content);
		}
	}
);
