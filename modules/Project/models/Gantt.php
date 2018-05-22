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

class Project_Gantt_Model extends App\Base
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
	 * @var bool is project loaded already?
	 */
	public $loaded = false;

	/**
	 * @var array associative array where key is task/milestone/project id and value is an array of all parent ids
	 */
	public $taskParents = [];

	/**
	 * @var array colors for statuses
	 */
	public $statusColors = [];

	/**
	 * @var array already loaded ids
	 */
	private $loadedIds = [];

	/**
	 * @var array if some task is already loaded get it from here
	 */
	private $tasksById = [];

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
		// not set parents are children of root node
		foreach ($this->tasks as &$task) {
			if (!isset($task['parent']) && $task['id'] !== 0) {
				$task['parent'] = 0;
			}
		}
		// if parent id is set but we don't have it - it means that project is subproject so connect it to root node
		foreach ($this->tasks as &$task) {
			if (!empty($task['parent'])) {
				$idExists = false;
				foreach ($this->tasks as $parent) {
					if ($task['parent'] === $parent['id']) {
						$idExists = true;
						break;
					}
				}
				if (!$idExists) {
					$task['parent'] = 0;
				}
			}
		}
	}

	/**
	 * Normalize numbers.
	 */
	private function normalizeNumbers()
	{
		foreach ($this->tasks as &$task) {
			if (!empty($task['projecttask_no'])) {
				$task['no'] = $task['projecttask_no'];
			} elseif (!empty($task['projectmilestone_no'])) {
				$task['no'] = $task['projectmilestone_no'];
			} elseif (!empty($task['project_no'])) {
				$task['no'] = $task['project_no'];
			}
		}
	}

	/**
	 * Normalize statuses to display in table.
	 */
	private function normalizeStatuses()
	{
		foreach ($this->tasks as &$task) {
			if (!empty($task['projectstatus'])) {
				$task['internal_status'] = App\Language::translate($task['projectstatus'], 'Project');
			} elseif (!empty($task['projectmilestone_status'])) {
				$task['internal_status'] = App\Language::translate($task['projectmilestone_status'], 'ProjectMilestone');
			} elseif (!empty($task['projecttaskstatus'])) {
				$task['internal_status'] = App\Language::translate($task['projecttaskstatus'], 'ProjectTask');
			} else {
				$task['internal_status'] = '';
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
		$this->tree = &$this->getRecordWithChildren($this->rootNode);
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

	/**
	 * Iterate through all tasks in tree.
	 *
	 * @param array    $node         starting point - might by rootNode
	 * @param mixed    $currentValue initial result which will be evaluated if there are some child nodes like array reduce
	 * @param callable $callback     what to do with task
	 *
	 * @return mixed
	 */
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
	 * Iterate through children and search for start date.
	 *
	 * @param array $node
	 *
	 * @return int timestamp
	 */
	private function findOutStartDates(&$node)
	{
		$maxTimeStampValue = 2147483647;
		$firstDate = $this->iterateNodes($node, $maxTimeStampValue, function (&$child, $firstDate) {
			if (!empty($child['start_date']) && $child['start_date'] !== '1970-01-01') {
				$taskStartDate = strtotime($child['start_date']);
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
	 * Iterate through children and search for end date.
	 *
	 * @param array $node
	 *
	 * @return int timestamp
	 */
	private function findOutEndDates(&$node)
	{
		$lastDate = $this->iterateNodes($node, 0, function (&$child, $lastDate) {
			if (!empty($child['start_date']) && $child['start_date'] !== '1970-01-01') {
				$taskDate = strtotime($child['end_date']);
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

	/**
	 * Calculate task duration in days.
	 */
	private function calculateDurations()
	{
		foreach ($this->tasks as &$task) {
			if (empty($task['duration'])) {
				$task['duration'] = $this->calculateDuration($task['start_date'], $task['end_date']);
			}
		}
	}

	/**
	 * Collect all modules picklists colors to use in gantt bars.
	 *
	 * @return array
	 */
	public function getStatusColors()
	{
		$this->statusColors['Project'] = \App\Colors::getPicklists('Project');
		$this->statusColors['ProjectMilestone'] = App\Colors::getPicklists('ProjectMilestone');
		$this->statusColors['ProjectTask'] = App\Colors::getPicklists('ProjectTask');
		return $this->statusColors;
	}

	/**
	 * Collect all modules picklists names and values that we can use in filters.
	 *
	 * @return array
	 */
	public static function getPicklistValues()
	{
		$picklistsNames = [];
		$picklists = [];
		$picklistsNames['Project'] = App\Fields\Picklist::getModulesByName('Project');
		$picklistsNames['ProjectMilestone'] = App\Fields\Picklist::getModulesByName('ProjectMilestone');
		$picklistsNames['ProjectTask'] = App\Fields\Picklist::getModulesByName('ProjectTask');
		$picklists['Project'] = [];
		foreach ($picklistsNames['Project'] as $name) {
			$picklists['Project'][$name] = [];
			$picklistValues = array_values(App\Fields\Picklist::getValues($name));
			$values = array_column($picklistValues, 'picklistValue');
			foreach ($values as $index => $value) {
				$picklists['Project'][$name][] = [
					'value' => $value,
					'label' => App\Language::translate($value, 'Project'),
				];
			}
		}
		$picklists['ProjectMilestone'] = [];
		foreach ($picklistsNames['ProjectMilestone'] as $name) {
			$picklists['ProjectMilestone'][$name] = [];
			$values = array_column(array_values(App\Fields\Picklist::getValues($name)), 'picklistValue');
			foreach ($values as $value) {
				$picklists['ProjectMilestone'][$name][] = ['value' => $value, 'label' => App\Language::translate($value, 'ProjectMilestone')];
			}
		}
		$picklists['ProjectTask'] = [];
		foreach ($picklistsNames['ProjectTask'] as $name) {
			$picklists['ProjectTask'][$name] = [];
			$values = array_column(array_values(App\Fields\Picklist::getValues($name)), 'picklistValue');
			foreach ($values as $value) {
				$picklists['ProjectTask'][$name][] = ['value' => $value, 'label' => App\Language::translate($value, 'ProjectTask')];
			}
		}
		return $picklists;
	}

	/**
	 * Get project data.
	 *
	 * @param int $id project id
	 *
	 * @return array
	 */
	private function getProject($id)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($id, 'Project');
		$project['id'] = $id;
		$project['parent'] = $recordModel->get('parentid'); // we must collet parents
		$project['name'] = \App\Purifier::encodeHtml($recordModel->get('projectname'));
		$project['text'] = \App\Purifier::encodeHtml($recordModel->get('projectname'));
		$project['priority'] = $recordModel->get('projectpriority');
		$project['priority_label'] = \App\Language::translate($recordModel->get('projectpriority'), 'Project');
		$project['status'] = 'STATUS_ACTIVE';
		$project['type'] = 'project';
		$project['module'] = 'Project';
		$project['open'] = true;
		$project['canWrite'] = false;
		$project['canDelete'] = false;
		$project['cantWriteOnParent'] = false;
		$project['canAdd'] = false;
		$project['description'] = \App\Purifier::encodeHtml($recordModel->get('description'));
		$project['project_no'] = $recordModel->get('project_no');
		$project['projectstatus'] = $recordModel->get('projectstatus');
		$color = $this->statusColors['Project']['projectstatus'][$project['projectstatus']];
		if (empty($color)) {
			$color = App\Colors::getRandomColor($project['projectstatus'] . '_status');
		}
		$project['color'] = $color;
		if (!empty($recordModel->get('startdate'))) {
			$project['start_date'] = $recordModel->get('startdate');
			$project['start'] = strtotime($project['start_date']) * 1000;
		}
		$project['end_date'] = $recordModel->get('actualenddate');
		if (empty($project['end_date']) && !empty($recordModel->get('targetenddate'))) {
			$project['end_date'] = $recordModel->get('targetenddate');
			$project['end'] = strtotime($project['end_date']) * 1000;
		}
		$this->loadedIds[] = $id;
		$this->tasksById[$id] = $project;
		return $project;
	}

	/**
	 * Recursively collect project children (sub projects).
	 *
	 * @param $id project id
	 *
	 * @return array
	 */
	private function getProjectChildren($id)
	{
		$queryGenerator = new App\QueryGenerator('Project');
		$queryGenerator->setField('id');
		$queryGenerator->setField('parentid');
		$queryGenerator->addNativeCondition(['parentid'=>(int) $id]);
		$childrenRows = $queryGenerator->createQuery()->createCommand()->queryAll();
		$childrenIds = array_map(function ($item) {
			return $item['id'];
		}, $childrenRows);
		$children = [];
		foreach ($childrenIds as $childrenId) {
			$child = $this->getProject($childrenId);
			$children[] = $child;
			$childChildren = $this->getProjectChildren($childrenId);
			$children = array_merge($children, $childChildren);
		}
		return $children;
	}

	/**
	 * Get flatt array of projects with children.
	 *
	 * @param int $id project id
	 *
	 * @return array
	 */
	private function getProjects($id)
	{
		$projects = [];
		$project = $this->getProject($id);
		$projects[] = $project;
		$children = $this->getProjectChildren($id);
		$projects = array_merge($projects, $children);
		return $projects;
	}

	/**
	 * Get all projects from the system.
	 *
	 * @return array
	 */
	public function getAllGanttProjects($viewName = null)
	{
		$this->getStatusColors();
		$response = ['tasks' => [], 'links' => []];
		$queryGenerator = new App\QueryGenerator('Project');
		$queryGenerator->setField('id');
		$queryGenerator->setField('parentid');
		$queryGenerator->addNativeCondition(['vtiger_project.parentid' => 0]);
		if ($viewName) {
			$query = $queryGenerator->getCustomViewQueryById($viewName);
		} else {
			$query = $queryGenerator->createQuery();
		}
		$projectIdsRows = $query->createCommand()->queryAll();
		$rootProjectIds = array_map(function ($item) {
			return $item['id'];
		}, $projectIdsRows);
		$projects = [];
		foreach ($rootProjectIds as $projectId) {
			$projects = array_merge($projects, $this->getProjects($projectId));
		}
		$projectIds = array_map(function ($item) {
			return $item['id'];
		}, $projects);
		$milestones = $this->getGanttMilestones($projectIds);
		$tasks = $this->getGanttTasks($projectIds);
		$this->tasks = array_merge($projects, $milestones, $tasks);
		$this->addRootNode();
		$this->normalizeParents();
		$this->collectChildrens();
		$this->calculateLevels();
		$this->normalizeNumbers();
		$this->normalizeStatuses();
		$this->calculateDates();
		$this->calculateDurations();
		if (!empty($this->tree) && !empty($this->tree['children'])) {
			$response['tasks'] = $this->cleanup($this->removeChildren($this->flattenRecordTasks($this->tree['children'])));
		}
		$response['statusColors'] = $this->statusColors;
		$response['canWrite'] = false;
		$response['canDelete'] = false;
		$response['cantWriteOnParent'] = false;
		$response['canAdd'] = false;
		$response['picklists'] = static::getPicklistValues();
		$this->loaded = true;
		return $response;
	}

	/**
	 * Get project data to display in view as gantt.
	 *
	 * @param int|string $id
	 *
	 * @return array
	 */
	public function getGanttProject($id)
	{
		$this->getStatusColors();
		$response = ['tasks' => [], 'links' => []];
		$projects = $this->getProjects($id);
		$projectIds = array_map(function ($item) {
			return $item['id'];
		}, $projects);
		$milestones = $this->getGanttMilestones($projectIds);
		$tasks = $this->getGanttTasks($projectIds);
		$this->tasks = array_merge($projects, $milestones, $tasks);
		$this->addRootNode();
		$this->normalizeParents();
		$this->collectChildrens();
		$this->calculateLevels();
		$this->normalizeNumbers();
		$this->normalizeStatuses();
		$this->calculateDates();
		$this->calculateDurations();
		if (!empty($this->tree) && !empty($this->tree['children'])) {
			$response['tasks'] = $this->cleanup($this->removeChildren($this->flattenRecordTasks($this->tree['children'])));
		}
		$response['statusColors'] = $this->statusColors;
		$response['canWrite'] = false;
		$response['canDelete'] = false;
		$response['cantWriteOnParent'] = false;
		$response['canAdd'] = false;
		$response['picklists'] = static::getPicklistValues();
		$this->loaded = true;
		return $response;
	}

	/**
	 * Get project milestones.
	 *
	 * @param int|int[] $projectIds
	 *
	 * @return array
	 */
	public function getGanttMilestones($projectIds)
	{
		$queryGenerator = new App\QueryGenerator('ProjectMilestone');
		$queryGenerator->setField('id');
		$queryGenerator->setField('parentid');
		$queryGenerator->setField('projectid');
		$queryGenerator->setField('projectmilestonename');
		$queryGenerator->setField('projectmilestonedate');
		$queryGenerator->setField('projectmilestone_no');
		$queryGenerator->setField('projectmilestone_progress');
		$queryGenerator->setField('projectmilestone_status');
		$queryGenerator->addNativeCondition(['vtiger_projectmilestone.projectid' => $projectIds]);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();

		$milestones = [];
		while ($row = $dataReader->read()) {
			$milestone = [];
			$milestone['id'] = $row['id'];
			$milestone['name'] = \App\Purifier::encodeHtml($row['projectmilestonename']);
			$milestone['text'] = \App\Purifier::encodeHtml($row['projectmilestonename']);
			$milestone['parent'] = $row['parentid'] ? $row['parentid'] : $row['projectid'];
			$milestone['module'] = 'ProjectMilestone';
			if ($row['projectmilestonedate']) {
				$endDate = strtotime($row['projectmilestonedate']);
				$milestone['end'] = $endDate * 1000;
				$milestone['end_date'] = date('Y-m-d', $endDate);
			}
			$milestone['progress'] = (int) $row['projectmilestone_progress'];
			$milestone['description'] = $row['description'];
			$milestone['priority'] = $row['projectmilestone_priority'];
			$milestone['priority_label'] = \App\Language::translate($row['projectmilestone_priority'], 'ProjectMilestone');
			$milestone['open'] = true;
			$milestone['type'] = 'milestone';
			$milestone['projectmilestone_status'] = $row['projectmilestone_status'];
			$milestone['canWrite'] = false;
			$milestone['canDelete'] = false;
			$milestone['status'] = 'STATUS_ACTIVE';
			$milestone['cantWriteOnParent'] = false;
			$milestone['canAdd'] = false;
			$milestone['projectmilestone_no'] = $row['projectmilestone_no'];
			$color = $this->statusColors['ProjectMilestone']['projectmilestone_priority'][$row['projectmilestone_priority']];
			if (empty($color)) {
				$color = App\Colors::getRandomColor($row['projectmilestone_priority'] . '_status');
			}
			$milestone['color'] = $color;
			$milestones[] = $milestone;
		}
		$dataReader->close();
		return $milestones;
	}

	/**
	 * Get project tasks.
	 *
	 * @param int|int[] $projectIds
	 *
	 * @return array
	 */
	public function getGanttTasks($projectIds)
	{
		$taskTime = 0;
		$queryGenerator = new App\QueryGenerator('ProjectTask');
		$queryGenerator->setField('id');
		$queryGenerator->setField('projectid');
		$queryGenerator->setField('projecttaskname');
		$queryGenerator->setField('parentid');
		$queryGenerator->setField('projectmilestoneid');
		$queryGenerator->setField('projecttaskprogress');
		$queryGenerator->setField('projecttaskpriority');
		$queryGenerator->setField('startdate');
		$queryGenerator->setField('targetenddate');
		$queryGenerator->setField('projecttask_no');
		$queryGenerator->setField('projecttaskstatus');
		$queryGenerator->addNativeCondition(['vtiger_projecttask.projectid' => $projectIds]);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();

		$tasks = [];
		while ($row = $dataReader->read()) {
			$task = [];
			$task['id'] = $row['id'];
			$task['name'] = \App\Purifier::encodeHtml($row['projecttaskname']);
			$task['text'] = \App\Purifier::encodeHtml($row['projecttaskname']);
			$task['parent'] = $row['parentid'] ? $row['parentid'] : null;
			if (empty($task['parent'])) {
				$task['parent'] = $row['projectmilestoneid'] ? $row['projectmilestoneid'] : $row['projectid'];
			}
			$task['canWrite'] = false;
			$task['canDelete'] = false;
			$task['cantWriteOnParent'] = false;
			$task['canAdd'] = false;
			$task['progress'] = (int) $row['projecttaskprogress'];
			$task['priority'] = $row['projecttaskpriority'];
			$task['priority_label'] = \App\Language::translate($row['projecttaskpriority'], 'ProjectTask');
			$task['description'] = App\Purifier::encodeHtml($row['description']);
			$task['projecttask_no'] = $row['projecttask_no'];
			$task['projecttaskstatus'] = $row['projecttaskstatus'];
			$color = $this->statusColors['ProjectTask']['projecttaskstatus'][$row['projecttaskstatus']];
			if (empty($color)) {
				$color = App\Colors::getRandomColor($row['projecttaskstatus'] . '_status');
			}
			$task['color'] = $color;

			$task['start_date'] = date('d-m-Y', strtotime($row['startdate']));
			$task['start'] = strtotime($row['startdate']) * 1000;
			$endDate = strtotime(date('Y-m-d', strtotime($row['targetenddate'])) . ' +1 days');
			$task['end_date'] = date('d-m-Y', $endDate);
			$task['end'] = $endDate * 1000;
			$sDate = new DateTime($task['start_date']);
			$eDate = new DateTime($task['end_date']);
			$interval = $eDate->diff($sDate);
			$task['duration'] = (int) $interval->format('%d');

			$task['open'] = true;
			$task['type'] = 'task';
			$task['module'] = 'ProjectTask';
			$task['status'] = 'STATUS_ACTIVE';
			$taskTime += $row['estimated_work_time'];
			$tasks[] = $task;
		}
		$dataReader->close();
		return $tasks;
	}
}
