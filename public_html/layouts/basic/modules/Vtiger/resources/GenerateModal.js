/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Vtiger_GenerateModal_Js',
	{},
	{
		registerGenetateButton: function (container) {
			const thisInstance = this;
			container.find('button.js-genetate-button').on('click', function (e) {
				document.progressLoader = $.progressIndicator({
					message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				let currentTarget = container.find('.js-generate-mapping option:selected'),
					actionUrl = currentTarget.data('url'),
					method = container.find('[name="method"]:checked');
				if (method.length <= 0) {
					window.location.href = actionUrl;
				} else {
					AppConnector.request({
						data: {
							module: app.getModuleName(),
							action: 'GenerateRecords',
							records: container.find('[name="all_records"]').val(),
							template: currentTarget.data('id'),
							target: currentTarget.data('name'),
							method: method.val()
						},
						dataType: 'json'
					})
						.done(function (data) {
							let response = data['result'];
							if (data['success']) {
								let records = response.ok;
								thisInstance.summary(container, response);
								document.progressLoader.progressIndicator({ mode: 'hide' });
								if ('1' === method.val()) {
									for (let i in records) {
										window.open(actionUrl + records[i], '_blank');
									}
								}
							}
						})
						.fail(function (data, err) {
							app.errorLog(data, err);
						});
				}
			});
		},
		summary: function (container, data) {
			container.find('.modal-title').text(app.vtranslate('JS_SUMMARY'));
			container
				.find('.modal-body')
				.html(
					'<div>' +
						app.vtranslate('JS_SELECTED_RECORDS') +
						': <strong>' +
						data.all +
						'</strong></div><div>' +
						app.vtranslate('JS_SUCCESSFULLY_PERFORMED_ACTION_FOR') +
						': <strong>' +
						data.ok.length +
						'</strong></div><div>' +
						app.vtranslate('JS_ACTION_FAILED_FOR') +
						': <strong>' +
						data.fail.length +
						'</strong></div>'
				);
		},
		registerEvents: function () {
			var container = jQuery('.generateMappingModal');
			this.registerGenetateButton(container);
		}
	}
);

jQuery(document).ready(function (e) {
	var instance = new Vtiger_GenerateModal_Js();
	instance.registerEvents();
});
