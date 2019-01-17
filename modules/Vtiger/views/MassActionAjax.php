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
		$viewer->assign('CVID', $request->getByType('viewname', 2));
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', 2));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', 2));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MASS_EDIT_FIELD_DETAILS', $fieldInfo);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('OPERATOR', $request->getByType('operator'));
		$viewer->assign('ALPHABET_VALUE', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $request->getByType('search_key', 'Alnum'), $request->getByType('operator')));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 'Alnum'));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($moduleName, $request->getArray('search_params')));
		$viewer->view('MassEditForm.tpl', $moduleName);
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
		$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($sourceModule) || !($moduleModel->isCommentEnabled() && $currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'EditView') && $moduleModel->isPermitted('MassAddComment'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $request->getByType('viewname', 2));
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', 2));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', 2));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('OPERATOR', $request->getByType('operator'));
		$viewer->assign('ALPHABET_VALUE', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $request->getByType('search_key', 'Alnum'), $request->getByType('operator')));
		$viewer->assign('ENTITY_STATE', $request->getByType('entityState'));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 'Alnum'));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($sourceModule, $request->getArray('search_params')));
		$viewer->view('AddCommentForm.tpl', $moduleName);
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
		$selectedIds = $request->getArray('selected_ids', 2);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'CreateView') || !$currentUserPriviligesModel->hasModuleActionPermission($sourceModule, 'MassSendSMS') || !SMSNotifier_Module_Model::checkServer()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$phoneFields = $moduleModel->getFieldsByType('phone');
		$viewer = $this->getViewer($request);

		if (is_array($selectedIds) && count($selectedIds) === 1 && $selectedIds[0] !== 'all') {
			$recordId = current($selectedIds);
			$selectedRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $sourceModule);
			$viewer->assign('SINGLE_RECORD', $selectedRecordModel);
		}
		$viewer->assign('VIEWNAME', $request->getByType('viewname', 2));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', 2));
		$viewer->assign('ENTITY_STATE', $request->getByType('entityState'));
		$viewer->assign('PHONE_FIELDS', $phoneFields);
		$viewer->assign('OPERATOR', $request->getByType('operator'));
		$viewer->assign('ALPHABET_VALUE', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $request->getByType('search_key', 'Alnum'), $request->getByType('operator')));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 'Alnum'));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($sourceModule, $request->getArray('search_params')));
		$viewer->view('SendSMSForm.tpl', $moduleName);
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
