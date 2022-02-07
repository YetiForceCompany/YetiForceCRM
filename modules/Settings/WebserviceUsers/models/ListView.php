<?php

/**
 * WebserviceUsers ListView Model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_WebserviceUsers_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/**
	 * Function sets module instance.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setModule($name)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $name);
		$this->module = new $modelClassName();
		$this->module->typeApi = \App\Request::_get('typeApi');

		return $this;
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
				'linkdata' => ['url' => $moduleModel->getEditViewUrl()],
				'linkicon' => 'fas fa-plus',
				'linkclass' => 'btn-light addRecord',
				'showLabel' => 1,
				'modalView' => true,
			];
		}
		return $basicLinks;
	}

	/** {@inheritdoc} */
	public function getListViewEntries($pagingModel)
	{
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$parentModuleName = $moduleModel->getParentName();
		$qualifiedModuleName = $moduleName;
		if (!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName . ':' . $qualifiedModuleName;
		}
		$listQuery = $this->getBasicListQuery();

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy) && 'smownerid' === $orderBy) {
			$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
			if ('owner' == $fieldModel->getFieldDataType()) {
				$orderBy = 'COALESCE(' . App\Module::getSqlForNameInDisplayFormat('Users') . ',vtiger_groups.groupname)';
			}
		}
		if (!empty($orderBy)) {
			if ('DESC' === $this->getForSql('sortorder')) {
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
			$record = $moduleModel->getService();
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
}
