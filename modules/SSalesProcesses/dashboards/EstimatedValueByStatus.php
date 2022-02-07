<?php

/**
 * Widget show estimated value by status.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$query = (new \App\Db\Query())->select([
			'estimated' => new \yii\db\Expression('SUM(u_#__ssalesprocesses.estimated)'),
			'u_#__ssalesprocesses.ssalesprocesses_status',
			'vtiger_ssalesprocesses_status.ssalesprocesses_statusid',
		])
			->from('u_yf_ssalesprocesses')
			->innerJoin('vtiger_crmentity', 'u_#__ssalesprocesses.ssalesprocessesid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_ssalesprocesses_status', 'u_#__ssalesprocesses.ssalesprocesses_status = vtiger_ssalesprocesses_status.ssalesprocesses_status')
			->where(['and', ['<>', 'u_#__ssalesprocesses.ssalesprocesses_status', ''], ['vtiger_crmentity.deleted' => 0], ['not', ['u_#__ssalesprocesses.ssalesprocesses_status' => null]]])
			->orderBy(['vtiger_ssalesprocesses_status.sortorderid' => SORT_DESC]);
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		if (!empty($owner)) {
			$query->andWhere(['vtiger_crmentity.smownerid' => $owner]);
		}
		$query->groupBy(['u_#__ssalesprocesses.ssalesprocesses_status', 'vtiger_ssalesprocesses_status.ssalesprocesses_statusid']);
		$dataReader = $query->createCommand()->query();
		$currencyInfo = \App\Fields\Currency::getDefault();
		$colors = \App\Fields\Picklist::getColors('ssalesprocesses_status');
		$chartData = [
			'labels' => [],
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'names' => [], // names for link generation
					'links' => [], // links generated in proccess method
				],
			],
			'show_chart' => false,
		];
		while ($row = $dataReader->read()) {
			$chartData['datasets'][0]['data'][] = round($row['estimated'], 2);
			$chartData['datasets'][0]['backgroundColor'][] = $colors[$row['ssalesprocesses_statusid']];
			$chartData['datasets'][0]['links'][] = $moduleModel->getListViewUrl() . $this->getSearchParams($owner, $row['ssalesprocesses_status']);
			$chartData['labels'][] = \App\Language::translate($row['ssalesprocesses_status'], $moduleName) . ' - ' . CurrencyField::convertToUserFormat($row['estimated']) . ' ' . $currencyInfo['currency_symbol'];
		}
		$chartData['show_chart'] = (bool) \count($chartData['datasets'][0]['data']);
		$dataReader->close();
		return $chartData;
	}

	/**
	 * Main function.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
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
		if ('all' == $owner) {
			$owner = '';
		}
		$data = $this->getEstimatedValue($owner);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance($moduleName, $currentUser->getId())->getAccessibleUsersForModule());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance($moduleName, $currentUser->getId())->getAccessibleGroupForModule());
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/EstimatedValueByStatus.tpl', $moduleName);
		}
	}
}
