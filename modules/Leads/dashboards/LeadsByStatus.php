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

class Leads_LeadsByStatus_Dashboard extends Vtiger_IndexAjax_View
{

	function getSearchParams($value, $assignedto, $dates)
	{
		$listSearchParams = array();
		$conditions = array(array('leadstatus', 'e', $value));
		if ($assignedto != '')
			array_push($conditions, array('assigned_user_id', 'e', getUserFullName($assignedto)));
		if (!empty($dates)) {
			array_push($conditions, array('createdtime', 'bw', $dates['start'] . ' 00:00:00,' . $dates['end'] . ' 23:59:59'));
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function returns Leads grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsByStatus($owner, $dateFilter)
	{
		$db = PearDatabase::getInstance();
		$module = 'Leads';
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$ownerSql = $moduleModel->getOwnerWhereConditionForDashBoards($owner);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		$leadsClosed = Settings_MarketingProcesses_Module_Model::getConfig('lead');

		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}

		$response = [];
		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$sql = 'SELECT COUNT(*) as count, CASE WHEN vtiger_leadstatus.leadstatus IS NULL OR vtiger_leadstatus.leadstatus = "" THEN "" ELSE 
						vtiger_leadstatus.leadstatus END AS leadstatusvalue
				FROM vtiger_leaddetails 
				INNER JOIN vtiger_crmentity
					ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
					AND deleted=0 AND converted = 0 ' . $ownerSql . ' ' . $dateFilterSql .
			'INNER JOIN vtiger_leadstatus ON vtiger_leaddetails.leadstatus = vtiger_leadstatus.leadstatus ';
		if ($securityParameter != '')
			$sql .= $securityParameter;

		if (!empty($leadsClosed['status'])) {
			$leadStatusSearch = implode("','", $leadsClosed['status']);
			$sql .= " AND vtiger_leaddetails.leadstatus NOT IN ('$leadStatusSearch')";
		}

		$sql .= ' GROUP BY leadstatusvalue ORDER BY vtiger_leadstatus.sortorderid ';
		$result = $db->pquery($sql, $params);

		$response = array();
		$numRows = $db->num_rows($result);
		if ($numRows > 0) {
			for ($i = 0; $i < $numRows; $i++) {
				$row = $db->query_result_rowdata($result, $i);
				$data[$i]['label'] = vtranslate($row['leadstatusvalue'], 'Leads');
				$ticks[$i][0] = $i;
				$ticks[$i][1] = vtranslate($row['leadstatusvalue'], 'Leads');
				$data[$i]['data'][0][0] = $i;
				$data[$i]['data'][0][1] = $row['count'];
				$name[] = $row['leadstatusvalue'];
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
		$data = ($owner === false) ? array() : $this->getLeadsByStatus($owner, $dates);
		$listViewUrl = $moduleModel->getListViewUrl();
		$leadStatusAmount = count($data['name']);
		for ($i = 0; $i < $leadStatusAmount; $i++) {
			$data['links'][$i][0] = $i;
			$data['links'][$i][1] = $listViewUrl . $this->getSearchParams($data['name'][$i], $owner, $dates);
		}

		//Include special script and css needed for this widget

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);

		$accessibleUsers = $currentUser->getAccessibleUsersForModule('Leads');
		$accessibleGroups = $currentUser->getAccessibleGroupForModule('Leads');
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('OWNER', $ownerForwarded);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/LeadsByStatus.tpl', $moduleName);
		}
	}
}
