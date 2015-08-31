/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

function ApiAddress() {

	this.registerChangeApi = function () {
		jQuery('#change_api').on('change', function () {
			var value = jQuery(this).val(),
					table = jQuery(this).parents('table');

			jQuery(table).find('.api_row').hide();

			if (value) {
				jQuery(table).find('.' + value).removeClass('hide');
				jQuery(table).find('.' + value).show();
			}
		});
	},
			this.registerSave = function () {
				var thisInstance = this;
				jQuery('.save').on('click', function () {
					var elements = {};

					jQuery(this).parents('table:first').find('.api').each(function () {
						var name = jQuery(this).attr('name');

						if (jQuery(this).attr('type') == 'checkbox') {
							elements[name] = jQuery(this).prop('checked') ? 1 : 0;
						} else {
							elements[name] = jQuery(this).val();
						}
					});

					elements['api_name'] = jQuery(this).parents('table').data('api-name');

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
								}
								else {
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
			this.registerRemoveConnection = function () {
				var thisInstance = this;
				jQuery('.delete').on('click', function () {
					var elements = {'key': '0', 'nominatim': '0', api_name: jQuery(this).parents('table').data('api-name')};
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
								}
								else {
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
			this.registerReload = function () {
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
			this.registerValidate = function (elements) {
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
			this.registerValidatemin_lenght = function (val) {
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
			this.registerValidatekey = function (val, apiName) {
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
			this.registerEvents = function () {
				var thisInstance = this;
				thisInstance.registerSave();
				thisInstance.registerRemoveConnection();
				thisInstance.registerChangeApi();
			};
}


jQuery(document).ready(function () {
	var dc = new ApiAddress();
	dc.registerEvents();
})
