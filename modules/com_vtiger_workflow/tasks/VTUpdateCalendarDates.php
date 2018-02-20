<?php

/**
 * Update the dates of created events automatically.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class VTUpdateCalendarDates extends VTTask
{
	/**
	 * Execute immediately.
	 *
	 * @var bool
	 */
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return array
	 */
	public function getFieldNames()
	{
		return [];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$entityId = $recordModel->getId();

		$delta = $recordModel->getPreviousValue();
		if (!$delta) {
			return;
		}
		$query = (new \App\Db\Query())->from('vtiger_activity_update_dates')->innerJoin('com_vtiger_workflowtasks', 'com_vtiger_workflowtasks.task_id = vtiger_activity_update_dates.task_id')->where(['vtiger_activity_update_dates.parent' => $entityId]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$task = new \ArrayObject(unserialize($row['task']));
			$rowRecordModel = Vtiger_Record_Model::getInstanceById($row['activityid'], 'Calendar');

			if ($task['datefield_start'] === 'wfRunTime') {
				$baseDateStart = date('Y-m-d H:i:s');
			} else {
				$baseDateStart = $recordModel->get($task['datefield_start']);
				if ($baseDateStart === '') {
					$baseDateStart = date('Y-m-d');
				}
			}

			$time = explode(' ', $baseDateStart);
			if (count($time) < 2) {
				$timeWithSec = Vtiger_Time_UIType::getTimeValueWithSeconds($task['time']);
				$dbInsertDateTime = DateTimeField::convertToDBTimeZone($baseDateStart . ' ' . $timeWithSec);
				$time = $dbInsertDateTime->format('H:i:s');
			} else {
				$time = $time[1];
			}
			preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDateStart, $match);
			$baseDateStart = strtotime($match[0]);

			if ($task['datefield_end'] === 'wfRunTime') {
				$baseDateEnd = date('Y-m-d H:i:s');
			} else {
				$baseDateEnd = $recordModel->get($task['datefield_end']);
				if ($baseDateEnd === '') {
					$baseDateEnd = date('Y-m-d');
				}
			}
			$timeEnd = explode(' ', $baseDateEnd);
			if (count($timeEnd) < 2) {
				$userId = $rowRecordModel->get('assigned_user_id');
				if ($userId === null) {
					$userId = 1;
				}
				$result = (new \App\Db\Query())->select(['end_hour'])->from('vtiger_users')->where(['id' => $userId])->scalar();
				if ($result) {
					$timeEnd = $result;
					$timeWithSec = Vtiger_Time_UIType::getTimeValueWithSeconds($timeEnd);
					$dbInsertDateTime = DateTimeField::convertToDBTimeZone($baseDateEnd . ' ' . $timeWithSec);
					$timeEnd = $dbInsertDateTime->format('H:i:s');
				} else {
					$adminUser = Users::getActiveAdminUser();
					$timeEnd = $adminUser->column_fields['end_hour'];
					$timeWithSec = Vtiger_Time_UIType::getTimeValueWithSeconds($timeEnd);
					$dbInsertDateTime = DateTimeField::convertToDBTimeZone($baseDateEnd . ' ' . $timeWithSec);
					$timeEnd = $dbInsertDateTime->format('H:i:s');
				}
			} else {
				$timeEnd = $timeEnd[1];
			}
			preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDateEnd, $match);
			$baseDateEnd = strtotime($match[0]);

			$date_start = strftime('%Y-%m-%d', $baseDateStart + $task['days_start'] * 24 * 60 * 60 * (strtolower($task['direction_start']) == 'before' ? -1 : 1));
			$due_date = strftime('%Y-%m-%d', $baseDateEnd + $task['days_end'] * 24 * 60 * 60 * (strtolower($task['direction_start']) == 'before' ? -1 : 1));

			$rowRecordModel->set('date_start', $date_start);
			$rowRecordModel->set('due_date', $due_date);
			$rowRecordModel->save();
		}
	}
}
