<?php

/**
 * Settings TimeControlProcesses index view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_TimeControlProcesses_Index_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{

		\App\Log::trace('Start ' . __METHOD__);
		$qualifiedModule = $request->getModule(false);
		$moduleModel = Settings_TimeControlProcesses_Module_Model::getCleanInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('USER_MODEL', $currentUser);
		$viewer->assign('MODULE_MODEL', $moduleModel->getConfigInstance());
		$viewer->view('Index.tpl', $qualifiedModule);
		\App\Log::trace('End ' . __METHOD__);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Index",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
