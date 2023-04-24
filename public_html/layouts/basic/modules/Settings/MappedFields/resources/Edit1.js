/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_MappedFields_Edit_Js(
	'Settings_MappedFields_Edit1_Js',
	{},
	{
		init: function () {
			this.initialize();
		},
		/**
		 * Function to get the container which holds all the reports step1 elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.step1Container;
		},
		/**
		 * Function to set the reports step1 container
		 * @params : element - which represents the reports step1 container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.step1Container = element;
			return this;
		},
		/**
		 * Function  to intialize the reports step1
		 */
		initialize: function (container) {
			if (typeof container === 'undefined') {
				container = jQuery('#mf_step1');
			}
			if (container.is('#mf_step1')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('#mf_step1'));
			}
		},
		submit: function () {
			var aDeferred = jQuery.Deferred();
			var form = this.getContainer();
			var formData = form.serializeFormData();
			formData['async'] = false;

			var saveData = form.serializeFormData();
			delete saveData['_csrf'];
			delete saveData['module'];
			delete saveData['view'];
			delete saveData['mode'];
			delete saveData['parent'];
			saveData['step'] = 1;
			if (this.checkModulesName()) {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				app.saveAjax('step1', saveData).done(function (data) {
					if (data.success === true) {
						if (!data.result.id && data.result.message) {
							Settings_Vtiger_Index_Js.showMessage({ text: data.result.message, type: 'error' });
							aDeferred.resolve(false);
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							return false;
						}
						Settings_Vtiger_Index_Js.showMessage({
							text: app.vtranslate('JS_MF_SAVED_SUCCESSFULLY')
						});
						var mfRecordElement = jQuery('[name="record"]', form);
						if (mfRecordElement.val() === '') {
							mfRecordElement.val(data.result.id);
							formData['record'] = data.result.id;
						}

						formData['record'] = data.result.id;
						AppConnector.request(formData).done(function (data) {
							form.hide();
							progressIndicatorElement.progressIndicator({
								mode: 'hide'
							});
							aDeferred.resolve(data);
						});
					}
				});
			}
			return aDeferred.promise();
		},
		registerCancelStepClickEvent: function (form) {
			jQuery('button.cancelLink', form).on('click', function () {
				window.history.back();
			});
		},
		checkModulesName: function () {
			var sourceModule = jQuery('[name="tabid"]').val();
			var targetModule = jQuery('[name="reltabid"]').val();
			if (sourceModule === targetModule) {
				var notificationParams = {
					text: app.vtranslate('JS_YOU_CAN_NOT_SELECT_THE_SAME_MODULES'),
					type: 'error'
				};
				Settings_Vtiger_Index_Js.showMessage(notificationParams);
				return false;
			}
			return true;
		},
		registerEvents: function () {
			var container = this.getContainer();
			//After loading 1st step only, we will enable the Next button
			container.find('[type="submit"]').removeAttr('disabled');

			var opts = app.validationEngineOptions;
			// to prevent the page reload after the validation has completed
			opts['onValidationComplete'] = function (form, valid) {
				//returns the valid status
				return valid;
			};
			opts['promptPosition'] = 'bottomRight';
			container.validationEngine(opts);
			this.registerCancelStepClickEvent(container);
		}
	}
);
