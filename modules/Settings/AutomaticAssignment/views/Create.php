<?php

/**
 * Create View Class for Automatic assignment.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Create_View extends Settings_Vtiger_BasicModal_View
{
	/**
	 * Function returns name that defines modal window size.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getSize(\App\Request $request)
	{
		return 'modal-sm';
	}

	/**
	 * Function proccess.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		if ($request->has('tabid')) {
			$sourceModule = \App\Module::getModuleName($request->getInteger('tabid'));
			$viewer->assign('SUPPORTED_FIELDS', Settings_AutomaticAssignment_Module_Model::getFieldsByModule($sourceModule));
			$viewer->assign('SELECTED_MODULE', $sourceModule);
			$viewer->view('Create.tpl', $moduleName);
		} else {
			$viewer->assign('MODULE_MODEL', Settings_Vtiger_Module_Model::getInstance($moduleName));
			$viewer->assign('WIZARD_BASE', true);
			$viewer->assign('SUPPORTED_MODULES', Settings_AutomaticAssignment_Module_Model::getSupportedModules());
			$this->preProcess($request);
			$viewer->view('Create.tpl', $moduleName);
			$this->postProcess($request);
		}
	}
}
