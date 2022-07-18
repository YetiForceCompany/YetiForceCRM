/**
 * SlaPolicy Edit Js class
 *
 * @package     Edit
 *
 * @description SlaPolicy Edit View scripts
 * @license     YetiForce Public License 5.0
 * @author      Rafal Pospiech <r.pospiech@yetiforce.com>
 */
'use strict';
Settings_Vtiger_List_Js(
	'Settings_Wapro_List_Js',
	{},
	{
		/**
		 * Register button to create record
		 */
		registerButtons: function () {
			const container = this.getListViewContainer();
			container.on('click', '.js-add-record-modal, .js-edit-record-modal', (e) => {
				app.showModalWindow({
					url: e.currentTarget.dataset.url,
					sendByAjaxCb: (_, responseData) => {
						this.getListViewRecords();
						if (responseData['result']) {
							this.showListModal(responseData['result']);
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
				this.showListModal(e.currentTarget.dataset.id);
			});
		},
		/**
		 * Show list modal
		 * @param {int} id
		 */
		showListModal: function (id) {
			app.showModalWindow(
				null,
				'index.php?parent=Settings&module=Wapro&view=ListSynchronizerModal&id=' + id,
				(modalContainer) => {
					modalContainer.find('.js-modal__save').on('click', () => {
						let synchronizer = [];
						modalContainer.find('.js-synchronizer:checked').each(function () {
							synchronizer.push($(this).val());
						});
						AppConnector.request({
							module: app.getModuleName(),
							parent: app.getParentModuleName(),
							action: 'SaveAjax',
							mode: 'updateSynchronizer',
							id: id,
							synchronizer: synchronizer
						}).done(() => {
							app.hideModalWindow();
						});
					});
				}
			);
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
