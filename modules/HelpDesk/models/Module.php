<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class HelpDesk_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);

		$quickLink = array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_DASHBOARD',
				'linkurl' => $this->getDashBoardUrl(),
				'linkicon' => '',
		);

		//Check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		
		return $parentQuickLinks;
	}

	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getOpenTickets() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$sql = 'SELECT count(*) AS count, case when ( concat(vtiger_users.first_name, " " ,vtiger_users.last_name)  not like "") then
			concat(vtiger_users.first_name, " " ,vtiger_users.last_name) else vtiger_groups.groupname end as name, vtiger_users.id as id FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
			AND vtiger_crmentity.deleted = 0'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName())." WHERE vtiger_troubletickets.status in ('Open','In Progress','Wait For Response') GROUP BY smownerid ";
		$result = $db->pquery($sql , array());
		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getTicketsByStatus($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if(!empty($ownerSql)) {
			$ownerSql = ' AND '.$ownerSql;
		}
		
		$params = array();
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}
		
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.status IS NULL OR vtiger_troubletickets.status = "" THEN "" ELSE vtiger_troubletickets.status END AS statusvalue 
							FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
							'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.
							" INNER JOIN vtiger_ticketstatus ON vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus WHERE vtiger_ticketstatus.ticketstatus <> 'Closed' GROUP BY statusvalue ORDER BY vtiger_ticketstatus.sortorderid", $params);

		$response = array();

		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$ticketStatusVal = $row['statusvalue'];
			if($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($ticketStatusVal, $this->getName());
			$response[$i][2] = $ticketStatusVal;
		}
		return $response;
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule,$relationModel = false) {
		if ($functionName === 'get_activities') {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_activity.visibility, vtiger_seactivityrel.crmid AS parent_id,
						CASE WHEN (vtiger_activity.activitytype = 'Task') THEN (vtiger_activity.status) ELSE (vtiger_activity.eventstatus) END AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.activitytype <> 'Emails'
								AND vtiger_seactivityrel.crmid = ".$recordId;

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationModel);
		}

		return $query;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Assets', 'Project', 'ServiceContracts', 'Services'))) {
			$condition = " vtiger_troubletickets.ticketid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			$pos = stripos($listQuery, 'where');

			if ($pos) {
				$overRideQuery = $listQuery. ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	public function getTimeEmployee($id) {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$result = $db->pquery('SELECT count(*) AS count, concat(vtiger_users.first_name, " " ,vtiger_users.last_name) as name, vtiger_users.id as id, SUM(vtiger_osstimecontrol.sum_time) as time  FROM vtiger_osstimecontrol
						INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
						LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).
						' WHERE vtiger_osstimecontrol.ticketid = ? AND vtiger_osstimecontrol.osstimecontrol_status = ?  GROUP BY smownerid', array( $id, 'Accepted'));

		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$data[] = $row;
		}
		return $data;
	}

}
