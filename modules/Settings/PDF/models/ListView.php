<?php

/**
 * List View Model Class for PDF Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_ListView_Model extends Settings_Vtiger_ListView_Model
{

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return array - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel)
	{
		$module = $this->getModule();
		$parentModuleName = $module->getParentName();
		$qualifiedModuleName = 'PDF';
		if (!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName . ':' . $qualifiedModuleName;
		}
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
		$listFields = array_keys($module->listFields);
		$listFields [] = $module->baseIndex;
		$query = (new \App\Db\Query())->select($listFields)
			->from($module->baseTable);
		$sourceModule = $this->get('sourceModule');
		if (!empty($sourceModule)) {
			$query->where(['module_name' => $sourceModule]);
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			$query->orderBy($orderBy . ' ' . $this->getForSql('sortorder'));
		}
		$dataReader = $query->limit($pageLimit)->offset($startIndex)->createCommand()->query();
		$listViewRecordModels = [];
		while ($row = $dataReader->read()) {
			$record = new $recordModelClass();
			$module_name = $row['module_name'];

			//To handle translation of calendar to To Do
			if ($module_name == 'Calendar') {
				$module_name = vtranslate('LBL_TASK', $module_name);
			} else {
				$module_name = vtranslate($module_name, $module_name);
			}
			$row['module_name'] = $module_name;
			$row['summary'] = vtranslate($row['summary'], $qualifiedModuleName);

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
		return $listViewRecordModels;
	}
	/*
	 * Function which will get the list view count
	 * @return - number of records
	 */

	public function getListViewCount()
	{
		$module = $this->getModule();
		$query = (new \App\Db\Query())->from($module->baseTable);
		$sourceModule = $this->get('sourceModule');
		if ($sourceModule) {
			$query->where(['module_name' => $sourceModule]);
		}
		return $query->count();
	}
}
