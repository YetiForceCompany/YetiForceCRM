<?php

/**
 * Calendar Handler Class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_CalendarHandler_Handler
{

	const UPDATE_FIELDS = ['link', 'process', 'subprocess'];

	/**
	 * EntityAfterSave function
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
	 * EntityAfterRestore handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterRestore(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		foreach (static::UPDATE_FIELDS as &$fieldName) {
			if (!$recordModel->isEmpty($fieldName)) {
				$ids[$recordModel->get($fieldName)] = $fieldName;
			}
		}
		Calendar_Record_Model::setCrmActivity($ids);
	}

	/**
	 * EntityAfterUnLink handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterUnLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$fieldName = \App\ModuleHierarchy::getMappingRelatedField($params['sourceModule']);
		Calendar_Record_Model::setCrmActivity([$params['sourceRecordId'] => $fieldName]);
	}

	/**
	 * EntityBeforeSave handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		if (vtlib\Cron::isCronAction()) {
			return false;
		}
		$recordModel = $eventHandler->getRecordModel();
		$data = $recordModel->getData();
		$state = Calendar_Module_Model::getCalendarState($data);
		if ($state) {
			$recordModel->set('activitystatus', $state);
		}
	}
}
