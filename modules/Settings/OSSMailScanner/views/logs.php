<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_OSSMailScanner_logs_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
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
		if (false != $stopButtonStatus) {
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
