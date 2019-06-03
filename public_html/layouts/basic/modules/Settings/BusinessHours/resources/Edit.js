/**
 * Business Hours Edit
 *
 * @package     Edit
 *
 * @description Edit scripts for business hours
 * @license     YetiForce Public License 3.0
 * @author      Rafal Pospiech <r.pospiech@yetiforce.com>
 */
'use strict';
$.Class(
	'Settings_BusinessHours_Edit_Js',
	{},
	{
		/**
		 * Function to register form for validation
		 */
		registerFormForValidation(container) {
			container.validationEngine(app.validationEngineOptions);
		},

		/**
		 * Function which will handle the registrations for the elements
		 */
		registerEvents() {
			const container = $('#EditView');
			this.registerFormForValidation(container);
			app.registerEventForClockPicker();
		}
	}
);
