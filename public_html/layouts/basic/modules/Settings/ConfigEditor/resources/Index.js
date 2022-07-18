/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

'use strict';
window.Settings_ConfigEditor_Index_Js = class {
	/**
	 * Register events
	 */
	registerEvents() {
		this.container = $('.contentsDiv');
		app.showPopoverElementView(this.container.find('.js-popover-tooltip'));
	}
};

Vtiger_WholeNumberGreaterThanZero_Validator_Js(
	'Vtiger_NumberRange100_Validator_Js',
	{
		/**
		 *Function which invokes field validation
		 *@param accepts field element as parameter
		 * @return error if validation fails true on success
		 */
		invokeValidation: function (field, rules, i, options) {
			var rangeInstance = new Vtiger_NumberRange100_Validator_Js();
			rangeInstance.setElement(field);
			var response = rangeInstance.validate();
			if (response != true) {
				return rangeInstance.getError();
			}
		}
	},
	{
		/**
		 * Function to validate the percentage field data
		 * @return true if validation is successfull
		 * @return false if validation error occurs
		 */
		validate: function () {
			var response = this._super();
			if (response != true) {
				return response;
			} else {
				var fieldValue = this.getFieldValue();
				if (fieldValue < 1 || fieldValue > 100) {
					var errorInfo = app.vtranslate('JS_PLEASE_ENTER_NUMBER_IN_RANGE_1TO100');
					this.setError(errorInfo);
					return false;
				}
				return true;
			}
		}
	}
);
