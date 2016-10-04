<?php

/**
 * List View Model Class for MappedFields Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_MappedFields_ListView_Model extends Settings_Vtiger_ListView_Model
{

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel)
	{
		$db = PearDatabase::getInstance();

		$module = $this->getModule();
		$parentModuleName = $module->getParentName();
		if (!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName . ':' . $module->getName();
		}
		$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);

		$listFields = $module->listFields;
		$listQuery = 'SELECT ';
		foreach ($listFields as $fieldName => $fieldLabel) {
			$listQuery .= '`' . $fieldName . '`, ';
		}
		$listQuery .= '`' . $module->baseIndex . '` FROM `' . $module->baseTable . '`';

		$params = [];
		$sourceModule = $this->get('sourceModule');
		if (!empty($sourceModule)) {
			$sourceModule = vtlib\Functions::getModuleName($sourceModule);
			$listQuery .= ' WHERE `tabid` = ?';
			$params[] = $sourceModule;
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');

		if (!empty($orderBy)) {
			$listQuery .= sprintf(' ORDER BY %s %s ', $orderBy, $this->getForSql('sortorder'));
		}
		$nextListQuery = $listQuery . ' LIMIT ' . ($startIndex + $pageLimit) . ',1';
		$listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);

		$listResult = $db->pquery($listQuery, $params);

		$listViewRecordModels = [];
		while ($row = $db->getRow($listResult)) {
			$recordModel = new $recordModelClass();
			$moduleName = vtlib\Functions::getModuleName($row['tabid']);
			$relModuleName = vtlib\Functions::getModuleName($row['reltabid']);

			$row['tabid'] = vtranslate($moduleName, $moduleName);
			$row['reltabid'] = vtranslate($relModuleName, $relModuleName);

			$recordModel->setData($row);
			$listViewRecordModels[$recordModel->getId()] = $recordModel;
		}

		$pagingModel->calculatePageRange($listViewRecordModels);

		if ($listResult->rowCount() > $pageLimit) {
			array_pop($listViewRecordModels);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		$nextPageResult = $db->pquery($nextListQuery, $params);
		if ($nextPageResult->rowCount() <= 0) {
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
		$db = PearDatabase::getInstance();

		$module = $this->getModule();
		$params = [];
		$listQuery = sprintf('SELECT COUNT(1) AS count FROM %s', $module->baseTable);

		$sourceModule = $this->get('sourceModule');
		if ($sourceModule) {
			$listQuery .= ' WHERE `tabid` = ?;';
			$params[] = $sourceModule;
		}

		$listResult = $db->pquery($listQuery, $params);
		return $db->getSingleValue($listResult);
	}
}
