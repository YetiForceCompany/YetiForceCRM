<?php

/**
 * Settings OSSMailScanner logs view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_OSSMailScanner_Logs_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$ossMailScannerRecordModel = \Vtiger_Record_Model::getCleanInstance('OSSMailScanner');

		$cronHistoryActionList = $ossMailScannerRecordModel->getScanHistory();
		$viewer = $this->getViewer($request);
		$viewer->assign('RecordModel', $ossMailScannerRecordModel);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('WIDGET_CFG', $ossMailScannerRecordModel->getConfig(false));
		$viewer->assign('HISTORYACTIONLIST', $cronHistoryActionList);
		$viewer->assign('HISTORYACTIONLIST_NUM', $this->getNumLog());
		$viewer->assign('STOP_BUTTON_STATUS', \OSSMailScanner_Record_Model::isActiveScan());

		echo $viewer->view('logs.tpl', $request->getModule(false), true);
	}

	public function getNumLog()
	{
		$numRecord = (new App\Db\Query())->from('vtiger_ossmails_logs')->count();

		return ceil($numRecord / 30);
	}
}
