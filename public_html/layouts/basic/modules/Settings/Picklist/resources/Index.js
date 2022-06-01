/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Settings_Picklist_Index_Js = class {
	/**
	 * Get progress inducator element
	 * @param {jQuery} block
	 * @returns
	 */
	getProgressIndicator(block) {
		let params = { position: 'html', blockInfo: { enabled: true } };
		if (typeof block !== 'undefined') {
			params.blockInfo.elementToBlock = block;
		}
		return $.progressIndicator(params);
	}
	/**
	 * Change module event
	 * @param {string} selectedModule
	 */
	changeModule(selectedModule) {
		if (!selectedModule) {
			app.showNotify({ type: 'error', text: app.vtranslate('JS_PLEASE_SELECT_MODULE') });
			return;
		}
		let progressIndicatorElement = this.getProgressIndicator();
		AppConnector.request({
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			source_module: selectedModule,
			view: 'IndexAjax',
			mode: 'getPickListDetailsForModule'
		}).done((data) => {
			this.picklistContainer.html(data);
			this.picklistField = this.picklistContainer.find('.js-picklist-field');
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
			App.Fields.Picklist.changeSelectElementView(this.picklistContainer);
			this.picklistField.trigger('change');
		});
	}
	/**
	 * Change picklist event
	 * @param {int} picklistId
	 * @returns
	 */
	changePicklist(picklistId) {
		if (!picklistId) {
			this.picklistDataContainer.html('');
			return false;
		}
		let progressIndicatorElement = this.getProgressIndicator();
		AppConnector.request({
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			source_module: this.moduleField.val(),
			view: 'IndexAjax',
			mode: 'getPickListValueForField',
			picklistName: picklistId
		}).done((data) => {
			this.picklistDataContainer.html(data);
			this.registerSortableEvent();
			App.Fields.Picklist.showSelect2ElementView(this.picklistDataContainer.find('.select2'));
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
		});
	}
	/**
	 * Change the order of picklist values
	 */
	saveOrder() {
		let seq = {};
		this.picklistDataContainer.find('.js-picklist-value').each((i, element) => {
			seq[element.dataset.keyId] = i + 1;
		});
		this.saveAjax({ mode: 'saveOrder', seq: seq, ...this.getDefaultParams() });
	}
	/**
	 * Get default params data
	 * @returns
	 */
	getDefaultParams() {
		return {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			source_module: this.moduleField.val(),
			picklistName: this.picklistField.val()
		};
	}
	/**
	 * Register basic events
	 */
	registerBasicEvents() {
		this.moduleField.on('change', (e) => {
			this.changeModule($(e.currentTarget).val());
		});
		this.picklistContainer.on('change', '.js-picklist-field', (e) => {
			this.changePicklist($(e.currentTarget).val());
		});
		this.picklistDataContainer.on('click', '.js-picklist-edit', (e) => {
			let fieldValueId = e.currentTarget.dataset.id;
			this.showEditView({ fieldValueId, view: 'Edit', ...this.getDefaultParams() });
		});
		this.picklistDataContainer.on('click', '.js-picklist-create', () => {
			this.showEditView({ view: 'Create', ...this.getDefaultParams() });
		});
		this.picklistDataContainer.on('click', '.js-picklist-role', () => {
			this.showAssignRoleView({ view: 'Create', ...this.getDefaultParams() });
		});
		this.picklistDataContainer.on('click', '.js-picklist-order', () => {
			this.saveOrder();
		});
		this.picklistDataContainer.on('click', '.js-picklist-import', () => {
			this.importView();
		});
		this.picklistDataContainer.on('click', '.js-picklist-delete', (e) => {
			this.deleteItem(e.currentTarget.dataset.id);
		});
		this.picklistDataContainer.on('change', '.js-role-list', (e) => {
			this.loadRolePermissions($(e.currentTarget).val());
		});
		this.picklistDataContainer.on('click', '#assignedToRoleTab', () => {
			this.loadRolePermissions(this.picklistDataContainer.find('.js-role-list').val());
		});

		this.regiserChangeRolePermissions();
		this.registerSortableEvent();
		this.registerEnablePickListValueClickEvent();
	}
	/**
	 * Edit item
	 * @param {Object} params
	 */
	showEditView(params) {
		AppConnector.request(params).done((data) => {
			app.showModalWindow(data, (container) => {
				App.Fields.Icon.register(container);
				container.find('.js-modal__save').on('click', (e, skipConfirmation) => {
					let form = container.find('form');
					if (form.validationEngine('validate')) {
						let confirmation = this.picklistField.find('option:selected').data('confirmation');
						if (skipConfirmation !== true && confirmation !== undefined) {
							app.showConfirmModal({
								text: confirmation,
								confirmedCallback: () => {
									$(e.currentTarget).trigger('click', true);
								}
							});
						} else {
							let progress = this.getProgressIndicator();
							this.preSaveValidation(form).done((valid) => {
								progress.progressIndicator({ mode: 'hide' });
								if (valid === true) {
									this.saveAjax(form.serializeFormData());
								}
							});
						}
					}
				});
			});
		});
	}
	/**
	 * Assig role view
	 */
	showAssignRoleView() {
		AppConnector.request({ view: 'AssignRoles', ...this.getDefaultParams() }).done((data) => {
			app.showModalWindow(data, (container) => {
				container.find('.js-modal__save').on('click', () => {
					let form = container.find('form');
					if (form.validationEngine('validate')) {
						this.saveAjax(form.serializeFormData());
					}
				});
			});
		});
	}
	/**
	 * Import view
	 */
	importView() {
		AppConnector.request({ view: 'Import', ...this.getDefaultParams() }).done((modal) => {
			app.showModalWindow(modal, (container) => {
				container.find('.js-modal__save').on('click', () => {
					let form = container.find('form');
					if (form.validationEngine('validate')) {
						let formData = new FormData(form[0]);
						if (formData.get('file').type !== 'text/csv') {
							app.showNotify({ text: app.vtranslate('JS_INVALID_FILE_TYPE'), type: 'error' });
							return false;
						}
						if (formData.get('file').size > CONFIG.maxUploadLimit) {
							app.showNotify({ text: app.vtranslate('JS_UPLOADED_FILE_SIZE_EXCEEDS'), type: 'error' });
							return false;
						}
						let progress = this.getProgressIndicator();
						AppConnector.request({
							url: 'index.php',
							type: 'POST',
							data: formData,
							processData: false,
							contentType: false
						})
							.done((response) => {
								progress.progressIndicator({ mode: 'hide' });
								form.find('.js-summary').removeClass('d-none');
								form.find('.js-all-number').val(response.result.all);
								form.find('.js-imported-number').val(response.result.success);
								form.find('.js-errors-number').val(response.result.errors);
								form.find('.js-errors').val(response.result.errorMessage);
								container.find('.js-modal__save').addClass('d-none');
								this.picklistField.trigger('change');
							})
							.fail((error, title) => {
								progress.progressIndicator({ mode: 'hide' });
								app.showNotify({
									titleTrusted: false,
									textTrusted: false,
									title: title,
									text: error,
									type: 'error'
								});
							});
					}
				});
			});
		});
	}
	/**
	 * Delete item
	 * @param {int} fieldValueId
	 */
	deleteItem(fieldValueId) {
		AppConnector.request({ fieldValueId, view: 'Delete', ...this.getDefaultParams() }).done((modal) => {
			app.showModalWindow(modal, (container) => {
				container.find('.js-modal__save').on('click', (e, skipConfirmation) => {
					let form = container.find('form');
					if (form.validationEngine('validate')) {
						let confirmation = this.picklistField.find('option:selected').data('confirmation');
						if (skipConfirmation !== true && confirmation !== undefined) {
							app.showConfirmModal({
								text: confirmation,
								confirmedCallback: () => {
									$(e.currentTarget).trigger('click', true);
								}
							});
						} else {
							this.saveAjax(form.serializeFormData());
						}
					}
				});
			});
		});
	}
	/**
	 * Save data
	 * @param {Object} formData
	 */
	saveAjax(formData) {
		let progress = this.getProgressIndicator();
		app.saveAjax('', [], formData).done((response) => {
			if (response.result) {
				app.showNotify({ text: app.vtranslate('JS_SAVE_NOTIFY_OK'), type: 'success' });
				this.picklistField.trigger('change');
			} else {
				app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
			}
			app.hideModalWindow();
			progress.progressIndicator({ mode: 'hide' });
		});
	}
	/**
	 * Register sortable event
	 */
	registerSortableEvent() {
		let tbody = $('.js-picklist-table tbody', this.container);
		tbody.sortable({
			helper: function (_e, ui) {
				ui.children().each(function (_index, element) {
					element = $(element);
					element.width(element.width());
				});
				return ui;
			},
			containment: tbody,
			revert: true,
			update: () => {
				$('#saveSequence', this.container).removeAttr('disabled');
			}
		});
	}
	/**
	 * Load picklist values for role
	 * @param {string} roleId
	 */
	loadRolePermissions(roleId) {
		let progressIndicatorElement = this.getProgressIndicator(this.container.find('.tab-content'));
		AppConnector.request({
			view: 'IndexAjax',
			mode: 'getPickListValueByRole',
			rolesSelected: roleId,
			...this.getDefaultParams()
		}).done((data) => {
			this.container.find('#pickListValeByRoleContainer').html(data);
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
		});
	}
	/**
	 * Register change role permission event
	 */

	regiserChangeRolePermissions() {
		this.picklistDataContainer.on('click', '.js-role-order', (e) => {
			let progressIndicatorElement = this.getProgressIndicator(this.container.find('.tab-content'));
			let disabledValues = [],
				enabledValues = [];
			this.picklistDataContainer.find('.js-picklist-value-role').each((_i, element) => {
				if (element.classList.contains('selectedCell')) {
					enabledValues.push(element.dataset.id);
				} else {
					disabledValues.push(element.dataset.id);
				}
			});
			AppConnector.request({
				action: 'SaveAjax',
				mode: 'enableOrDisable',
				rolesSelected: this.picklistDataContainer.find('.js-role-list').val(),
				enabled_values: enabledValues,
				disabled_values: disabledValues,
				...this.getDefaultParams()
			}).done((data) => {
				if (typeof data.result !== 'undefined') {
					e.currentTarget.setAttribute('disabled', 'disabled');
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.showNotify({
						text: app.vtranslate('JS_LIST_UPDATED_SUCCESSFULLY'),
						type: 'success'
					});
				}
			});
		});
	}
	/**
	 * Register click element event for role permissions
	 */
	registerEnablePickListValueClickEvent() {
		this.picklistDataContainer.on('click', '.js-picklist-value-role', (e) => {
			if (e.currentTarget.classList.contains('selectedCell')) {
				e.currentTarget.classList.remove('selectedCell');
				e.currentTarget.children[0].remove();
			} else {
				e.currentTarget.classList.add('selectedCell');
				let icon = document.createElement('i');
				icon.setAttribute('class', 'fas fa-check float-left');
				e.currentTarget.prepend(icon);
			}

			this.picklistDataContainer.find('.js-role-order').removeAttr('disabled');
		});
	}
	/**
	 * PreSave validation
	 */
	preSaveValidation(form) {
		const aDeferred = $.Deferred();
		let formData = new FormData(form.get(0));
		formData.append('mode', 'preSaveValidation');
		if (formData.get('name')) {
			AppConnector.request({
				async: false,
				url: 'index.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false
			})
				.done((data) => {
					let response = data.result;
					for (let i in response) {
						if (response[i].result !== true) {
							app.showNotify({
								text: response[i].message ? response[i].message : app.vtranslate('JS_ERROR'),
								type: 'error'
							});
						}
					}
					aDeferred.resolve(data.result.length <= 0);
				})
				.fail((textStatus, errorThrown) => {
					app.showNotify({ text: app.vtranslate('JS_ERROR'), type: 'error' });
					app.errorLog(textStatus, errorThrown);
					aDeferred.resolve(false);
				});
		} else {
			aDeferred.resolve(true);
		}
		return aDeferred.promise();
	}
	/**
	 * Register events
	 */
	registerEvents() {
		this.container = $('.js-container');
		this.picklistContainer = $('.js-picklist-container');
		this.picklistDataContainer = $('.js-picklist-data-container');
		this.moduleField = this.container.find('#pickListModules');
		this.picklistField = this.picklistContainer.find('.js-picklist-field');

		this.registerBasicEvents();
	}
};

Vtiger_Base_Validator_Js(
	'Vtiger_FieldLabel_Validator_Js',
	{
		/**
		 * Function which invokes field validation
		 * @param accepts field element as parameter
		 * @return error if validation fails true on success
		 */
		invokeValidation: function (field, _rules, _i, _options) {
			var instance = new Vtiger_FieldLabel_Validator_Js();
			instance.setElement(field);
			var response = instance.validate();
			if (response !== true) {
				return instance.getError();
			}
		}
	},
	{
		/**
		 * Function to validate the field label
		 * @return {boolean} if validation is successfull or error occurs
		 */
		validate: function () {
			var fieldValue = this.getFieldValue();
			let specialChars = /[\<\>\"\,\#]/;
			if (specialChars.test(fieldValue)) {
				this.setError(app.vtranslate('JS_SPECIAL_CHARACTERS') + ' < > " , # ' + app.vtranslate('JS_NOT_ALLOWED'));
				return false;
			}
			return true;
		}
	}
);
