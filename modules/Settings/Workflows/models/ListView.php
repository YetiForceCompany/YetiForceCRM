<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */
/**
 * Settings List View Model Class.
 */
class Settings_Workflows_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries($pagingModel)
	{
		$module = $this->getModule();
		$moduleName = $module->getName();
		$parentModuleName = $module->getParentName();
		$qualifiedModuleName = $moduleName;
		if (!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName . ':' . $qualifiedModuleName;
		}
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);

		$listFields = $module->listFields;
		unset($listFields['all_tasks'], $listFields['active_tasks']);

		$listFields = array_keys($listFields);
		$listFields[] = $module->baseIndex;
		$listQuery = (new App\Db\Query())->select($listFields)
			->from($module->baseTable);

		$sourceModule = $this->get('sourceModule');
		if (!empty($sourceModule)) {
			$listQuery->where(['module_name' => $sourceModule]);
		}
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			$listQuery->orderBy(sprintf('%s %s', $orderBy, $this->getForSql('sortorder')));
		}
		$listQuery->limit($pageLimit + 1)->offset($startIndex);

		$dataReader = $listQuery->createCommand()->query();
		$listViewRecordModels = [];
		while ($row = $dataReader->read()) {
			$record = new $recordModelClass();
			$workflowModel = $record->getInstance($row['workflow_id']);
			$taskList = $workflowModel->getTasks(false);
			$row['module_name'] = \App\Language::translate($row['module_name'], $row['module_name']);
			$row['execution_condition'] = \App\Language::translate($record->executionConditionAsLabel($row['execution_condition']), 'Settings:Workflows');
			$row['summary'] = \App\Language::translate($row['summary'], 'Settings:Workflows');
			$row['all_tasks'] = \count($taskList);
			$activeCount = 0;
			foreach ($taskList as $taskRecord) {
				if ($taskRecord->isActive() && $taskRecord->isEditable()) {
					++$activeCount;
				}
			}
			$row['active_tasks'] = $activeCount;

			$record->setData($row);
			$listViewRecordModels[$record->getId()] = $record;
		}
		$pagingModel->calculatePageRange($dataReader->count());
		if ($dataReader->count() > $pageLimit) {
			array_pop($listViewRecordModels);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$dataReader->close();

		return $listViewRecordModels;
	}

	/**	 * *
	 * Function which will get the list view count.
	 *
	 * @return int number of records
	 */
	public function getListViewCount()
	{
		$module = $this->getModule();
		$query = (new App\Db\Query())->from($module->baseTable);
		$sourceModule = $this->get('sourceModule');
		if ($sourceModule) {
			$query->where(['module_name' => $sourceModule]);
		}
		return $query->count();
	}
}
