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

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!$request->isEmpty('src_record', true) && !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('src_record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		if (!$request->isEmpty('related_module', true) && !$userPrivilegesModel->hasModulePermission($request->getByType('related_module', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!$request->isEmpty('relatedModule', true) && !$userPrivilegesModel->hasModulePermission($request->getByType('relatedModule'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(\App\Request $request)
	{
		return true;
	}

	public function postProcess(\App\Request $request)
	{
		return true;
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to add relation for specified source record id and related record id list
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function addRelation(\App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		$relatedModule = $request->getByType('related_module', 2);
		if (is_numeric($relatedModule)) {
			$relatedModule = \App\Module::getModuleName($relatedModule);
		}
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		if (!is_array($relatedRecordIdList)) {
			$relatedRecordIdList = [$relatedRecordIdList];
		}
		foreach ($relatedRecordIdList as $relatedRecordId) {
			if (\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedRecordId)) {
				$relationModel->addRelation($sourceRecordId, (int) $relatedRecordId);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function deleteRelation(\App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		$relatedModule = $request->getByType('related_module', 2);
		$relatedRecordIdList = $request->get('related_record_list');

		//Setting related module as current module to delete the relation
		vglobal('currentModule', $relatedModule);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		$result = false;
		foreach ($relatedRecordIdList as $relatedRecordId) {
			if (\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedRecordId)) {
				$result = $relationModel->deleteRelation($sourceRecordId, (int) $relatedRecordId);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to update the relation for specified source record id and related record id list
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function updateRelation(\App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		$relatedModule = $request->getByType('related_module', 2);
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
				if (\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedRecordId)) {
					$relationModel->addRelation($sourceRecordId, $relatedRecordId);
				}
			}
		}
		if (!empty($recordsToRemove)) {
			if ($relationModel->isDeletable()) {
				foreach ($recordsToRemove as $relatedRecordId) {
					$relationModel->deleteRelation((int) $sourceRecordId, (int) $relatedRecordId);
				}
			} else {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
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
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the page count for reltedlist
	 * @param \App\Request $request
	 */
	public function getRelatedListPageCount(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule');
		$parentId = $request->getInteger('record');
		if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $parentId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		$label = $request->get('tab_label');
		$totalCount = 0;
		$pageCount = 0;
		if ($relatedModuleName === 'ModComments') {
			$totalCount = ModComments_Record_Model::getCommentsCount($parentId);
		} elseif ($relatedModuleName === 'ModTracker') {
			$count = (int) ($unreviewed = current(ModTracker_Record_Model::getUnreviewed($parentId, false, true))) ? array_sum($unreviewed) : '';
			$totalCount = $count ? $count : '';
		} else {
			$relModules = !empty($relatedModuleName) ? [$relatedModuleName] : [];
			if ($relatedModuleName === 'ProductsAndServices') {
				$label = '';
				$relModules = ['Products', 'OutsourcedProducts', 'Assets', 'Services', 'OSSOutsourcedServices', 'OSSSoldServices'];
			}
			$categoryCount = ['Products', 'OutsourcedProducts', 'Services', 'OSSOutsourcedServices'];
			$pagingModel = new Vtiger_Paging_Model();
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
			$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			foreach ($relModules as $relModule) {
				if (!$currentUserPriviligesModel->hasModulePermission($relModule)) {
					continue;
				}
				$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relModule, $label);
				if (!$relationListView) {
					continue;
				}
				if ($relatedModuleName === 'ProductsAndServices' && in_array($relModule, $categoryCount)) {
					$totalCount += (int) $relationListView->getRelatedTreeEntriesCount();
				}
				if ($relatedModuleName === 'Calendar' && \AppConfig::module($relatedModuleName, 'SHOW_ONLY_CURRENT_RECORDS_COUNT')) {
					$totalCount += (int) $relationListView->getRelationQuery()->andWhere(['vtiger_activity.status' => Calendar_Module_Model::getComponentActivityStateLabel('current')])->count();
				} else {
					$totalCount += (int) $relationListView->getRelatedEntriesCount();
				}
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

	public function updateFavoriteForRecord(\App\Request $request)
	{
		$sourceModuleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$relatedModuleModel = Vtiger_Module_Model::getInstance($request->getByType('relatedModule'));
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

		if (!empty($relationModel)) {
			$result = $relationModel->updateFavoriteForRecord($request->getByType('actionMode'), ['crmid' => $request->getInteger('record'), 'relcrmid' => $request->getInteger('relcrmid')]);
		}

		$response = new Vtiger_Response();
		$response->setResult((bool) $result);
		$response->emit();
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
