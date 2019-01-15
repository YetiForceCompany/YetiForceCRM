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
\Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/ical-parser-class.php');

class iCalendar
{
	public static function import($filePath)
	{
		$userModel = \App\User::getCurrentUserModel();
		$lastImport = new \IcalLastImport();
		new \IcalendarComponent();
		$lastImport->clearRecords($userModel->getId());
		$ical = new \Ical();
		$icalActivities = $ical->iCalReader($filePath);
		$noOfActivities = count($icalActivities);
		$activitiesList = [];
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Calendar');
		for ($i = 0; $i < $noOfActivities; ++$i) {
			if ($icalActivities[$i]['TYPE'] == 'VEVENT') {
				$activity = new \IcalendarEvent();
			} else {
				$activity = new \IcalendarTodo();
			}
			$activityFieldsList = $activity->generateArray($icalActivities[$i]);
			$activityFieldsList['time_end'] = $activityFieldsList['time_end'] ?? $userModel->getDetail('end_hour') . ':00';
			$record = \Vtiger_Record_Model::getCleanInstance('Calendar');
			foreach ($recordModel->getModule()->getFields() as $fieldName => $fieldModel) {
				if (!$fieldModel->isWritable() || !isset($activityFieldsList[$fieldName])) {
					continue;
				}
				$record->set($fieldName, $activityFieldsList[$fieldName]);
			}
			array_push($activitiesList, $record);
		}
		return $activitiesList;
	}
}
