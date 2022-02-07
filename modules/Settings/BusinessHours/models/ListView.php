<?php
/**
 * BusinessHours ListView model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_ListView_Model extends Settings_Vtiger_ListView_Model
{
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
		$pagingModel->set('page', 1);
		if (!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName . ':' . $qualifiedModuleName;
		}
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
		$listQuery = $this->getBasicListQuery();
		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			if ('DESC' === $this->getForSql('sortorder')) {
				$listQuery->orderBy([$orderBy => SORT_DESC]);
			} else {
				$listQuery->orderBy([$orderBy => SORT_ASC]);
			}
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
		$dataReader->close();

		return $listViewRecordModels;
	}

	/**
	 * Function to get the instance of Settings module model.
	 *
	 * @param mixed $name
	 *
	 * @return Settings_Vtiger_Module_Model instance
	 */
	public static function getInstance($name = 'Settings:BusinessHours')
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $name);
		$instance = new $modelClassName();
		return $instance->setModule($name);
	}
}
