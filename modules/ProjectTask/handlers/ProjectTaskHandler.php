<?php

/**
 * ProjectTask ProjectTaskHandler handler class.
 *
 * @package   Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class ProjectTask_ProjectTaskHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->isNew()) {
			(new \App\BatchMethod(['method' => 'Project_Module_Model::updateProgress', 'params' => [$recordModel->get('projectmilestoneid')]]))->save();
		} else {
			$delta = $recordModel->getPreviousValue();
			$calculateMilestone = $calculateProject = [];
			foreach ($delta as $name => $value) {
				if ('projectmilestoneid' === $name || 'estimated_work_time' === $name || 'projecttaskprogress' === $name) {
					if ('projectmilestoneid' === $name) {
						$calculateMilestone[$recordModel->get($name)] = true;
						$calculateMilestone[$value] = true;
					} else {
						$calculateMilestone[$recordModel->get('projectmilestoneid')] = true;
					}
					$calculateProject[$recordModel->get('projectid')] = true;
				} elseif ('projectid' === $name) {
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
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		(new \App\BatchMethod(['method' => 'ProjectMilestone_Module_Model::updateProgress', 'params' => [$recordModel->get('projectmilestoneid')]]))->save();
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		(new \App\BatchMethod(['method' => 'ProjectMilestone_Module_Model::updateProgress', 'params' => [$recordModel->get('projectmilestoneid')]]))->save();
	}
}
