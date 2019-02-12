/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Settings_ApiAddress_Configuration_Js", {}, {
	registerChangeApi: function (content) {
		content.find('#change_api').on('change', function () {
			var value = $(this).val();
			content.find('.api_row').addClass('d-none');
			if (value) {
				content.find('.' + value).removeClass('d-none');
			}
		});
	},
	registerSave: function (content) {
		const thisInstance = this;
		content.find('.saveGlobal').on('click', function () {
			AppConnector.request({
				data: {
					module: 'ApiAddress',
					parent: 'Settings',
					action: 'SaveConfig',
					elements: {
						min_length: $('[name="min_length"]').val(),
						result_num: $('[name="result_num"]').val(),
						api_name: 'global',
					}
				},
				async: false,
				dataType: 'json'
			}).done(
				function (data) {
					Vtiger_Helper_Js.showPnotify({
						text: data['result']['message'],
						type: 'success'
					});
				}).fail(function () {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_ERROR'),
					type: 'error'
				});
			});
		});
		content.find('.save').on('click', function () {
			var elements = {};
			jQuery(this).closest('.apiContainer').find('.api').each(function () {
				var name = jQuery(this).attr('name');

				if (jQuery(this).attr('type') == 'checkbox') {
					elements[name] = jQuery(this).prop('checked') ? 1 : 0;
				} else {
					elements[name] = jQuery(this).val();
				}
			});
			elements['api_name'] = jQuery(this).closest('.apiContainer').find('.apiAdrress').data('api-name');
			// validate fields
			if (!thisInstance.registerValidate(elements)) {
				return false;
			}

			elements = jQuery.extend({}, elements);
			let params = {}
			params.data = {module: 'ApiAddress', parent: 'Settings', action: 'SaveConfig', 'elements': elements}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).done(function (data) {
				let response = data['result'];
				if (response['success']) {
					if (elements['key']) {
						thisInstance.registerReload();
					}
					Vtiger_Helper_Js.showPnotify({
						text: response['message'],
						type: 'success'
					});
				} else {
					Vtiger_Helper_Js.showPnotify({
						text: response['message'],
						type: 'error'
					});
				}
			}).fail(function () {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_ERROR'),
					type: 'error'
				});
			});
		});
	},
	registerRemoveConnection: function (content) {
		const thisInstance = this;
		content.find('.delete').on('click', function () {
			AppConnector.request({
				data: {
					module: 'ApiAddress',
					parent: 'Settings',
					action: 'SaveConfig',
					elements: {
						key: '0',
						nominatim: '0',
						api_name: jQuery(this).closest('.apiContainer').find('.apiAdrress').data('api-name')
					}
				},
				async: false,
				dataType: 'json'
			}).done(function (data) {
				let response = data['result'];
				if (response['success']) {
					thisInstance.registerReload();
					Vtiger_Helper_Js.showPnotify({
						text: response['message'],
						type: 'success'
					});
				} else {
					Vtiger_Helper_Js.showPnotify({
						text: response['message'],
						type: 'error'
					});
				}
			}).fail(function () {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_ERROR'),
					type: 'error'
				});
			});
		});
	},
	registerReload: function () {
		var thisInstance = this;
		var progress = jQuery.progressIndicator({
			'message': app.vtranslate('JS_LOADING_PLEASE_WAIT'),
			'position': '.contentsDiv',
			'blockInfo': {
				'enabled': true
			}
		});

		jQuery.get("index.php?module=ApiAddress&parent=Settings&view=Configuration", function (data) {
			jQuery('.contentsDiv').html(data);
			App.Fields.Picklist.showSelect2ElementView(jQuery('.contentsDiv').find('select.select2'));
			progress.progressIndicator({'mode': 'hide'});
			thisInstance.registerEvents();
		});
	},
	registerValidate: function (elements) {
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
	registerValidatemin_length: function (val) {
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
	registerValidatekey: function (val, apiName) {
		var status = true;

		if ('opencage_data' == apiName) {
			var test = "https://api.opencagedata.com/geocode/v1/json?query=test&pretty=1&key=" + val;
			jQuery.ajax({
				url: test,
				async: false,
				complete: function (data) {
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
	registerEvents: function () {
		var thisInstance = this;
		var content = $('.contentsDiv');

		thisInstance.registerChangeApi(content);
		thisInstance.registerSave(content);
		thisInstance.registerRemoveConnection(content);
	}
});
