<?php

/**
 * Settings OSSMailView index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Widgets_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$source = $request->getInteger('source');
		if (empty($source)) {
			$source = \App\Module::getModuleId('Accounts');
		}
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$relatedModule = $moduleModel->getRelatedModule($source);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SOURCE', $source);
		$viewer->assign('SOURCEMODULE', App\Module::getModuleName($source));
		$viewer->assign('WIDGETS', $moduleModel->getWidgets($source));
		$viewer->assign('RELATEDMODULES', $relatedModule);
		$viewer->assign('FILTERS', \App\Json::encode($moduleModel->getFiletrs($relatedModule)));
		$viewer->assign('CHECKBOXS', \App\Json::encode($moduleModel->getCheckboxs($relatedModule)));
		$viewer->assign('SWITCHES_HEADER', \App\Json::encode($moduleModel->getHeaderSwitch()));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	public function getHeaderCss(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$cssFileNames = [
			"modules.Settings.$moduleName.resources.$moduleName",
		];

		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles($cssFileNames));
	}

	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$jsFileNames = [
			"modules.Settings.$moduleName.resources.$moduleName",
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}
}
