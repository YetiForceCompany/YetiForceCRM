/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

jQuery.Class('Settings_PDF_Import_Js', {}, {
  /**
   * Function to register event for PDF import
   */
  registerEvents: function() {
    let form = $('.js-validation-engine');
    form.validationEngine();
  }
});

jQuery(document).ready(function() {
	var settingPDFImportInstance = new Settings_PDF_Import_Js();
	settingPDFImportInstance.registerEvents();
});
