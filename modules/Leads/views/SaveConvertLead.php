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
vimport('~include/Webservices/ConvertLead.php');

class Leads_SaveConvertLead_View extends Vtiger_View_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'ConvertLead')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}

		$recordPermission = \App\Privilege::isPermitted($moduleName, 'EditView', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		if (!Leads_Module_Model::checkIfAllowedToConvert($recordModel->get('leadstatus'))) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		
	}

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$modules = $request->get('modules');
		$assignId = $request->get('assigned_user_id');
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$entityValues = [];
		$entityValues['transferRelatedRecordsTo'] = $request->get('transferModule');
		$entityValues['assignedTo'] = $assignId;
		$entityValues['leadId'] = $recordId;
		$createAlways = Vtiger_Processes_Model::getConfig('marketing', 'conversion', 'create_always');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $request->getModule());
		$convertLeadFields = $recordModel->getConvertLeadFields();
		$availableModules = ['Accounts'];
		foreach ($availableModules as $module) {
			if (\App\Module::isModuleActive($module) && in_array($module, $modules)) {
				$entityValues['entities'][$module]['create'] = true;
				$entityValues['entities'][$module]['name'] = $module;
				foreach ($convertLeadFields[$module] as $fieldModel) {
					$fieldName = $fieldModel->getName();
					$fieldValue = $fieldModel->getUITypeModel()->getDBValue($request->get($fieldName, null));
					$entityValues['entities'][$module][$fieldName] = $fieldValue;
				}
			}
		}
		try {
			$results = true;
			if ($createAlways === true || $createAlways === 'true') {
				$leadModel = Vtiger_Module_Model::getCleanInstance($request->getModule());
				$results = $leadModel->searchAccountsToConvert($recordModel);
				$entityValues['entities']['Accounts']['convert_to_id'] = $results;
			}
			if (!$results) {
				$message = vtranslate('LBL_TOO_MANY_ACCOUNTS_TO_CONVERT', $request->getModule(), '');
				if ($currentUser->isAdminUser()) {
					$message = vtranslate('LBL_TOO_MANY_ACCOUNTS_TO_CONVERT', $request->getModule(), '<a href="index.php?module=MarketingProcesses&view=Index&parent=Settings"><span class="glyphicon glyphicon-folder-open"></span></a>');
				}
				$this->showError($request, '', $message);
				throw new \Exception\AppException('LBL_TOO_MANY_ACCOUNTS_TO_CONVERT');
			}
		} catch (Exception $e) {
			$this->showError($request, $e);
			throw new \Exception\AppException($e->getMessage());
		}
		try {
			$result = vtws_convertlead($entityValues, $currentUser);
		} catch (Exception $e) {
			$this->showError($request, $e);
			throw new \Exception\AppException($e->getMessage());
		}

		if (!empty($result['Accounts'])) {
			$accountId = $result['Accounts'];
		}

		if (!empty($accountId)) {
			ModTracker_Record_Model::addConvertToAccountRelation('Accounts', $accountId, $assignId);
			header("Location: index.php?view=Detail&module=Accounts&record=$accountId");
		} else {
			$this->showError($request);
			throw new \Exception\AppException('Error');
		}
	}

	public function showError($request, $exception = false, $message = '')
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		if ($exception != false) {
			$viewer->assign('EXCEPTION', vtranslate($exception->getMessage(), $moduleName));
		} elseif ($message) {
			$viewer->assign('EXCEPTION', $message);
		}

		$viewer->assign('CURRENT_USER', $currentUser);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('ConvertLeadError.tpl', $moduleName);
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
