<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Leads_LeadsByIndustry_Dashboard extends Vtiger_IndexAjax_View
{

	public function getSearchParams($value, $assignedto, $dates)
	{
		$listSearchParams = [];
		$conditions = array(array('industry', 'e', $value));
		if ($assignedto != '')
			array_push($conditions, array('assigned_user_id', 'e', $assignedto));
		if (!empty($dates)) {
			array_push($conditions, array('createdtime', 'bw', $dates['start'] . ' 00:00:00,' . $dates['end'] . ' 23:59:59'));
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function returns Leads grouped by Industry
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsByIndustry($owner, $dateFilter)
	{
		$db = PearDatabase::getInstance();
		$module = 'Leads';
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		$securityParameterSql = $dateFilterSql = $ownerSql = '';
		$params = [];
		if (!empty($owner)) {
			$ownerSql = ' && smownerid = ' . $owner;
		}
		if (!empty($securityParameter))
			$securityParameterSql = $securityParameter;

		if (!empty($dateFilter)) {
			$dateFilterSql = ' && createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$query = sprintf('SELECT COUNT(*) as count, CASE WHEN vtiger_leaddetails.industry IS NULL || vtiger_leaddetails.industry = "" THEN "" 
						ELSE vtiger_leaddetails.industry END AS industryvalue FROM vtiger_leaddetails 
						INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
						AND deleted=0 && converted = 0 %s %s %s
						INNER JOIN vtiger_industry ON vtiger_leaddetails.industry = vtiger_industry.industry 
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
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Leads');
		else
			$owner = $request->get('owner');
		$ownerForwarded = $owner;
		if ($owner == 'all')
			$owner = '';

		$createdTime = $request->get('createdtime');

		//Date conversion from user to database format
		if (!empty($createdTime)) {
			$dates['start'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['start']);
			$dates['end'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['end']);
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = ($owner === false) ? [] : $this->getLeadsByIndustry($owner, $dates);
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

		$accessibleUsers = \includes\fields\Owner::getInstance('Leads', $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \includes\fields\Owner::getInstance('Leads', $currentUser)->getAccessibleGroupForModule();
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('OWNER', $ownerForwarded);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/LeadsByIndustry.tpl', $moduleName);
		}
	}
}
