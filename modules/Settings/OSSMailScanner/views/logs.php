<?php

/**
 * Settings OSSMailScanner logs view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_OSSMailScanner_logs_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$OSSMailScanner_Record_Model = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');

		$cron_history_action_list = $OSSMailScanner_Record_Model->get_scan_history();
		$viewer = $this->getViewer($request);
		$viewer->assign('RecordModel', $OSSMailScanner_Record_Model);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('WIDGET_CFG', $OSSMailScanner_Record_Model->getConfig(''));
		$viewer->assign('HISTORYACTIONLIST', $cron_history_action_list);
		$viewer->assign('HISTORYACTIONLIST_NUM', $this->getNumLog());

		$stopButtonStatus = $OSSMailScanner_Record_Model->checkLogStatus();
		if (false !== $stopButtonStatus) {
			$viewer->assign('STOP_BUTTON_STATUS', 'true');
		} else {
			$viewer->assign('STOP_BUTTON_STATUS', 'false');
		}

		echo $viewer->view('logs.tpl', $request->getModule(false), true);
	}

	public function getNumLog()
	{
		$db = PearDatabase::getInstance();
		$limit = 30;
		$result = $db->query("SELECT COUNT(id) as num FROM vtiger_ossmails_logs");
		$numRecord = $db->query_result($result, 0, 'num');
		return ceil($numRecord / $limit);
	}
}

?>
