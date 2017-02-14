<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSTimeControl_InRelation_View extends Vtiger_RelatedList_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$totalCount = $request->get('totalCount');
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
		if ($sortOrder == 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'icon-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'icon-chevron-up';
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

		$relatedSummary = $relatedModuleModel->getRelatedSummary($relationListView->getRelationQuery());

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_SUMMARY', $relatedSummary);
		$viewer->assign('RELATED_MODULE_NAME', $relatedModuleName);
		$viewer->view('RelatedSummary.tpl', $relatedModuleName);

		$viewer->assign('RELATED_RECORDS', $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);

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

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);

		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SEARCH_DETAILS', $searchParmams);
		$viewer->assign('VIEW', $request->get('view'));
		return $viewer->view('RelatedList.tpl', $moduleName, 'true');
	}
}
