/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.Users_Groups_JS = class {
	/**
	 * Modal container
	 */
	container = false;
	/**
	 * DataTable api object
	 */
	dataTable;

	/**
	 * Register DataTable
	 */
	registerDataTable() {
		let table = this.container.find('.js-data-table');
		let form = this.container.find('form');
		return app.registerDataTables(table, {
			order: [],
			processing: true,
			serverSide: false,
			ajax: {
				url: table.data('url'),
				type: 'POST',
				data: function (data) {
					$.extend(data, form.serializeFormData());
				}
			}
		});
	}
	/**
	 * Register remove member
	 */
	registerRemoveMember() {
		this.container.find('.js-data-table').on('click', '.js-member-delete', (e) => {
			let url = e.currentTarget.dataset.url;
			app.showConfirmModal({
				text: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE'),
				confirmedCallback: () => {
					let progress = jQuery.progressIndicator();
					AppConnector.request(url)
						.done(() => {
							progress.progressIndicator({ mode: 'hide' });
							this.dataTable.ajax.reload();
						})
						.fail((_) => {
							app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
							progress.progressIndicator({ mode: 'hide' });
						});
				}
			});
		});
	}
	/**
	 * Register add members
	 */
	registerAddMember() {
		this.container.find('.js-data-table').on('click', '.js-member-add', (e) => {
			let url = e.currentTarget.dataset.url + '&groupID=' + this.container.find('form').serializeFormData()['groupID'];
			app.showModalWindow(
				null,
				url,
				(modalContainer) => {
					modalContainer.find('.js-modal__save').on('click', () => {
						let modalForm = modalContainer.find('form');
						if (modalForm.validationEngine('validate')) {
							let progress = $.progressIndicator({
								message: app.vtranslate('JS_SAVE_LOADER_INFO'),
								blockInfo: { enabled: true }
							});
							let formData = modalForm.serializeFormData();
							app
								.saveAjax('', [], formData)
								.done((response) => {
									let result = response.result;
									progress.progressIndicator({ mode: 'hide' });
									if (result.success) {
										app.hideModalWindow(null, 'memberList');
										this.dataTable.ajax.reload();
									} else {
										app.showNotify({
											text: result.message ? result.message : app.vtranslate('JS_ERROR'),
											type: 'error',
											delay: 3000,
											hide: true
										});
									}
								})
								.fail(() => {
									progress.progressIndicator({ mode: 'hide' });
									app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
								});
						}
					});
				},
				{ modalId: 'memberList' }
			);
		});
	}
	/**
	 * Register base events
	 * @param {jQuery} modalContainer
	 */
	registerEvents(modalContainer) {
		this.container = modalContainer;
		this.dataTable = this.registerDataTable();
		this.registerRemoveMember();
		this.registerAddMember();
		this.container.on('change', '[name="groupID"]', () => {
			this.dataTable.ajax.reload();
		});
	}
};
