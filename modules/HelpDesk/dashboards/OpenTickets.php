<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************************************************************/

class HelpDesk_OpenTickets_Dashboard extends Vtiger_IndexAjax_View {
	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getOpenTickets() {
		$db = PearDatabase::getInstance();
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$module = 'HelpDesk';
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		$usersSqlFullName = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		
		$sql = 'SELECT count(*) AS count, case when ('.$usersSqlFullName.' not like "") then
			'.$usersSqlFullName.' else vtiger_groups.groupname end as name, 
			case when ('.$usersSqlFullName.' not like "") then
			vtiger_users.cal_color else vtiger_groups.color end as color, smownerid as id
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0';
		if ($securityParameter != '')
			$sql.= $securityParameter;
		
		if(!empty($ticketStatus)){
			$ticketStatusSearch = implode("','", $ticketStatus);
			$sql .=	" AND vtiger_troubletickets.status NOT IN ('$ticketStatusSearch')";
		}
		$sql .= ' GROUP BY smownerid';
		$result = $db->pquery($sql , array());
		$moduleModel = Vtiger_Module_Model::getInstance('HelpDesk');
		$listViewUrl = $moduleModel->getListViewUrl();
		$chartData = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$data['id'] = $row['id'];
			$data['label'] = trim($row['name']);
			$data['data'] = $row['count'];
			$data['color'] = $row['color'];
			$data['links'] = $listViewUrl.$this->getSearchParams($row['name']);
			$chartData[] = $data;
		}
		return $chartData;
	}
	
    function getSearchParams($value) {
		$openTicketsStatus = Settings_SupportProcesses_Module_Model::getOpenTicketStatus();
		if($openTicketsStatus)
			$openTicketsStatus = implode(',', $openTicketsStatus);
		else{
			$allTicketStatus = Settings_SupportProcesses_Module_Model::getAllTicketStatus();
			$openTicketsStatus = implode(',', $allTicketStatus);
		}
    	
        $listSearchParams = array();
        $conditions = array(array('assigned_user_id','e',$value));
		array_push($conditions,array('ticketstatus','e',"$openTicketsStatus"));
        $listSearchParams[] = $conditions;
        return '&search_params='. json_encode($listSearchParams);
    }

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		
		$data = $this->getOpenTickets();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
        
		//Include special script and css needed for this widget
		$viewer->assign('CURRENTUSER', $currentUser);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/OpenTickets.tpl', $moduleName);
		}
	}
}
