/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_MappedFields_Edit_Js',
	{
		instance: {}
	},
	{
		currentInstance: false,
		editContainer: false,
		init: function () {
			this.initiate();
		},
		/**
		 * Function to get the container which holds all the workflow elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.editContainer;
		},
		/**
		 * Function to set the reports container
		 * @params : element - which represents the workflow container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.editContainer = element;
			return this;
		},
		/*
		 * Function to return the instance based on the step of the Workflow
		 */
		getInstance: function (step) {
			if (step in Settings_MappedFields_Edit_Js.instance) {
				return Settings_MappedFields_Edit_Js.instance[step];
			} else {
				var moduleClassName = 'Settings_MappedFields_Edit' + step + '_Js';
				Settings_MappedFields_Edit_Js.instance[step] = new window[moduleClassName]();
				return Settings_MappedFields_Edit_Js.instance[step];
			}
		},
		/*
		 * Function to get the value of the step
		 * returns 1 or 2 or 3
		 */
		getStepValue: function () {
			var container = this.currentInstance.getContainer();
			return jQuery('.step', container).val();
		},
		/*
		 * Function to initiate the step 1 instance
		 */
		initiate: function (container) {
			if (typeof container === 'undefined') {
				container = jQuery('.mfTemplateContents');
			}
			if (container.is('.mfTemplateContents')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('.mfTemplateContents', container));
			}
			this.initiateStep('1');
			this.currentInstance.registerEvents();
		},
		/*
		 * Function to initiate all the operations for a step
		 * @params step value
		 */
		initiateStep: function (stepVal) {
			var step = 'step' + stepVal;
			this.activateHeader(step);
			this.currentInstance = this.getInstance(stepVal);
		},
		/*
		 * Function to activate the header based on the class
		 * @params class name
		 */
		activateHeader: function (step) {
			var headersContainer = jQuery('.crumbs ');
			headersContainer.find('.active').removeClass('active');
			jQuery('#' + step, headersContainer).addClass('active');
		},
		/*
		 * Function to register the click event for next button
		 */
		registerFormSubmitEvent: function (form) {
			var thisInstance = this;
			if (jQuery.isFunction(thisInstance.currentInstance.submit)) {
				form.on('submit', function (e) {
					var form = jQuery(e.currentTarget);
					var specialValidation = true;
					if (jQuery.isFunction(thisInstance.currentInstance.isFormValidate)) {
						specialValidation = thisInstance.currentInstance.isFormValidate();
					}
					if (form.validationEngine('validate') && specialValidation) {
						thisInstance.currentInstance.submit().done(function (data) {
							if (data) {
								thisInstance.getContainer().append(data);
								var stepVal = thisInstance.getStepValue();
								var nextStepVal = parseInt(stepVal) + 1;
								thisInstance.initiateStep(nextStepVal);
								thisInstance.currentInstance.initialize();
								var container = thisInstance.currentInstance.getContainer();
								thisInstance.registerFormSubmitEvent(container);
								thisInstance.currentInstance.registerEvents();
							}
						});
					}
					e.preventDefault();
				});
			}
		},
		back: function () {
			var step = this.getStepValue();
			var prevStep = parseInt(step) - 1;
			this.currentInstance.initialize();
			var container = this.currentInstance.getContainer();
			var mfRecordElement = jQuery('[name="record"]', container);
			var mfId = mfRecordElement.val();
			container.remove();
			this.initiateStep(prevStep);
			var currentContainer = this.currentInstance.getContainer();
			currentContainer.show();
			jQuery('[name="record"]', currentContainer).val(mfId);
		},
		registerCancelStepClickEvent: function (form) {
			jQuery('button.cancelLink', form).on('click', function () {
				window.history.back();
			});
		},
		/*
		 * Function to register the click event for back step
		 */
		registerBackStepClickEvent: function () {
			var thisInstance = this;
			var container = this.getContainer();
			container.on('click', '.backStep', function (e) {
				thisInstance.back();
			});
		},
		registerEvents: function () {
			var form = this.currentInstance.getContainer();
			this.registerFormSubmitEvent(form);
			this.registerBackStepClickEvent();
			this.registerCancelStepClickEvent(form);
		}
	}
);
