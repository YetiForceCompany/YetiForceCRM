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
		if (!App\Record::isExists($id)) {
			return [];
		}
		$recordModel = Project_Record_Model::getInstanceById($id);
		if (empty($parentId)) {
			foreach ($recordModel->getChildren() as $childRecordModel) {
				$progressItem = $this->calFromChProgress($childRecordModel->getId());
				if (isset($progressItem['projectProgress'])) {
					$estimatedWorkTime += $progressItem['estimatedWorkTime'];
					$recordProgress = ($progressItem['estimatedWorkTime'] * (int) $progressItem['projectProgress']) / 100;
					$progressInHours += $recordProgress;
				}
			}
		}
		$relatedListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'ProjectTask');
		$relatedListView->getRelationModel()->set('QueryFields', [
			'estimated_work_time' => 'estimated_work_time',
			'projecttaskprogress' => 'projecttaskprogress',
		]);
		$dataReader = $relatedListView->getRelationQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$estimatedWorkTime += $row['estimated_work_time'];
			$recordProgress = ($row['estimated_work_time'] * (int) $row['projecttaskprogress']) / 100;
			$progressInHours += $recordProgress;
		}
		$dataReader->close();
		if ($estimatedWorkTime) {
			$projectProgress = round((100 * $progressInHours) / $estimatedWorkTime);
		} else {
			$projectProgress = 0;
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->getName());
		$recordModel->set('progress', $projectProgress . '%');
		$recordModel->save();
		if ($recordModel->hasParent() && $recordModel->get('parentid') !== $parentId) {
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

	public function calFromChProgress($id, float $estimatedWorkTime = 0, float $progressInHours = 0)
	{
		if (!App\Record::isExists($id)) {
			return [
				'estimatedWorkTime' => 0,
				'projectProgress' => 0
			];
		}
		$recordModel = Project_Record_Model::getInstanceById($id);
		$relatedListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'ProjectTask');
		$relatedListView->getRelationModel()->set('QueryFields', [
			'estimated_work_time' => 'estimated_work_time',
			'projecttaskprogress' => 'projecttaskprogress',
		]);
		$dataReader = $relatedListView->getRelationQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$estimatedWorkTime += $row['estimated_work_time'];
			$recordProgress = ($row['estimated_work_time'] * (int) $row['projecttaskprogress']) / 100;
			$progressInHours += $recordProgress;
		}
		if ($estimatedWorkTime) {
			$projectProgress = round((100 * $progressInHours) / $estimatedWorkTime);
		} else {
			$projectProgress = 0;
		}
		return [
			'estimatedWorkTime' => $estimatedWorkTime,
			'projectProgress' => $projectProgress
		];
	}
}
