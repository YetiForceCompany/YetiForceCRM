/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_PDF_Edit_Js("Settings_PDF_Edit4_Js", {}, {
	step4Container: false,
	advanceFilterInstance: false,
	init: function () {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer: function () {
		return this.step4Container;
	},
	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer: function (element) {
		this.step4Container = element;
		return this;
	},
	/**
	 * Function  to intialize the reports step1
	 */
	initialize: function (container) {
		if (typeof container === 'undefined') {
			container = jQuery('#pdf_step4');
		}
		if (container.is('#pdf_step4')) {
			this.setContainer(container);
		} else {
			this.setContainer(jQuery('#pdf_step4'));
		}
	},
	submit: function () {
		for (instance in CKEDITOR.instances) {
			CKEDITOR.instances[instance].updateElement();
		}
		var aDeferred = jQuery.Deferred();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		var saveData = form.serializeFormData();
		saveData['action'] = 'Save';
		saveData['step'] = 4;
		AppConnector.request(saveData).then(
				function (data) {
					data = JSON.parse(data);
					if (data.success == true) {
						Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')});

						AppConnector.request(formData).then(
								function (data) {
									form.hide();
									progressIndicatorElement.progressIndicator({
										'mode': 'hide'
									})
									aDeferred.resolve(data);
								},
								function (error, err) {
									app.errorLog(error, err);
								}
						);
					}
				},
				function (error, err) {
					app.errorLog(error, err);
				}
		);
		return aDeferred.promise();
	},
	registerCancelStepClickEvent: function (form) {
		jQuery('button.cancelLink', form).on('click', function () {
			window.history.back();
		});
	},
	registerMainFieldsChangeEvent: function (form) {
		var thisInstance = this;
		form.find('[name="main_fields"]').on('change', function () {
			thisInstance.updateMainFieldsValue(form);
			thisInstance.updateMainFieldsLabel(form);
		});
	},
	registerRelatedModuleChangeEvent: function (form) {
		form.find('[name="related_module"]').on('change', function () {
			var relatedFields = form.find('[name="related_fields"]');
			var params = {};
			params.data = {
				parent: app.getParentModuleName(),
				module: app.getModuleName(),
				action: 'GetMainFields',
				for_module: jQuery(this).val()
			};
			params.dataType = 'json';
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						if (data['success']) {
							relatedFields.prop('disabled', true);
							relatedFields.find('optgroup').remove();
							relatedFields.find('option').remove();
							for (var prop in response) {
								relatedFields.append('<optgroup label="' + prop + '">');
								for (var field in response[prop]) {
									relatedFields.append('<option value="' + response[prop][field]['name'] + '">' + response[prop][field]['label'] + '</option>');
								}
								relatedFields.append('</optgroup>');
							}
							relatedFields.prop('disabled', false).trigger('liszt:updated');
							relatedFields.select2('val', relatedFields.find('option').first().val());
						}
					},
					function (data, err) {
						app.errorLog(data, err);
					}
			);
		});
	},
	registerRelatedFieldsChangeEvent: function (form) {
		var thisInstance = this;
		form.find('[name="related_fields"]').on('change', function () {
			thisInstance.updateRelatedFieldsValue(form);
			thisInstance.updateRelatedFieldsLabel(form);
		});
	},
	registerCompanyFieldsChangeEvent: function (form) {
		var thisInstance = this;
		form.find('[name="company_fields"]').on('change', function () {
			thisInstance.updateCompanyFieldsValue(form);
			thisInstance.updateCompanyFieldsLabel(form);
		});
	},
	registerSpecialFunctionsChangeEvent: function (form) {
		var thisInstance = this;
		form.find('#special_functions').on('change', function () {
			thisInstance.updateSpecialFunctionsFieldsValue(form);
		});
	},
	updateMainFieldsValue: function (container) {
		var value = '$' + container.find('[name="main_fields"]').val() + '$';
		container.find('#mainFieldValue4').val(value);
	},
	updateMainFieldsLabel: function (container) {
		var value = '%' + container.find('[name="main_fields"]').val() + '%';
		container.find('#mainFieldLabel4').val(value);
	},
	updateRelatedFieldsValue: function (container) {
		var value = '$' + container.find('[name="related_module"]').val() + '+' + container.find('[name="related_fields"]').val() + '$';
		container.find('#relatedFieldValue4').val(value);
	},
	updateRelatedFieldsLabel: function (container) {
		var value = '%' + container.find('[name="related_module"]').val() + '+' + container.find('[name="related_fields"]').val() + '%';
		container.find('#relatedFieldLabel4').val(value);
	},
	updateCompanyFieldsValue: function (container) {
		var value = '$Company+' + container.find('[name="company_fields"]').val() + '$';
		container.find('#companyFieldValue4').val(value);
	},
	updateCompanyFieldsLabel: function (container) {
		var value = '%Company+' + container.find('[name="company_fields"]').val() + '%';
		container.find('#companyFieldLabel4').val(value);
	},
	updateSpecialFunctionsFieldsValue: function (container) {
		var value = '#' + container.find('[name="special_functions"]').val() + '#';
		container.find('#specialFieldValue4').val(value);
	},
	registerCopyClipboard: function (element) {
		var clip = new ZeroClipboard(
				element, {
					moviePath: "libraries/jquery/ZeroClipboard/ZeroClipboard.swf"
				});

		clip.on('complete', function (client, args) {
			// notification about copy to clipboard
			var params = {
				text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
				animation: 'show',
				title: app.vtranslate('JS_NOTIFY_COPY_TITLE'),
				type: 'success'
			};
			Vtiger_Helper_Js.showPnotify(params);
		});
	},
	/**
	 * Registers updated version of CkEditor on textarea fields
	 * spellcheck disabled
	 */
	registerNewCkEditor: function () {
		CKEDITOR.replace('body_content', {
			disableNativeSpellChecker: true,
			scayt_autoStartup: false,
			removePlugins: 'scayt'}
		);
	},
	registerEvents: function () {
		var container = this.getContainer();

		var opts = app.validationEngineOptions;
		// to prevent the page reload after the validation has completed
		opts['onValidationComplete'] = function (form, valid) {
			//returns the valid status
			return valid;
		};
		opts['promptPosition'] = "bottomRight";
		container.validationEngine(opts);
		app.showSelect2ElementView(container.find('.chzn-select'));
		this.registerCancelStepClickEvent(container);
		this.registerRelatedModuleChangeEvent(container);
		this.registerMainFieldsChangeEvent(container);
		this.registerRelatedFieldsChangeEvent(container);
		this.registerCompanyFieldsChangeEvent(container);
		this.registerSpecialFunctionsChangeEvent(container);
		this.updateMainFieldsValue(container);
		this.updateMainFieldsLabel(container);
		this.updateRelatedFieldsValue(container);
		this.updateRelatedFieldsLabel(container);
		this.updateCompanyFieldsValue(container);
		this.updateCompanyFieldsLabel(container);
		this.updateSpecialFunctionsFieldsValue(container);
		this.registerNewCkEditor();

		// zerocliboards
		this.registerCopyClipboard(container.find('#mainFieldsValueCopy4'));
		this.registerCopyClipboard(container.find('#mainFieldsLabelCopy4'));
		this.registerCopyClipboard(container.find('#relatedFieldsValueCopy4'));
		this.registerCopyClipboard(container.find('#relatedFieldsLabelCopy4'));
		this.registerCopyClipboard(container.find('#companyFieldsValueCopy4'));
		this.registerCopyClipboard(container.find('#companyFieldsLabelCopy4'));
		this.registerCopyClipboard(container.find('#specialFieldValueCopy4'));
	}
});
