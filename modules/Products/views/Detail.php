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

class Products_Detail_View extends Vtiger_Detail_View
{

	public function showModuleDetailView(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();

		$viewer = $this->getViewer($request);
		$viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
		$viewer->assign('TAXCLASS_DETAILS', $recordModel->getTaxClassDetails());
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}

	public function showModuleBasicView(Vtiger_Request $request)
	{
		return $this->showModuleDetailView($request);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$moduleDetailFile = 'modules.' . $moduleName . '.resources.Detail';
		$moduleRelatedListFile = 'modules.' . $moduleName . '.resources.RelatedList';
		unset($headerScriptInstances[$moduleDetailFile]);
		unset($headerScriptInstances[$moduleRelatedListFile]);

		$jsFileNames = array(
			'~libraries/jquery/jquery.cycle.min.js',
			'modules.PriceBooks.resources.Detail',
			'modules.PriceBooks.resources.RelatedList',
		);

		$jsFileNames[] = $moduleDetailFile;
		$jsFileNames[] = $moduleRelatedListFile;

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Function returns related records
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	public function showRelatedList(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');

		if ($relatedModuleName == 'OSSPasswords')
			return parent::showRelatedList($request);

		$requestedPage = $request->get('page');
		if (empty($requestedPage)) {
			$requestedPage = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $requestedPage);

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if (empty($orderBy) && empty($sortOrder) && $request->get('relatedModule')) {
			$moduleInstance = CRMEntity::getInstance($request->get('relatedModule'));
			$orderBy = $moduleInstance->default_order_by;
			$sortOrder = $moduleInstance->default_sort_order;
		}
		if ($sortOrder == "ASC") {
			$nextSortOrder = "DESC";
			$sortImage = "glyphicon glyphicon-chevron-down";
		} else {
			$nextSortOrder = "ASC";
			$sortImage = "glyphicon glyphicon-chevron-up";
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
		$transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParmams, $relationListView->getRelationModel()->getRelationModuleModel());
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

		if ($relatedModuleName == 'PriceBooks') {
			foreach ($models as $recordId => $recorModel) {
				$productIdsList[$parentId] = $parentId;
				$relatedRecordCurrencyId = $recorModel->get('currency_id');
				$parentModuleModel = $parentRecordModel->getModule();
				$unitPricesList = $parentModuleModel->getPricesForProducts($relatedRecordCurrencyId, $productIdsList);
				$recorModel->set('unit_price', $unitPricesList[$parentId]);
			}
		}

		$relationModel = $relationListView->getRelationModel();
		$relationField = $relationModel->getRelationField();

		$viewer->assign('RELATED_RECORDS', $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relationModel->getRelationModuleModel());
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);

		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			$totalCount = $relationListView->getRelatedEntriesCount();
			$pagingModel->set('totalCount', (int) $totalCount);
			$viewer->assign('TOTAL_ENTRIES', $totalCount);
		}
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_NUMBER', $requestedPage);
		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);

		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SEARCH_DETAILS', $searchParmams);

		return $viewer->view('RelatedList.tpl', $moduleName, 'true');
	}

	public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel)
	{
		return Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
	}
}
