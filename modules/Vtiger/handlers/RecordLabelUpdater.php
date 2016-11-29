<?php

/**
 * Abstract base handler class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RecordLabelUpdater_Handler
{

	/**
	 * Entity.AfterSave function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		\App\Record::updateLabel($eventHandler->getModuleName(), $recordModel->getId(), $recordModel->get('mode'));
	}
}
