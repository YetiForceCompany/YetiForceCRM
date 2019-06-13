<?php

/**
 * Record of status history handler.
 *
 * @package Handler
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RecordStatusHistory_Handler
{
	/**
	 * EntityBeforeSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (($fieldStatusName = App\RecordStatus::getFieldName($recordModel->getModuleName()))) {
			if ($recordModel->isNew()) {
				\App\Utils\ServiceContracts::updateExpectedTimes($recordModel);
			} elseif ($recordModel->getPreviousValue($fieldStatusName)) {
				App\RecordStatus::update($recordModel, $fieldStatusName);
				if (\in_array($recordModel->get($fieldStatusName), \App\RecordStatus::getStates($recordModel->getModuleName(), \App\RecordStatus::RECORD_STATE_CLOSED))) {
					$recordModel->set('closing_range_time', App\RecordStatus::getDiff($recordModel->get('createdtime'), '', $recordModel));
					$recordModel->set('closing_datatime', date('Y-m-d H:i:s'));
				}
			}
		}
	}

	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (($fieldStatusName = App\RecordStatus::getFieldName($recordModel->getModuleName())) && ($recordModel->isNew() || $recordModel->getPreviousValue($fieldStatusName))) {
			App\RecordStatus::addHistory($recordModel, $fieldStatusName);
		}
	}
}
