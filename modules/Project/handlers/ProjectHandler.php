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
			$delta = $recordModel->getPreviousValue();
			foreach ($delta as $name => $value) {
				if ($name === 'parentid') {
					$projectModel = Vtiger_Module_Model::getInstance('Project');
					if (!empty($recordModel->get($name))) {
						$projectModel->updateProgress($recordModel->get($name));
					}
					if (!empty($value)) {
						$projectModel->updateProgress($value);
					}
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
			Vtiger_Module_Model::getInstance('Project')->updateProgress($recordModel->get('parentid'));
		}
	}
}
