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
		$response = ['data' => [], 'links' => []];
		$focus = CRMEntity::getInstance($this->getName());
		$relatedListMileston = $focus->get_dependents_list($id, $this->getId(), \includes\Modules::getModuleId('ProjectMilestone'));
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
		$response = ['data' => [], 'links' => []];
		$focus = CRMEntity::getInstance('ProjectMilestone');
		$relatedListMileston = $focus->get_dependents_list($id, \includes\Modules::getModuleId('ProjectMilestone'), \includes\Modules::getModuleId('ProjectTask'));
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
