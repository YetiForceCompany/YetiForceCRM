/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_PDF_Import_Js',
	{},
	{
		/**
		 * Function to register event for PDF import
		 */
		registerEvents: function () {
			let form = $('.js-validation-engine');
			form.validationEngine();
		}
	}
);
