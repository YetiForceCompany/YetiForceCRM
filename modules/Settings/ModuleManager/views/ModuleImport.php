<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Settings_ModuleManager_ModuleImport_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Settings_ModuleManager_ModuleImport_View constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('importUserModuleStep1');
		$this->exposeMethod('importUserModuleStep2');
		$this->exposeMethod('importUserModuleStep3');
		$this->exposeMethod('updateUserModuleStep3');
	}

	/** {@inheritdoc} */
	public function getViewer(App\Request $request)
	{
		$viewer = parent::getViewer($request);
		$viewer->assign('MODULE_NAME', $request->getModule(false));
		return $viewer;
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$systemMode = \App\Config::main('systemMode');
		if ('demo' == $systemMode) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_ERROR_IMPORT_IN_DEMO'));
		}
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('importUserModuleStep1.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.Settings.{$request->getModule()}.resources.ModuleImport",
		]));
	}

	public function importUserModuleStep1(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('ImportUserModuleStep1.tpl', $qualifiedModuleName);
	}

	public function importUserModuleStep2(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$qualifiedModuleName = $request->getModule(false);
		if ($request->has('upgradePackage')) {
			$uploadFile = $request->getByType('upgradePackage', 'Alnum') . '.zip';
		} else {
			$uploadFile = 'usermodule_' . time() . '.zip';
		}
		$uploadFileName = "$uploadDir/$uploadFile";
		$error = '';
		\vtlib\Deprecated::checkFileAccess($uploadDir);
		if (!$request->has('upgradePackage') && !move_uploaded_file($_FILES['moduleZip']['tmp_name'], $uploadFileName)) {
			$error = 'LBL_ERROR_MOVE_UPLOADED_FILE';
		} else {
			$package = new vtlib\Package();
			$importModuleName = $package->getModuleNameFromZip($uploadFileName);
			$importModuleDepVtVersion = $package->getDependentVtigerVersion();
			if (null === $importModuleName || $package->_errorText) {
				$error = $package->_errorText;
				\vtlib\Deprecated::checkFileAccessForDeletion($uploadFileName);
				unlink($uploadFileName);
			} else {
				// We need these information to push for Update if module is detected to be present.
				$moduleLicence = App\Purifier::purify($package->getLicense());
				$viewer->assign('MODULEIMPORT_FILE', $uploadFile);
				$viewer->assign('MODULEIMPORT_TYPE', $package->type());
				$viewer->assign('MODULEIMPORT_NAME', $importModuleName);
				$viewer->assign('MODULEIMPORT_PACKAGE', $package);
				$viewer->assign('MODULEIMPORT_DEP_VTVERSION', $importModuleDepVtVersion);
				$viewer->assign('MODULEIMPORT_LICENSE', $moduleLicence);
				$viewer->assign('MODULEIMPORT_PARAMETERS', $package->getParameters());
				if (!$package->isLanguageType() && !$package->isUpdateType() && !$package->isModuleBundle()) {
					$moduleInstance = vtlib\Module::getInstance($importModuleName);
					$moduleimport_exists = ($moduleInstance) ? 'true' : 'false';
					$moduleimport_dir_name = "modules/$importModuleName";
					$moduleimport_dir_exists = (is_dir($moduleimport_dir_name) ? 'true' : 'false');
					$viewer->assign('MODULEIMPORT_EXISTS', $moduleimport_exists);
					$viewer->assign('MODULEIMPORT_DIR', $moduleimport_dir_name);
					$viewer->assign('MODULEIMPORT_DIR_EXISTS', $moduleimport_dir_exists);
				} else {
					$viewer->assign('MODULEIMPORT_EXISTS', false);
					$viewer->assign('MODULEIMPORT_DIR_EXISTS', false);
				}
			}
		}
		$viewer->assign('ICONS', \App\YetiForce\Shop::PREMIUM_ICONS);
		$viewer->assign('MODULEIMPORT_ERROR', $error);
		$viewer->view('ImportUserModuleStep2.tpl', $qualifiedModuleName);
	}

	public function importUserModuleStep3(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		\vtlib\Deprecated::checkFileAccess($uploadFileName);

		$importModuleType = false;
		$importType = $request->getByType('module_import_type');
		if ('language' === strtolower($importType)) {
			$package = new vtlib\Language();
			$importModuleType = 'Language';
		} elseif ('layout' === strtolower($importType)) {
			$package = new vtlib\Layout();
			$importModuleType = 'Layout';
		} else {
			$package = new vtlib\Package();
		}
		$package->initParameters($request);
		$package->import($uploadFileName);

		if ($package->packageType) {
			$importModuleType = $package->packageType;
		}
		if ('' != $package->_errorText) {
			$viewer->assign('MODULEIMPORT_ERROR', $package->_errorText);
		}
		\vtlib\Deprecated::checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);

		$viewer->assign('IMPORT_MODULE_TYPE', $importModuleType);
		$viewer->assign('IMPORT_MODULE_NAME', $importModuleName);
		$viewer->assign('MODULEIMPORT_LABEL', (string) ($package->_modulexml->label ?? $package->_modulexml->name));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('ImportUserModuleStep3.tpl', $qualifiedModuleName);
	}

	public function updateUserModuleStep3(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		\vtlib\Deprecated::checkFileAccess($uploadFileName);

		$importType = $request->get('module_import_type');
		if ('language' == strtolower($importType)) {
			$package = new vtlib\Language();
		} else {
			$package = new vtlib\Package();
		}
		$package->initParameters($request);

		if ('language' == strtolower($importType)) {
			$package->import($uploadFileName);
		} else {
			$package->update(vtlib\Module::getInstance($importModuleName), $uploadFileName);
		}

		\vtlib\Deprecated::checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);

		$viewer->assign('UPDATE_MODULE_NAME', $importModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('UpdateUserModuleStep3.tpl', $qualifiedModuleName);
	}
}
