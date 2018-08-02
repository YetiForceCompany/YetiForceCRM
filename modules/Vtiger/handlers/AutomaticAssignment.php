<?php

/**
 * Automatic assignment Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_AutomaticAssignment_Handler
{
	/**
	 * EntitySystemAfterCreate handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entitySystemAfterCreate(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		Settings_AutomaticAssignment_Module_Model::autoAssignExecute($recordModel);
	}
}
