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
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel)
	{
		$db = PearDatabase::getInstance();

		$module = $this->getModule();
		$parentModuleName = $module->getParentName();
		$qualifiedModuleName = 'PDF';
		if (!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName . ':' . $qualifiedModuleName;
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
			$listQuery .= ' WHERE `module_name` = ?';
			$params[] = $sourceModule;
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy) && $orderBy === 'smownerid') {
			$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
			if ($fieldModel->getFieldDataType() == 'owner') {
				$orderBy = 'COALESCE(CONCAT(`vtiger_users`.`first_name`, `vtiger_users`.`last_name`), `vtiger_groups`.`groupname`)';
			}
		}
		if (!empty($orderBy)) {
			$listQuery .= sprintf(' ORDER BY %s %s', $orderBy, $this->getForSql('sortorder'));
		}
		$nextListQuery = $listQuery . ' LIMIT ' . ($startIndex + $pageLimit) . ',1';
		$listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);

		$listResult = $db->pquery($listQuery, $params);

		$listViewRecordModels = [];
		while ($row = $db->fetchByAssoc($listResult)) {
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

		$pagingModel->calculatePageRange($listViewRecordModels);

		if ($db->num_rows($listResult) > $pageLimit) {
			array_pop($listViewRecordModels);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		$nextPageResult = $db->pquery($nextListQuery, $params);
		$nextPageNumRows = $db->num_rows($nextPageResult);
		if ($nextPageNumRows <= 0) {
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
			$listQuery .= ' WHERE module_name = ?;';
			$params[] = $sourceModule;
		}

		$listResult = $db->pquery($listQuery, $params);
		return $db->getSingleValue($listResult);
	}
}
