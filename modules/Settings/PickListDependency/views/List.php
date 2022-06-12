<?php
/**
 * PickList dependency List View file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * PickList dependency List View class.
 */
class Settings_PickListDependency_List_View extends Settings_Vtiger_List_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_PickListDependency_Module_Model::getPicklistSupportedModules());
		parent::preProcess($request, $display);
	}
}
