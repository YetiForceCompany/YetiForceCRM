<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_ModuleManager_Basic_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateModuleStatus');
		$this->exposeMethod('checkModuleName');
		$this->exposeMethod('createModule');
		$this->exposeMethod('deleteModule');
	}

	/**
	 * The action enable / disable the module.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function updateModuleStatus(App\Request $request)
	{
		$moduleName = $request->getByType('forModule', 'Standard');
		$moduleManagerModel = new Settings_ModuleManager_Module_Model();
		$response = new Vtiger_Response();
		try {
			if ($request->getBoolean('updateStatus')) {
				$moduleManagerModel->enableModule($moduleName);
			} else {
				$moduleManagerModel->disableModule($moduleName);
			}
		} catch (\App\Exceptions\NotAllowedMethod $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Action to check module name.
	 *
	 * @param \App\Request $request
	 */
	public function checkModuleName(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = ucfirst($request->getByType('moduleName', 'Standard'));
		$module = vtlib\Module::getInstance($moduleName);
		$paths = array_map('strtolower', array_keys(iterator_to_array((new \RecursiveDirectoryIterator('modules', FilesystemIterator::SKIP_DOTS)))));
		if ($module || \in_array('modules' . DIRECTORY_SEPARATOR . strtolower($moduleName), $paths)) {
			$result = ['success' => false, 'text' => \App\Language::translate('LBL_MODULE_ALREADY_EXISTS_TRY_ANOTHER', $qualifiedModuleName)];
		} elseif (false !== strpos($moduleName, 'Settings')) {
			$result = ['success' => false, 'text' => \App\Language::translate('LBL_ERROR_MODULE_NAME_CONTAINS_SETTINGS', $qualifiedModuleName)];
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
	public function createModule(App\Request $request)
	{
		$formData = $request->getMultiDimensionArray('formData', [
			'module_name' => 'Standard',
			'module_label' => 'Text',
			'entityfieldname' => 'Text',
			'entityfieldlabel' => 'Text',
			'entitytype' => 'Integer'
		]);
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

	/**
	 * Action delete module.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function deleteModule(App\Request $request)
	{
		$moduleName = $request->getByType('forModule', 'Standard');
		$moduleInstance = vtlib\Module::getInstance($moduleName);
		if ($moduleInstance && 1 === (int) $moduleInstance->customized) {
			$moduleInstance->delete();
			$result = ['success' => true];
		} else {
			$result = ['success' => false];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
