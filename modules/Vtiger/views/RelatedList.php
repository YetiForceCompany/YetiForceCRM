<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_RelatedList_View extends Vtiger_Index_View
{
	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getByType('relatedModule', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$parentId = $request->getInteger('record');
		if ($request->isEmpty('relatedView', true)) {
			$relatedView = empty($_SESSION['relatedView'][$moduleName][$relatedModuleName]) ? 'List' : $_SESSION['relatedView'][$moduleName][$relatedModuleName];
		} else {
			$relatedView = $request->getByType('relatedView');
			$_SESSION['relatedView'][$moduleName][$relatedModuleName] = $relatedView;
		}
		$pageNumber = $request->isEmpty('page', true) ? 1 : $request->getInteger('page');
		$totalCount = $request->isEmpty('totalCount', true) ? 0 : $request->getInteger('totalCount');
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if ($request->has('limit')) {
			$pagingModel->set('limit', $request->getInteger('limit'));
		}
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $request->getInteger('relationId'), $cvId);

		$orderBy = $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL);
		if (empty($orderBy)) {
			$moduleInstance = $relationListView->getRelatedModuleModel()->getEntityInstance();
			if ($moduleInstance->default_order_by && $moduleInstance->default_sort_order) {
				$orderBy = [];
				foreach ((array) $moduleInstance->default_order_by as $value) {
					$orderBy[$value] = $moduleInstance->default_sort_order;
				}
			}
		}
		if (!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
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
		$searchParams = App\Condition::validSearchParams($relationListView->getQueryGenerator()->getModule(), $request->getArray('search_params'));
		if (empty($searchParams) || !\is_array($searchParams)) {
			$searchParamsRaw = $searchParams = [];
		}
		$queryGenerator = $relationListView->getQueryGenerator();
		$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParams);
		$relationListView->set('search_params', $transformedSearchParams);
		$relationListView->loadSearchLockedFields($request);
		//To make smarty to get the details easily accesible
		foreach ($request->getArray('search_params') as $fieldListGroup) {
			$searchParamsRaw[] = $fieldListGroup;
			foreach ($fieldListGroup as $fieldSearchInfo) {
				$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2] ?? '';
				$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0] ?? '';
				$fieldSearchInfo['specialOption'] = $fieldSearchInfo[3] ?? '';
				$searchParams[$fieldName] = $fieldSearchInfo;
			}
		}
		$showHeader = true;
		if ($request->has('showHeader')) {
			$showHeader = $request->getBoolean('showHeader');
		}
		if ($showHeader) {
			$links = $relationListView->getLinks();
			if (!($request->has('showViews') ? $request->getBoolean('showViews') : true)) {
				unset($links['RELATEDLIST_VIEWS']);
				$relatedView = 'List';
			}
			if (!($request->has('showMassActions') ? $request->getBoolean('showMassActions') : true)) {
				unset($links['RELATEDLIST_MASSACTIONS']);
			}
			$viewer->assign('RELATED_LIST_LINKS', $links);
		}
		if ('ListPreview' === $relatedView) {
			$relationListView->setFields(array_merge(['id'], $relationListView->getRelatedModuleModel()->getNameFields()));
		}
		if ($request->has('fields')) {
			$relationListView->setFields(array_merge(['id'], $request->getArray('fields', 'Alnum')));
		}
		if ($request->has('quickSearchEnabled')) {
			$relationListView->set('quickSearchEnabled', $request->getBoolean('quickSearchEnabled'));
		}
		$models = $relationListView->getEntries($pagingModel);
		$header = $relationListView->getHeaders();
		$relationModel = $relationListView->getRelationModel();
		if ($request->has('sortEnabled')) {
			$relationListView->set('advSortEnabled', $request->getBoolean('sortEnabled'));
		}
		$viewer->assign('VIEW_MODEL', $relationListView);
		$viewer->assign('RELATED_RECORDS', $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_VIEW', $relatedView);
		$viewer->assign('SHOW_SUMMATION_ROW', $request->has('showSummation') ? $request->getBoolean('showSummation') : true);
		$viewer->assign('SHOW_HEADER', $showHeader);
		$viewer->assign('SHOW_CREATOR_DETAIL', $relationModel->showCreatorDetail());
		$viewer->assign('SHOW_COMMENT', $relationModel->showComment());
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relationModel->getRelationModuleModel());
		$viewer->assign('RELATED_ENTIRES_COUNT', \count($models));
		$viewer->assign('RELATION_FIELD', $relationModel->getRelationField());
		if (\App\Config::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			$totalCount = (int) $relationListView->getRelatedEntriesCount();
			$pagingModel->set('totalCount', $totalCount);
		} elseif (!empty($totalCount)) {
			$pagingModel->set('totalCount', $totalCount);
		}
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('TOTAL_ENTRIES', $totalCount);
		$viewer->assign('PAGE_COUNT', $pagingModel->getPageCount());
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $pagingModel->getStartPagingFrom());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('INVENTORY_FIELDS', $relationModel->getRelationInventoryFields());
		$isFavorites = false;
		if ($relationModel->isFavorites() && \App\Privilege::isPermitted($moduleName, 'FavoriteRecords')) {
			$favorites = $relationListView->getFavoriteRecords();
			$viewer->assign('FAVORITES', $favorites);
			$isFavorites = $relationModel->isFavorites();
		}
		$viewer->assign('IS_FAVORITES', $isFavorites);
		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SEARCH_DETAILS', $searchParams);
		$viewer->assign('SEARCH_PARAMS', $searchParamsRaw);
		$viewer->assign('VIEW', $request->getByType('view'));
		$viewer->assign('SHOW_RELATED_WIDGETS', \in_array($relationModel->getId(), App\Config::module($moduleName, 'showRelatedWidgetsByDefault', [])));
		$viewer->assign('LOCKED_EMPTY_FIELDS', $request->isEmpty('lockedEmptyFields', true) ? [] : $request->getArray('lockedEmptyFields'));
		if ($relationListView->isWidgetsList()) {
			$viewer->assign('IS_WIDGETS', true);
			$viewer->assign('HIERARCHY_VALUE', \Config\Modules\ModComments::$defaultSource);
			$viewer->assign('HIERARCHY', \App\ModuleHierarchy::getModuleLevel($relatedModuleName));
		} else {
			$viewer->assign('IS_WIDGETS', false);
		}
		return $this->loadView();
	}

	/**
	 * Load template.
	 */
	public function loadView()
	{
		return $this->viewer->view($this->getTemplateName(), $this->viewer->getTemplateVars('MODULE_NAME'), true);
	}

	/**
	 * Template name.
	 */
	public function getTemplateName()
	{
		return 'RelatedList.tpl';
	}
}
