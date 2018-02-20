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
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateModuleStatus');
		$this->exposeMethod('importUserModuleStep3');
		$this->exposeMethod('updateUserModuleStep3');
		$this->exposeMethod('checkModuleName');
		$this->exposeMethod('createModule');
		$this->exposeMethod('deleteModule');
	}

	public function updateModuleStatus(\App\Request $request)
	{
		$moduleName = $request->get('forModule');
		$moduleManagerModel = new Settings_ModuleManager_Module_Model();
		$response = new Vtiger_Response();
		try {
			if ($request->getBoolean('updateStatus')) {
				$moduleManagerModel->enableModule($moduleName);
			} else {
				$moduleManagerModel->disableModule($moduleName);
			}
		} catch (\App\Exceptions\NotAllowedMethod $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	public function importUserModuleStep3(\App\Request $request)
	{
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		vtlib\Deprecated::checkFileAccess($uploadFileName);

		$importType = $request->get('module_import_type');
		if (strtolower($importType) == 'language') {
			$package = new vtlib\Language();
		} elseif (strtolower($importType) == 'layout') {
			$package = new vtlib\Layout();
		} else {
			$package = new vtlib\Package();
		}

		$package->import($uploadFileName);

		\vtlib\Deprecated::checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);

		$result = ['success' => true, 'importModuleName' => $importModuleName];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function updateUserModuleStep3(\App\Request $request)
	{
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		vtlib\Deprecated::checkFileAccess($uploadFileName);

		$importType = strtolower($request->get('module_import_type'));
		if ($importType == 'language') {
			$package = new vtlib\Language();
		} elseif ($importType == 'layout') {
			$package = new vtlib\Layout();
		} else {
			$package = new vtlib\Package();
		}

		if ($importType == 'language' || $importType == 'layout') {
			$package->import($uploadFileName);
		} else {
			$package->update(vtlib\Module::getInstance($importModuleName), $uploadFileName);
		}

		\vtlib\Deprecated::checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);

		$result = ['success' => true, 'importModuleName' => $importModuleName];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}

	/**
	 * Action to check module name.
	 *
	 * @param \App\Request $request
	 */
	public function checkModuleName(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->get('moduleName');
		$module = vtlib\Module::getInstance($moduleName);
		if ($module) {
			$result = ['success' => false, 'text' => \App\Language::translate('LBL_MODULE_ALREADY_EXISTS_TRY_ANOTHER', $qualifiedModuleName)];
		} elseif (Settings_ModuleManager_Module_Model::checkModuleName($moduleName)) {
			$result = ['success' => false, 'text' => \App\Language::translate('LBL_INVALID_MODULE_NAME', $qualifiedModuleName)];
		} else {
			$result = ['success' => true];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Action to create module.
	 *
	 * @param \App\Request $request
	 */
	public function createModule(\App\Request $request)
	{
		$formData = $request->get('formData');
		$moduleName = $formData['module_name'];
		if (!Settings_ModuleManager_Module_Model::checkModuleName($moduleName)) {
			$result = ['success' => true, 'text' => ucfirst($moduleName)];
			$moduleManagerModel = new Settings_ModuleManager_Module_Model();
			try {
				$moduleManagerModel->createModule($formData);
			} catch (Exception $e) {
				$result = ['success' => false, 'text' => $e->getMessage()];
			}
		} else {
			$result = ['success' => false, 'text' => \App\Language::translate('LBL_INVALID_MODULE_NAME', $request->getModule(false))];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function deleteModule(\App\Request $request)
	{
		$moduleName = $request->get('forModule');
		$moduleInstance = vtlib\Module::getInstance($moduleName);
		if ($moduleInstance && (int) $moduleInstance->customized === 1) {
			$moduleInstance->delete();
			$result = ['success' => true];
		} else {
			$result = ['success' => false];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function updateColor(\App\Request $request)
	{
		$id = $request->getInteger('id');
		$color = $request->get('color');
		if (!$color) {
			$color = \App\Colors::getRandomColor();
		}
		\App\Colors::updateModuleColor($id, $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function removeColor(\App\Request $request)
	{
		\App\Colors::updateModuleColor($request->getInteger('id'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function activeColor(\App\Request $request)
	{
		$color = \App\Colors::activeModuleColor($request->getInteger('id'), $request->get('status'), $request->get('color'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}
}
