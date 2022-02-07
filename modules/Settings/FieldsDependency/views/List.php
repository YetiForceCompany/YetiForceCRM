<?php

/**
 * Settings fields dependency list view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings fields dependency list view class.
 */
class Settings_FieldsDependency_List_View extends Settings_Vtiger_List_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_FieldsDependency_Module_Model::getSupportedModules());
		parent::preProcess($request, $display);
	}
}
