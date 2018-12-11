<?php

/**
 * Settings TreesManager ListView model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance
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
		$listQuery = $this->getBasicListQuery();

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy) && $orderBy === 'smownerid') {
			$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
			if ($fieldModel->getFieldDataType() === 'owner') {
				$orderBy = 'COALESCE(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ',vtiger_groups.groupname)';
			}
		}
		if (!empty($orderBy)) {
			if ($this->getForSql('sortorder') === 'ASC') {
				$listQuery->orderBy([$orderBy => SORT_ASC]);
			} else {
				$listQuery->orderBy([$orderBy => SORT_DESC]);
			}
		}
		$sourceModule = $this->get('sourceModule');
		if (!empty($sourceModule)) {
			$listQuery->where(['module' => \App\Module::getModuleId($sourceModule)]);
		}

		if ($module->isPagingSupported()) {
			$listQuery->limit($pageLimit + 1)->offset($startIndex);
		}

		$dataReader = $listQuery->createCommand()->query();
		$listViewRecordModels = [];
		while ($row = $dataReader->read()) {
			$record = new $recordModelClass();
			$record->setData($row);

			$recordModule = \App\Module::getModuleName($row['module']);
			$record->set('module', \App\Language::translate($recordModule, $recordModule));

			if (method_exists($record, 'getModule') && method_exists($record, 'setModule')) {
				$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
				$record->setModule($moduleModel);
			}
			$listViewRecordModels[$record->getId()] = $record;
		}
		if ($module->isPagingSupported()) {
			$pagingModel->calculatePageRange($dataReader->count());
			if ($dataReader->count() > $pageLimit) {
				array_pop($listViewRecordModels);
				$pagingModel->set('nextPageExists', true);
			} else {
				$pagingModel->set('nextPageExists', false);
			}
		}
		$dataReader->close();

		return $listViewRecordModels;
	}

	/**
	 * Function which will get the list view count.
	 *
	 * @return int
	 */
	public function getListViewCount()
	{
		return $this->loadListViewCondition()->count();
	}

	/**
	 * Load list view conditions.
	 *
	 * @return object
	 */
	public function loadListViewCondition()
	{
		$listQuery = $this->getBasicListQuery();
		$sourceModule = $this->get('sourceModule');
		if (!empty($sourceModule)) {
			$listQuery->where(['module' => \App\Module::getModuleId($sourceModule)]);
		}
		return $listQuery;
	}
}
