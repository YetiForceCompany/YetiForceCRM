<?php

/**
 * Popup View Class for Products
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Products_Popup_View extends Vtiger_Popup_View
{
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */

	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $this->getModule($request);
		$cvId = $request->get('cvid');
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$sourceModule = $request->get('src_module');
		$sourceField = $request->get('src_field');
		$sourceRecord = $request->get('src_record');
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$currencyId = $request->get('currency_id');
		$relatedParentModule = $request->get('related_parent_module');
		$relatedParentId = $request->get('related_parent_id');
		$filterFields = $request->get('filterFields');

		//To handle special operation when selecting record from Popup
		$getUrl = $request->get('get_url');

		//Check whether the request is in multi select mode
		$multiSelectMode = $request->get('multi_select');
		if (empty($multiSelectMode)) {
			$multiSelectMode = false;
		}

		if (empty($cvId)) {
			$cvId = '0';
		}
		if (empty($pageNumber)) {
			$pageNumber = '1';
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (vglobal('popupAjax'))
			$pagingModel->set('noLimit', true);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

		if (!\App\Record::isExists($relatedParentId)) {
			$relatedParentModule = '';
			$relatedParentId = '';
		}
		if (!empty($relatedParentModule) && !empty($relatedParentId)) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
			$listViewModel = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, $label);
		} else {
			$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName, $sourceModule);
		}
		if (empty($orderBy) && empty($sortOrder)) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$orderBy = $moduleInstance->default_order_by;
			$sortOrder = $moduleInstance->default_sort_order;
		}
		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}
		if (!empty($filterFields)) {
			$listViewModel->set('filterFields', $filterFields);
		}
		if (!empty($sourceModule)) {
			$listViewModel->set('src_module', $sourceModule);
			$listViewModel->set('src_field', $sourceField);
			$listViewModel->set('src_record', $sourceRecord);
		}
		if ((!empty($searchKey)) && (!empty($searchValue))) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
		}
		$searchParmams = $request->get('search_params');
		if (empty($searchParmams)) {
			$searchParmams = array();
		}
		$transformedSearchParams = $listViewModel->get('query_generator')->parseBaseSearchParamsToCondition($searchParmams);
		$listViewModel->set('search_params', $transformedSearchParams);
		//To make smarty to get the details easily accesible
		foreach ($searchParmams as $fieldListGroup) {
			foreach ($fieldListGroup as $fieldSearchInfo) {
				$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
				$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
				$searchParmams[$fieldName] = $fieldSearchInfo;
			}
		}
		// Limit the choice of products/services only to the ones related to currently selected Opportunity - second step.
		if (Settings_SalesProcesses_Module_Model::checkRelatedToPotentialsLimit($sourceModule)) {
			$salesProcessId = $request->get('salesprocessid');
			if (empty($salesProcessId))
				$salesProcessId = -1;
			$listViewModel->set('salesprocessid', $salesProcessId);
			$viewer->assign('INVENTORY_LIMITED_FROM_POTENTIALS', true);
		}
		if (!empty($relatedParentModule) && !empty($relatedParentId)) {
			$this->listViewHeaders = $listViewModel->getHeaders();
			$this->listViewEntries = $listViewModel->getEntries($pagingModel);
			if (count($this->listViewEntries) > 0) {
				$parentRelatedRecords = true;
			}
		} else {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}

		// If there are no related records with parent module then, we should show all the records
		if (!$parentRelatedRecords && !empty($relatedParentModule) && !empty($relatedParentId)) {
			$relatedParentModule = null;
			$relatedParentId = null;
			$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName, $sourceModule);
			$listViewModel->set('search_params', $transformedSearchParams);
			if (!empty($orderBy)) {
				$listViewModel->set('orderby', $orderBy);
				$listViewModel->set('sortorder', $sortOrder);
			}
			if (!empty($sourceModule)) {
				$listViewModel->set('src_module', $sourceModule);
				$listViewModel->set('src_field', $sourceField);
				$listViewModel->set('src_record', $sourceRecord);
			}
			if ((!empty($searchKey)) && (!empty($searchValue))) {
				$listViewModel->set('search_key', $searchKey);
				$listViewModel->set('search_value', $searchValue);
			}
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		// End

		$noOfEntries = count($this->listViewEntries);

		if (empty($sortOrder)) {
			$sortOrder = "ASC";
		}
		if ($sortOrder == "ASC") {
			$nextSortOrder = "DESC";
			$sortImage = "downArrowSmall.png";
		} else {
			$nextSortOrder = "ASC";
			$sortImage = "upArrowSmall.png";
		}
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RELATED_MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', $moduleName);

		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SOURCE_FIELD', $sourceField);
		$viewer->assign('SOURCE_RECORD', $sourceRecord);
		$viewer->assign('RELATED_PARENT_MODULE', $relatedParentModule);
		$viewer->assign('RELATED_PARENT_ID', $relatedParentId);

		$viewer->assign('SEARCH_KEY', $searchKey);
		$viewer->assign('SEARCH_VALUE', $searchValue);

		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('GETURL', $getUrl);
		$viewer->assign('CURRENCY_ID', $currencyId);

		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pageNumber);

		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);

		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			if (!$this->listViewCount) {
				$this->listViewCount = $listViewModel->getListViewCount();
			}
			$totalCount = $this->listViewCount;
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if ($pageCount == 0) {
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('LISTVIEW_COUNT', $totalCount);
		}

		$viewer->assign('MULTI_SELECT', $multiSelectMode);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SEARCH_DETAILS', $searchParmams);
	}
}
