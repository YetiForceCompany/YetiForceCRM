/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_TimeControlProcesses_Index_Js',
	{},
	{
		registerChangeVal: function (content) {
			content.find('input[type="checkbox"]').on('change', function (e) {
				let target = $(e.currentTarget),
					value = target.is(':checked'),
					params = {
						module: app.getModuleName(),
						parent: app.getParentModuleName(),
						action: 'SaveAjax',
						type: target.closest('.editViewContainer').data('type'),
						param: target.attr('name'),
						value: value
					};
				AppConnector.request(params).done(function (data) {
					Settings_Vtiger_Index_Js.showMessage({ type: 'success', text: data.result.message });
					if (value) {
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
				});
			});
		},
		registerEvents: function () {
			this.registerChangeVal($('.processesContainer'));
		}
	}
);
