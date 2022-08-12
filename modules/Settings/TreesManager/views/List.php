<?php

/**
 * Settings TreesManager list view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_List_View extends Settings_Vtiger_List_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_TreesManager_Module_Model::getInstance('Settings:TreesManager')->getSupportedModules());
		parent::preProcess($request, $display);
	}
}
