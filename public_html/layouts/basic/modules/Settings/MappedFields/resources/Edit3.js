/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_MappedFields_Edit_Js(
	'Settings_MappedFields_Edit3_Js',
	{},
	{
		step3Container: false,
		advanceFilterInstance: false,
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
				container = jQuery('#mf_step3');
			}
			if (container.is('#mf_step3')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('#mf_step3'));
			}
		},
		calculateValues: function () {
			//handled advanced filters saved values.
			var enableFilterElement = jQuery('#enableAdvanceFilters');
			if (enableFilterElement.length > 0 && enableFilterElement.is(':checked') == false) {
				jQuery('#advanced_filter').val(jQuery('#olderConditions').val());
			} else {
				jQuery('[name="filtersavedinnew"]').val('6');
				var advfilterlist = this.advanceFilterInstance.getValues();
				jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
			}
		},
		submit: function () {
			var aDeferred = jQuery.Deferred();
			this.calculateValues();
			var form = this.getContainer();
			var formData = form.serializeFormData();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			var saveData = form.serializeFormData();
			saveData['step'] = 3;
			delete saveData['_csrf'];
			delete saveData['module'];
			delete saveData['view'];
			delete saveData['mode'];
			delete saveData['parent'];
			app.saveAjax('step1', saveData).done(function (data) {
				if (data.success == true) {
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_MF_SAVED_SUCCESSFULLY')
					});
					AppConnector.request(formData)
						.done(function (data) {
							form.hide();
							progressIndicatorElement.progressIndicator({
								mode: 'hide'
							});
							aDeferred.resolve(data);
						})
						.fail(function (error, err) {
							app.errorLog(error, err);
						});
				}
			});
			return aDeferred.promise();
		},
		registerCancelStepClickEvent: function (form) {
			jQuery('button.cancelLink', form).on('click', function () {
				window.history.back();
			});
		},
		registerEvents: function () {
			var container = this.getContainer();
			var opts = app.validationEngineOptions;
			// to prevent the page reload after the validation has completed
			opts['onValidationComplete'] = function (form, valid) {
				//returns the valid status
				return valid;
			};
			opts['promptPosition'] = 'bottomRight';
			container.validationEngine(opts);
			this.registerCancelStepClickEvent(container);
			this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('#advanceFilterContainer', container));
			App.Fields.Picklist.changeSelectElementView(container);
		}
	}
);
