<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */
require_once('include/utils/CommonUtils.php');
require_once 'include/Webservices/Utils.php';
require_once 'include/Webservices/DescribeObject.php';
require_once 'modules/com_vtiger_workflow/expression_engine/VTExpressionsManager.php';

function vtJsonFields($adb, Vtiger_Request $request)
{
	$mem = new VTExpressionsManager($adb);
	$fields = $mem->fields($request->getModule());
	echo \App\Json::encode(array('moduleFields' => $fields));
}

function vtJsonFunctions($adb)
{
	$mem = new VTExpressionsManager($adb);
	$functions = $mem->expressionFunctions();
	echo \App\Json::encode($functions);
}

function vtJsonDependentModules($adb, Vtiger_Request $request)
{
	$moduleName = $request->getModule();

	$result = $adb->pquery("SELECT fieldname, tabid, typeofdata, vtiger_ws_referencetype.type as reference_module FROM vtiger_field
									INNER JOIN vtiger_ws_fieldtype ON vtiger_field.uitype = vtiger_ws_fieldtype.uitype
									INNER JOIN vtiger_ws_referencetype ON vtiger_ws_fieldtype.fieldtypeid = vtiger_ws_referencetype.fieldtypeid
							UNION
							SELECT fieldname, tabid, typeofdata, relmodule as reference_module FROM vtiger_field
									INNER JOIN vtiger_fieldmodulerel ON vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid", []);

	$noOfFields = $adb->num_rows($result);
	$dependentFields = [];
	// List of modules which will not be supported by 'Create Entity' workflow task
	$filterModules = ['Calendar', 'Events', 'Accounts'];
	$skipFieldsList = [];
	for ($i = 0; $i < $noOfFields; ++$i) {
		$tabId = $adb->query_result($result, $i, 'tabid');
		$fieldName = $adb->query_result($result, $i, 'fieldname');
		$typeOfData = $adb->query_result($result, $i, 'typeofdata');
		$referenceModule = $adb->query_result($result, $i, 'reference_module');
		$tabModuleName = \App\Module::getModuleName($tabId);
		if (in_array($tabModuleName, $filterModules))
			continue;
		if ($referenceModule == $moduleName && $tabModuleName != $moduleName) {
			if (!\App\Module::isModuleActive($tabModuleName))
				continue;
			$dependentFields[$tabModuleName] = array('fieldname' => $fieldName, 'modulelabel' => \App\Language::translate($tabModuleName, $tabModuleName));
		} else {
			$dataTypeInfo = explode('~', $typeOfData);
			if ($dataTypeInfo[1] == 'M') { // If the current reference field is mandatory
				$skipFieldsList[$tabModuleName] = array('fieldname' => $fieldName);
			}
		}
	}
	foreach ($skipFieldsList as $tabModuleName => $fieldInfo) {
		$dependentFieldInfo = $dependentFields[$tabModuleName];
		if ($dependentFieldInfo['fieldname'] != $fieldInfo['fieldname']) {
			unset($dependentFields[$tabModuleName]);
		}
	}

	$returnValue = array('count' => count($dependentFields), 'entities' => $dependentFields);

	echo \App\Json::encode($returnValue);
}

function vtJsonOwnersList($adb)
{
	$ownersList = [];
	$owner = \App\Fields\Owner::getInstance();
	$activeUsersList = $owner->getUsers();
	$allGroupsList = $owner->getGroups();
	foreach ($activeUsersList as $userId => $userName) {
		$ownersList[] = array('label' => $userName, 'value' => \App\Fields\Owner::getLabel($userId));
	}
	foreach ($allGroupsList as $groupId => $groupName) {
		$ownersList[] = array('label' => $groupName, 'value' => $groupName);
	}

	echo \App\Json::encode($ownersList);
}
$adb = PearDatabase::getInstance();
$request = AppRequest::init();
$mode = $request->get('mode');

if ($mode == 'getfieldsjson') {
	vtJsonFields($adb, $request);
} elseif ($mode == 'getfunctionsjson') {
	vtJsonFunctions($adb);
} elseif ($mode == 'getdependentfields') {
	vtJsonDependentModules($adb, $request);
} elseif ($mode == 'getownerslist') {
	vtJsonOwnersList($adb);
}
