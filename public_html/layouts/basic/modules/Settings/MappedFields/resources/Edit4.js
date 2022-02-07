/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_MappedFields_Edit_Js(
	'Settings_MappedFields_Edit4_Js',
	{},
	{
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
				container = jQuery('#mf_step4');
			}
			if (container.is('#mf_step4')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('#mf_step4'));
			}
		},
		submit: function () {
			var aDeferred = jQuery.Deferred();
			var form = this.getContainer();
			var formData = form.serializeFormData();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var saveData = {};
			saveData.permissions = jQuery('#permissions').val();
			saveData.record = formData.record;
			saveData.step = 4;
			saveData = jQuery.extend({}, saveData);
			app.saveAjax('step1', saveData).done(function (data) {
				if (data.success == true) {
					Settings_Vtiger_Index_Js.showMessage({
						text: app.vtranslate('JS_MF_SAVED_SUCCESSFULLY')
					});

					setTimeout(function () {
						window.location.href = 'index.php?module=MappedFields&parent=Settings&page=1&view=List';
						progressIndicatorElement.progressIndicator({
							mode: 'hide'
						});
					}, 1000);
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
			App.Fields.Picklist.showSelect2ElementView(container.find('.select2'));
		}
	}
);
