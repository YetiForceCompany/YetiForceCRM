<?php

/**
 * Automatic Assignment List View Class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_List_View extends Settings_Vtiger_List_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_AutomaticAssignment_Module_Model::getSupportedModules());
		parent::preProcess($request, $display);
	}
}
