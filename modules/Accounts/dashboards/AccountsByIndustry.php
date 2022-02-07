<?php

/**
 * Widget show accounts by industry.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Accounts_AccountsByIndustry_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Function to get params to searching in listview.
	 *
	 * @param string $industry
	 * @param int    $assignedto
	 * @param array  $dates
	 *
	 * @return string
	 */
	public function getSearchParams($industry, $assignedto, $dates)
	{
		$listSearchParams = [];
		$conditions = [['industry', 'e', $industry]];
		if ('' != $assignedto) {
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		}
		if (!empty($dates)) {
			array_push($conditions, ['createdtime', 'bw', implode(',', $dates)]);
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . App\Json::encode($listSearchParams);
	}

	/**
	 * Function to get data to display chart.
	 *
	 * @param int   $owner
	 * @param array $dateFilter
	 *
	 * @return array
	 */
	public function getAccountsByIndustry($owner, $dateFilter)
	{
		$moduleName = 'Accounts';
		$query = new \App\Db\Query();
		$query->select([
			'industryid' => 'vtiger_industry.industryid',
			'count' => new \yii\db\Expression('COUNT(*)'),
			'industryvalue' => new \yii\db\Expression("CASE WHEN vtiger_account.industry IS NULL OR vtiger_account.industry = '' THEN '' ELSE vtiger_account.industry END"), ])
			->from('vtiger_account')
			->innerJoin('vtiger_crmentity', 'vtiger_account.accountid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_industry', 'vtiger_account.industry = vtiger_industry.industry')
			->where(['deleted' => 0]);
		if (!empty($owner)) {
			$query->andWhere(['smownerid' => $owner]);
		}
		if (!empty($dateFilter)) {
			$query->andWhere(['between', 'createdtime', $dateFilter[0] . ' 00:00:00', $dateFilter[1] . ' 23:59:59']);
		}
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->groupBy(['vtiger_industry.sortorderid', 'industryvalue', 'vtiger_industry.industryid'])->orderBy('vtiger_industry.sortorderid');
		$dataReader = $query->createCommand()->query();
		$colors = \App\Fields\Picklist::getColors('industry');
		$chartData = [
			'labels' => [],
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'borderColor' => [],
					'tooltips' => [],
					'names' => [], // names for link generation,
					'links' => [], // links generated in proccess method
				],
			],
			'show_chart' => false,
		];
		while ($row = $dataReader->read()) {
			$chartData['labels'][] = \App\Language::translate($row['industryvalue'], $moduleName);
			$chartData['datasets'][0]['data'][] = $row['count'];
			$chartData['datasets'][0]['backgroundColor'][] = $colors[$row['industryid']];
			$chartData['datasets'][0]['borderColor'][] = $colors[$row['industryid']];
			$chartData['datasets'][0]['names'][] = $row['industryvalue'];
		}
		$chartData['show_chart'] = (bool) \count($chartData['datasets'][0]['data']);
		$dataReader->close();
		return $chartData;
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Accounts');
		} else {
			$owner = $request->getByType('owner', 2);
		}
		$ownerForwarded = $owner;
		if ('all' === $owner) {
			$owner = '';
		}
		$createdTime = $request->getDateRange('createdtime');
		if (empty($createdTime)) {
			$createdTime = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $this->getAccountsByIndustry($owner, $createdTime);
		$createdTime = \App\Fields\Date::formatRangeToDisplay($createdTime);
		$listViewUrl = $moduleModel->getListViewUrl();
		$leadSIndustryAmount = \count($data['datasets'][0]['names']);
		for ($i = 0; $i < $leadSIndustryAmount; ++$i) {
			$data['datasets'][0]['links'][$i] = $listViewUrl . '&viewname=All&entityState=Active' . $this->getSearchParams($data['datasets'][0]['names'][$i], $owner, $createdTime);
		}
		//Include special script and css needed for this widget
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', $createdTime);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance('Accounts', $currentUserId)->getAccessibleUsersForModule());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance('Accounts', $currentUserId)->getAccessibleGroupForModule());
		$viewer->assign('OWNER', $ownerForwarded);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/AccountsByIndustry.tpl', $moduleName);
		}
	}
}
