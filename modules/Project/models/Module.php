<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Project_Module_Model extends Vtiger_Module_Model
{

	public function getSideBarLinks($linkParams)
	{
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = parent::getSideBarLinks($linkParams);

		$quickLinks = array();
		$quickLinks[] = array(
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_TASKS_LIST',
			'linkurl' => $this->getTasksListUrl(),
			'linkicon' => '',
		);

		$quickLinks[] = array(
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_MILESTONES_LIST',
			'linkurl' => $this->getMilestonesListUrl(),
			'linkicon' => '',
		);

		foreach ($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		return $links;
	}

	public function getTasksListUrl()
	{
		$taskModel = Vtiger_Module_Model::getInstance('ProjectTask');
		return $taskModel->getListViewUrl();
	}

	public function getMilestonesListUrl()
	{
		$milestoneModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
		return $milestoneModel->getListViewUrl();
	}

	public function getTimeEmployee($id)
	{
		$db = PearDatabase::getInstance();
		$moduleModel = Vtiger_Record_Model::getCleanInstance('OSSTimeControl');
		$Ids = $moduleModel->getProjectRelatedIDS($id);
		foreach ($Ids as $module) {
			foreach ($module as $moduleId) {
				$idArray .= $moduleId . ',';
			}
		}

		if (null == $idArray)
			$response = false;
		else {
			$idArray = substr($idArray, 0, -1);
			$addSql = ' WHERE vtiger_osstimecontrol.osstimecontrolid IN (' . $idArray . ') ';
			$userSqlFullName = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

			//TODO need to handle security
			$result = $db->query('SELECT count(*) AS count, ' . $userSqlFullName . ' as name, vtiger_users.id as id, SUM(vtiger_osstimecontrol.sum_time) as time  FROM vtiger_osstimecontrol
							INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
							INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
							AND vtiger_crmentity.deleted = 0' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $addSql
				. ' GROUP BY smownerid');

			$data = array();
			$numRows = $db->num_rows($result);
			for ($i = 0; $i < $numRows; $i++) {
				$row = $db->query_result_rowdata($result, $i);
				$data[$i]['label'] = $row['name'];
				$ticks[$i][0] = $i;
				$ticks[$i][1] = $row['name'];
				$data[$i]['data'][0][0] = $i;
				$data[$i]['data'][0][1] = $row['time'];
			}

			$response['ticks'] = $ticks;
			$response['chart'] = $data;
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
	public function getRelationQuery($recordId, $functionName, $relatedModule, $relationModel = false)
	{
		if ($functionName === 'get_activities') {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_activity.visibility,
						vtiger_activity.status AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.process = " . $recordId;
			$time = vtlib_purify($_REQUEST['time']);
			if ($time == 'current') {
				$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('current');
				$query .= " AND (vtiger_activity.activitytype NOT IN ('Emails') AND vtiger_activity.status IN ('" . implode("','", $stateActivityLabels) . "'))";
			}
			if ($time == 'history') {
				$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('history');
				$query .= " AND (vtiger_activity.activitytype NOT IN ('Emails') AND vtiger_activity.status IN ('" . implode("','", $stateActivityLabels) . "'))";
			}
			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$instance = CRMEntity::getInstance($relatedModuleName);
			$securityParameter = $instance->getUserAccessConditionsQuerySR($relatedModuleName, false, $recordId);
			if ($securityParameter != '')
				$query .= $securityParameter;
		} elseif ($functionName === 'get_mails' && $relatedModule->getName() == 'OSSMailView') {
			$query = OSSMailView_Record_Model::getMailsQuery($recordId, $relatedModule->getName());
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationModel);
		}

		return $query;
	}

	public function getTimeProject($id)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->getName());
		$response = array();
		$response[0]['data'][0][0] = 0;
		$response[0]['data'][0][1] = $recordModel->get('sum_time');
		$response[0]['label'] = vtranslate('Total time [Project]', $this->getName());
		$response[1]['data'][0][0] = 1;
		$response[1]['data'][0][1] = $recordModel->get('sum_time_pt');
		$response[1]['label'] = vtranslate('Total time [Project Task]', $this->getName());
		$response[2]['data'][0][0] = 2;
		$response[2]['data'][0][1] = $recordModel->get('sum_time_h');
		$response[2]['label'] = vtranslate('Total time [Tickets]', $this->getName());
		$response[3]['data'][0][0] = 3;
		$response[3]['data'][0][1] = $recordModel->get('sum_time_all');
		$response[3]['label'] = vtranslate('Total time [Sum]', $this->getName());
		return $response;
	}

	public function getGanttProject($id)
	{
		$adb = PearDatabase::getInstance();
		$branches = $this->getGanttMileston($id);
		$response = ['data' => [], 'links' => []];
		if ($branches) {
			$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->getName());
			$project['id'] = $id;
			$project['text'] = $recordModel->get('projectname');
			$project['priority'] = $recordModel->get('projectpriority');
			$project['priority_label'] = vtranslate($recordModel->get('projectpriority'), $this->getName());
			$project['type'] = 'project';
			$project['module'] = $this->getName();
			$project['open'] = true;
			$project['progress'] = $branches['progress'];
			$response['data'][] = $project;
			$response['data'] = array_merge($response['data'], $branches['data']);
			$response['links'] = array_merge($response['links'], $branches['links']);
		}
		return $response;
	}

	public function getGanttMileston($id)
	{
		$adb = PearDatabase::getInstance();
		//TODO need to handle security
		$response = ['data' => [], 'links' => []];
		$focus = CRMEntity::getInstance($this->getName());
		$relatedListMileston = $focus->get_dependents_list($id, $this->getId(), getTabid('ProjectMilestone'));
		$resultMileston = $adb->query($relatedListMileston['query']);
		$num = $adb->num_rows($resultMileston);
		$milestoneTime = 0;
		$progressInHours = 0;
		for ($i = 0; $i < $num; $i++) {
			$projectmilestone = [];
			$link = [];
			$row = $adb->query_result_rowdata($resultMileston, $i);
			$link['id'] = $row['projectmilestoneid'];
			$link['target'] = $row['projectmilestoneid'];
			$link['type'] = 1;
			$link['source'] = $row['projectid'];
			$projectmilestone['id'] = $row['projectmilestoneid'];
			$projectmilestone['text'] = $row['projectmilestonename'];
			$projectmilestone['parent'] = $row['projectid'];
			$projectmilestone['module'] = 'ProjectMilestone';
			if ($row['projectmilestonedate']) {
				$endDate = strtotime(date('Y-m-d', strtotime($row['projectmilestonedate'])) . ' +1 days');
				$projectmilestone['start_date'] = date('d-m-Y', $endDate);
			}
			$projectmilestone['progress'] = (int) $row['projectmilestone_progress'] / 100;
			$projectmilestone['priority'] = $row['projectmilestone_priority'];
			$projectmilestone['priority_label'] = vtranslate($row['projectmilestone_priority'], 'ProjectMilestone');
			$projectmilestone['open'] = true;
			$projectmilestone['type'] = 'milestone';
			$projecttask = $this->getGanttTask($row['projectmilestoneid']);
			$response['data'][] = $projectmilestone;
			$response['links'][] = $link;
			$response['data'] = array_merge($response['data'], $projecttask['data']);
			$response['links'] = array_merge($response['links'], $projecttask['links']);
			$milestoneTime += $projecttask['task_time'];
			$progressInHours += $projecttask['task_time'] * $projectmilestone['progress'];
		}
		if ($milestoneTime) {
			$response['progress'] = round($progressInHours / $milestoneTime, 1);
		}
		return $response;
	}

	public function getGanttTask($id)
	{
		$adb = PearDatabase::getInstance();
		//TODO need to handle security
		$response = ['data' => [], 'links' => []];
		$focus = CRMEntity::getInstance('ProjectMilestone');
		$relatedListMileston = $focus->get_dependents_list($id, getTabid('ProjectMilestone'), getTabid('ProjectTask'));
		$resultMileston = $adb->query($relatedListMileston['query']);
		$num = $adb->num_rows($resultMileston);
		$taskTime = 0;
		for ($i = 0; $i < $num; $i++) {
			$projecttask = [];
			$link = [];
			$row = $adb->query_result_rowdata($resultMileston, $i);
			$link['id'] = $row['projecttaskid'];
			$link['target'] = $row['projecttaskid'];
			$projecttask['id'] = $row['projecttaskid'];
			$projecttask['text'] = $row['projecttaskname'];
			if ($row['parentid']) {
				$link['type'] = 0;
				$link['source'] = $row['parentid'];
				$projecttask['parent'] = $row['parentid'];
			} else {
				$link['type'] = 2;
				$link['source'] = $row['projectmilestoneid'];
				$projecttask['parent'] = $row['projectmilestoneid'];
			}
			settype($row['projecttaskprogress'], "integer");
			$projecttask['progress'] = $row['projecttaskprogress'] / 100;
			$projecttask['priority'] = $row['projecttaskpriority'];
			$projecttask['priority_label'] = vtranslate($row['projecttaskpriority'], 'ProjectTask');
			$projecttask['start_date'] = date('d-m-Y', strtotime($row['startdate']));
			$endDate = strtotime(date('Y-m-d', strtotime($row['targetenddate'])) . ' +1 days');
			$projecttask['end_date'] = date('d-m-Y', $endDate);
			$projecttask['open'] = true;
			$projecttask['type'] = 'task';
			$projecttask['module'] = 'ProjectTask';
			$taskTime += $row['estimated_work_time'];
			$response['data'][] = $projecttask;
			$response['links'][] = $link;
		}
		$response['task_time'] = $taskTime;
		return $response;
	}
}
