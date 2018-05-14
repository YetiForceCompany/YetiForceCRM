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
	private $tasks = [];
	private $rootNode;

	/**
	 * Get parent nodes id.
	 *
	 * @param string|int $parentId
	 * @param array      $tasks
	 * @param array      $parents
	 *
	 * @return array
	 */
	private function getRecordParents($parentId, $parents = [])
	{
		if (empty($parentId)) {
			return $parents;
		}
		if (!in_array($parentId, $parents)) {
			$parents[] = $parentId;
		}
		foreach ($this->tasks as $task) {
			if ($task['id'] === $parentId) {
				if (!empty($task['parent'])) {
					$parents = $this->getRecordParents($task['parent'], $parents);
				}
				break;
			}
		}
		return $parents;
	}

	/**
	 * Collect all parents.
	 *
	 * @param array $tasks
	 *
	 * @return array
	 */
	private function collectRecordParents()
	{
		$parents = [];
		foreach ($this->tasks as $task) {
			if (!empty($task['parent'])) {
				$parents[$task['id']] = $this->getRecordParents($task['parent']);
			} else {
				$parents[$task['id']] = [];
			}
		}
		return $parents;
	}

	/**
	 * Calculate task levels and dependencies.
	 *
	 * @param $tasks
	 */
	private function calculateLevels()
	{
		$parents = $this->collectRecordParents();
		foreach ($this->tasks as &$task) {
			$task['level'] = count($parents[$task['id']]);
			$task['parents'] = $parents[$task['id']];
		}
		$hasChild = [];
		foreach ($parents as $childId => $parentsId) {
			foreach ($parentsId as $parentId) {
				if (!in_array((int) $parentId, $hasChild)) {
					$hasChild[] = (int) $parentId;
				}
			}
		}
		foreach ($this->tasks as &$task) {
			if (in_array((int) $task['id'], $hasChild)) {
				$task['hasChild'] = true;
			} else {
				$task['hasChild'] = false;
			}
		}
	}

	/**
	 * Calculate duration in days.
	 *
	 * @param $startDateStr
	 * @param $endDateStr
	 *
	 * @return int
	 */
	private function calculateDuration($startDateStr, $endDateStr)
	{
		$sDate = new DateTime($startDateStr);
		$eDate = new DateTime($endDateStr);
		$interval = $eDate->diff($sDate);
		return (int) $interval->format('%d');
	}

	private function normalizeParents()
	{
		foreach ($this->tasks as &$task) {
			if (!isset($task['parent']) && $task['id'] !== 0) {
				$task['parent'] = 0;
			}
		}
	}

	private function getRecordWithChildren($task)
	{
		foreach ($this->tasks as $child) {
			if (isset($child['parent']) && $child['parent'] === $task['id']) {
				if (empty($task['children'])) {
					$task['children'] = [];
				}
				$child = $this->getRecordWithChildren($child);
				$task['children'][] = $child;
			}
		}
		return $task;
	}

	private function flattenRecordTasks($nodes, $flat = [])
	{
		foreach ($nodes as $node) {
			$flat[] = $node;
			if (!empty($node['children'])) {
				$flat = $this->flattenRecordTasks($node['children'], $flat);
			}
		}
		return $flat;
	}

	private function removeChildren($tasks)
	{
		$cleaned = [];
		foreach ($tasks as $task) {
			if (isset($task['children'])) {
				unset($task['children']);
			}
			$cleaned[] = $task;
		}
		return $cleaned;
	}

	private function sortByParents()
	{
		$tree = $this->getRecordWithChildren($this->rootNode);
		$this->tree = $tree;
		$flat = $this->flattenRecordTasks($tree['children']);
		return $this->removeChildren($flat);
	}

	private function addRootNode()
	{
		$this->rootNode = ['id' => 0];
		array_unshift($this->tasks, $this->rootNode);
	}

	private function removeRootNode()
	{
		$tasks = [];
		foreach ($this->tasks as $task) {
			if ($task['id'] !== 0) {
				if ($task['parent'] === 0) {
					unset($task['parent']);
					$task['depends'] = '';
				}
				$tasks[] = $task;
			}
		}
		return $tasks;
	}

	private function getTaskIndex($taskId)
	{
		foreach ($this->tasks as $index => $task) {
			if ((int) $task['id'] === (int) $taskId) {
				return $index;
			}
		}
	}

	private function setUpDepends()
	{
		foreach ($this->tasks as $index => &$task) {
			if (!empty($task['parent'])) {
				$task['depends'] = (string) $this->getTaskIndex($task['parent']);
			}
		}
	}

	/**
	 * Get list of gantt projects.
	 *
	 * @param int|string $id
	 *
	 * @return array
	 */
	public function getGanttProject($id)
	{
		$branches = $this->getGanttMileston($id);
		$response = ['tasks' => [], 'data' => [], 'links' => []];
		if ($branches) {
			$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->getName());
			$project['id'] = $id;
			$project['name'] = \App\Purifier::encodeHtml($recordModel->get('projectname'));
			$project['text'] = \App\Purifier::encodeHtml($recordModel->get('projectname'));
			$project['priority'] = $recordModel->get('projectpriority');
			$project['priority_label'] = \App\Language::translate($recordModel->get('projectpriority'), $this->getName());
			$project['status'] = 'STATUS_ACTIVE';
			$project['type'] = 'project';
			$project['module'] = $this->getName();
			$project['open'] = true;
			$project['progress'] = $branches['progress'];
			$project['canWrite'] = false;
			$project['canDelete'] = false;
			$project['cantWriteOnParent'] = false;
			$project['canAdd'] = false;
			$project['description'] = \App\Purifier::encodeHtml($recordModel->get('description'));

			$project['start_date'] = date('Y-m-d', strtotime($recordModel->get('startdate')));
			$project['end_date'] = $recordModel->get('actualenddate');
			if (empty($project['end_date'])) {
				$project['end_date'] = date('Y-m-d', strtotime($recordModel->get('targetenddate')));
			}
			$project['start'] = strtotime($project['start_date']) * 1000;
			$project['end'] = strtotime($project['end_date']) * 1000;
			$project['duration'] = $this->calculateDuration($project['start_date'], $project['end_date']);

			$response['tasks'][] = $project;
			$response['data'][] = $project;
			$response['tasks'] = array_merge($response['tasks'], $branches['tasks']);
			$response['data'] = array_merge($response['data'], $branches['data']);
			$response['links'] = array_merge($response['links'], $branches['links']);
		}
		$this->tasks = $response['tasks'];
		$this->calculateLevels();
		$this->normalizeParents();
		$this->addRootNode();
		$this->tasks = $this->sortByParents();
		$this->setUpDepends();
		$response['tasks'] = $this->removeRootNode();
		$response['canWrite'] = false;
		$response['canDelete'] = false;
		$response['cantWriteOnParent'] = false;
		$response['canAdd'] = false;
		return $response;
	}

	public function getGanttMileston($id)
	{
		$response = ['tasks' => [], 'data' => [], 'links' => []];
		$relatedListView = Vtiger_RelationListView_Model::getInstance(Vtiger_Record_Model::getInstanceById($id), 'ProjectMilestone');
		$relatedListView->getRelationModel()->set('QueryFields', [
			'projectmilestoneid' => 'projectmilestoneid',
			'projectid' => 'projectid',
			'projectmilestonename' => 'projectmilestonename',
			'projectmilestonedate' => 'projectmilestonedate',
			'projectmilestone_progress' => 'projectmilestone_progress',
			'description' => 'description'
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
			$projectmilestone['name'] = \App\Purifier::encodeHtml($row['projectmilestonename']);
			$projectmilestone['text'] = \App\Purifier::encodeHtml($row['projectmilestonename']);
			$projectmilestone['parent'] = $row['projectid'];
			$projectmilestone['module'] = 'ProjectMilestone';
			if ($row['projectmilestonedate']) {
				$startDate = strtotime(date('Y-m-d', strtotime($row['projectmilestonedate'])));
				$endDate = strtotime(date('Y-m-d', strtotime($row['projectmilestonedate'])) . ' +1 days');
				$projectmilestone['start'] = $startDate * 1000;
				$projectmilestone['start_date'] = date('Y-m-d', $startDate);
				$projectmilestone['end'] = $endDate * 1000;
				$projectmilestone['end_date'] = date('Y-m-d', $endDate);
				$projectmilestone['duration'] = $this->calculateDuration($projectmilestone['start_date'], $projectmilestone['end_date']);
			}
			$projectmilestone['progress'] = (int) $row['projectmilestone_progress'];
			$projectmilestone['description'] = $row['description'];
			$projectmilestone['priority'] = $row['projectmilestone_priority'];
			$projectmilestone['priority_label'] = \App\Language::translate($row['projectmilestone_priority'], 'ProjectMilestone');
			$projectmilestone['open'] = true;
			$projectmilestone['type'] = 'milestone';
			$projectmilestone['canWrite'] = false;
			$projectmilestone['canDelete'] = false;
			$projectmilestone['status'] = 'STATUS_ACTIVE';
			$projectmilestone['cantWriteOnParent'] = false;
			$projectmilestone['canAdd'] = false;
			$projecttask = $this->getGanttTask($row['id']);
			$response['tasks'][] = $projectmilestone;
			$response['data'][] = $projectmilestone;
			$response['links'][] = $link;
			$response['tasks'] = array_merge($response['tasks'], $projecttask['tasks']);
			$response['data'] = array_merge($response['data'], $projecttask['data']);
			$response['links'] = array_merge($response['links'], $projecttask['links']);
			$milestoneTime += $projecttask['task_time'];
			$progressInHours += $projecttask['task_time'] * $projectmilestone['progress'];
		}
		$dataReader->close();
		if ($milestoneTime) {
			$response['progress'] = round($progressInHours / $milestoneTime, 1) * 100;
		} else {
			$response['progress'] = 0;
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
			'targetenddate' => 'targetenddate',
			'description' => 'description'
		]);
		$dataReader = $relatedListView->getRelationQuery()->createCommand()->query();
		$response = ['tasks' => [], 'data' => [], 'links' => []];
		$taskTime = 0;
		while ($row = $dataReader->read()) {
			$projecttask = [];
			$link = [];
			$link['id'] = $row['id'];
			$link['target'] = $row['id'];
			$projecttask['id'] = $row['id'];
			$projecttask['name'] = \App\Purifier::encodeHtml($row['projecttaskname']);
			$projecttask['text'] = \App\Purifier::encodeHtml($row['projecttaskname']);
			if ($row['parentid']) {
				$link['type'] = 0;
				$link['source'] = $row['parentid'];
				$projecttask['parent'] = $row['parentid'];
			} else {
				$link['type'] = 2;
				$link['source'] = $row['projectmilestoneid'];
				$projecttask['parent'] = $row['projectmilestoneid'];
			}
			$projecttask['canWrite'] = false;
			$projecttask['canDelete'] = false;
			$projecttask['cantWriteOnParent'] = false;
			$projecttask['canAdd'] = false;
			$projecttask['progress'] = (int) $row['projecttaskprogress'];
			$projecttask['priority'] = $row['projecttaskpriority'];
			$projecttask['priority_label'] = \App\Language::translate($row['projecttaskpriority'], 'ProjectTask');
			$projecttask['description'] = App\Purifier::encodeHtml($row['description']);

			$projecttask['start_date'] = date('d-m-Y', strtotime($row['startdate']));
			$projecttask['start'] = strtotime($row['startdate']) * 1000;
			$endDate = strtotime(date('Y-m-d', strtotime($row['targetenddate'])) . ' +1 days');
			$projecttask['end_date'] = date('d-m-Y', $endDate);
			$projecttask['end'] = $endDate * 1000;
			$sDate = new DateTime($projecttask['start_date']);
			$eDate = new DateTime($projecttask['end_date']);
			$interval = $eDate->diff($sDate);
			$projecttask['duration'] = (int) $interval->format('%d');

			$projecttask['open'] = true;
			$projecttask['type'] = 'task';
			$projecttask['module'] = 'ProjectTask';
			$projecttask['status'] = 'STATUS_ACTIVE';
			$taskTime += $row['estimated_work_time'];
			$response['tasks'][] = $projecttask;
			$response['data'][] = $projecttask;
			$response['links'][] = $link;
		}
		$dataReader->close();
		$response['task_time'] = $taskTime;

		return $response;
	}
}
