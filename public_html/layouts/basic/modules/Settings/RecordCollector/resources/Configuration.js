'use strict';

$.Class(
	'Settings_RecordCollector_Configuration_Js',
	{},
	{
		changeStatus() {
			AppConnector.request({
				module: 'RecordCollector',
				parent: 'Settings',
				action: 'ActiveAjax',
				mode: 'changeStatus',
				collector: this.value,
				status: this.checked
			}).done((data) => {
				app.showNotify({
					type: 'success',
					text: data.result.message
				});
			});
		},
		registerOnClickEventOnCheckbox: function () {
			const checkboxes = $('.js-status-change');
			for (const element of checkboxes) {
				element.addEventListener('click', this.changeStatus);
			}
		},
		registerEvents: function () {
			this.registerOnClickEventOnCheckbox();
		}
	}
);
