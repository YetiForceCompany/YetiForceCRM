<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Calendar_InRelation_View extends Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$pageNumber = $request->get('page');
		$time = $request->get('time');
		$totalCount = $request->get('totalCount');
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		if (empty($time)) {
			$time = 'current';
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if ($sortOrder == 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'glyphicon glyphicon-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'glyphicon glyphicon-chevron-up';
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

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if (!empty($operator)) {
			$relationListView->set('operator', $operator);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('OPERATOR', $operator);
		$viewer->assign('ALPHABET_VALUE', $searchValue);
		if (!empty($searchKey) && !empty($searchValue)) {
			$relationListView->set('search_key', $searchKey);
			$relationListView->set('search_value', $searchValue);
		}

		$searchParmams = $request->get('search_params');
		if (empty($searchParmams) || !is_array($searchParmams)) {
			$searchParmams = [];
		}
		$transformedSearchParams = $relationListView->get('query_generator')->parseBaseSearchParamsToCondition($searchParmams);
		$relationListView->set('search_params', $transformedSearchParams);

		//To make smarty to get the details easily accesible
		foreach ($searchParmams as $fieldListGroup) {
			foreach ($fieldListGroup as $fieldSearchInfo) {
				$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
				$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
				$fieldSearchInfo['specialOption'] = $fieldSearchInfo[3];
				$searchParmams[$fieldName] = $fieldSearchInfo;
			}
		}

		$models = $relationListView->getEntries($pagingModel);
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$noOfEntries = count($models);

		$relationModel = $relationListView->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationField = $relationModel->getRelationField();

		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			$totalCount = $relationListView->getRelatedEntriesCount();
		}
		if (!empty($totalCount)) {
			$pagingModel->set('totalCount', (int) $totalCount);
			$viewer->assign('TOTAL_ENTRIES', (int) $totalCount);
			$viewer->assign('LISTVIEW_COUNT', (int) $totalCount);
		}
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('RELATED_RECORDS', $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);
		$viewer->assign('TIME', $time);
		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);

		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);

		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('SHOW_CREATOR_DETAIL', $relationModel->showCreatorDetail());
		$viewer->assign('SHOW_COMMENT', $relationModel->showComment());
		$isFavorites = false;
		if ($relationModel->isFavorites() && Users_Privileges_Model::isPermitted($moduleName, 'FavoriteRecords')) {
			$favorites = $relationListView->getFavoriteRecords();
			$viewer->assign('FAVORITES', $favorites);
			$isFavorites = $relationModel->isFavorites();
		}
		$viewer->assign('IS_FAVORITES', $isFavorites);
		$viewer->assign('SEARCH_DETAILS', $searchParmams);
		return $viewer->view('RelatedList.tpl', $relatedModuleName, 'true');
	}

}
