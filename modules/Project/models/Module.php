<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Project_Module_Model extends Vtiger_Module_Model {

	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = parent::getSideBarLinks($linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_TASKS_LIST',
				'linkurl' => $this->getTasksListUrl(),
				'linkicon' => '',
			),
            array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_MILESTONES_LIST',
				'linkurl' => $this->getMilestonesListUrl(),
				'linkicon' => '',
			),
            array(
			   'linktype' => 'SIDEBARLINK',
			   'linklabel' => 'LBL_DASHBOARD',
			   'linkurl' => $this->getDashBoardUrl(),
			   'linkicon' => '',
            ),           
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		return $links;
	}

	public function getTasksListUrl() {
		$taskModel = Vtiger_Module_Model::getInstance('ProjectTask');
		return $taskModel->getListViewUrl();
	}
    public function getMilestonesListUrl() {
		$milestoneModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
		return $milestoneModel->getListViewUrl();
	}
	public function getTimeEmployee($id) {
		$db = PearDatabase::getInstance();
		$moduleModel = Vtiger_Record_Model::getCleanInstance('OSSTimeControl');
		$Ids = $moduleModel->getProjectRelatedIDS($id);
		foreach($Ids as $module){
			foreach ($module as $moduleId){
				$idArray .= $moduleId . ',';
			}
		}
		$idArray = substr($idArray, 0, -1);
		$addSql='';
		if($idArray) {
		    $addSql=' WHERE vtiger_osstimecontrol.osstimecontrolid IN (' . $idArray . ') ';
		}
		//TODO need to handle security
		$result = $db->pquery('SELECT count(*) AS count, concat(vtiger_users.first_name, " " ,vtiger_users.last_name) as name, vtiger_users.id as id, SUM(vtiger_osstimecontrol.sum_time) as time  FROM vtiger_osstimecontrol
						INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						AND vtiger_crmentity.deleted = 0'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).$addSql 
						. ' GROUP BY smownerid', array());

		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$data[] = $row;
		}
		return $data;
	}
	
	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule,$relationModel = false) {
		if ($functionName === 'get_activities' || $functionName === 'get_history') {
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
							WHERE vtiger_crmentity.deleted = 0 AND vtiger_seactivityrel.crmid = ".$recordId;
			if($functionName === 'get_activities') {
				$query .= " AND ((vtiger_activity.activitytype='Task' and vtiger_activity.status not in ('Completed','Deferred'))
				OR (vtiger_activity.activitytype not in ('Emails','Task') and  vtiger_activity.eventstatus not in ('','Not Held','Held')))";
			} else {
				$query .= " AND ((vtiger_activity.activitytype='Task' and vtiger_activity.status in ('Completed','Deferred'))
				OR (vtiger_activity.activitytype not in ('Emails','Task') and  vtiger_activity.eventstatus in ('','Not Held','Held')))";
			}
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
	
	public function getTimeProject($id) {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$result = $db->pquery('SELECT   vtiger_project.sum_time AS TIME,  vtiger_project.sum_time_h AS timehelpdesk,
			vtiger_project.sum_time_pt AS projecttasktime FROM  vtiger_project LEFT JOIN vtiger_crmentity   ON vtiger_project.projectid = vtiger_crmentity.crmid 
			AND vtiger_crmentity.deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).'WHERE vtiger_project.projectid = ?' , array($id), true);

		$response = array();
		if($db->num_rows($result)>0){
			$projectTime = $db->query_result($result, $i, 'time');
			$response[0][0] = $projectTime;
			$response[0][1] =vtranslate('Total time [h]', 'Project');
			$response[1][0] = $db->query_result($result, $i, 'timehelpdesk');
			$response[1][1] = vtranslate('Total time [Tickets]', $this->getName());
			$response[2][0] = $db->query_result($result, $i, 'projecttasktime');
			$response[2][1] =vtranslate('Total time [Project Task]', $this->getName());
		}
		
		$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->getName());
		$response = array();
		$response[0][0] = $recordModel->get('sum_time');
		$response[0][1] = vtranslate('Total time [Project]', $this->getName());
		$response[1][0] = $recordModel->get('sum_time_pt');
		$response[1][1] = vtranslate('Total time [Project Task]', $this->getName());
		$response[2][0] = $recordModel->get('sum_time_h');
		$response[2][1] = vtranslate('Total time [Tickets]', $this->getName());
		$response[3][0] = $recordModel->get('sum_time_all');
		$response[3][1] = vtranslate('Total time [Sum]', $this->getName());
		return $response;
	}

}