<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */


require_once('include/utils/UserInfoUtil.php');
require_once("include/utils/utils.php");
require_once("include/ListView/RelatedListViewSession.php");

function CheckFieldPermission($fieldname, $module)
{
	if ($fieldname == '' || $module == '') {
		return 'false';
	}
	if (\App\Field::getFieldPermission($module, $fieldname)) {
		return 'true';
	}
	return 'false';
}

function CheckColumnPermission($tablename, $columnname, $module)
{
	if (\App\Field::getColumnPermission($module, $columnname)) {
		return 'true';
	}
	return 'false';
}
