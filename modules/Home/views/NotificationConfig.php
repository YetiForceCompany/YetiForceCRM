<?php

/**
 * Show modal with configuration
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Home_NotificationConfig_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleList = Vtiger_Functions::getAllModules();
		foreach($moduleList as $tabId => &$module){
			if(!Users_Privileges_Model::isPermitted($module['name'], 'WatchingModule')){
				unset($moduleList[$tabId]);
			}
		}
		$watchingModules = Vtiger_Watchdog_Model::getWatchingModules();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_LIST', $moduleList);
		$viewer->assign('WATCHING_MODULES', $watchingModules);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('NotificationConfig.tpl', $moduleName);
	}
}
