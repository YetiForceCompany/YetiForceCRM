<?php

/**
 * Settings DataAccess index view  class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class Settings_DataAccess_Index_View extends Settings_Vtiger_Index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('DOC_TPL_LIST', Settings_DataAccess_Module_Model::getDataAccessList());
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_DataAccess_Module_Model::getEntityModulesList());
		$viewer->assign('SETTINGS_MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('DOCUMENT_LIST', $qualifiedModuleName);

		echo $viewer->view('Index.tpl', $qualifiedModuleName, true);
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
