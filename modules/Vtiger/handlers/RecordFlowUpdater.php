<?php
/**
 * @package   Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_RecordFlowUpdater_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		(new \App\Automatic\RecordFlowUpdater($recordModel->getModuleName()))->entityAfterSave($recordModel);
	}

	/**
	 * EntityAfterDelete handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		(new \App\Automatic\RecordFlowUpdater($recordModel->getModuleName()))->entityAfterDelete($recordModel);
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		(new \App\Automatic\RecordFlowUpdater($recordModel->getModuleName()))->entityChangeState($recordModel);
	}
}
