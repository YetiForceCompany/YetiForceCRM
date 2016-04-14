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
		$startHour = $startWorkHour;
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT time_start, time_end FROM vtiger_activity '
			. 'INNER JOIN vtiger_crmentity ON vtiger_activity.activityid=vtiger_crmentity.crmid '
			. 'WHERE vtiger_activity.date_start=? AND vtiger_activity.deleted=? AND vtiger_crmentity.smownerid=? '
			. 'ORDER BY vtiger_activity.time_start ASC', [$day, 0, $currentUser->getId()]);
		while ($row = $db->getRow($result)) {
			if (Vtiger_Functions::getDateTimeMinutesDiff($startHour, $row['time_start']) >= $durationEvent) {
				$tempDate = $day . ' ' . $startHour;
				$date = new DateTime($tempDate);
				$date->add(new DateInterval('PT' . $durationEvent . 'M0S'));
				$startHour = new DateTimeField($startHour);
				$endHour = new DateTimeField(date_format($date, 'H:i:s'));
				return ['day' => $day, 'time_start' => $startHour->getDisplayTime(), 'time_end' => $endHour->getDisplayTime()];
			} else {
				$startHour = $row['time_end'];
			}
		}
		$tempDate = $day . ' ' . $startHour;
		$date = new DateTime($tempDate);
		$date->add(new DateInterval('PT' . $durationEvent . 'M0S'));
		if (Vtiger_Functions::getDateTimeMinutesDiff(date_format($date, 'H:i:s'), $endWorkHour) <= 0) {
			$date->add(new DateInterval('P1D'));
			return $this->getFreeTimeInDay(date_format($date, 'Y-m-d'));
		} else {
			$startHour = new DateTimeField($startHour);
			$endHour = new DateTimeField(date_format($date, 'H:i:s'));
			return ['day' => $day, 'time_start' => $startHour->getDisplayTime(), 'time_end' => $endHour->getDisplayTime()];
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
