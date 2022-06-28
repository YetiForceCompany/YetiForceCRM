'use strict';

$.Class(
	'Settings_RecordCollector_List_Js',
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
		registerConfigModal: function () {
			$('.js-show-config-modal').on('click', function () {
				const recordCollectorName = this.dataset.name;
				app.showModalWindow(null, 'index.php?module=RecordCollector&parent=Settings&view=ConfigModal&recordCollectorName=' + recordCollectorName,  function (modal) {
					modal.on('click', ".js-modal__save", function() {
						//todo
					})
				});
			});
		},
		/**
		 * register events.
		 */
		registerEvents: function () {
			this.registerOnClickEventOnCheckbox();
			this.registerConfigModal();
		}
	}
);
