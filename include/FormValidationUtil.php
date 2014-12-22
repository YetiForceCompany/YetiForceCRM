<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

/*
 * File containing methods to proceed with the ui validation for all the forms
 *
 */
/**
 * Get field validation information
 */
function getDBValidationData($tablearray, $tabid='') {
	return Vtiger_Deprecated::getModuleFieldTypeOfDataInfos($tablearray, $tabid);
  }

?>
