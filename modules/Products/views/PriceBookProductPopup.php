<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Products_PriceBookProductPopup_View extends Vtiger_Popup_View
{

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$this->initializeListViewContents($request, $viewer);

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('TRIGGER_EVENT_NAME', $request->get('triggerEventName'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->view('PriceBookProductPopup.tpl', 'Products');
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->get('src_module');
		$jsFileNames = array(
			"modules.$moduleName.resources.PriceBooksPopup",
			'modules.Vtiger.resources.validator.BaseValidator',
			'modules.Vtiger.resources.validator.FieldValidator',
			"modules.$moduleName.resources.validator.FieldValidator"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */

	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getModule();
		$cvId = $request->get('cvid');
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$sourceModule = $request->get('src_module');
		$sourceField = $request->get('src_field');
		$sourceRecord = $request->get('src_record');
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');

		if (empty($cvId)) {
			$cvId = '0';
		}
		if (empty($pageNumber)) {
			$pageNumber = '1';
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
		if (empty($orderBy) && empty($sortOrder)) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$orderBy = $moduleInstance->default_order_by;
			$sortOrder = $moduleInstance->default_sort_order;
		}
		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}
		if (!empty($sourceModule)) {
			$listViewModel->set('src_module', $sourceModule);
			$listViewModel->set('src_field', $sourceField);
			$listViewModel->set('src_record', $sourceRecord);
			$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
			$currencyId = $sourceRecordModel->get('currency_id');
		}
		if ((!empty($searchKey)) && (!empty($searchValue))) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
		}

		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}

		if ($currencyId) {
			foreach ($this->listViewEntries as $recordId => $recordModel) {
				$productIdsList[$recordId] = $recordId;
			}
			$unitPricesList = $moduleModel->getPricesForProducts($currencyId, $productIdsList);

			foreach ($this->listViewEntries as $recordId => $recordModel) {
				$recordModel->set('unit_price', $unitPricesList[$recordId]);
			}
		}

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
		$viewer->assign('MODULE', $request->getModule());

		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SOURCE_FIELD', $sourceField);
		$viewer->assign('SOURCE_RECORD', $sourceRecord);
		//PARENT_MODULE is used for only translations
		$viewer->assign('PARENT_MODULE', 'Products');

		$viewer->assign('SEARCH_KEY', $searchKey);
		$viewer->assign('SEARCH_VALUE', $searchValue);

		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);

		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pageNumber);

		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);

		$viewer->assign('VIEW', 'PriceBookProductPopup');
	}
}
