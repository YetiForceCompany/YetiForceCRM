<?php

/**
 * Show modal with configuration
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Notification_NotificationConfig_View extends Vtiger_BasicModal_View
{

	/**
	 * Function get modal size
	 * @param Vtiger_Request $request
	 * @return string
	 */
	public function getSize(Vtiger_Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Function gets module settings
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
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
		$viewer->assign('FREQUENCY', $scheduleData['frequency']);
		$viewer->assign('SCHEDULE_DATA', $scheduleData);
		$viewer->assign('CRON_INFO', \vtlib\Cron::getInstance('LBL_SEND_NOTIFICATIONS'));
		$viewer->view('NotificationConfig.tpl', $moduleName);
		parent::postProcess($request);
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param Vtiger_Request $request
	 * @return array - List of Vtiger_CssScript_Model instances
	 */
	public function getModalScripts(Vtiger_Request $request)
	{
		$parentScriptInstances = parent::getModalScripts($request);
		$scripts = [
			'~libraries/jquery/datatables/media/js/jquery.dataTables.min.js',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.min.js'
		];
		$modalInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($modalInstances, $parentScriptInstances);
		return $scriptInstances;
	}
}
