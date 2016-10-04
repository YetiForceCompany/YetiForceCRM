<?php

/**
 * Configuration notifications
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_Configuration_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$listModules = $moduleModel->getModulesList();
		$listUsers = Users_Record_Model::getAll();
		foreach ($listModules as $moduleName => &$module) {
			$watchdogModule = Vtiger_Watchdog_Model::getInstance($moduleName);
			$modulesWatchingsByUsers[$moduleName] = $watchdogModule->getWatchingUsers();
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('LIST_MODULES', $listModules);
		$viewer->assign('LIST_MODULES_USERS', $modulesWatchingsByUsers);
		$viewer->assign('LIST_USERS', $listUsers);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Configuration.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Configuration",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getBreadcrumbTitle(Vtiger_Request $request)
	{
		return vtranslate('LBL_NOTIFICATIONS');
	}
}
