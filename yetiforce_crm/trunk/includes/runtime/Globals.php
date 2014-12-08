<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Function to get or set a global variable
 * @param type $key
 * @param type $value
 * @return value of the given key
 */
function vglobal($key, $value=null) {
	if($value !== null) {
		$GLOBALS[$key] = $value;
	}
	return $GLOBALS[$key];
}