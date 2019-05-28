/**
 * Business Hours Edit
 *
 * @description Edit scripts for business hours
 * @license YetiForce Public License 3.0
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_BusinessHours_Edit_Js',
	{},
	{
		/**
		 * Function to register form for validation
		 */
		registerFormForValidation() {
			const editViewForm = this.getForm();
			editViewForm.validationEngine(app.validationEngineOptions);
		},

		/**
		 * Function which will handle the registrations for the elements
		 */
		registerEvents() {
			this.registerFormForValidation();
			app.registerEventForClockPicker();
		}
	}
);
