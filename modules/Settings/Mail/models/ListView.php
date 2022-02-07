<?php

/**
 * List View Model Class for Mail Settings.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_ListView_Model extends Settings_Vtiger_ListView_Model
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
		$searchParams = $this->get('searchParams');
		$fieldsToFilter = $module->getFilterFields();
		if (!empty($searchParams)) {
			foreach ($searchParams as $key => $value) {
				if ('' !== $value['value'] && \in_array($key, $fieldsToFilter)) {
					$query->andWhere([$key => $value['value']]);
				}
			}
		}
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy) && \in_array($orderBy, $listFields)) {
			if ('DESC' === $this->getForSql('sortorder')) {
				$query->orderBy([$orderBy => SORT_DESC]);
			} else {
				$query->orderBy([$orderBy => SORT_ASC]);
			}
		}
		$query->limit($pageLimit + 1)->offset($startIndex);
		$dataReader = $query->createCommand()->query();
		$listViewRecordModels = [];
		while ($row = $dataReader->read()) {
			$recordModel = new $recordModelClass();
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

	public function getBasicLinks()
	{
		return [];
	}
}
