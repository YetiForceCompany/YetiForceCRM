<?php

/**
 * Show modal with configuration
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.c
 */
class Home_NotificationConfig_View extends Vtiger_BasicModal_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleList = vtlib\Functions::getAllModules(true, true);
		foreach ($moduleList as $tabId => &$module) {
			if ($module['name'] == 'Events' || !Users_Privileges_Model::isPermitted($module['name'], 'WatchingModule')) {
				unset($moduleList[$tabId]);
			}
		}
		$watchingModules = Vtiger_Watchdog_Model::getWatchingModules();
		$frequency = Vtiger_Watchdog_Model::getWatchingModulesSchedule();
		$selectAllModules = false;
		if (count($moduleList) == count($watchingModules))
			$selectAllModules = true;
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_LIST', $moduleList);
		$viewer->assign('WATCHING_MODULES', $watchingModules);
		$viewer->assign('SELECT_ALL_MODULES', $selectAllModules);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FREQUENCY', $frequency);
		$viewer->assign('CRON_INFO', vtlib\Cron::getInstance('LBL_SEND_NOTIFICATIONS'));
		$viewer->view('NotificationConfig.tpl', $moduleName);
	}
}
