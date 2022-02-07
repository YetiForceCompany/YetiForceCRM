<?php
/**
 * Project module model class.
 *
 * @package   Module
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Project_Module_Model extends Vtiger_Module_Model
{
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
		foreach (static::getChildren($id) as $child) {
			$estimatedWorkTime += static::calculateEstimatedWorkTime($child['id']);
		}
		static::calculateProgressOfMilestones($id, $estimatedWorkTime, $progressInHours);
		return $estimatedWorkTime;
	}

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
			->select(['id' => 'vtiger_project.projectid', 'vtiger_project.progress'])
			->from('vtiger_project')
			->innerJoin('vtiger_crmentity', 'vtiger_project.projectid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => [0, 2]])
			->andWhere(['vtiger_project.parentid' => $id])->all();
	}

	/**
	 * Calculate the progress of milestones.
	 *
	 * @param int   $id
	 * @param float $estimatedWorkTime
	 * @param float $progressInHours
	 *
	 * @throws \App\Exceptions\AppException
	 */
	protected static function calculateProgressOfMilestones(int $id, float &$estimatedWorkTime, float &$progressInHours)
	{
		$dataReader = (new \App\Db\Query())
			->select([
				'id' => 'vtiger_projectmilestone.projectmilestoneid',
				'projectmilestonename' => 'vtiger_projectmilestone.projectmilestonename',
				'projectmilestone_progress' => 'vtiger_projectmilestone.projectmilestone_progress',
			])
			->from('vtiger_projectmilestone')
			->innerJoin('vtiger_crmentity', 'vtiger_projectmilestone.projectmilestoneid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => [0, 2]])
			->andWhere(['vtiger_projectmilestone.projectid' => $id])
			->andWhere(['or', ['vtiger_projectmilestone.parentid' => 0], ['vtiger_projectmilestone.parentid' => null]])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$milestoneEstimatedWorkTime = ProjectMilestone_Module_Model::calculateEstimatedWorkTime($row['id']);
			$estimatedWorkTime += $milestoneEstimatedWorkTime;
			$progressInHours += ($milestoneEstimatedWorkTime * (float) $row['projectmilestone_progress']) / 100;
		}
		$dataReader->close();
	}

	/** {@inheritdoc} */
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
	 * @param int      $id
	 * @param float    $estimatedWorkTime
	 * @param float    $progressInHours
	 * @param int|null $callerId
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function updateProgress(int $id, float $estimatedWorkTime = 0, float $progressInHours = 0, ?int $callerId = null): array
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($id);
		foreach (static::getChildren($id) as $child) {
			if ($callerId !== $child['id']) {
				$childEstimatedWorkTime = static::calculateEstimatedWorkTime($child['id']);
				$estimatedWorkTime += $childEstimatedWorkTime;
				$progressInHours += ($childEstimatedWorkTime * $child['progress'] / 100);
			}
		}
		static::calculateProgressOfMilestones($id, $estimatedWorkTime, $progressInHours);
		$projectProgress = $estimatedWorkTime ? round((100 * $progressInHours) / $estimatedWorkTime) : 0;
		$recordModel->set('progress', $projectProgress);
		$recordModel->set('estimated_work_time', $estimatedWorkTime);
		$recordModel->save();
		if (!$recordModel->isEmpty('parentid') && $recordModel->get('parentid') !== $callerId) {
			static::updateProgress(
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
}
