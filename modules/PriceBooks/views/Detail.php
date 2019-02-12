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

class PriceBooks_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 *
	 * This function is based on the Vtiger_RelatedList_View class
	 *
	 * @see Vtiger_RelatedList_View
	 */
	public function showRelatedList(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$parentId = $request->getInteger('record');
		$label = $request->getByType('tab_label', 'Text');
		if ($request->isEmpty('relatedView', true)) {
			$relatedView = empty($_SESSION['relatedView'][$moduleName][$relatedModuleName]) ? 'List' : $_SESSION['relatedView'][$moduleName][$relatedModuleName];
		} else {
			$relatedView = $request->getByType('relatedView');
			$_SESSION['relatedView'][$moduleName][$relatedModuleName] = $relatedView;
		}
		$pageNumber = $request->isEmpty('page', true) ? 1 : $request->getInteger('page');
		$totalCount = $request->isEmpty('totalCount', true) ? false : $request->getInteger('totalCount');
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if ($request->has('limit')) {
			$pagingModel->set('limit', $request->getInteger('limit'));
		}
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$orderBy = $request->getForSql('orderby');
		$sortOrder = $request->getForSql('sortorder');
		if ($sortOrder === 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'fas fa-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'fas fa-chevron-up';
		}
		if (empty($orderBy) && empty($sortOrder)) {
			$relatedInstance = CRMEntity::getInstance($relatedModuleName);
			$orderBy = $relatedInstance->default_order_by;
			$sortOrder = $relatedInstance->default_sort_order;
		}
		if (!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder', $sortOrder);
		}
		if ($request->has('entityState')) {
			$relationListView->set('entityState', $request->getByType('entityState'));
		}
		$viewer = $this->getViewer($request);
		$operator = 's';
		if (!$request->isEmpty('operator', true)) {
			$operator = $request->getByType('operator');
			$relationListView->set('operator', $operator);
			$viewer->assign('OPERATOR', $operator);
		}
		if (!$request->isEmpty('search_key', true)) {
			$searchKey = $request->getByType('search_key', 'Alnum');
			$searchValue = App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $relationListView->getQueryGenerator()->getModule(), $searchKey, $operator);
			$relationListView->set('search_key', $searchKey);
			$relationListView->set('search_value', $searchValue);
			$viewer->assign('ALPHABET_VALUE', $searchValue);
		}
		$searchParmams = App\Condition::validSearchParams($relationListView->getQueryGenerator()->getModule(), $request->getArray('search_params'));
		if (empty($searchParmams) || !is_array($searchParmams)) {
			$searchParmams = [];
		}
		$queryGenerator = $relationListView->getQueryGenerator();
		$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParmams);
		$relationListView->set('search_params', $transformedSearchParams);
		//To make smarty to get the details easily accesible
		foreach ($searchParmams as $fieldListGroup) {
			foreach ($fieldListGroup as $fieldSearchInfo) {
				$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
				$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
				$fieldSearchInfo['specialOption'] = $fieldSearchInfo[3] ?? null;
				$searchParmams[$fieldName] = $fieldSearchInfo;
			}
		}
		if ($relatedView === 'ListPreview') {
			$relationListView->setFields(array_merge(['id'], $relationListView->getRelatedModuleModel()->getNameFields()));
		}
		$models = $relationListView->getEntries($pagingModel);
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$relationModel = $relationListView->getRelationModel();

		// [start] modified code compared to base function
		$parentRecordCurrencyId = $parentRecordModel->get('currency_id');
		if ($parentRecordCurrencyId) {
			$productIdsList = [];
			foreach ($models as $recordId => $recordModel) {
				$productIdsList[$recordId] = $recordId;
			}
			$unitPricesList = $relationListView->getRelatedModuleModel()->getPricesForProducts($parentRecordCurrencyId, $productIdsList);
			foreach ($models as $recordId => $recordModel) {
				if (isset($unitPricesList[$recordId])) {
					$recordModel->set('unit_price', $unitPricesList[$recordId]);
				}
			}
		}
		// [end] modified code compared to base function
		$viewer->assign('VIEW_MODEL', $relationListView);
		$viewer->assign('RELATED_RECORDS', $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_VIEW', $relatedView);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relationModel->getRelationModuleModel());
		$viewer->assign('RELATED_ENTIRES_COUNT', count($models));
		$viewer->assign('RELATION_FIELD', $relationModel->getRelationField());
		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			$totalCount = $relationListView->getRelatedEntriesCount();
		}
		if (empty($totalCount)) {
			$totalCount = 0;
		}
		$pagingModel->set('totalCount', (int) $totalCount);
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('TOTAL_ENTRIES', $totalCount);
		$viewer->assign('PAGE_COUNT', $pagingModel->getPageCount());
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $pagingModel->getStartPagingFrom());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);
		$viewer->assign('INVENTORY_FIELDS', $relationModel->getRelationInventoryFields());
		$viewer->assign('SHOW_CREATOR_DETAIL', $relationModel->showCreatorDetail());
		$viewer->assign('SHOW_COMMENT', $relationModel->showComment());
		$isFavorites = false;
		if ($relationModel->isFavorites() && \App\Privilege::isPermitted($moduleName, 'FavoriteRecords')) {
			$favorites = $relationListView->getFavoriteRecords();
			$viewer->assign('FAVORITES', $favorites);
			$isFavorites = $relationModel->isFavorites();
		}
		$viewer->assign('IS_FAVORITES', $isFavorites);
		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->privilegeToDelete());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SEARCH_DETAILS', $searchParmams);
		$viewer->assign('VIEW', $request->getByType('view'));
		return $viewer->view('RelatedList.tpl', $moduleName, true);
	}
}
