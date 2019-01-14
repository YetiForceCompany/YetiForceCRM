<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_CustomRecordNumbering_View extends Settings_Vtiger_Index_View
{
	/**
	 * Page title.
	 *
	 * @var type
	 */
	protected $pageTitle = 'LBL_CUSTOMIZE_RECORD_NUMBERING';

	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$supportedModules = Settings_Vtiger_CustomRecordNumberingModule_Model::getSupportedModules();

		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule) {
			$defaultModuleModel = $supportedModules[\App\Module::getModuleId($sourceModule)];
		} else {
			$defaultModuleModel = reset($supportedModules);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModules);
		$viewer->assign('DEFAULT_MODULE_MODEL', $defaultModuleModel);
		$viewer->assign('PICKLISTS', $defaultModuleModel->getFieldsByType(['picklist']));
		$viewer->view('CustomRecordNumbering.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.CustomRecordNumbering',
			'libraries.clipboard.dist.clipboard',
			'modules.Settings.Vtiger.resources.Edit',
		]));
	}
}
