<?php

/**
 * Settings TreesManager list view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_List_View extends Settings_Vtiger_List_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_Workflows_Module_Model::getSupportedModules());
		parent::preProcess($request, $display);
	}
}
