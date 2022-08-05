<?php

/**
 * List View Model Class for MappedFields Settings.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_MappedFields_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array - Associative array of record id mapped to Vtiger_Record_Model instance
	 */
	public function getListViewEntries($pagingModel)
	{
		$module = $this->getModule();
		$parentModuleName = $module->getParentName();
		if (!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName . ':' . $module->getName();
		}
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
		$listFields = array_keys($module->listFields);
		$listFields[] = $module->baseIndex;
		$query = (new \App\Db\Query())->select($listFields)
			->from($module->baseTable);
		$sourceModule = $this->get('sourceModule');
		if (!empty($sourceModule)) {
			$query->where(['tabid' => $sourceModule]);
		}
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			$query->orderBy(sprintf('%s %s ', $orderBy, $this->getForSql('sortorder')));
		}
		$query->limit($pageLimit + 1)->offset($startIndex);
		$dataReader = $query->createCommand()->query();
		$listViewRecordModels = [];
		while ($row = $dataReader->read()) {
			$recordModel = new $recordModelClass();
			$moduleName = \App\Module::getModuleName($row['tabid']);
			$relModuleName = \App\Module::getModuleName($row['reltabid']);
			$row['tabid'] = \App\Language::translate($moduleName, $moduleName);
			$row['reltabid'] = \App\Language::translate($relModuleName, $relModuleName);
			$recordModel->setData($row);
			$listViewRecordModels[$recordModel->getId()] = $recordModel;
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

	/** {@inheritdoc} */
	public function getListViewCount()
	{
		$module = $this->getModule();
		$db = \App\Db::getInstance('admin');
		$query = (new \App\Db\Query())->from($module->baseTable);
		$sourceModule = $this->get('sourceModule');
		if ($sourceModule) {
			$query->where(['tabid' => $sourceModule]);
		}
		return $query->count('*', $db);
	}
}
