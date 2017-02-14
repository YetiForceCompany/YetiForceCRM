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
		$response = ['data' => [], 'links' => []];
		$relatedListView = Vtiger_RelationListView_Model::getInstance(Vtiger_Record_Model::getInstanceById($id), 'ProjectMilestone');
		$relatedListView->getRelationModel()->set('QueryFields', [
			'projectmilestoneid' => 'projectmilestoneid',
			'projectid' => 'projectid',
			'projectmilestonename' => 'projectmilestonename',
			'projectmilestonedate' => 'projectmilestonedate',
			'projectmilestone_progress' => 'projectmilestone_progress'
		]);
		$dataReader = $relatedListView->getRelationQuery()->createCommand()->query();
		$milestoneTime = 0;
		$progressInHours = 0;
		while ($row = $dataReader->read()) {
			$projectmilestone = [];
			$link = [];
			$link['id'] = $row['id'];
			$link['target'] = $row['id'];
			$link['type'] = 1;
			$link['source'] = $row['projectid'];
			$projectmilestone['id'] = $row['id'];
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
			$projecttask = $this->getGanttTask($row['id']);
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
		$relatedListView = Vtiger_RelationListView_Model::getInstance(Vtiger_Record_Model::getInstanceById($id), 'ProjectTask');
		$relatedListView->getRelationModel()->set('QueryFields', [
			'id' => 'id',
			'projectid' => 'projectid',
			'projecttaskname' => 'projecttaskname',
			'parentid' => 'parentid',
			'projectmilestoneid' => 'projectmilestoneid',
			'projecttaskprogress' => 'projecttaskprogress',
			'projecttaskpriority' => 'projecttaskpriority',
			'startdate' => 'startdate',
			'targetenddate' => 'targetenddate'
		]);
		$dataReader = $relatedListView->getRelationQuery()->createCommand()->query();
		$response = ['data' => [], 'links' => []];
		$taskTime = 0;
		while ($row = $dataReader->read()) {
			$projecttask = [];
			$link = [];
			$link['id'] = $row['id'];
			$link['target'] = $row['id'];
			$projecttask['id'] = $row['id'];
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
