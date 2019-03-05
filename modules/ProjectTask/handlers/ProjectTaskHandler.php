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
			(new \App\BatchMethod(['method' => 'Project_Module_Model::updateProgress', 'params' => [$recordModel->get('projectmilestoneid')]]))->save();
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
				(new \App\BatchMethod(['method' => 'ProjectMilestone_Module_Model::updateProgress', 'params' => [$milestoneId]]))->save();
			}
			foreach ($calculateProject as $projectId => $val) {
				(new \App\BatchMethod(['method' => 'Project_Module_Model::updateProgress', 'params' => [$projectId]]))->save();
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
		(new \App\BatchMethod(['method' => 'ProjectMilestone_Module_Model::updateProgress', 'params' => [$eventHandler->getRecordModel()->get('projectmilestoneid')]]))->save();
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityChangeState(\App\EventHandler $eventHandler)
	{
		(new \App\BatchMethod(['method' => 'ProjectMilestone_Module_Model::updateProgress', 'params' => [$eventHandler->getRecordModel()->get('projectmilestoneid')]]))->save();
	}
}
