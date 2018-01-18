<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

Vtiger_Loader::includeOnce('~modules/Calendar/iCal/iCalendar_rfc2445.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/iCalendar_components.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/Icalendar.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarAlarm.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarEvent.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarFreebusy.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarJournal.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarTimezone.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarTodo.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/iCalendar_properties.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyAction.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyAttach.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyAttendee.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyCalscale.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyCategories.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyClass.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyComment.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyCompleted.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyContact.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyCreated.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyDaylightc.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyDescription.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyDtend.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyDtstamp.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyDtstart.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyDue.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyDuration.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyExdate.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyExrule.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyFreebusy.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyGeo.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyLastmodified.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyLocation.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyMethod.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyOrganizer.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyPercentcomplete.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyPriority.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyProdid.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyRdate.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyRecurrenceid.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyRelatedto.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyRequeststatus.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyResources.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyRrule.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertySequence.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyStandardc.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyStatus.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertySummary.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyTransp.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyTrigger.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyTzid.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyTzoffsetto.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyUid.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyUrl.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyVersion.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyX.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarPropertyXwralarmuid.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/iCalendar_parameters.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/ical-parser-class.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCalLastImport.php');

class Calendar_Import_View extends Vtiger_Import_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('import');
		$this->exposeMethod('importResult');
		$this->exposeMethod('undoImport');
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			parent::preProcess($request);
		}
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		echo $this->import($request);
	}

	public function postprocess(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			parent::postProcess($request);
		}
	}

	/**
	 * Function to show import UI in Calendar Module
	 * @param \App\Request $request
	 */
	public function import(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SUPPORTED_FILE_TYPES', Import_Utils_Helper::getSupportedFileExtensions($moduleName));
		$viewer->assign('SUPPORTED_FILE_TYPES_TEXT', Import_Utils_Helper::getSupportedFileExtensionsDescription($moduleName));
		$viewer->view('Import.tpl', $moduleName);
	}

	/**
	 * Function to show result of import
	 * @param \App\Request $request
	 */
	public function importResult(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUserModel->getId();
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$request->set('type', 'ics');

		if (Import_Utils_Helper::validateFileUpload($request)) {
			$lastImport = new IcalLastImport();
			$lastImport->clearRecords($userId);

			$eventModule = 'Events';
			$todoModule = 'Calendar';

			$skipFields = [
				$eventModule => ['duration_hours'],
				$todoModule => ['activitystatus']
			];

			$requiredFields = [];
			$modules = [$eventModule, $todoModule];
			$calendarModel = Vtiger_Module_Model::getInstance($moduleName);

			foreach ($modules as $module) {
				$moduleRequiredFields = array_keys($calendarModel->getRequiredFields($module));
				$requiredFields[$module] = array_diff($moduleRequiredFields, $skipFields[$module]);
				$totalCount[$module] = 0;
				$skipCount[$module] = 0;
			}

			$ical = new Ical();
			$icalActivities = $ical->iCalReader("IMPORT_" . $userId);
			$noOfActivities = count($icalActivities);

			for ($i = 0; $i < $noOfActivities; $i++) {
				if ($icalActivities[$i]['TYPE'] == 'VEVENT') {
					$activity = new IcalendarEvent;
					$module = $eventModule;
				} else {
					$activity = new IcalendarTodo;
					$module = $todoModule;
				}

				$totalCount[$module] ++;
				$activityFieldsList = $activity->generateArray($icalActivities[$i]);
				if (!array_key_exists('visibility', $activityFieldsList)) {
					$activityFieldsList['visibility'] = ' ';
				}
				if (array_key_exists('taskpriority', $activityFieldsList)) {
					$priorityMap = ['0' => 'Medium', '1' => 'High', '2' => 'Medium', '3' => 'Low'];
					$priorityval = $activityFieldsList['taskpriority'];
					if (array_key_exists($priorityval, $priorityMap))
						$activityFieldsList['taskpriority'] = $priorityMap[$priorityval];
				}

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
				if ($skipRecord === true) {
					continue;
				}
				$recordModel->save();

				$lastImport = new IcalLastImport();
				$lastImport->setFields(['userid' => $userId, 'entitytype' => $todoModule, 'crmid' => $recordModel->getId()]);
				$lastImport->save();

				if (!empty($icalActivities[$i]['VALARM'])) {
					$recordModel->setActivityReminder(0, '', '');
				}
			}

			$importedEvents = $totalCount[$eventModule] - $skipCount[$eventModule];
			$importedTasks = $totalCount[$todoModule] - $skipCount[$todoModule];

			$viewer->assign('SUCCESS_EVENTS', $importedEvents);
			$viewer->assign('SKIPPED_EVENTS', $skipCount[$eventModule]);
			$viewer->assign('SUCCESS_TASKS', $importedTasks);
			$viewer->assign('SKIPPED_TASKS', $skipCount[$todoModule]);
		} else {
			$viewer->assign('ERROR_MESSAGE', $request->get('error_message'));
		}

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEW', 'List');

		$viewer->view('ImportResult.tpl', $moduleName);
	}

	/**
	 * Function to show result of undo import
	 * @param \App\Request $request
	 */
	public function undoImport(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleName = $request->getModule();

		$lastImport = new IcalLastImport();
		$returnValue = $lastImport->undo($moduleName, $currentUserModel->getId());
		if (!empty($returnValue)) {
			$undoStatus = true;
		} else {
			$undoStatus = false;
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEW', 'List');
		$viewer->assign('UNDO_STATUS', $undoStatus);

		$viewer->view('ImportFinalResult.tpl', $moduleName);
	}
}
