<?php

/**
 * ProjectTask ProjectTaskHandler handler class.
 *
 * @package   Handler
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class ProjectTask_ProjectTaskHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterSave(\App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->isNew()) {
			ProjectMilestone_Module_Model::updateProgress($recordModel->get('projectmilestoneid'));
		} else {
			$delta = $recordModel->getPreviousValue();
			$calculateMilestone = $calculateProject = [];
			foreach ($delta as $name => $value) {
				if ($name === 'projectmilestoneid' || $name === 'estimated_work_time' || $name === 'projecttaskprogress') {
					if ($name === 'projectmilestoneid') {
						$calculateMilestone[$recordModel->get($name)] = true;
						$calculateMilestone[$value] = true;
					} else {
						$calculateMilestone[$recordModel->get('projectmilestoneid')] = true;
					}
					$calculateProject[$recordModel->get('projectid')] = true;
				} elseif ($name === 'projectid') {
					$calculateProject[$recordModel->get($name)] = true;
					$calculateProject[$value] = true;
				}
			}
			foreach ($calculateMilestone as $milestoneId => $val) {
				ProjectMilestone_Module_Model::updateProgress($milestoneId);
			}
			foreach ($calculateProject as $projectId => $val) {
				Project_Module_Model::updateProgress($projectId);
			}
		}
	}

	/**
	 * EntityAfterDelete handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(\App\EventHandler $eventHandler)
	{
		ProjectMilestone_Module_Model::updateProgress($eventHandler->getRecordModel()->get('projectmilestoneid'));
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityChangeState(\App\EventHandler $eventHandler)
	{
		ProjectMilestone_Module_Model::updateProgress($eventHandler->getRecordModel()->get('projectmilestoneid'));
	}
}
