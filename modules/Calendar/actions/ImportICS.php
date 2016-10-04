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
vimport('~modules/Calendar/iCal/iCalendar_rfc2445.php');
vimport('~modules/Calendar/iCal/iCalendar_components.php');
vimport('~modules/Calendar/iCal/iCalendar_properties.php');
vimport('~modules/Calendar/iCal/iCalendar_parameters.php');
vimport('~modules/Calendar/iCal/ical-parser-class.php');
vimport('~modules/Calendar/iCalLastImport.php');

class Calendar_ImportICS_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$ics = $request->get('ics') . '.ics';
		$icsUrl = 'cache/import/' . $ics;
		if (file_exists($icsUrl)) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUserModel->getId();

			$lastImport = new iCalLastImport();
			$lastImport->clearRecords($userId);

			$eventModule = 'Events';
			$todoModule = 'Calendar';
			$skipFields = array(
				$eventModule => array('duration_hours'),
				$todoModule => array('activitystatus')
			);

			$requiredFields = array();
			$modules = array($eventModule, $todoModule);
			$calendarModel = Vtiger_Module_Model::getInstance($moduleName);

			foreach ($modules as $module) {
				$moduleRequiredFields = array_keys($calendarModel->getRequiredFields($module));
				$requiredFields[$module] = array_diff($moduleRequiredFields, $skipFields[$module]);
				$totalCount[$module] = 0;
				$skipCount[$module] = 0;
			}

			$ical = new iCal();
			$icalActivities = $ical->iCalReader($ics);
			$noOfActivities = count($icalActivities);

			for ($i = 0; $i < $noOfActivities; $i++) {
				if ($icalActivities[$i]['TYPE'] == 'VEVENT') {
					$activity = new iCalendar_event;
					$module = $eventModule;
				} else {
					$activity = new iCalendar_todo;
					$module = $todoModule;
				}

				$totalCount[$module] ++;
				$activityFieldsList = $activity->generateArray($icalActivities[$i]);

				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
				$recordModel->setData($activityFieldsList);
				$recordModel->set('assigned_user_id', $userId);

				$skipRecord = false;
				foreach ($requiredFields[$module] as $key) {
					$value = $recordModel->get($key);
					if (empty($value)) {
						$skipCount[$module] ++;
						$skipRecord = true;
						break;
					}
				}
				$recordModel->save();

				$lastImport = new iCalLastImport();
				$lastImport->setFields(array('userid' => $userId, 'entitytype' => $todoModule, 'crmid' => $recordModel->getId()));
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
		$response->setResult(vtranslate($return, $moduleName));
		$response->emit();
	}
}
