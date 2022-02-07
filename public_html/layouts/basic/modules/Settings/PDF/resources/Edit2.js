/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_PDF_Edit_Js(
	'Settings_PDF_Edit2_Js',
	{},
	{
		step2Container: false,
		advanceFilterInstance: false,
		init: function () {
			this.initialize();
		},
		/**
		 * Function to get the container which holds all the reports step1 elements
		 * @return {jQuery} object
		 */
		getContainer: function () {
			return this.step2Container;
		},
		/**
		 * Function to set the reports step1 container
		 * @params : element - which represents the reports step1 container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.step2Container = element;
			return this;
		},
		/**
		 * Function  to intialize the reports step1
		 */
		initialize(container) {
			if (typeof container === 'undefined') {
				container = $('#pdf_step2');
			}
			if (container.is('#pdf_step2')) {
				this.setContainer(container);
			} else {
				this.setContainer($('#pdf_step2'));
			}
		},
		submit() {
			var aDeferred = $.Deferred();
			var form = this.getContainer();
			var formData = form.serializeFormData();
			var progressIndicatorElement = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var saveData = form.serializeFormData();
			saveData['action'] = 'Save';
			saveData['step'] = 2;
			AppConnector.request(saveData)
				.done(function (data) {
					data = JSON.parse(data);
					if (data.success === true) {
						AppConnector.request(formData)
							.done(function (data) {
								form.hide();
								aDeferred.resolve(data);
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
								Settings_Vtiger_Index_Js.showMessage({
									text: app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')
								});
							})
							.fail(function (error, err) {
								app.errorLog(error, err);
							});
					}
				})
				.fail(function (error, err) {
					app.errorLog(error, err);
				});
			return aDeferred.promise();
		},
		registerCancelStepClickEvent: function (form) {
			$('button.cancelLink', form).on('click', function () {
				window.history.back();
			});
		},
		/**
		 * Register events
		 */
		registerEvents() {
			const container = this.getContainer();
			var opts = app.validationEngineOptions;
			// to prevent the page reload after the validation has completed
			opts['onValidationComplete'] = function (form, valid) {
				//returns the valid status
				return valid;
			};
			opts['promptPosition'] = 'topLeft';
			container.validationEngine(opts);
			App.Fields.Picklist.showSelect2ElementView(container.find('.select2'));
			this.registerCancelStepClickEvent(container);
			App.Fields.Text.registerCopyClipboard(container);
		}
	}
);
