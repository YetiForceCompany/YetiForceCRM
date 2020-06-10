/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Proxy_ConfigEditor_Js',
	{},
	{
		/*
		 * Function to save the Configuration Editor content
		 */
		saveConfigProxy: function (form) {
			let aDeferred = jQuery.Deferred();
			let params = form.serializeFormData();
			params.module = app.getModuleName();
			params.parent = app.getParentModuleName();
			params.action = 'ConfigProxySaveAjax';
			AppConnector.request(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {
					aDeferred.reject(error, err);
				});
			return aDeferred.promise();
		},

		/*
		 * function to register the events in editView
		 */
		registerEditViewEvents: function () {
			let thisInstance = this;
			let form = $('#ConfigProxyForm');
			App.Fields.Picklist.showSelect2ElementView(form.find('select.select2'), {
				dropdownCss: { 'z-index': 0 }
			});
			let fieldsChange = form.find(
				"*[name='proxyConnection'], *[name='proxyProtocol'], *[name='proxyHost'], *[name='proxyPort'], *[name='proxyLogin'], *[name='proxyPassword']"
			  );
			form.validationEngine(app.validationEngineOptions);
			fieldsChange.each(function() {
				let element = $(this);
				element.on('change', function (e) {
					if (form.validationEngine('validate')) {
						let progressIndicatorElement = jQuery.progressIndicator({
							position: 'html',
							blockInfo: {
								enabled: true
							}
						});
						thisInstance.saveConfigProxy(form).done(function (data) {
							let params = {};
							if (data['success']) {
								params['text'] = app.vtranslate('JS_CONFIGURATION_DETAILS_SAVED');
								Settings_Vtiger_Index_Js.showMessage(params);
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
							}
						}).fail(function (error, err) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
					}
				});
			});
		},

		registerEvents: function () {
				this.registerEditViewEvents();
		}
	}
);

jQuery(document).ready(function (e) {
	var tacInstance = new Settings_Proxy_ConfigEditor_Js();
	tacInstance.registerEvents();
});
