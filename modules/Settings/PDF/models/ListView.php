<?php

/**
 * List View Model Class for PDF Settings.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author 	  Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_PDF_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/** {@inheritdoc} */
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
		$listFields[] = $module->baseIndex;
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
		$dataReader = $query->limit($pageLimit + 1)->offset($startIndex)->createCommand()->query();
		$listViewRecordModels = [];
		while ($row = $dataReader->read()) {
			$record = new $recordModelClass();
			$row['module_name'] = \App\Language::translate($row['module_name'], $row['module_name']);
			$row['summary'] = isset($row['summary']) ? \App\Language::translate($row['summary'], $qualifiedModuleName) : '';
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

	/** {@inheritdoc} */
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
