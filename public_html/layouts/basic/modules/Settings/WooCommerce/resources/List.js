/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_WooCommerce_List_Js',
	{
		/**
		 * Restart synchronization
		 *
		 * @param {int}  id
		 */
		reload(id) {
			AppConnector.request({
				module: 'WooCommerce',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'reload',
				record: id
			}).done((data) => {
				app.showNotify({
					type: 'success',
					text: data.result.message
				});
			});
		}
	},
	{
		/**
		 * Register button to create record
		 */
		registerButtons: function () {
			const container = this.getListViewContainer();
			container.on('click', '.js-add-record-modal, .js-edit-record-modal', (e) => {
				app.showModalWindow({
					url: e.currentTarget.dataset.url,
					cb: function (modalContainer) {
						Vtiger_Edit_Js.getInstance().registerBasicEvents(modalContainer);
					},
					sendByAjaxCb: (_, responseData) => {
						this.getListViewRecords();
						if (responseData['result']) {
							this.showConfigModal(responseData['result']['url']);
						}
					}
				});
			});
		},
		/**
		 * Register list modal
		 */
		registerListModal: function () {
			this.getListViewContainer().on('click', '.js-list-sync', (e) => {
				this.showConfigModal(e.currentTarget.dataset.url);
			});
		},
		/**
		 * Show list modal
		 * @param {string} url
		 */
		showConfigModal: function (url) {
			app.showModalWindow(null, url);
		},
		/**
		 * Function to register events
		 */
		registerEvents: function () {
			this._super();
			this.registerListModal();
		}
	}
);
