<?php

/**
 * Calendar Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_CalendarHandler_Handler
{
	const UPDATE_FIELDS = ['link', 'process', 'subprocess'];

	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		if (vtlib\Cron::isCronAction()) {
			return false;
		}
		$recordModel = $eventHandler->getRecordModel();
		$ids = [];
		foreach (static::UPDATE_FIELDS as &$fieldName) {
			if (!$recordModel->isEmpty($fieldName)) {
				$ids[$recordModel->get($fieldName)] = $fieldName;
			}
			if ($recordModel->getPreviousValue($fieldName)) {
				$ids[$recordModel->getPreviousValue($fieldName)] = $fieldName;
			}
		}
		Calendar_Record_Model::setCrmActivity($ids);
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$ids = [];
		foreach (static::UPDATE_FIELDS as &$fieldName) {
			if (!$recordModel->isEmpty($fieldName)) {
				$ids[$recordModel->get($fieldName)] = $fieldName;
			}
		}
		Calendar_Record_Model::setCrmActivity($ids);
	}

	/**
	 * EntityAfterUnLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterUnLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$fieldName = \App\ModuleHierarchy::getMappingRelatedField($params['sourceModule']);
		Calendar_Record_Model::setCrmActivity([$params['sourceRecordId'] => $fieldName]);
	}

	/**
	 * EntityBeforeSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->get('allday') && ($recordModel->isNew() || $recordModel->getPreviousValue('allday') !== false)) {
			$userModel = \App\User::getUserModel($recordModel->get('assigned_user_id'));
			$recordModel->set('time_start', $userModel->getDetail('start_hour') . ':00');
			$recordModel->set('time_end', $userModel->getDetail('end_hour') . ':00');
		}
		$minutes = \App\Fields\Date::getDiff($recordModel->get('date_start') . ' ' . $recordModel->get('time_start'), $recordModel->get('due_date') . ' ' . $recordModel->get('time_end'), 'minutes');
		$hours = floor($minutes / 60);
		$recordModel->set('duration_hours', $hours);
		$recordModel->set('duration_minutes', $minutes - ($hours * 60));
		if (!vtlib\Cron::isCronAction() && ($state = Calendar_Module_Model::getCalendarState($recordModel->getData()))) {
			$recordModel->set('activitystatus', $state);
		}
	}
}
