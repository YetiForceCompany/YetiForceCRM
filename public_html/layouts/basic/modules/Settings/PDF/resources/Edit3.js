/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_PDF_Edit_Js(
	'Settings_PDF_Edit3_Js',
	{},
	{
		step3Container: false,
		advanceFilterInstance: false,
		ckEditorInstance: false,
		fieldValueMap: false,
		init: function () {
			this.initialize();
		},
		/**
		 * Function to get the container which holds all the reports step1 elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.step3Container;
		},
		/**
		 * Function to set the reports step1 container
		 * @params : element - which represents the reports step1 container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.step3Container = element;
			return this;
		},
		/**
		 * Function  to intialize the reports step1
		 */
		initialize: function (container) {
			if (typeof container === 'undefined') {
				container = $('#pdf_step3');
			}
			if (container.is('#pdf_step3')) {
				this.setContainer(container);
			} else {
				this.setContainer($('#pdf_step3'));
			}
		},
		submit() {
			var aDeferred = jQuery.Deferred();
			this.calculateValues();
			var form = this.getContainer();
			var progressIndicatorElement = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var saveData = form.serializeFormData();
			saveData['action'] = 'Save';
			saveData['step'] = 3;
			AppConnector.request(saveData)
				.done(function (data) {
					if (data.success === true) {
						Settings_Vtiger_Index_Js.showMessage({
							text: app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')
						});
						progressIndicatorElement.progressIndicator({
							mode: 'hide'
						});
						app.openUrl('index.php?module=PDF&parent=Settings&view=List');
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
		calculateValues: function () {
			//handled advanced filters saved values.
			var enableFilterElement = $('#enableAdvanceFilters');
			if (enableFilterElement.length > 0 && enableFilterElement.is(':checked') == false) {
				$('#advanced_filter').val($('#olderConditions').val());
			} else {
				$('[name="filtersavedinnew"]').val('6');
				var advfilterlist = this.advanceFilterInstance.getValues();
				$('#advanced_filter').val(JSON.stringify(advfilterlist));
			}
		},
		registerEvents() {
			const container = this.getContainer();
			const opts = app.validationEngineOptions;
			// to prevent the page reload after the validation has completed
			opts['onValidationComplete'] = function (form, valid) {
				//returns the valid status
				return valid;
			};
			opts['promptPosition'] = 'bottomRight';
			container.validationEngine(opts);
			App.Fields.Picklist.changeSelectElementView(container);
			this.registerCancelStepClickEvent(container);
			this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance($('#advanceFilterContainer', container));
		}
	}
);
