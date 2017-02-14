<?php

/**
 * @package YetiForce.Views
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Inventory_DiscountConfiguration_View extends Settings_Vtiger_Index_View
{

	public function getView()
	{
		return 'DiscountConfiguration';
	}

	public function process(Vtiger_Request $request)
	{
		
		\App\Log::trace('Start ' . __METHOD__);
		$qualifiedModule = $request->getModule(false);
		$view = $this->getView();
		$config = Settings_Inventory_Module_Model::getConfig($view);
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$viewer = $this->getViewer($request);
		$viewer->assign('PAGE_LABELS', $this->getPageLabels($request));
		$viewer->assign('VIEW', $view);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('USER_MODEL', $currentUser);
		$viewer->assign('CONFIG', $config);
		$viewer->view('Config.tpl', $qualifiedModule);
		\App\Log::trace('End ' . __METHOD__);
	}

	public function getPageLabels(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$view = $this->getView();
		$translations = [];
		$translations['title'] = 'LBL_' . strtoupper($view);
		$translations['title_single'] = 'LBL_' . strtoupper($view) . '_SINGLE';
		$translations['description'] = 'LBL_' . strtoupper($view) . '_DESCRIPTION';
		return $translations;
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = ["modules.Settings.$moduleName.resources.Config"];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
