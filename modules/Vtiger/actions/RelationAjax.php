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

class Vtiger_RelationAjax_Action extends Vtiger_Action_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addRelation');
		$this->exposeMethod('deleteRelation');
		$this->exposeMethod('updateRelation');
		$this->exposeMethod('getRelatedListPageCount');
		$this->exposeMethod('updateFavoriteForRecord');
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(Vtiger_Request $request)
	{
		return true;
	}

	public function postProcess(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 * 		keys					Content
	 * 		src_module				source module name
	 * 		src_record				source record id
	 * 		related_module			related module name
	 * 		related_record_list		json encoded of list of related record ids
	 */

	public function addRelation($request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		if (is_numeric($relatedModule)) {
			$relatedModule = vtlib\Functions::getModuleName($relatedModule);
		}
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		if (!is_array($relatedRecordIdList)) {
			$relatedRecordIdList = [$relatedRecordIdList];
		}
		foreach ($relatedRecordIdList as $relatedRecordId) {
			$relationModel->addRelation($sourceRecordId, $relatedRecordId);
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 * 		keys					Content
	 * 		src_module				source module name
	 * 		src_record				source record id
	 * 		related_module			related module name
	 * 		related_record_list		json encoded of list of related record ids
	 */
	public function deleteRelation($request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		//Setting related module as current module to delete the relation
		vglobal('currentModule', $relatedModule);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach ($relatedRecordIdList as $relatedRecordId) {
			$result = $relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to update the relation for specified source record id and related record id list
	 * @param <array> $request
	 * 		keys					Content
	 * 		src_module				source module name
	 * 		src_record				source record id
	 * 		related_module			related module name
	 * 		toRemove				list of related record to remove
	 * 		toAdd					list of related record to add
	 */
	public function updateRelation(Vtiger_Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');
		$relatedModule = $request->get('related_module');
		$recordsToRemove = $request->get('recordsToRemove');
		$recordsToAdd = $request->get('recordsToAdd');
		$categoryToAdd = $request->get('categoryToAdd');
		$categoryToRemove = $request->get('categoryToRemove');
		vglobal('currentModule', $sourceModule);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

		if (!empty($recordsToAdd)) {
			foreach ($recordsToAdd as $relatedRecordId) {
				$relationModel->addRelation($sourceRecordId, $relatedRecordId);
			}
		}
		if (!empty($recordsToRemove)) {
			if ($relationModel->isDeletable()) {
				foreach ($recordsToRemove as $relatedRecordId) {
					$relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
				}
			} else {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		}
		if (!empty($categoryToAdd)) {
			foreach ($categoryToAdd as $category) {
				$relationModel->addRelTree($sourceRecordId, $category);
			}
		}
		if (!empty($categoryToRemove)) {
			if ($relationModel->isDeletable()) {
				foreach ($categoryToRemove as $category) {
					$relationModel->deleteRelTree($sourceRecordId, $category);
				}
			} else {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the page count for reltedlist
	 * @return total number of pages
	 */
	public function getRelatedListPageCount(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$relModules = $relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$totalCount = 0;
		if (!is_array($relatedModuleName)) {
			$relModules = !empty($relatedModuleName) ? [$relatedModuleName] : [];
		}
		$pageCount = 0;
		if (in_array('ProductsAndServices', $relModules)) {
			$label = '';
			$relModules = ['Products', 'OutsourcedProducts', 'Assets', 'Services', 'OSSOutsourcedServices', 'OSSSoldServices'];
		}
		if (in_array('Comments', $relModules)) {
			$totalCount = ModComments_Record_Model::getCommentsCount($parentId);
		} elseif ($relatedModuleName == 'Updates') {
			$count = (int) ($unreviewed = current(ModTracker_Record_Model::getUnreviewed($parentId, false, true))) ? array_sum($unreviewed) : '';
			$totalCount = $count ? $count : '';
		} else {
			$categoryCount = ['Products', 'OutsourcedProducts', 'Services', 'OSSOutsourcedServices'];
			$pagingModel = new Vtiger_Paging_Model();
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
			foreach ($relModules as $relModule) {
				if (!\App\Privilege::isPermitted($relModule)) {
					continue;
				}
				$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relModule, $label);
				if (!$relationListView->getRelationModel()) {
					continue;
				}
				if ($relatedModuleName == 'ProductsAndServices' && in_array($relModule, $categoryCount)) {
					$totalCount += (int) $relationListView->getRelatedTreeEntriesCount();
				}
				$totalCount += (int) $relationListView->getRelatedEntriesCount();
				$pageLimit = $pagingModel->getPageLimit();
				$pageCount = ceil((int) $totalCount / (int) $pageLimit);
			}
		}
		if ($pageCount == 0) {
			$pageCount = 1;
		}
		$result = [];
		$result['numberOfRecords'] = $totalCount;
		$result['page'] = $pageCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function updateFavoriteForRecord(Vtiger_Request $request)
	{
		$sourceModule = $request->getModule();
		$relatedModule = $request->get('relatedModule');
		$actionMode = $request->get('actionMode');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

		if (!empty($relationModel)) {
			$result = $relationModel->updateFavoriteForRecord($actionMode, ['crmid' => $request->get('record'), 'relcrmid' => $request->get('relcrmid')]);
		}

		$response = new Vtiger_Response();
		$response->setResult((bool) $result);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
