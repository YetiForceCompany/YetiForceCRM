<?php

/**
 * Widget show estimated value by status
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class SSalesProcesses_EstimatedValueByStatus_Dashboard extends Vtiger_IndexAjax_View
{

	/**
	 * Function to get search params in address listview
	 * @param int $owner number id of user
	 * @param string $status
	 * @return string
	 */
	public function getSearchParams($owner, $status)
	{
		$listSearchParams = [];
		$conditions = [];
		if (!empty($owner)) {
			$conditions [] = ['assigned_user_id', 'e', $owner];
		}
		if (!empty($status)) {
			$conditions [] = ['ssalesprocesses_status', 'e', $status];
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function to get data to chart
	 * @param int $owner
	 * @return <Array>
	 */
	private function getEstimatedValue($owner = false)
	{
		$moduleName = 'SSalesProcesses';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$securityQuery = \App\PrivilegeQuery::getAccessConditions($moduleName);
		$paramsSql = [];
		$query = 'SELECT SUM(u_yf_ssalesprocesses.estimated) AS estimated, u_yf_ssalesprocesses.ssalesprocesses_status FROM u_yf_ssalesprocesses 
			INNER JOIN vtiger_crmentity ON u_yf_ssalesprocesses.ssalesprocessesid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
			WHERE (ssalesprocesses_status <> \'\' AND  ssalesprocesses_status IS NOT NULL) ';
		$query .= $securityQuery;
		if (!empty($owner)) {
			$query .= ' AND vtiger_crmentity.smownerid = ?';
			$paramsSql [] = $owner;
		}
		$query .= ' GROUP BY u_yf_ssalesprocesses.ssalesprocesses_status';
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $paramsSql);
		$data = [];
		$i = 1;
		$currencyInfo = vtlib\Functions::getDefaultCurrencyInfo();
		while ($row = $db->getRow($result)) {
			$data [] = [
				\includes\Language::translate($row['ssalesprocesses_status'], $moduleName) . ' - ' . CurrencyField::convertToUserFormat($row['estimated']) . ' ' .$currencyInfo['currency_symbol'],
				$i++,
				$moduleModel->getListViewUrl() . $this->getSearchParams($owner, $row['ssalesprocesses_status'])
			];
		}
		return $data;
	}

	/**
	 * Main function
	 * @param <Vtiger_Request> $request
	 */
	public function process(\Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$data = $request->get('data');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner'))
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, $moduleName);
		else
			$owner = $request->get('owner');
		if ($owner == 'all')
			$owner = '';


		$data = $this->getEstimatedValue($owner);

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/EstimatedValueByStatus.tpl', $moduleName);
		}
	}
}
