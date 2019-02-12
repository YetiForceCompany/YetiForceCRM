<?php

/**
 * Show modal with configuration.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Notification_NotificationConfig_View extends Vtiger_BasicModal_View
{
	/**
	 * Function get modal size.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getSize(\App\Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Function gets module settings.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		parent::preProcess($request);
		$moduleName = $request->getModule();
		$moduleList = Vtiger_Watchdog_Model::getSupportedModules();
		foreach ($moduleList as $tabId => &$module) {
			if (!\App\Privilege::isPermitted($module->getName(), 'WatchingModule')) {
				unset($moduleList[$tabId]);
			}
		}
		$watchingModules = Vtiger_Watchdog_Model::getWatchingModules();
		$scheduleData = Vtiger_Watchdog_Model::getWatchingModulesSchedule();
		$selectedAllModules = count($moduleList) === count($watchingModules) ? true : false;
		$selectedAllSendNotice = count($moduleList) === count($scheduleData['modules']) ? true : false;
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_LIST', $moduleList);
		$viewer->assign('WATCHING_MODEL', Vtiger_Watchdog_Model::getInstance($moduleName));
		$viewer->assign('WATCHING_MODULES', $watchingModules);
		$viewer->assign('SELECT_ALL_MODULES', $selectedAllModules);
		$viewer->assign('IS_ALL_EMAIL_NOTICE', $selectedAllSendNotice);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FREQUENCY', $scheduleData['frequency'] ?? null);
		$viewer->assign('SCHEDULE_DATA', $scheduleData);
		$viewer->assign('CRON_INFO', \vtlib\Cron::getInstance('LBL_SEND_NOTIFICATIONS'));
		$viewer->view('NotificationConfig.tpl', $moduleName);
		parent::postProcess($request);
	}

	/**
	 * Function to get the list of Js models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return array - List of Vtiger_JsScript_Model instances
	 */
	public function getModalScripts(\App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js'
		]), parent::getModalScripts($request));
	}
}
