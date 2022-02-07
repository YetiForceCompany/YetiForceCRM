<?php

/**
 * Automatic assignment Handler - file.
 *
 * @package		Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Automatic assignment Handler Class.
 */
class Vtiger_AutoAssign_Handler
{
	/**
	 * EntityBeforeSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler): void
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->isNew() && ($autoAssignModel = \App\AutoAssign::getAutoAssignForRecord($recordModel, \App\AutoAssign::MODE_HANDLER)) && ($assignedUserId = $autoAssignModel->getOwner())) {
			$fieldModel = $recordModel->getField('assigned_user_id');
			$recordModel->set($fieldModel->getName(), $assignedUserId);
			$recordModel->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => $assignedUserId]]);
			$autoAssignModel->postProcess($assignedUserId);
		}
	}
}
