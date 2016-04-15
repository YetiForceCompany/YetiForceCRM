/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Settings_ApiAddress_Configuration_Js", {}, {
	registerChangeApi: function (content) {
		content.find('#change_api').on('change', function () {
			var value = $(this).val();
			content.find('.api_row').addClass('hide');
			if (value) {
				content.find('.' + value).removeClass('hide');
			}
		});
	},
	registerSave: function (content) {
		var thisInstance = this;
		content.find('.saveGlobal').on('click', function () {
			var elements = {
				min_lenght: $('[name="min_lenght"]').val(),
				result_num: $('[name="result_num"]').val(),
				api_name: 'global',
			};
			var params = {}
			params.data = {module: 'ApiAddress', parent: 'Settings', action: 'SaveConfig', 'elements': elements}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					var parametres = {
						text: response['message'],
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(parametres);
				},
				function (data, err) {
					var parametres = {
						text: app.vtranslate('JS_ERROR'),
						type: 'error'
					};
					Vtiger_Helper_Js.showPnotify(parametres);
				}
			);
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
			var params = {}
			params.data = {module: 'ApiAddress', parent: 'Settings', action: 'SaveConfig', 'elements': elements}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						if (response['success']) {
							if (elements['key']) {
								thisInstance.registerReload();
							}
							var parametry = {
								text: response['message'],
								type: 'success'
							};
							Vtiger_Helper_Js.showPnotify(parametry);
						} else {
							var parametry = {
								text: response['message'],
								type: 'error'
							};
							Vtiger_Helper_Js.showPnotify(parametry);
						}
					},
					function (data, err) {
						var parametry = {
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						};
						Vtiger_Helper_Js.showPnotify(parametry);
					}
			);
		});
	},
	registerRemoveConnection: function (content) {
		var thisInstance = this;
		content.find('.delete').on('click', function () {
			var elements = {'key': '0', 'nominatim': '0', api_name: jQuery(this).closest('.apiContainer').find('.apiAdrress').data('api-name')};
			var params = {}
			params.data = {module: 'ApiAddress', parent: 'Settings', action: 'SaveConfig', 'elements': elements}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						if (response['success']) {
							thisInstance.registerReload();
							var parametry = {
								text: response['message'],
								type: 'success'
							};
							Vtiger_Helper_Js.showPnotify(parametry);
						} else {
							var parametry = {
								text: response['message'],
								type: 'error'
							};
							Vtiger_Helper_Js.showPnotify(parametry);
						}
					},
					function (data, err) {
						var parametry = {
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						};
						Vtiger_Helper_Js.showPnotify(parametry);
					}
			);
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
			app.showSelect2ElementView(jQuery('.contentsDiv').find('select.select2'));
			progress.progressIndicator({'mode': 'hide'});
			thisInstance.registerEvents();
		});
	},
	registerValidate: function (elements) {
		var thisInstance = this;
		var status = true;
		for (var i in elements) {
			if (i == 'min_lenght' || i == 'result_num') {
				if (!thisInstance.registerValidatemin_lenght(elements[i])) {
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
	registerValidatemin_lenght: function (val) {
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
