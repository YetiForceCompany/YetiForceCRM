<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_ModuleManager_Basic_Action extends Settings_Vtiger_IndexAjax_View
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateModuleStatus');
		$this->exposeMethod('importUserModuleStep3');
		$this->exposeMethod('updateUserModuleStep3');
		$this->exposeMethod('checkModuleName');
		$this->exposeMethod('createModule');
		$this->exposeMethod('deleteModule');
	}

	function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function updateModuleStatus(Vtiger_Request $request)
	{
		$moduleName = $request->get('forModule');
		$updateStatus = $request->get('updateStatus');

		$moduleManagerModel = new Settings_ModuleManager_Module_Model();

		if ($updateStatus == 'true') {
			$moduleManagerModel->enableModule($moduleName);
		} else {
			$moduleManagerModel->disableModule($moduleName);
		}

		$response = new Vtiger_Response();
		$response->emit();
	}

	public function importUserModuleStep3(Vtiger_Request $request)
	{
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		checkFileAccess($uploadFileName);

		$importType = $request->get('module_import_type');
		if (strtolower($importType) == 'language') {
			$package = new Vtiger_Language();
		} else if (strtolower($importType) == 'layout') {
			vimport('vtlib.Vtiger.Layout');
			$package = new Vtiger_Layout();
		} else {
			$package = new Vtiger_Package();
		}

		$package->import($uploadFileName);
		checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);

		$result = array('success' => true, 'importModuleName' => $importModuleName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function updateUserModuleStep3(Vtiger_Request $request)
	{
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		checkFileAccess($uploadFileName);

		$importType = strtolower($request->get('module_import_type'));
		if ($importType == 'language') {
			$package = new Vtiger_Language();
		} else if ($importType == 'layout') {
			vimport('vtlib.Vtiger.Layout');
			$package = new Vtiger_Layout();
		} else {
			$package = new Vtiger_Package();
		}

		if ($importType == 'language' || $importType == 'layout') {
			$package->import($uploadFileName);
		} else {
			$package->update(Vtiger_Module::getInstance($importModuleName), $uploadFileName);
		}

		checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);

		$result = array('success' => true, 'importModuleName' => $importModuleName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}

	public function checkModuleName(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->get('moduleName');
		$module = Vtiger_Module::getInstance($moduleName);
		if ($module) {
			$result = array('success' => false, 'text' => vtranslate('LBL_MODULE_ALREADY_EXISTS_TRY_ANOTHER', $qualifiedModuleName));
		} elseif (preg_match('/[^A-Za-z]/i', $moduleName)) {
			$result = array('success' => false, 'text' => vtranslate('LBL_INVALID_MODULE_NAME', $qualifiedModuleName));
		} else {
			$result = array('success' => true);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function createModule(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$formData = $request->get('formData');
		$moduleManagerModel = new Settings_ModuleManager_Module_Model();
		$result = array('success' => true, 'text' => ucfirst($formData['module_name']));
		try {
			$moduleManagerModel->createModule($formData);
		} catch (Exception $e) {
			$result = array('success' => false, 'text' => $e->getMessage());
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function deleteModule(Vtiger_Request $request)
	{
		$moduleName = $request->get('forModule');
		$moduleInstance = Vtiger_Module::getInstance($moduleName);
		if ($moduleInstance) {
			$moduleInstance->delete();
			$result = array('success' => true);
		} else
			$result = array('success' => false);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
