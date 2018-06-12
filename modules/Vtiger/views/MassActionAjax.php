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

class Vtiger_MassActionAjax_View extends Vtiger_IndexAjax_View
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showMassEditForm');
		$this->exposeMethod('showAddCommentForm');
		$this->exposeMethod('showSendSMSForm');
		$this->exposeMethod('transferOwnership');
	}

	/**
	 * Function returns the mass edit form.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function showMassEditForm(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$cvId = $request->getByType('viewname', 2);
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$viewer = $this->getViewer($request);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if (!$moduleModel->isPermitted('MassEdit')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_MASSEDIT);
		$fieldInfo = [];
		$fieldList = $moduleModel->getFields();
		foreach ($fieldList as $fieldName => $fieldModel) {
			$fieldInfo[$fieldName] = $fieldModel->getFieldInfo();
		}
		$picklistDependencyDatasource = \App\Fields\Picklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODE', 'massedit');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MASS_EDIT_FIELD_DETAILS', $fieldInfo);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		if (!$request->isEmpty('operator', true)) {
			$viewer->assign('OPERATOR', $request->getByType('operator', 1));
			$viewer->assign('ALPHABET_VALUE', $request->get('search_value'));
			$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 1));
		}
		$searchParams = $request->get('search_params');
		if (!empty($searchParams)) {
			$viewer->assign('SEARCH_PARAMS', $searchParams);
		}
		echo $viewer->view('MassEditForm.tpl', $moduleName, true);
	}

	/**
	 * Function returns the Add Comment form.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function showAddCommentForm(\App\Request $request)
	{
		$sourceModule = $request->getModule();
		$moduleName = 'ModComments';
		$cvId = $request->getByType('viewname', 2);
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($sourceModule) || !($moduleModel->isCommentEnabled() && $currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'EditView') && $moduleModel->isPermitted('MassAddComment'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		if (!$request->isEmpty('operator', true)) {
			$viewer->assign('OPERATOR', $request->getByType('operator', 1));
			$viewer->assign('ALPHABET_VALUE', $request->get('search_value'));
			$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 1));
		}
		$searchParams = $request->get('search_params');
		if (!empty($searchParams)) {
			$viewer->assign('SEARCH_PARAMS', $searchParams);
		}
		echo $viewer->view('AddCommentForm.tpl', $moduleName, true);
	}

	/**
	 * Function shows form that will lets you send SMS.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function showSendSMSForm(\App\Request $request)
	{
		$sourceModule = $request->getModule();
		$moduleName = 'SMSNotifier';
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$cvId = $request->getByType('viewname', 2);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'CreateView') || !$currentUserPriviligesModel->hasModuleActionPermission($sourceModule, 'MassSendSMS') || !SMSNotifier_Module_Model::checkServer()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$phoneFields = $moduleModel->getFieldsByType('phone');
		$viewer = $this->getViewer($request);

		if (is_array($selectedIds) && count($selectedIds) === 1) {
			$recordId = current($selectedIds);
			$selectedRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $sourceModule);
			$viewer->assign('SINGLE_RECORD', $selectedRecordModel);
		}
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('PHONE_FIELDS', $phoneFields);
		if (!$request->isEmpty('operator', true)) {
			$viewer->assign('OPERATOR', $request->getByType('operator', 1));
			$viewer->assign('ALPHABET_VALUE', $request->get('search_value'));
			$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 1));
		}
		$searchParams = $request->get('search_params');
		if (!empty($searchParams)) {
			$viewer->assign('SEARCH_PARAMS', $searchParams);
		}
		echo $viewer->view('SendSMSForm.tpl', $moduleName, true);
	}

	/**
	 * Rransfer record ownership.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function transferOwnership(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'MassTransferOwnership')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$transferModel = Vtiger_TransferOwnership_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('REL_BY_FIELDS', $transferModel->getRelationsByFields());
		$viewer->assign('REL_BY_RELATEDLIST', $transferModel->getRelationsByRelatedList());
		$viewer->assign('SKIP_MODULES', $transferModel->getSkipModules());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TransferRecordOwnership.tpl', $moduleName);
	}
}
