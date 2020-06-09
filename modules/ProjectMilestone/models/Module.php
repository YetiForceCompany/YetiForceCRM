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
	 * Cache for estimated work time.
	 *
	 * @var float[]
	 */
	protected static $cacheEstimatedWorkTime = [];

	/**
	 * Get children by parent ID.
	 *
	 * @param int $id
	 *
	 * @return int[]
	 */
	protected static function getChildren(int $id): array
	{
		return (new \App\Db\Query())
			->select(['id' => 'vtiger_projectmilestone.projectmilestoneid', 'vtiger_projectmilestone.projectmilestone_progress'])
			->from('vtiger_projectmilestone')
			->innerJoin('vtiger_crmentity', 'vtiger_projectmilestone.projectmilestoneid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => [0, 2]])
			->andWhere(['vtiger_projectmilestone.parentid' => $id])->all();
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
		$row = (new \App\Db\Query())
			->select([
				'estimated_work_time' => new \yii\db\Expression('SUM(vtiger_projecttask.estimated_work_time)'),
				'progress_in_hours' => new \yii\db\Expression('SUM(vtiger_projecttask.estimated_work_time * vtiger_projecttask.projecttaskprogress / 100)')
			])
			->from('vtiger_projecttask')
			->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => [0, 2]])
			->andWhere(['vtiger_projecttask.projectmilestoneid' => $id])
			->one();
		if (false !== $row && null !== $row['estimated_work_time']) {
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
		if (isset(static::$cacheEstimatedWorkTime[$id])) {
			$estimatedWorkTime += static::$cacheEstimatedWorkTime[$id];
		} else {
			$progressInHours = 0;
			$tmpEstimatedWorkTime = 0;
			foreach (static::getChildren($id) as $child) {
				$tmpEstimatedWorkTime += static::calculateEstimatedWorkTime($child['id']);
			}
			static::calculateProgressOfTasks($id, $tmpEstimatedWorkTime, $progressInHours);
			static::$cacheEstimatedWorkTime[$id] = $tmpEstimatedWorkTime;
			$estimatedWorkTime += $tmpEstimatedWorkTime;
		}
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
	public static function updateProgress(int $id, float $estimatedWorkTime = 0, float $progressInHours = 0, ?int $callerId = null)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($id);
		foreach (static::getChildren($id) as $child) {
			if ($callerId !== $child['id']) {
				$childEstimatedWorkTime = static::calculateEstimatedWorkTime($child['id']);
				$estimatedWorkTime += $childEstimatedWorkTime;
				$progressInHours += ($childEstimatedWorkTime * $child['projectmilestone_progress'] / 100);
			}
		}
		static::calculateProgressOfTasks($id, $estimatedWorkTime, $progressInHours);
		$projectProgress = $estimatedWorkTime ? round((100 * $progressInHours) / $estimatedWorkTime) : 0;
		$recordModel->set('projectmilestone_progress', $projectProgress);
		$recordModel->set('estimated_work_time', $estimatedWorkTime);
		$recordModel->save();
		if ($recordModel->isEmpty('parentid')) {
			static::$cacheEstimatedWorkTime[$id] = $estimatedWorkTime;
			Project_Module_Model::updateProgress($recordModel->get('projectid'));
		} else {
			static::updateProgress(
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
	public function getQueryByRelatedField(Vtiger_ListView_Model $listviewModel, App\QueryGenerator $queryGenerator)
	{
		if ('Project' === $listviewModel->get('src_module') && !$listviewModel->isEmpty('filterFields')) {
			$filterFields = $listviewModel->get('filterFields');
			if (!empty($filterFields['projectid'])) {
				$queryGenerator->addNativeCondition(['projectid' => (int) $filterFields['projectid']]);
			}
		}
	}
}
