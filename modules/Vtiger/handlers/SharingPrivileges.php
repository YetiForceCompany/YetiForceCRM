<?php

/**
 * Sharing privileges handler
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_SharingPrivileges_Handler
{

	/**
	 * Entity.AfterSave function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		if (!\AppConfig::security('PERMITTED_BY_SHARED_OWNERS')) {
			return false;
		}
		$recordModel = $eventHandler->getRecordModel();
		$oldValue = $recordModel->getChanges('assigned_user_id');
		if ($oldValue) {
			$currentValue = $recordModel->get('assigned_user_id');
			$addUsers = $currentValue;
			$removeUser = array_diff($oldValue, $currentValue);
			Users_Privileges_Model::setSharedOwnerRecursively($recordModel->getId(), $addUsers, $removeUser, $recordModel->getModuleName());
		}
	}
}
