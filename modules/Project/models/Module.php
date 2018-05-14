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
	/**
	 * @var array project tasks,milesones and projects
	 */
	private $tasks = [];

	/**
	 * @var array rootNode needed for tree generation process
	 */
	private $rootNode;

	/**
	 * @var array task nodes as tree with children
	 */
	private $tree = [];

	/**
	 * @var array all nodes segregated by type
	 */
	private $taskByType = [];

	/**
	 * @var bool is project loaded already?
	 */
	public $loaded = false;

	/**
	 * @var array associative array where key is task/milestone/project id and value is an array of all parent ids
	 */
	public $taskParents = [];

	/**
	 * Get parent nodes id as associative array [taskId]=>[parentId1,parentId2,...].
	 *
	 * @param string|int $parentId
	 * @param array      $parents  initial value
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
	 * Collect all parents of all tasks.
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
		$this->taskParents = $parents;
		return $parents;
	}

	/**
	 * Calculate task levels and dependencies.
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
	 * @param string $startDateStr
	 * @param string $endDateStr
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

	/**
	 * Normalize task parent property set as 0 if not exists (root node).
	 */
	private function normalizeParents()
	{
		foreach ($this->tasks as &$task) {
			if (!isset($task['parent']) && $task['id'] !== 0) {
				$task['parent'] = 0;
			}
		}
	}

	/**
	 * Collect task all parent nodes.
	 *
	 * @param array $task
	 *
	 * @return array task with parents property int[]
	 */
	private function &getRecordWithChildren(&$task)
	{
		foreach ($this->tasks as &$child) {
			if (isset($child['parent']) && $child['parent'] === $task['id']) {
				if (empty($task['children'])) {
					$task['children'] = [];
				}
				$task['children'][] = &$this->getRecordWithChildren($child);
			}
		}
		return $task;
	}

	/**
	 * Flatten task tree with proper order to use it in frontend gantt lib.
	 *
	 * @param       $nodes tasks tree
	 * @param array $flat  initial array
	 *
	 * @return array
	 */
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

	/**
	 * Remove children property from tasks (we don't need them in frontend).
	 *
	 * @param $tasks
	 *
	 * @return array new array (not mutated)
	 */
	private function removeChildren($tasks)
	{
		$cleaned = [];
		foreach ($tasks as &$task) {
			if (isset($task['children'])) {
				unset($task['children']);
			}
			$cleaned[] = $task;
		}
		return $cleaned;
	}

	/**
	 * Sort all node types (task,milestones,projects) so each parent task is before its child (frontend lib needs this).
	 *
	 * @return array all node types as flat 1-dimensioned array
	 */
	private function collectChildrens()
	{
		$tree = $this->getRecordWithChildren($this->rootNode);
		$this->tree = $tree;
	}

	/**
	 * Add root node to generate tree structure.
	 */
	private function addRootNode()
	{
		$this->rootNode = ['id' => 0];
		$tasks = [
			&$this->rootNode,
		];
		foreach ($this->tasks as &$task) {
			$tasks[] = $task;
		}
		$this->tasks = $tasks;
	}

	/**
	 * Remove root node because it is not needed anymore.
	 *
	 * @return array new array (not mutated)
	 */
	private function cleanup($tasks)
	{
		$clean = [];
		foreach ($tasks as $task) {
			if ($task['id'] !== 0) {
				if ($task['parent'] === 0) {
					unset($task['parent']);
					$task['depends'] = '';
				}
				$clean[] = $task;
			}
		}
		return $clean;
	}

	public function iterateNodes(&$node, $currentValue, $callback)
	{
		if (empty($node['children'])) {
			return $currentValue;
		}
		foreach ($node['children'] as &$child) {
			$currentValue = $callback($child, $currentValue);
			if (!empty($child['children'])) {
				$currentValue = $this->iterateNodes($child, $currentValue, $callback);
			}
		}
		return $currentValue;
	}

	/**
	 * search for tasks within milestone.
	 *
	 * @param $milestone
	 */
	private function findOutStartDates(&$node)
	{
		$maxTimeStampValue = 2147483647;
		$firstDate = $this->iterateNodes($node, $maxTimeStampValue, function (&$child, $firstDate) {
			if (!empty($child['start_date']) && $child['start_date'] !== '1970-01-01') {
				$taskStartDate = strtotime($child['start_date']);
				// echo "[{$child['text']}]($taskStartDate:$startDate) ";
				if ($taskStartDate < $firstDate && $taskStartDate > 0) {
					return $taskStartDate;
				}
			}
			return $firstDate;
		});
		if ($firstDate < 0 || date('Y-m-d', $firstDate) === '2038-01-19') {
			$firstDate = strtotime(date('Y-m-d'));
			$node['duration'] = 1;
		}
		//echo "<br><br>firstDate '$firstDate' <br>" . date('Y-m-d', $firstDate) . '<br><br>';
		if (empty($node['start_date'])) {
			$node['start_date'] = date('Y-m-d', $firstDate);
			$node['start'] = $firstDate * 1000;
		}
		// iterate one more time setting up empty dates
		$this->iterateNodes($node, $firstDate, function (&$child, $firstDate) {
			if (empty($child['start_date']) || $child['start_date'] === '1970-01-01') {
				$child['start_date'] = date('Y-m-d', $firstDate);
				$child['start'] = $firstDate * 1000;
			}
			return $firstDate;
		});
		return $firstDate;
	}

	/**
	 * search for tasks within milestone.
	 *
	 * @param $milestone
	 */
	private function findOutEndDates(&$node)
	{
		$lastDate = $this->iterateNodes($node, 0, function (&$child, $lastDate) {
			if (!empty($child['start_date']) && $child['start_date'] !== '1970-01-01') {
				$taskDate = strtotime($child['end_date']);
				// echo "[{$child['text']}]($taskStartDate:$startDate) ";
				if ($taskDate > $lastDate) {
					return $taskDate;
				}
			}
			return $lastDate;
		});
		if ($lastDate === 0) {
			$lastDate = strtotime(date('Y-m-d'));
		}
		if (empty($node['end_date'])) {
			$node['end_date'] = date('Y-m-d', $lastDate);
			$node['end'] = $lastDate * 1000;
		}
		// iterate one more time setting up empty dates
		$this->iterateNodes($node, $lastDate, function (&$child, $lastDate) {
			if (empty($child['end_date'])) {
				$child['end_date'] = date('Y-m-d', $lastDate);
				$child['end'] = $lastDate * 1000;
			}
			return $lastDate;
		});
		return $lastDate;
	}

	/**
	 * Calculate milestone start date from children tasks/milestones.
	 */
	private function calculateDates()
	{
		$this->findOutStartDates($this->rootNode);
		$this->findOutEndDates($this->rootNode);
	}

	private function calculateDurations()
	{
		foreach ($this->tasks as &$task) {
			if (empty($task['duration'])) {
				$task['duration'] = $this->calculateDuration($task['start_date'], $task['end_date']);
			}
		}
	}

	/**
	 * Check if project was loaded.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	private function checkIfProjectWasLoaded()
	{
		if (!$this->loaded) {
			throw new \App\Exceptions\AppException('LBL_PROJECT_NOT_LOADED');
		}
		return true;
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

			if (!empty($recordModel->get('startdate'))) {
				$project['start_date'] = $recordModel->get('startdate');
				$project['start'] = strtotime($project['start_date']) * 1000;
			}
			$project['end_date'] = $recordModel->get('actualenddate');
			if (empty($project['end_date']) && !empty($recordModel->get('targetenddate'))) {
				$project['end_date'] = $recordModel->get('targetenddate');
				$project['end'] = strtotime($project['end_date']) * 1000;
			}
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
		$this->collectChildrens();
		$this->calculateDates();
		$this->calculateDurations();
		$response['tasks'] = $this->cleanup($this->removeChildren($this->flattenRecordTasks($this->tree['children'])));
		$response['canWrite'] = false;
		$response['canDelete'] = false;
		$response['cantWriteOnParent'] = false;
		$response['canAdd'] = false;
		$this->loaded = true;
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
				$endDate = strtotime($row['projectmilestonedate']);
				$projectmilestone['end'] = $endDate * 1000;
				$projectmilestone['end_date'] = date('Y-m-d', $endDate);
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
			$this->milestones[] = $projectmilestone;
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
