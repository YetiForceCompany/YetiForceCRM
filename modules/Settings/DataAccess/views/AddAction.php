<?php

/**
 * Settings DataAccess AddAction view  class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class Settings_DataAccess_AddAction_View extends Settings_Vtiger_Index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();
		$baseModule = $request->get('base_module');
		$id = $request->get('id');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('STEP', 3);
		$viewer->assign('BASE_MODULE', $baseModule);
		$viewer->assign('TPL_ID', $id);
		$viewer->assign('ACTIONS_LIST', Settings_DataAccess_Module_Model::listAccesDataDirector($baseModule));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('DOCUMENT_LIST', $qualifiedModuleName);
		echo $viewer->view('AddAction.tpl', $qualifiedModuleName, true);
	}
}
