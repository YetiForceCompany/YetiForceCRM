<?php

/**
 * Settings Pagination view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Vtiger_Pagination_View extends Settings_Vtiger_IndexAjax_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPagination');
	}

	/**
	 * Pagination.
	 *
	 * @param \App\Request $request
	 */
	public function getPagination(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$pageNumber = $request->getInteger('page');
		$searchResult = $request->get('searchResult');
		$qualifiedModuleName = $request->getModule(false);
		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		if (!$request->isEmpty('sourceModule')) {
			$sourceModule = $request->getByType('sourceModule', 2);
			$listViewModel->set('sourceModule', $sourceModule);
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $request->getByType('viewname', 2));
		$searchKey = $request->getByType('search_key', 'Alnum');
		$searchValue = $request->getByType('search_value', 'Text');
		$operator = $request->getByType('operator', 1);
		if (!empty($operator)) {
			$listViewModel->set('operator', $operator);
			$viewer->assign('OPERATOR', $operator);
			$viewer->assign('ALPHABET_VALUE', $searchValue);
		}
		if (!empty($searchKey) && !empty($searchValue)) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
		}

		$searchParams = $request->getArray('search_params');
		if (empty($searchParams) || !\is_array($searchParams)) {
			$searchParams = [];
		}
		$transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $listViewModel->getModule());
		$listViewModel->set('search_params', $transformedSearchParams);
		if (!empty($searchResult) && \is_array($searchResult)) {
			$listViewModel->get('query_generator')->addNativeCondition(['vtiger_crmentity.crmid' => $searchResult]);
		}
		if (!property_exists($this, 'listViewEntries') || empty($this->listViewEntries)) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		if (!property_exists($this, 'listViewCount') || empty($this->listViewCount)) {
			$this->listViewCount = $listViewModel->getListViewCount();
		}
		$noOfEntries = \count($this->listViewEntries);
		$totalCount = $this->listViewCount;
		$pagingModel->set('totalCount', (int) $totalCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		echo $viewer->view('Pagination.tpl', $qualifiedModuleName, true);
	}

	/**
	 * Function to transfer a list of searched parameters to the filter.
	 *
	 * @param array                        $searchParams
	 * @param Settings_Vtiger_Module_Model $moduleModel
	 *
	 * @return array
	 */
	public function transferListSearchParamsToFilterCondition($searchParams, Settings_Vtiger_Module_Model $moduleModel)
	{
		if (empty($searchParams)) {
			return [];
		}
		$advFilterConditionFormat = [];
		$glueOrder = ['and', 'or'];
		$groupIterator = 0;
		foreach ($searchParams as &$groupInfo) {
			if (empty($groupInfo)) {
				continue;
			}
			$groupColumnsInfo = [];
			foreach ($groupInfo as &$fieldSearchInfo) {
				[$fieldName, $operator, $fieldValue, $specialOption] = $fieldSearchInfo;
				$field = $moduleModel->getFieldByName($fieldName);
				if ('tree' === $field->getFieldDataType() && $specialOption) {
					$fieldValue = Settings_TreesManager_Record_Model::getChildren($fieldValue, $fieldName, $moduleModel);
				}
				//Request will be having in terms of AM and PM but the database will be having in 24 hr format so converting
				if ('time' === $field->getFieldDataType()) {
					$fieldValue = \App\Fields\Time::sanitizeDbFormat($fieldValue);
				}
				if ('date_start' === $fieldName || 'due_date' === $fieldName || 'datetime' === $field->getFieldDataType()) {
					$dateValues = explode(',', $fieldValue);
					//Indicate whether it is fist date in the between condition
					$isFirstDate = true;
					foreach ($dateValues as $key => $dateValue) {
						$dateTimeCompoenents = explode(' ', $dateValue);
						if (empty($dateTimeCompoenents[1])) {
							if ($isFirstDate) {
								$dateTimeCompoenents[1] = '00:00:00';
							} else {
								$dateTimeCompoenents[1] = '23:59:59';
							}
						}
						$dateValue = implode(' ', $dateTimeCompoenents);
						$dateValues[$key] = $dateValue;
						$isFirstDate = false;
					}
					$fieldValue = implode(',', $dateValues);
				}
				$groupColumnsInfo[] = ['columnname' => $field->getCustomViewColumnName(), 'comparator' => $operator, 'value' => $fieldValue];
			}
			$advFilterConditionFormat[$glueOrder[$groupIterator]] = $groupColumnsInfo;
			++$groupIterator;
		}
		return $advFilterConditionFormat;
	}
}
