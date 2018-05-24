<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o
 * *********************************************************************************** */

Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_rfc2445.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_components.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_properties.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_parameters.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/ical-parser-class.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCalLastImport.php');

class Calendar_Import_View extends Vtiger_Import_View
{
	use \App\Controller\ExposeMethod;

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

	public function postprocess(\App\Request $request, $display = true)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			parent::postProcess($request);
		}
	}

	/**
	 * Function to show import UI in Calendar Module.
	 *
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
	 * Function to show result of import.
	 *
	 * @param \App\Request $request
	 */
	public function importResult(\App\Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
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
			$icalActivities = $ical->iCalReader(Import_Utils_Helper::getImportFilePath($currentUserModel));
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
				if (!array_key_exists('visibility', $activityFieldsList)) {
					$activityFieldsList['visibility'] = ' ';
				}
				if (array_key_exists('taskpriority', $activityFieldsList)) {
					$priorityMap = ['0' => 'Medium', '1' => 'High', '2' => 'Medium', '3' => 'Low'];
					$priorityval = $activityFieldsList['taskpriority'];
					if (array_key_exists($priorityval, $priorityMap)) {
						$activityFieldsList['taskpriority'] = $priorityMap[$priorityval];
					}
				}

				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
				$recordModel->setData($activityFieldsList);
				$recordModel->set('assigned_user_id', $userId);

				$skipRecord = false;
				foreach ($requiredFields[$module] as $key) {
					$value = $recordModel->get($key);
					if (empty($value)) {
						++$skipCount[$module];
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
	 * Function to show result of undo import.
	 *
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
