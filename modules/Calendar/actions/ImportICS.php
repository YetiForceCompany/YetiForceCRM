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

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$ics = $request->get('ics') . '.ics';
		$icsUrl = 'cache/import/' . $ics;
		if (file_exists($icsUrl)) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUserModel->getId();

			$lastImport = new IcalLastImport();
			$lastImport->clearRecords($userId);

			$eventModule = 'Events';
			$todoModule = 'Calendar';
			$skipFields = [
				$eventModule => ['duration_hours'],
				$todoModule => ['activitystatus'],
			];

			$requiredFields = [];
			foreach ([$eventModule, $todoModule] as $module) {
				$moduleRequiredFields = [];
				$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
				foreach ($moduleModel->getFields() as $field) {
					if ($field->isActiveField() && $field->isMandatory() && !in_array($field->getUIType(), [53, 70])) {
						$moduleRequiredFields[] = $field->getName();
					}
				}
				$requiredFields[$module] = array_diff($moduleRequiredFields, $skipFields[$module]);
				$totalCount[$module] = 0;
				$skipCount[$module] = 0;
			}

			$ical = new Ical();
			$icalActivities = $ical->iCalReader($icsUrl);
			$noOfActivities = count($icalActivities);

			for ($i = 0; $i < $noOfActivities; ++$i) {
				if ($icalActivities[$i]['TYPE'] == 'VEVENT') {
					$activity = new IcalendarEvent();
					$module = $eventModule;
				} else {
					$activity = new IcalendarTodo();
					$module = $todoModule;
				}

				++$totalCount[$module];
				$activityFieldsList = $activity->generateArray($icalActivities[$i]);

				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
				$recordModel->setData($activityFieldsList);
				$recordModel->set('assigned_user_id', $userId);

				foreach ($requiredFields[$module] as $key) {
					$value = $recordModel->get($key);
					if (empty($value)) {
						++$skipCount[$module];
						break;
					}
				}
				$recordModel->save();

				$lastImport = new IcalLastImport();
				$lastImport->setFields(['userid' => $userId, 'entitytype' => $todoModule, 'crmid' => $recordModel->getId()]);
				$lastImport->save();

				if (!empty($icalActivities[$i]['VALARM'])) {
					$recordModel->setActivityReminder(0, '', '');
				}
			}
			$return = 'LBL_IMPORT_ICS_ERROR_NO_RECORD';
			$importedEvents = $totalCount[$eventModule] - $skipCount[$eventModule];
			$importedTasks = $totalCount[$todoModule] - $skipCount[$todoModule];
			if ($importedEvents > 0 || $importedTasks > 0) {
				$return = 'LBL_IMPORT_ICS_SUCCESS';
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
