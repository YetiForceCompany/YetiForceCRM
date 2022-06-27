'use strict';

$.Class(
	'Settings_RecordCollector_Configuration_Js',
	{},
	{	/**
	 	* change status activity of RecordCollectors.
	 	*/
		changeStatus() {
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
		},
		/**
		 * register onClick event.
		 */
		registerOnClickEventOnCheckbox: function () {
			const checkboxes = $('.js-status-change');
			for (const element of checkboxes) {
				element.addEventListener('click', this.changeStatus);
			}
		},
		/**
		 * register events.
		 */
		registerEvents: function () {
			this.registerOnClickEventOnCheckbox();
		}
	}
);
