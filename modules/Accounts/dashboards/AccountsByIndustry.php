<?php

/**
 * Widget show accounts by industry
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Accounts_AccountsByIndustry_Dashboard extends Vtiger_IndexAjax_View
{

	/**
	 * Function to get params to searching in listview
	 * @param string $industry
	 * @param int $assignedto
	 * @param array $dates
	 * @return string
	 */
	public function getSearchParams($industry, $assignedto, $dates)
	{
		$listSearchParams = [];
		$conditions = array(array('industry', 'e', $industry));
		if ($assignedto != '')
			array_push($conditions, array('assigned_user_id', 'e', $assignedto));
		if (!empty($dates)) {
			array_push($conditions, array('createdtime', 'bw', $dates['start'] . ' 00:00:00,' . $dates['end'] . ' 23:59:59'));
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . includes\utils\Json::encode($listSearchParams);
	}

	/**
	 * Function to get data to display chart
	 * @param int $owner
	 * @param array $dateFilter
	 * @return array
	 */
	public function getAccountsByIndustry($owner, $dateFilter)
	{
		$db = PearDatabase::getInstance();
		$module = 'Accounts';
		$dateFilterSql = $ownerSql = '';
		$params = [];
		if (!empty($owner)) {
			$ownerSql = ' AND smownerid = ' . $owner;
		}
		$securityParameterSql = \App\PrivilegeQuery::getAccessConditions($module);
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}
		$query = sprintf('SELECT COUNT(*) as count, CASE WHEN vtiger_account.industry IS NULL || vtiger_account.industry = "" THEN "" 
						ELSE vtiger_account.industry END AS industryvalue FROM vtiger_account 
						INNER JOIN vtiger_crmentity ON vtiger_account.accountid = vtiger_crmentity.crmid
						AND deleted=0 %s %s %s
						INNER JOIN vtiger_industry ON vtiger_account.industry = vtiger_industry.industry 
						GROUP BY industryvalue ORDER BY vtiger_industry.sortorderid', $ownerSql, $dateFilterSql, $securityParameterSql);
		$result = $db->pquery($query, $params);
		$response = [];
		if ($db->num_rows($result) > 0) {
			$i = 0;
			while ($row = $db->getRow($result)) {
				$data[$i]['label'] = vtranslate($row['industryvalue'], 'Leads');
				$ticks[$i][0] = $i;
				$ticks[$i][1] = vtranslate($row['industryvalue'], 'Leads');
				$data[$i]['data'][0][0] = $i;
				$data[$i]['data'][0][1] = $row['count'];
				$name[] = $row['industryvalue'];
				$i++;
			}
			$response['chart'] = $data;
			$response['ticks'] = $ticks;
			$response['name'] = $name;
		}
		return $response;
	}

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');
		$data = $request->get('data');

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner'))
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Accounts');
		else
			$owner = $request->get('owner');
		$ownerForwarded = $owner;
		if ($owner == 'all')
			$owner = '';

		$createdTime = $request->get('createdtime');

		//Date conversion from user to database format
		$dates = [];
		if (!empty($createdTime)) {
			$dates['start'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['start']);
			$dates['end'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['end']);
		} else {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if($time !== false){
				$dates = $time;
			}
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $this->getAccountsByIndustry($owner, $dates);
		$listViewUrl = $moduleModel->getListViewUrl();
		$leadSIndustryAmount = count($data['name']);
		for ($i = 0; $i < $leadSIndustryAmount; $i++) {
			$data['links'][$i][0] = $i;
			$data['links'][$i][1] = $listViewUrl . $this->getSearchParams($data['name'][$i], $owner, $dates);
		}
		//Include special script and css needed for this widget

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('DTIME', $dates);
		
		$accessibleUsers = \includes\fields\Owner::getInstance('Accounts', $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \includes\fields\Owner::getInstance('Accounts', $currentUser)->getAccessibleGroupForModule();
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('OWNER', $ownerForwarded);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/AccountsByIndustry.tpl', $moduleName);
		}
	}
}
