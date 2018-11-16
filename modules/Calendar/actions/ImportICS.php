<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_rfc2445.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_components.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_properties.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_parameters.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/ical-parser-class.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCalLastImport.php');

class Calendar_ImportICS_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($moduleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!\App\Privilege::isPermitted($moduleName, 'EditView')) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$ics = $request->get('ics') . '.ics';
		$icsUrl = 'cache/import/' . $ics;
		if (file_exists($icsUrl)) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$result = $moduleModel->importICS($icsUrl);
			if ($result['events'] > 0 || $result['task'] > 0) {
				$return = 'LBL_IMPORT_ICS_SUCCESS';
			} else {
				$return = 'LBL_IMPORT_ICS_ERROR_NO_RECORD';
			}
			@unlink($icsUrl);
		} else {
			$return = 'LBL_IMPORT_ICS_ERROR_NO_RECORD';
		}
		$response = new Vtiger_Response();
		$response->setResult(\App\Language::translate($return, $moduleName));
		$response->emit();
	}
}
