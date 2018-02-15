<?php

/**
 * Action to get free time for events.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_GetFreeTime_Action extends Vtiger_BasicAjax_Action
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
		$dbStartDate = $dbStartDateOject->format('Y-m-d');
		$dbEndDate = $dbEndDateObject->format('Y-m-d');

		$db = App\Db::getInstance();
		$startTime = $dbStartDateOject->format('H:i:s');
		$dataReader = (new \App\Db\Query())->select(['date_start', 'time_start', 'time_end'])
			->from('vtiger_activity')
			->where([
					'and',
					['deleted' => 0],
					['smownerid' => $currentUser->getId()],
					['or',
						['and',
							['>=', new \yii\db\Expression('CONCAT(date_start, ' . $db->quoteValue(' ') . ', time_start)'), $dbStartDateTime],
							['<=', new \yii\db\Expression('CONCAT(date_start, ' . $db->quoteValue(' ') . ', time_start)'), $dbEndDateTime],
						],
						['and',
							['>=', new \yii\db\Expression('CONCAT(due_date, ' . $db->quoteValue(' ') . ', time_end)'), $dbStartDateTime],
							['<=', new \yii\db\Expression('CONCAT(due_date, ' . $db->quoteValue(' ') . ', time_end)'), $dbEndDateTime],
						],
						['and',
							['<', 'date_start', $dbStartDate],
							['>', 'due_date', $dbEndDate],
						],
					],
				])->orderBy(['time_start' => SORT_ASC])
					->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (\App\Fields\Date::getDiff($startTime, $row['time_start'], 'minutes') >= $durationEvent) {
				$date = new DateTime($row['date_start'] . ' ' . $startTime);
				$startTime = new DateTimeField($startTime);
				$date->add(new DateInterval('PT' . $durationEvent . 'M0S'));
				$endHour = new DateTimeField(date_format($date, 'H:i:s'));

				return ['day' => $day, 'time_start' => $startTime->getDisplayTime(), 'time_end' => $endHour->getDisplayTime()];
			} else {
				$startTime = $row['time_end'];
			}
		}
		$dataReader->close();
		$date = new DateTime($day . ' ' . $startTime);
		$startTime = new DateTimeField($startTime);
		$date->add(new DateInterval('PT' . $durationEvent . 'M0S'));
		$dbEndWorkHour = $dbEndDateObject->format('H:i:s');
		if (\App\Fields\Date::getDiff(date_format($date, 'H:i:s'), $dbEndWorkHour, 'minutes') <= 0) {
			$date->add(new DateInterval('P1D'));
			while (in_array(date_format($date, 'w'), AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW'))) {
				$date->add(new DateInterval('P1D'));
			}

			return $this->getFreeTimeInDay(date_format($date, 'Y-m-d'));
		} else {
			$endHour = new DateTimeField(date_format($date, 'H:i:s'));

			return ['day' => $day, 'time_start' => $startTime->getDisplayTime(), 'time_end' => $endHour->getDisplayTime()];
		}
	}

	public function process(\App\Request $request)
	{
		$dateStart = DateTimeField::convertToDBFormat($request->getByType('dateStart', 'DateInUserFormat'));
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$startWorkHour = $currentUser->get('start_hour');
		$endWorkHour = $currentUser->get('end_hour');
		if (\App\Fields\Date::getDiff($startWorkHour, $endWorkHour, 'minutes') > 0) {
			$startDate = $this->getFreeTimeInDay($dateStart);
			$data['time_start'] = $startDate['time_start'];
			$data['date_start'] = DateTimeField::convertToUserFormat($startDate['day']);
			$data['time_end'] = $startDate['time_end'];
		} else {
			$data['time_start'] = $startWorkHour;
			$data['date_start'] = $request->getByType('dateStart', 'DateInUserFormat');
			$data['time_end'] = $startWorkHour;
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
