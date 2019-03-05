<?php

/**
 * Project ProjectHandler handler class.
 *
 * @package   Handler
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Project_ProjectHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterSave(\App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (!$recordModel->isNew()) {
			if (($value = $recordModel->getPreviousValue('parentid')) !== false) {
				if (!empty($recordModel->get('parentid'))) {
					(new \App\BatchMethod(['method' => 'Project_Module_Model::updateProgress', 'params' => [$recordModel->get('parentid')]]))->save();
				}
				if (!empty($value)) {
					(new \App\BatchMethod(['method' => 'Project_Module_Model::updateProgress', 'params' => [$value]]))->save();
				}
			}
		}
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityChangeState(\App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if (!$recordModel->isEmpty('parentid')) {
			(new \App\BatchMethod(['method' => 'Project_Module_Model::updateProgress', 'params' => [$recordModel->get('parentid')]]))->save();
		}
	}
}
