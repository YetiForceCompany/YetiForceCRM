'use strict';

$.Class(
	'Settings_RecordCollector_Configuration_Js',
	{},
	{
		/**
		 * register onClick event.
		 */
		registerOnClickEventOnCheckbox: function () {
			$('.js-status-change').on('click', function () {
				AppConnector.request({
					module: 'RecordCollector',
					parent: 'Settings',
					action: 'SaveAjax',
					mode: 'changeStatus',
					collector: this.value,
					status: this.checked
				}).done((data) => {
					app.showNotify({
						type: 'success',
						text: data.result.message
					});
				});
			});
		},
		/**
		 * register events.
		 */
		registerEvents: function () {
			this.registerOnClickEventOnCheckbox();
		}
	}
);
