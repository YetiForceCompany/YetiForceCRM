<?php

/**
 * List View Class for MappedFields Settings.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_MappedFields_List_View extends Settings_Vtiger_List_View
{
	public function preProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_MappedFields_Module_Model::getSupportedModules());
		parent::preProcess($request, $display);
	}
}
