<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class ProjectMilestone_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param Vtiger_ListView_Model $listviewModel
	 * @param \App\QueryGenerator   $queryGenerator
	 */
	public function getQueryByRelatedField(Vtiger_ListView_Model $listviewModel, \App\QueryGenerator $queryGenerator)
	{
		if ($listviewModel->get('src_module') === 'Project' && !$listviewModel->isEmpty('filterFields')) {
			$filterFields = $listviewModel->get('filterFields');
			if (!empty($filterFields['projectid'])) {
				$queryGenerator->addNativeCondition(['projectid' => $filterFields['projectid']]);
			}
		}
	}

	/**
	 * Update progress milestone.
	 *
	 * @param int $id
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function updateProgressMilestone(int $id, float $estimatedWorkTime = 0, float $progressInHours = 0, ?int $callerId = null)
	{
		if (!App\Record::isExists($id)) {
			return [];
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($id);
		foreach ($recordModel->getChildren() as $childRecordModel) {
			if ($callerId !== $childRecordModel->getId()) {
				$childEstimatedWorkTime = $childRecordModel->getEstimatedWorkTime();
				$estimatedWorkTime += $childEstimatedWorkTime;
				$progressInHours += ($childEstimatedWorkTime * $childRecordModel->getProgress() / 100);
			}
		}
		$this->calculateProgressOfTasks($recordModel, $estimatedWorkTime, $progressInHours);
		$projectProgress = $estimatedWorkTime ? round((100 * $progressInHours) / $estimatedWorkTime) : 0;
		$recordModel->set('projectmilestone_progress', $projectProgress);
		$recordModel->save();
		if ($recordModel->isEmpty('parentid')) {
			Vtiger_Module_Model::getInstance('Project')->updateProgress($recordModel->get('projectid'));
		} else {
			$this->updateProgressMilestone(
				$recordModel->get('parentid'),
				$estimatedWorkTime,
				$progressInHours,
				$id
			);
		}
	}

	public function calculateEstimatedWorkTime(int $id, float $estimatedWorkTime = 0): float
	{
		if (!App\Record::isExists($id)) {
			return 0;
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($id);
		$progressInHours = 0;
		foreach ($recordModel->getChildren() as $childRecordModel) {
			$estimatedWorkTime += $childRecordModel->getEstimatedWorkTime();
		}
		$this->calculateProgressOfTasks($recordModel, $estimatedWorkTime, $progressInHours);
		return $estimatedWorkTime;
	}

	/**
	 * Calculate the progress of tasks.
	 *
	 * @param \Project_Record_Model $recordModel
	 * @param float                 $estimatedWorkTime
	 * @param float                 $progressInHours
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
}
