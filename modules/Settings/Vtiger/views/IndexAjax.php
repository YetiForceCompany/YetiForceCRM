<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_IndexAjax_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod,
	 App\Controller\ClearProcess;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getSettingsShortCutBlock');
		$this->exposeMethod('realignSettingsShortCutBlock');
	}

	public function getSettingsShortCutBlock(\App\Request $request)
	{
		$fieldid = $request->get('fieldid');
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$pinnedSettingsShortcuts = Settings_Vtiger_MenuItem_Model::getPinnedItems();
		$viewer->assign('SETTINGS_SHORTCUT', $pinnedSettingsShortcuts[$fieldid]);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->view('SettingsShortCut.tpl', $qualifiedModuleName);
	}

	public function realignSettingsShortCutBlock(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$pinnedSettingsShortcuts = Settings_Vtiger_MenuItem_Model::getPinnedItems();
		$viewer->assign('SETTINGS_SHORTCUT', $pinnedSettingsShortcuts);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->view('ReAlignSettingsShortCut.tpl', $qualifiedModuleName);
	}
}
