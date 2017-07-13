<?php

/**
 * Form to add widget
 * @package YetiForce.view
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_AddChart_View extends Settings_Vtiger_BasicModal_View
{

	public function getReports()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$query = 'SELECT reportid, reportname FROM vtiger_report WHERE reporttype = ? AND owner = ?';
		$params = ['chart', $currentUser->getId()];
		$result = $db->pquery($query, $params);
		$recordsReport = [];
		while ($row = $db->getRow($result)) {
			$recordsReport[$row['reportid']] = $row;
		}
		return $recordsReport;
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule(false);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('LIST_REPORTS', $this->getReports());
		$viewer->view('AddChart.tpl', $moduleName);
	}
}
