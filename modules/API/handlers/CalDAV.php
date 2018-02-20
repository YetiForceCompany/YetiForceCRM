<?php

/**
 * Api CalDAV Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_CalDAV_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (!$recordModel->isNew()) {
			\App\Db::getInstance()->createCommand()->update('vtiger_activity', ['dav_status' => 1], ['activityid' => $recordModel->getId()])->execute();
		}
	}
}
