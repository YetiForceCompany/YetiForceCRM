<?php

/**
 * Widget show estimated value by status.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class SSalesProcesses_EstimatedValueByStatus_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Function to get search params in address listview.
	 *
	 * @param int    $owner  number id of user
	 * @param string $status
	 *
	 * @return string
	 */
	public function getSearchParams($owner, $status)
	{
		$listSearchParams = [];
		$conditions = [];
		if (!empty($owner)) {
			$conditions[] = ['assigned_user_id', 'e', $owner];
		}
		if (!empty($status)) {
			$conditions[] = ['ssalesprocesses_status', 'e', $status];
		}
		$listSearchParams[] = $conditions;

		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function to get data to chart.
	 *
	 * @param int $owner
	 *
	 * @return <Array>
	 */
	private function getEstimatedValue($owner = false)
	{
		$moduleName = 'SSalesProcesses';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$query = (new \App\Db\Query())->select('SUM(u_#__ssalesprocesses.estimated) AS estimated, u_#__ssalesprocesses.ssalesprocesses_status')
			->from('u_yf_ssalesprocesses')
			->innerJoin('vtiger_crmentity', 'u_#__ssalesprocesses.ssalesprocessesid = vtiger_crmentity.crmid')
			->where(['and', ['<>', 'ssalesprocesses_status', ''], ['vtiger_crmentity.deleted' => 0], ['not', ['ssalesprocesses_status' => null]]]);
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		if (!empty($owner)) {
			$query->andWhere(['vtiger_crmentity.smownerid' => $owner]);
		}
		$query->groupBy('u_#__ssalesprocesses.ssalesprocesses_status');
		$dataReader = $query->createCommand()->query();
		$data = [];
		$i = 1;
		$currencyInfo = vtlib\Functions::getDefaultCurrencyInfo();
		while ($row = $dataReader->read()) {
			$data[] = [
				\App\Language::translate($row['ssalesprocesses_status'], $moduleName) . ' - ' . CurrencyField::convertToUserFormat($row['estimated']) . ' ' . $currencyInfo['currency_symbol'],
				$i++,
				$moduleModel->getListViewUrl() . $this->getSearchParams($owner, $row['ssalesprocesses_status']),
			];
		}
		$dataReader->close();

		return $data;
	}

	/**
	 * Main function.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, $moduleName);
		} else {
			$owner = $request->getByType('owner', 2);
		}
		if ($owner == 'all') {
			$owner = '';
		}
		$data = $this->getEstimatedValue($owner);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/EstimatedValueByStatus.tpl', $moduleName);
		}
	}
}
