<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_ModuleManager_List_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$viewer->assign('ALL_MODULES', Settings_ModuleManager_Module_Model::getAll());
		$viewer->assign('RESTRICTED_MODULES_LIST', Settings_ModuleManager_Module_Model::getActionsRestrictedModulesList());
		$viewer->assign('IMPORT_MODULE_URL', Settings_ModuleManager_Module_Model::getNewModuleImportUrl());
		$viewer->assign('IMPORT_USER_MODULE_URL', Settings_ModuleManager_Module_Model::getUserModuleImportUrl());
		$viewer->assign('ICONS', \App\YetiForce\Shop::PREMIUM_ICONS);
		$viewer->assign('MODULE', $moduleName);
		echo $viewer->view('ListContents.tpl', $qualifiedModuleName, true);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.ListSearch',
			"modules.{$moduleName}.resources.ListSearch"
		]));
	}
}
