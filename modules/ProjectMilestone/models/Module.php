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
	 * Get children by parent ID.
	 *
	 * @param int $id
	 *
	 * @return int[]
	 */
	protected static function getChildren(int $id): array
	{
		$queryGenerator = new \App\QueryGenerator('ProjectMilestone');
		$queryGenerator->permissions = false;
		$queryGenerator->addNativeCondition(['parentid' => $id]);
		return $queryGenerator->createQuery()->select(['id' => 'projectmilestoneid'])->column();
	}

	/**
	 * Calculate the progress of tasks.
	 *
	 * @param int   $id
	 * @param float $estimatedWorkTime
	 * @param float $progressInHours
	 *
	 * @throws \App\Exceptions\AppException
	 */
	protected static function calculateProgressOfTasks(int $id, float &$estimatedWorkTime, float &$progressInHours)
	{
		$relatedListView = Vtiger_RelationListView_Model::getInstance(Vtiger_Record_Model::getInstanceById($id), 'ProjectTask');
		$row = $relatedListView->getRelationQuery()->select(
			[
				'estimated_work_time' => new \yii\db\Expression('SUM(estimated_work_time)'),
				'progress_in_hours' => new \yii\db\Expression('SUM(estimated_work_time * projecttaskprogress / 100)')
			]
		)->one();
		if ($row !== false && !is_null($row['estimated_work_time'])) {
			$estimatedWorkTime += (float) $row['estimated_work_time'];
			$progressInHours += (float) $row['progress_in_hours'];
		}
	}

	/**
	 * Calculate estimated work time.
	 *
	 * @param int   $id
	 * @param float $estimatedWorkTime
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return float
	 */
	public static function calculateEstimatedWorkTime(int $id, float $estimatedWorkTime = 0): float
	{
		$progressInHours = 0;
		foreach (static::getChildren($id) as $childId) {
			$estimatedWorkTime += static::calculateEstimatedWorkTime($childId);
		}
		static::calculateProgressOfTasks($id, $estimatedWorkTime, $progressInHours);
		return $estimatedWorkTime;
	}

	/**
	 * Update progress milestone.
	 *
	 * @param int      $id
	 * @param float    $estimatedWorkTime
	 * @param float    $progressInHours
	 * @param int|null $callerId
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function updateProgressMilestone(int $id, float $estimatedWorkTime = 0, float $progressInHours = 0, ?int $callerId = null)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($id);
		foreach (static::getChildren($id) as $childId) {
			if ($callerId !== $childId) {
				$childEstimatedWorkTime = static::calculateEstimatedWorkTime($childId);
				$estimatedWorkTime += $childEstimatedWorkTime;
				$progressInHours += ($childEstimatedWorkTime * Vtiger_Record_Model::getInstanceById($childId)->get('projectmilestone_progress') / 100);
			}
		}
		static::calculateProgressOfTasks($id, $estimatedWorkTime, $progressInHours);
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
}
