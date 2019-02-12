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

// Settings List View Model Class

class Settings_Vtiger_ListView_Model extends \App\Base
{
	/**
	 * Function to get the Module Model.
	 *
	 * @return Vtiger_Module_Model instance
	 */
	public function getModule()
	{
		return $this->module;
	}

	public function setModule($name)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $name);
		$this->module = new $modelClassName();

		return $this;
	}

	public function setModuleFromInstance($module)
	{
		$this->module = $module;

		return $this;
	}

	/**
	 * Function to get the list view header.
	 *
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders()
	{
		$module = $this->getModule();

		return $module->getListFields();
	}

	public function getBasicListQuery()
	{
		$module = $this->getModule();

		return (new App\Db\Query())->from($module->getBaseTable());
	}

	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return Settings_Vtiger_Record_Model[] - Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries($pagingModel)
	{
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$parentModuleName = $moduleModel->getParentName();
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
			if ($fieldModel->getFieldDataType() == 'owner') {
				$orderBy = 'COALESCE(' . App\Module::getSqlForNameInDisplayFormat('Users') . ',vtiger_groups.groupname)';
			}
		}
		if (!empty($orderBy)) {
			if ($this->getForSql('sortorder') === 'DESC') {
				$listQuery->orderBy([$orderBy => SORT_DESC]);
			} else {
				$listQuery->orderBy([$orderBy => SORT_ASC]);
			}
		}
		if ($moduleModel->isPagingSupported()) {
			$listQuery->limit($pageLimit)->offset($startIndex);
		}
		$dataReader = $listQuery->createCommand()->query();
		$listViewRecordModels = [];
		while ($row = $dataReader->read()) {
			$record = new $recordModelClass();
			$record->setData($row);
			if (method_exists($record, 'getModule') && method_exists($record, 'setModule')) {
				$record->setModule($moduleModel);
			}
			$listViewRecordModels[$record->getId()] = $record;
		}
		if ($moduleModel->isPagingSupported()) {
			$pagingModel->calculatePageRange($dataReader->count());
		}
		$dataReader->close();

		return $listViewRecordModels;
	}

	public function getListViewLinks()
	{
		$links = [];
		$basicLinks = $this->getBasicLinks();

		foreach ($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}
		return $links;
	}

	/**
	 * Function to get Basic links.
	 *
	 * @return array of Basic links
	 */
	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();
		if ($moduleModel->hasCreatePermissions()) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkurl' => $moduleModel->getCreateRecordUrl(),
				'linkclass' => 'btn-light addButton',
				'linkicon' => 'fas fa-plus',
				'showLabel' => 1,
			];
		}
		return $basicLinks;
	}

	/*	 * *
	 * Function which will get the list view count
	 * @return - number of records
	 */

	public function getListViewCount()
	{
		$listQuery = $this->getBasicListQuery();

		return $listQuery->count();
	}

	/**
	 * Function to get the instance of Settings module model.
	 *
	 * @return Settings_Vtiger_Module_Model instance
	 */
	public static function getInstance($name = 'Settings:Vtiger')
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $name);
		$instance = new $modelClassName();

		return $instance->setModule($name);
	}
}
