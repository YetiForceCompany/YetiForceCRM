<?php

/**
 * Project module model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
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
		if (!App\Record::isExists($id)) {
			return [];
		}
		$recordModel = Project_Record_Model::getInstanceById($id);
		if (empty($parentId)) {
			foreach ($recordModel->getChildren() as $childRecordModel) {
				$progressItem = $this->calculateProgressOfChildren($childRecordModel->getId());
				$estimatedWorkTime += $progressItem['estimatedWorkTime'];
				$progressInHours += ($progressItem['estimatedWorkTime'] * $progressItem['projectProgress']) / 100;
			}
		}
		$this->calculateProgressOfTasks($recordModel, $estimatedWorkTime, $progressInHours);
		if ($estimatedWorkTime) {
			$projectProgress = round((100 * $progressInHours) / $estimatedWorkTime);
		} else {
			$projectProgress = 0;
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->getName());
		$recordModel->set('progress', $projectProgress . '%');
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
		if (!App\Record::isExists($id)) {
			return [
				'estimatedWorkTime' => 0,
				'projectProgress' => 0
			];
		}
		$recordModel = Project_Record_Model::getInstanceById($id);
		foreach ($recordModel->getChildren() as $childRecordModel) {
			$progressItem = $this->calculateProgressOfChildren($childRecordModel->getId());
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
	 * @param \Project_Record_Model $recordModel
	 * @param float                 $estimatedWorkTime
	 * @param float                 $progressInHours
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function calculateProgressOfTasks(\Project_Record_Model $recordModel, float &$estimatedWorkTime, float &$progressInHours)
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
