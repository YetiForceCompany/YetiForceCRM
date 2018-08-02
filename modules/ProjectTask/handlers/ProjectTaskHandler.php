<?php

/**
 * ProjectTask ProjectTaskHandler handler class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class ProjectTask_ProjectTaskHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->isNew()) {
			Vtiger_Module_Model::getInstance('ProjectMilestone')->updateProgressMilestone($recordModel->get('projectmilestoneid'));
		} else {
			$delta = $recordModel->getPreviousValue();
			foreach ($delta as $name => &$value) {
				if ($name === 'projectmilestoneid' || $name === 'estimated_work_time' || $name === 'projecttaskprogress') {
					$moduledModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
					if ($name === 'projectmilestoneid') {
						$moduledModel->updateProgressMilestone($recordModel->get($name));
						$moduledModel->updateProgressMilestone($value);
					} else {
						$moduledModel->updateProgressMilestone($recordModel->get('projectmilestoneid'));
					}
				}
			}
		}
	}

	/**
	 * EntityAfterDelete handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		Vtiger_Module_Model::getInstance('ProjectMilestone')->updateProgressMilestone($eventHandler->getRecordModel()->get('projectmilestoneid'));
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		Vtiger_Module_Model::getInstance('ProjectMilestone')->updateProgressMilestone($eventHandler->getRecordModel()->get('projectmilestoneid'));
	}
}
