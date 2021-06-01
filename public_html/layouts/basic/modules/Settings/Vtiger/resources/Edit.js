/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_Edit_Js(
	'Settings_Vtiger_Edit_Js',
	{},
	{
		/**
		 * Function to register form for validation
		 */
		registerFormForValidation: function () {
			var editViewForm = this.getForm();
			editViewForm.validationEngine(app.validationEngineOptions);
		},

		/**
		 * Function which will handle the registrations for the elements
		 */
		registerEvents: function () {
			this.registerFormForValidation();
		}
	}
);
