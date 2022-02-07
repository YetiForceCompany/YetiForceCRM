/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
$.Class(
	'Base_ChangesJsonModal_JS',
	{},
	{
		/**
		 * Register select field
		 */
		registerSelectField: function () {
			let editInstance = Vtiger_Edit_Js.getInstance(this.module);
			let form = this.container.find('form');
			this.container.find('.js-changesjson-select').on('change', (e) => {
				let element = $(e.currentTarget);
				let blockElement = element.closest('.js-form-row-container').find('.fieldValue');
				let fieldElement = blockElement.find('[data-validation-engine],[data-invalid-validation-engine]');
				let fieldInfo = fieldElement.data('fieldinfo');
				if (element.prop('checked')) {
					this.activeFieldValidation(fieldElement);
				} else {
					this.inactiveFieldValidation(fieldElement);
				}
				if (fieldInfo !== undefined && fieldInfo.type === 'reference') {
					let mapFields = editInstance.getMappingRelatedField(
						fieldInfo.name,
						editInstance.getReferencedModuleName(blockElement),
						form
					);
					$.each(mapFields, function (key, _) {
						let checkboxElement = form.find('[id="selectRow' + key + '"]');
						if (checkboxElement.length && checkboxElement.prop('disabled')) {
							checkboxElement.prop('disabled', false);
							checkboxElement.trigger('click');
							checkboxElement.prop('disabled', true);
						}
					});
				}
			});
		},
		/**
		 * Deactivate field validation
		 * @param {jQuery}
		 */
		inactiveFieldValidation: function (field) {
			field.validationEngine('hide');
			let form = field.closest('form');
			let invalidFields = form.data('jqv').InvalidFields;
			let fields = [field.get(0)];
			field.attr('data-invalid-validation-engine', field.attr('data-validation-engine'));
			field.removeAttr('data-validation-engine');

			if (field.is('select') && field.hasClass('select2')) {
				let selectElement = app.getSelect2ElementFromSelect(field);
				selectElement.validationEngine('hide');
				fields.push(selectElement.get(0));
			}
			for (let i in fields) {
				let response = jQuery.inArray(fields[i], invalidFields);
				if (response != '-1') {
					invalidFields.splice(response, 1);
				}
			}
		},
		/**
		 * Activate field validation
		 * @param {jQuery}
		 */
		activeFieldValidation: function (field) {
			let validationVal = field.attr('data-invalid-validation-engine');
			if (typeof validationVal === 'undefined') return;
			field.attr('data-validation-engine', validationVal);
			field.removeAttr('data-invalid-validation-engine');
		},
		/**
		 * Function to inactive field for validation in a form
		 * this will remove data-validation-engine attr of all the elements
		 */
		inactiveFieldsValidation: function () {
			let editFieldList = this.container.find('.js-edit-field-list').data('value');
			let form = this.container.find('form');
			for (let fieldName in editFieldList) {
				let fieldInfo = editFieldList[fieldName];

				let fieldElement = form.find('[name="' + fieldInfo.name + '"]');
				if (fieldInfo.type == 'reference') {
					fieldElement = form.find('[name="' + fieldInfo.name + '_display"]');
				} else if (fieldInfo.type == 'multipicklist' || fieldInfo.type == 'sharedOwner') {
					fieldElement = form.find('[name="' + fieldInfo.name + '[]"]');
				}
				if (
					fieldElement.length == 0 ||
					fieldElement.closest('.js-form-row-container').find('.js-changesjson-select').prop('checked')
				) {
					continue;
				}

				let elemData = fieldElement.data();
				let validationVal = 'validate[]';
				if ('validationEngine' in elemData) {
					validationVal = elemData.validationEngine;
					delete elemData.validationEngine;
				}
				fieldElement.attr('data-invalid-validation-engine', validationVal);
				fieldElement.removeAttr('data-validation-engine');
			}
		},
		/**
		 * Register change tab
		 */
		registerEventForTabClick: function () {
			let form = this.container.find('form');
			this.container.on('click', 'a[data-toggle="tab"]', function (e) {
				form.validationEngine('validate');
				let invalidFields = form.data('jqv').InvalidFields;
				if (invalidFields.length > 0) {
					e.stopPropagation();
				}
			});
		},
		/**
		 * Register events
		 * @param {jQuery} container
		 */
		registerEvents(container) {
			this.container = container;
			this.module = container.find('[name="module"]').val();
			this.container.find('form').validationEngine(app.validationEngineOptions);
			this.inactiveFieldsValidation();
			this.registerEventForTabClick();
			this.registerSelectField();
			Vtiger_Edit_Js.getInstance(this.module).registerBasicEvents(container);
		}
	}
);
