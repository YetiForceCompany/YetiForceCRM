<?php

/**
 * Settings Widgets index view class.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Widgets_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$source = $request->getInteger('source');
		if (empty($source)) {
			$source = \App\Module::getModuleId('Accounts');
		}
		$moduleModel = Settings_Widgets_Module_Model::getInstance($qualifiedModuleName);
		$sourceModuleName = App\Module::getModuleName($source);
		$relatedModule = \App\Relation::getByModule($sourceModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SOURCE', $source);
		$viewer->assign('SOURCEMODULE', $sourceModuleName);
		$viewer->assign('WIDGETS', $moduleModel->getWidgets($source));
		$viewer->assign('RELATEDMODULES', $relatedModule);
		$viewer->assign('FILTERS', \App\Json::encode($moduleModel->getFiletrs($relatedModule)));
		$viewer->assign('CHECKBOXS', \App\Json::encode($moduleModel->getCheckboxs($relatedModule)));
		$viewer->assign('SWITCHES_HEADER', \App\Json::encode($moduleModel->getHeaderSwitch($source)));
		$viewer->assign('CUSTOM_VIEW', \App\Json::encode($moduleModel->getCustomView($relatedModule)));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getHeaderCss(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			"modules.Settings.$moduleName.resources.$moduleName",
		]));
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.Settings.$moduleName.resources.$moduleName",
		]));
	}
}
