'use strict';

$.Class(
	'Settings_RecordCollector_List_Js',
	{},
	{
		/**
		 * register onClick event.
		 */
		registerChangeStatusEvent: function (container) {
			container.find('.js-status-change').on('click', function () {
				let configButton = container.find(`button[data-name=${this.value}].js-show-config-modal`);
				if(configButton) {
					if (this.checked) {
						configButton.removeClass('d-none');
					} else {
						configButton.addClass('d-none');
					}
				}
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
		 * register function for showing config modal.
		 */
		registerConfigModal: function (container) {
			container.find('.js-show-config-modal').on('click', function () {
				const recordCollectorName = this.dataset.name;
				app.showModalWindow(null, 'index.php?module=RecordCollector&parent=Settings&view=ConfigModal&recordCollectorName=' + recordCollectorName,  function (modal) {
					modal.on('click', ".js-modal__save", function() {
						let form = modal.find('form');
						AppConnector.request({
							module: 'RecordCollector',
							parent: 'Settings',
							action: 'SaveConfig',
							collector: recordCollectorName,
							config: form.serializeFormData(),
						}).done((data) => {
							app.showNotify({
								type: 'success',
								text: data.result.message
							});
							app.hideModalWindow();
						}).fail((data) => {
							app.showNotify({
								type: 'error',
								text: data
							});
						});
					})
				});
			});
		},
		/**
		 * register events.
		 */
		registerEvents: function () {
			const container = $('.js-config-table');
			this.registerChangeStatusEvent(container);
			this.registerConfigModal(container);
		}
	}
);
