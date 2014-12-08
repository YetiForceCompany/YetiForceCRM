<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

class Potentials_Popup_View extends Vtiger_Popup_View {

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$moduleName = $this->getModule($request);
		$sourceModule = $request->get('src_module');
		$sourceField = $request->get('src_field');

		if(in_array($sourceModule, array('Quotes', 'SalesOrder','OSSCosts')) && ($sourceField == 'potential_id' || $sourceField == 'potentialid')) {
            $relatedParentModule = $request->get('related_parent_module');
			$relatedParentId = $request->get('related_parent_id');

            if(empty($relatedParentModule)) {
                return parent::initializeListViewContents($request, $viewer);
            }

			$cvId = $request->get('cvid');
			$pageNumber = $request->get('page');
			$orderBy = $request->get('orderby');
			$sortOrder = $request->get('sortorder');
			$sourceRecord = $request->get('src_record');
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$currencyId = $request->get('currency_id');

			$requestedPage = $pageNumber;
			if(empty ($requestedPage)) {
				$requestedPage = 1;
			}

			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page',$requestedPage);

			$parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
			$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, $label);
			if(empty($orderBy) && empty($sortOrder)) {
				$moduleInstance = CRMEntity::getInstance($moduleName);
				$orderBy = $moduleInstance->default_order_by;
				$sortOrder = $moduleInstance->default_sort_order;
			}
			if($sortOrder == "ASC") {
				$nextSortOrder = "DESC";
				$sortImage = "icon-chevron-down";
			} else {
				$nextSortOrder = "ASC";
				$sortImage = "icon-chevron-up";
			}
			if(!empty($orderBy)) {
				$relationListView->set('orderby', $orderBy);
				$relationListView->set('sortorder',$sortOrder);
			}

			$headers = $relationListView->getHeaders();
			$models = $relationListView->getEntries($pagingModel);
			$noOfEntries = count($models);
			foreach ($models as $recordId => $recordModel) {
				foreach ($headers as $fieldName => $fieldModel) {
					$recordModel->set($fieldName, $recordModel->getDisplayValue($fieldName));
				}
				$models[$recordId] = $recordModel;
			}

			//To handle special operation when selecting record from Popup
			$getUrl = $request->get('get_url');

			//Check whether the request is in multi select mode
			$multiSelectMode = $request->get('multi_select');
			if(empty($multiSelectMode)) {
				$multiSelectMode = false;
			}

			if(empty($cvId)) {
				$cvId = '0';
			}
			if(empty ($pageNumber)){
				$pageNumber = '1';
			}

			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

			$viewer->assign('MODULE', $moduleName);

			$viewer->assign('SOURCE_MODULE', $sourceModule);
			$viewer->assign('SOURCE_FIELD', $sourceField);
			$viewer->assign('SOURCE_RECORD', $sourceRecord);

			$viewer->assign('RELATED_PARENT_MODULE', $relatedParentModule);
			$viewer->assign('RELATED_PARENT_ID', $relatedParentId);

			$viewer->assign('SEARCH_KEY', $searchKey);
			$viewer->assign('SEARCH_VALUE', $searchValue);

			$viewer->assign('ORDER_BY',$orderBy);
			$viewer->assign('SORT_ORDER',$sortOrder);
			$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
			$viewer->assign('SORT_IMAGE',$sortImage);
			$viewer->assign('GETURL', $getUrl);
			$viewer->assign('CURRENCY_ID', $currencyId);

			$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
			$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

			$viewer->assign('PAGING_MODEL', $pagingModel);
			$viewer->assign('PAGE_NUMBER',$pageNumber);

			$viewer->assign('LISTVIEW_ENTIRES_COUNT',$noOfEntries);
			$viewer->assign('LISTVIEW_HEADERS', $headers);
			$viewer->assign('LISTVIEW_ENTRIES', $models);
			
			if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
				if(!$this->listViewCount){
					$this->listViewCount = $relationListView->getRelatedEntriesCount();
				}
				$totalCount = $this->listViewCount;
				$pageLimit = $pagingModel->getPageLimit();
				$pageCount = ceil((int) $totalCount / (int) $pageLimit);

				if($pageCount == 0){
					$pageCount = 1;
				}
				$viewer->assign('PAGE_COUNT', $pageCount);
				$viewer->assign('LISTVIEW_COUNT', $totalCount);
			}

			$viewer->assign('MULTI_SELECT', $multiSelectMode);
			$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		} else {
			return parent::initializeListViewContents($request, $viewer);
		}
	}
}