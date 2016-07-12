<?php

/**
 * Action to get free time for events
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Calendar_GetFreeTime_Action extends Vtiger_BasicAjax_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if (!$permission) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
	}

	public function getFreeTimeInDay($day)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$durationEvent = $currentUser->get('othereventduration');
		$startWorkHour = $currentUser->get('start_hour');
		$endWorkHour = $currentUser->get('end_hour');
		$dbStartDateOject = DateTimeField::convertToDBTimeZone($day . ' ' . $startWorkHour);
		$dbEndDateObject = DateTimeField::convertToDBTimeZone($day . ' ' . $endWorkHour);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
		$dbStartDate =  $dbStartDateOject->format('Y-m-d');
		$dbEndDate = $dbEndDateObject->format('Y-m-d');
		$db = PearDatabase::getInstance();
		$params[]= 0;
		$params[]=  $currentUser->getId();
		$params[] = $dbStartDateTime;
		$params[] = $dbEndDateTime;
		$params[] = $dbStartDateTime;
		$params[] = $dbEndDateTime;
		$params[] = $dbStartDate;
		$params[] = $dbEndDate;
		$startTime = $dbStartDateOject->format('H:i:s');
		$result = $db->pquery('SELECT date_start, time_start, time_end FROM vtiger_activity '
			. 'INNER JOIN vtiger_crmentity ON vtiger_activity.activityid=vtiger_crmentity.crmid '
			. 'WHERE vtiger_activity.deleted=? AND vtiger_crmentity.smownerid=? '
			. "AND ( (concat(date_start, ' ', time_start)  >= ? AND concat(date_start, ' ', time_start) <= ?) OR (concat(due_date, ' ', time_end)  >= ? AND concat(due_date, ' ', time_end) <= ?) OR (date_start < ? AND due_date > ?) ) "
			. "ORDER BY time_start ASC", $params);
		while ($row = $db->getRow($result)) {
			if (vtlib\Functions::getDateTimeMinutesDiff($startTime, $row['time_start']) >= $durationEvent) {
				$date = new DateTime($row['date_start'] . ' ' . $startTime);
				$startTime = new DateTimeField($startTime);
				$date->add(new DateInterval('PT' . $durationEvent . 'M0S'));
				$endHour = new DateTimeField(date_format($date, 'H:i:s'));
				return ['day' => $day, 'time_start' => $startTime->getDisplayTime(), 'time_end' => $endHour->getDisplayTime()];
			} else {
				$startTime = $row['time_end'];
			}
		}
		$date = new DateTime($day . ' ' . $startTime);
		$startTime = new DateTimeField($startTime);
		$date->add(new DateInterval('PT' . $durationEvent . 'M0S'));
		$dbEndWorkHour = $dbEndDateObject->format('H:i:s');
			
		if (vtlib\Functions::getDateTimeMinutesDiff(date_format($date, 'H:i:s'), $dbEndWorkHour) <= 0) {
			$date->add(new DateInterval('P1D'));
			while( in_array(date_format($date, 'w'), AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW'))) {
				$date->add(new DateInterval('P1D'));
			}
			return $this->getFreeTimeInDay(date_format($date, 'Y-m-d'));
		} else {
			$endHour = new DateTimeField(date_format($date, 'H:i:s'));
			return ['day' => $day, 'time_start' => $startTime->getDisplayTime(), 'time_end' => $endHour->getDisplayTime()];
		}
	}

	public function process(Vtiger_Request $request)
	{
		$dateStart = $request->get('dateStart');
		$startDate = $this->getFreeTimeInDay($dateStart);
		$data ['time_start'] = $startDate['time_start'];
		$data ['date_start'] = $startDate['day'];
		$data ['time_end'] = $startDate['time_end'];
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
