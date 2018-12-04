<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Project_Module_Model extends Vtiger_Module_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_VIEW_GANTT',
			'linkurl' => 'index.php?module=Project&view=Gantt',
			'linkicon' => 'fas fa-briefcase',
		]);
		return $links;
	}

	/**
	 * Update progress in project.
	 *
	 * @param int $id
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function updateProgress(int $id, float $estimatedWorkTime = 0, float $progressInHours = 0, ?int $parentId = null): array
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($id);
		if (empty($parentId)) {
			foreach ($this->getChildren($id) as $childId) {
				$childRecordModel = Vtiger_Record_Model::getInstanceById($childId);
				//$childEstimatedWorkTime = $childRecordModel->getEstimatedWorkTime();
				$childEstimatedWorkTime = $this->calculateEstimatedWorkTime($childRecordModel);
				$estimatedWorkTime += $childEstimatedWorkTime;
				$progressInHours += ($childEstimatedWorkTime * $childRecordModel->get('progress') / 100);
			}
		}
		$this->calculateProgressOfTasks($recordModel, $estimatedWorkTime, $progressInHours);
		$projectProgress = $estimatedWorkTime ? round((100 * $progressInHours) / $estimatedWorkTime) : 0;
		$recordModel->set('progress', $projectProgress);
		$recordModel->save();
		if (!$recordModel->isEmpty('parentid') && $recordModel->get('parentid') !== $parentId) {
			$this->updateProgress(
				$recordModel->get('parentid'),
				$estimatedWorkTime,
				$progressInHours,
				$id
			);
		}
		return [
			'estimatedWorkTime' => $estimatedWorkTime,
			'projectProgress' => $projectProgress
		];
	}

	/**
	 * Calculate the progress of children.
	 *
	 * @param int   $id
	 * @param float $estimatedWorkTime
	 * @param float $progressInHours
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function calculateProgressOfChildren(int $id, float $estimatedWorkTime = 0, float $progressInHours = 0)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($id);
		foreach ($this->getChildren() as $childId) {
			$progressItem = $this->calculateProgressOfChildren($childId);
			$estimatedWorkTime += $progressItem['estimatedWorkTime'];
			$progressInHours += ($progressItem['estimatedWorkTime'] * $progressItem['projectProgress']) / 100;
		}
		$this->calculateProgressOfTasks($recordModel, $estimatedWorkTime, $progressInHours);
		if ($estimatedWorkTime) {
			$projectProgress = ((100 * $progressInHours) / $estimatedWorkTime);
		} else {
			$projectProgress = 0;
		}
		return [
			'estimatedWorkTime' => $estimatedWorkTime,
			'projectProgress' => $projectProgress
		];
	}

	/**
	 * Calculate the progress of tasks.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param float                $estimatedWorkTime
	 * @param float                $progressInHours
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function calculateProgressOfTasks(\Vtiger_Record_Model $recordModel, float &$estimatedWorkTime, float &$progressInHours)
	{
		$relatedListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'ProjectTask');
		$relatedListView->getRelationModel()->set('QueryFields', [
			'estimated_work_time' => 'estimated_work_time',
			'projecttaskprogress' => 'projecttaskprogress',
		]);
		$dataReader = $relatedListView->getRelationQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$estimatedWorkTime += $row['estimated_work_time'];
			$progressInHours += ($row['estimated_work_time'] * (int) $row['projecttaskprogress']) / 100;
		}
		$dataReader->close();
	}

	/**
	 * Calculate the progress of milestones.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param float                $estimatedWorkTime
	 * @param float                $progressInHours
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function calculateProgressOfMilestones(\Vtiger_Record_Model $recordModel, float &$estimatedWorkTime, float &$progressInHours)
	{
		$relatedListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'ProjectMilestone');
		$relatedListView->getRelationModel()->set('QueryFields', [
			'projectmilestone_progress' => 'projectmilestone_progress',
		]);
		$dataReader = $relatedListView->getRelationQuery()
			->andWhere(['or', ['parentid' => 0], ['parentid' => null]])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			//$milestoneEstimatedWorkTime = Vtiger_Record_Model::getInstanceById($row['id'])->getEstimatedWorkTime();
			$milestoneEstimatedWorkTime = Vtiger_Record_Model::getInstanceById($row['id'])->getEstimatedWorkTime();
			$estimatedWorkTime += $milestoneEstimatedWorkTime;
			$progressInHours += ($milestoneEstimatedWorkTime * (int) $row['projectmilestone_progress']) / 100;
		}
		$dataReader->close();
	}

	/**
	 * Calculate estimated work time.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param float                $estimatedWorkTime
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return float
	 */
	public function calculateEstimatedWorkTime(\Vtiger_Record_Model $recordModel, float $estimatedWorkTime = 0): float
	{
		$progressInHours = 0;
		foreach ($this->getChildren($recordModel->getId()) as $childId) {
			$estimatedWorkTime += $this->calculateEstimatedWorkTime(Vtiger_Record_Model::getInstanceById($childId));
		}
		$this->calculateProgressOfMilestones($recordModel, $estimatedWorkTime, $progressInHours);
		return $estimatedWorkTime;
	}

	/**
	 * Get children by parent ID.
	 *
	 * @param int $id
	 *
	 * @return int[]
	 */
	public function getChildren(int $id): array
	{
		$queryGenerator = new \App\QueryGenerator('Project');
		$queryGenerator->addNativeCondition(['parentid' => $id]);
		return $queryGenerator->createQuery()->select(['id' => 'projectid'])->column();
	}
}
