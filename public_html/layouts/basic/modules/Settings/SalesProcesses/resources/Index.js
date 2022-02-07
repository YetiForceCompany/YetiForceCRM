/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_SalesProcesses_Index_Js',
	{},
	{
		registerChangeVal: function (content) {
			content.on('change', '.configField', function (e) {
				let target = $(e.currentTarget),
					params = {
						module: app.getModuleName(),
						parent: app.getParentModuleName(),
						action: 'SaveAjax',
						mode: 'updateConfig',
						type: target.data('type'),
						param: target.attr('name')
					};
				if (target.attr('type') === 'checkbox') {
					params.val = this.checked;
				} else {
					params.val = target.val() != null ? target.val() : '';
				}
				AppConnector.request(params).done(function (data) {
					Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
					if (target.attr('type') === 'checkbox') {
						if (params.val) {
							target
								.parent()
								.removeClass('btn-light')
								.addClass('btn-success')
								.find('.fas')
								.removeClass('fa-square')
								.addClass('fa-check-square');
						} else {
							target
								.parent()
								.removeClass('btn-success')
								.addClass('btn-light')
								.find('.fas')
								.removeClass('fa-check-square')
								.addClass('fa-square');
						}
					}
				});
			});
		},
		registerEvents: function () {
			this.registerChangeVal($('#salesProcessesContainer'));
		}
	}
);
