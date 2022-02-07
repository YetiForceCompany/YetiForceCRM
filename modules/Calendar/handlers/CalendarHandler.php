<?php

/**
 * Calendar Handler Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Calendar_CalendarHandler_Handler
{
	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$ids = [];
		$recordModel = $eventHandler->getRecordModel();
		foreach ($recordModel->getModule()->getFieldsByReference() as $fieldModel) {
			if (!$fieldModel->isActiveField()) {
				continue;
			}
			if (!$recordModel->isEmpty($fieldModel->getName())) {
				$ids[$recordModel->get($fieldModel->getName())] = $fieldModel->getName();
			}
			if ($recordModel->getPreviousValue($fieldModel->getName())) {
				$ids[$recordModel->getPreviousValue($fieldModel->getName())] = $fieldModel->getName();
			}
		}
		if ($ids) {
			(new \App\BatchMethod(['method' => 'Calendar_Record_Model::setCrmActivity', 'params' => [$ids]]))->save();
		}
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		$this->entityAfterSave($eventHandler);
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
		if ($recordModel->get('allday') && ($recordModel->isNew() || false !== $recordModel->getPreviousValue('allday'))) {
			$userModel = \App\User::getUserModel($recordModel->get('assigned_user_id'));
			$recordModel->set('time_start', $userModel->getDetail('start_hour') . ':00');
			$recordModel->set('time_end', $userModel->getDetail('end_hour') . ':00');
		}
		$minutes = \App\Fields\DateTime::getDiff($recordModel->get('date_start') . ' ' . $recordModel->get('time_start'), $recordModel->get('due_date') . ' ' . $recordModel->get('time_end'), 'minutes');
		$hours = floor($minutes / 60);
		$recordModel->set('duration_hours', $hours);
		$recordModel->set('duration_minutes', $minutes - ($hours * 60));
		if (!vtlib\Cron::isCronAction() && ($state = Calendar_Module_Model::getCalendarState($recordModel->getData()))) {
			$recordModel->set('activitystatus', $state);
		}
	}
}
