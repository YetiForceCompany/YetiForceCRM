<?php
/**
 * iCalendar class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

namespace App\Utils;

\Vtiger_Loader::includeOnce('~modules/Calendar/iCalLastImport.php');
\Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_components.php');

class iCalendar
{
	public static function import($filePath)
	{
		var_dump('icalender');

		$userModel = \App\User::getCurrentUserModel();
		$lastImport = new \IcalLastImport();
		var_dump('icalender');
		new \IcalendarComponent();
		$lastImport->clearRecords($userModel->getId());
		$eventModule = 'Events';
		$todoModule = 'Calendar';
		$totalCount = $skipCount = [$eventModule => 0, $todoModule => 0];
		$ical = new \Ical();
		var_dump('icalender');

		$icalActivities = $ical->iCalReader($filePath);
		var_dump($icalActivities);
		$noOfActivities = count($icalActivities);
		$moduleModel = Vtiger_Module_Model::getInstance($todoModule);

		for ($i = 0; $i < $noOfActivities; ++$i) {
			if ($icalActivities[$i]['TYPE'] == 'VEVENT') {
				$activity = new \IcalendarEvent();
				$module = $eventModule;
			} else {
				$activity = new \IcalendarTodo();
				$module = $todoModule;
			}
			$skipRecord = false;
			++$totalCount[$module];
			$activityFieldsList = $activity->generateArray($icalActivities[$i]);
			$activityFieldsList['assigned_user_id'] = $userModel->getId();
			$activityFieldsList['time_end'] = $activityFieldsList['time_end'] ?? $userModel->getDetail('end_hour') . ':00';
			$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleModel->getName());
			foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
				if (empty($activityFieldsList[$fieldName]) && $fieldModel->isActiveField() && $fieldModel->isMandatory()) {
					++$skipCount[$module];
					$skipRecord = true;
					break;
				}
				if (!$fieldModel->isWritable() || !isset($activityFieldsList[$fieldName])) {
					continue;
				}
				$recordModel->set($fieldName, $activityFieldsList[$fieldName]);
			}
			if ($skipRecord) {
				continue;
			}
			$recordModel->save();
			$lastImport = new \IcalLastImport();
			$lastImport->setFields(['userid' => $userModel->getId(), 'entitytype' => $moduleModel->getName(), 'crmid' => $recordModel->getId()]);
			$lastImport->save();
		}
		return ['events' => $totalCount[$eventModule] - $skipCount[$eventModule], 'skipped_events' => $skipCount[$eventModule], 'task' => $totalCount[$todoModule] - $skipCount[$todoModule], 'skipped_task' => $skipCount[$todoModule]];
	}
}
