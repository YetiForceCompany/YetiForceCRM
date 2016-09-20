<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************************************************************** */

class HelpDesk_OpenTickets_Dashboard extends Vtiger_IndexAjax_View
{

	private $conditions = false;

	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getOpenTickets()
	{
		$db = PearDatabase::getInstance();
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$module = 'HelpDesk';
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		$usersSqlFullName = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

		$sql = sprintf('SELECT count(*) AS count, case when (%s not like "") then
			%s else vtiger_groups.groupname end as name, 
			case when (%s not like "") then
			vtiger_users.cal_color else vtiger_groups.color end as color, smownerid as id
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0', $usersSqlFullName, $usersSqlFullName, $usersSqlFullName);
		if (!empty($securityParameter)) {
			$sql .= $securityParameter;
		}
		if (!empty($ticketStatus)) {
			$ticketStatusSearch = implode("','", $ticketStatus);
			$sql .= " && vtiger_troubletickets.status NOT IN ('$ticketStatusSearch')";
			$this->conditions = ['vtiger_troubletickets.status', "'$ticketStatusSearch'", 'nin', QueryGenerator::$AND];
		}

		$sql .= ' GROUP BY smownerid';
		$result = $db->query($sql);
		$listViewUrl = $moduleModel->getListViewUrl();
		$chartData = [];
		while ($row = $db->getRow($result)) {
			$data['id'] = $row['id'];
			$data['label'] = trim($row['name']);
			$data['data'] = $row['count'];
			$data['color'] = $row['color'];
			$data['links'] = $listViewUrl . $this->getSearchParams($row['id']);
			$chartData[$row['id']] = $data;
		}
		return $chartData;
	}

	public function getSearchParams($value)
	{
		$openTicketsStatus = Settings_SupportProcesses_Module_Model::getOpenTicketStatus();
		if ($openTicketsStatus)
			$openTicketsStatus = implode(',', $openTicketsStatus);
		else {
			$allTicketStatus = Settings_SupportProcesses_Module_Model::getAllTicketStatus();
			$openTicketsStatus = implode(',', $allTicketStatus);
		}

		$listSearchParams = [];
		$conditions = array(array('assigned_user_id', 'e', $value));
		array_push($conditions, array('ticketstatus', 'e', "$openTicketsStatus"));
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$data = $this->getOpenTickets();
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('USER_CONDITIONS', $this->conditions);
		//Include special script and css needed for this widget
		$viewer->assign('CURRENTUSER', $currentUser);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/OpenTickets.tpl', $moduleName);
		}
	}
}
