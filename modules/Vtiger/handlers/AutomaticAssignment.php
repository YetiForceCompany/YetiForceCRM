<?php

/**
 * Automatic assignment Handler Class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_AutomaticAssignment_Handler
{

	/**
	 * EntitySystemAfterCreate handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entitySystemAfterCreate(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:AutomaticAssignment');
		$autoAssignRecord = $moduleInstance->searchRecord($recordModel);
		if ($autoAssignRecord) {
			$users = $autoAssignRecord->getUsers();
			if ($users) {
				$assignUser = $autoAssignRecord->getAssignUser($users);
				if ($assignUser && $assignUser !== $recordModel->get('assigned_user_id')) {
					$recordModel->set('assigned_user_id', $assignUser);
					$recordModel->set('mode', 'edit');
					$recordModel->save();
				}
			}
		}
	}
}
