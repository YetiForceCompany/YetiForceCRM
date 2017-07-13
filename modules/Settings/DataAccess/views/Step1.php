<?php

/**
 * Settings DataAccess Step1 view  class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class Settings_DataAccess_Step1_View extends Settings_Vtiger_Index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();

		$idTpl = $request->get('tpl_id');

		$viewer = $this->getViewer($request);

		if ($idTpl) {
			$docInfo = Settings_DataAccess_Module_Model::getDataAccessInfo($idTpl);

			$viewer->assign('BASE_INFO', $docInfo['basic_info']);
			$viewer->assign('TPL_ID', $idTpl);
		}
		$viewer->assign('STEP', 1);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_LIST', Settings_DataAccess_Module_Model::getSupportedModules());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		echo $viewer->view('Step1.tpl', $qualifiedModuleName, true);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Conditions"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
