<?php

/**
 * Gantt Model class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Project_Gantt_Model
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
	 * @var array colors for statuses
	 */
	public $statusColors = [];

	/**
	 * @var array if some task is already loaded get it from here
	 */
	private $tasksById = [];

	/**
	 * @var array statuses - with closing value
	 */
	private $statuses;

	/**
	 * @var array - without closing value - for JS filter
	 */
	private $activeStatuses;

	/**
	 * Get parent nodes id as associative array [taskId]=>[parentId1,parentId2,...].
	 *
	 * @param string|int $parentId
	 * @param array      $parents  initial value
	 *
	 * @return array
	 */
	private function getParentRecordsIdsRecursive($parentId, $parents = [])
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
					$parents = $this->getParentRecordsIdsRecursive($task['parent'], $parents);
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
	private function getAllParentRecordsIds()
	{
		$parents = [];
		foreach ($this->tasks as $task) {
			if (!empty($task['parent'])) {
				$parents[$task['id']] = $this->getParentRecordsIdsRecursive($task['parent']);
			} else {
				$parents[$task['id']] = [];
			}
		}
		return $parents;
	}

	/**
	 * Calculate task levels and dependencies.
	 */
	private function calculateLevels()
	{
		$parents = $this->getAllParentRecordsIds();
		foreach ($this->tasks as &$task) {
			$task['level'] = count($parents[$task['id']]);
			$task['parents'] = $parents[$task['id']];
		}
		unset($task);
		$hasChild = [];
		foreach ($parents as $parentsId) {
			foreach ($parentsId as $parentId) {
				if (!in_array($parentId, $hasChild)) {
					$hasChild[] = $parentId;
				}
			}
		}
		foreach ($this->tasks as &$task) {
			if (in_array($task['id'], $hasChild)) {
				$task['hasChild'] = true;
			} else {
				$task['hasChild'] = false;
			}
		}
		unset($parents);
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
		return ((int) (new DateTime($startDateStr))->diff(new DateTime($endDateStr), true)->format('%a')) * 24 * 60 * 60;
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
	 * @return task[]
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
	 * Sort all node types (task,milestones,projects) so each parent task is before its child (frontend lib needs this).
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
		array_unshift($this->tasks, $this->rootNode);
	}

	/**
	 * Remove root node and children because they are not needed anymore.
	 *
	 * @param task[] $tasks
	 *
	 * @return task[] new array (not mutated)
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
				if (isset($task['children'])) {
					unset($task['children']);
				}
				if (isset($task['parents'])) {
					unset($task['parents']);
				}
				if (isset($task['depends'])) {
					unset($task['depends']);
				}
				if (!isset($task['progress'])) {
					$task['progress'] = 100;
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
			$node['start'] = date('Y-m-d H:i:s', $firstDate);
		}
		// iterate one more time setting up empty dates
		$this->iterateNodes($node, $firstDate, function (&$child, $firstDate) {
			if (empty($child['start_date']) || $child['start_date'] === '1970-01-01') {
				$child['start_date'] = date('Y-m-d', $firstDate);
				$child['start'] = date('Y-m-d H:i:s', $firstDate);
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
	 * Calculate task duration in days.
	 */
	private function calculateDurations()
	{
		foreach ($this->tasks as &$task) {
			if (empty($task['duration']) && isset($task['start_date'], $task['end_date'])) {
				$task['duration'] = $this->calculateDuration($task['start_date'], $task['end_date']);
			}
		}
	}

	/**
	 * Collect all statuses.
	 */
	public function getStatuses()
	{
		$closingStatuses = Settings_RealizationProcesses_Module_Model::getStatusNotModify();
		if (empty($closingStatuses['Project'])) {
			$closingStatuses['Project'] = ['status' => []];
		}
		if (empty($closingStatuses['ProjectMilestone'])) {
			$closingStatuses['ProjectMilestone'] = ['status' => []];
		}
		if (empty($closingStatuses['ProjectTask'])) {
			$closingStatuses['ProjectTask'] = ['status' => []];
		}
		$colors = ['Project' => [], 'ProjectMilestone' => [], 'ProjectTask' => []];
		$project = array_values(App\Fields\Picklist::getValues('projectstatus'));
		foreach ($project as $value) {
			$this->statuses['Project'][] = $status = ['value' => $value['projectstatus'], 'label' => App\Language::translate($value['projectstatus'], 'Project'), 'closing' => in_array($value['projectstatus'], $closingStatuses['Project']['status'])];
			if (!$status['closing']) {
				$this->activeStatuses['Project'][] = $status;
			}
			$colors['Project']['projectstatus'][$value['projectstatus']] = \App\Colors::get($value['color'] ?? '', $value['projectstatus']);
		}
		$projectMilestone = array_values(App\Fields\Picklist::getValues('projectmilestone_status'));
		foreach ($projectMilestone as $value) {
			$this->statuses['ProjectMilestone'][] = $status = ['value' => $value['projectmilestone_status'], 'label' => App\Language::translate($value['projectmilestone_status'], 'ProjectMilestone'), 'closing' => in_array($value['projectmilestone_status'], $closingStatuses['ProjectMilestone']['status'])];
			if (!$status['closing']) {
				$this->activeStatuses['ProjectMilestone'][] = $status;
			}
			$colors['ProjectMilestone']['projectmilestone_status'][$value['projectmilestone_status']] = \App\Colors::get($value['color'] ?? '', $value['projectmilestone_status']);
		}
		$projectTask = array_values(App\Fields\Picklist::getValues('projecttaskstatus'));
		foreach ($projectTask as $value) {
			$this->statuses['ProjectTask'][] = $status = ['value' => $value['projecttaskstatus'], 'label' => App\Language::translate($value['projecttaskstatus'], 'ProjectTask'), 'closing' => in_array($value['projecttaskstatus'], $closingStatuses['ProjectTask']['status'])];
			if (!$status['closing']) {
				$this->activeStatuses['ProjectTask'][] = $status;
			}
			$colors['ProjectTask']['projecttaskstatus'][$value['projecttaskstatus']] = \App\Colors::get($value['color'] ?? '', $value['projecttaskstatus']);
		}
		$configColors = \AppConfig::module('Project', 'defaultGanttColors');
		if (!empty($configColors)) {
			$this->statusColors = $configColors;
		} else {
			$this->statusColors = $colors;
		}
	}

	/**
	 * Prepare tasks and gather some information.
	 */
	private function prepareRecords()
	{
		$this->addRootNode();
		$this->normalizeParents();
		$this->collectChildrens();
		$this->calculateLevels();
		$this->findOutStartDates($this->rootNode);
		$this->findOutEndDates($this->rootNode);
		$this->calculateDurations();
	}

	/**
	 * Get project data.
	 *
	 * @param int|array $id project id
	 *
	 * @return array
	 */
	private function getProject($id, $viewName = null)
	{
		if (!is_array($id) && isset($this->tasksById[$id])) {
			return [$this->tasksById[$id]];
		}
		if (!is_array($id)) {
			$id = [$id];
		}
		$projects = [];
		$queryGenerator = new App\QueryGenerator('Project');
		$queryGenerator->setFields(['id', 'projectid', 'parentid', 'projectname', 'projectpriority', 'description', 'project_no', 'projectstatus', 'startdate', 'actualenddate', 'targetenddate', 'assigned_user_id']);
		if ($id !== [0]) {
			// empty id means that we want all projects
			$queryGenerator->addNativeCondition([
				'or',
				['parentid' => $id],
				['projectid' => array_diff($id, array_keys($this->tasksById))]
			]);
		}
		if ($viewName) {
			$query = $queryGenerator->getCustomViewQueryById($viewName);
		} else {
			$query = $queryGenerator->createQuery();
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$project = [
				'id' => $row['id'],
				'name' => \App\Purifier::encodeHtml($row['projectname']),
				'label' => \App\Purifier::encodeHtml($row['projectname']),
				'url' => 'index.php?module=Project&view=Detail&record=' . $row['id'],
				'parentId' => !empty($row['parentid']) ? $row['parentid'] : null,
				'priority' => $row['projectpriority'],
				'priority_label' => \App\Language::translate($row['projectpriority'], 'Project'),
				'status' => 'STATUS_ACTIVE',
				'type' => 'project',
				'module' => 'Project',
				'open' => true,
				'canWrite' => false,
				'canDelete' => false,
				'cantWriteOnParent' => false,
				'canAdd' => false,
				'description' => \App\Purifier::encodeHtml($row['description']),
				'no' => $row['project_no'],
				'normalized_status' => $row['projectstatus'],
				'status_label' => App\Language::translate($row['projectstatus'], 'Project'),
				'assigned_user_id' => $row['assigned_user_id'],
				'assigned_user_name' => \App\Fields\Owner::getUserLabel($row['assigned_user_id']),
				'color' => $row['projectstatus'] ? $this->statusColors['Project']['projectstatus'][$row['projectstatus']] : \App\Colors::getRandomColor('projectstatus_' . $row['id']),
			];
			$project['number'] = '<a href="' . $project['url'] . '" target="_blank">' . $project['no'] . '</a>';
			if (empty($project['parentId'])) {
				unset($project['parentId']);
			}
			if (!empty($row['startdate'])) {
				$project['start_date'] = $row['startdate'];
				$project['start'] = date('Y-m-d H:i:s', strtotime($row['startdate']));
			}
			$project['end_date'] = $row['actualenddate'];
			if (empty($project['end_date']) && !empty($row['targetenddate'])) {
				$endDate = strtotime(date('Y-m-d', strtotime($row['targetenddate'])) . ' +1 days');
				$project['end_date'] = date('Y-m-d', $endDate);
				$project['end'] = strtotime($project['end_date']) * 1000;
			}
			$project['style'] = [
				'base' => [
					'fill' => $project['color'],
					'border' => $project['color']
				]
			];
			unset($project['color']);
			$this->tasksById[$row['id']] = $project;
			$projects[] = $project;
			if ($id !== [0] && !in_array($row['id'], $id)) {
				$childrenIds[] = $row['id'];
			}
		}
		$dataReader->close();
		if (!empty($childrenIds)) {
			$projects = array_merge($projects, $this->getProject($childrenIds, $viewName));
		}
		unset($queryGenerator, $query, $dataReader, $project);
		return $projects;
	}

	/**
	 * Get all projects from the system.
	 *
	 * @return array projects,milestones,tasks
	 */
	public function getAllData($viewName = null)
	{
		$this->getStatuses();
		$projects = $this->getProject(0, $viewName);
		$projectIds = array_column($projects, 'id');
		$milestones = $this->getGanttMilestones($projectIds);
		$ganttTasks = $this->getGanttTasks($projectIds);
		$this->tasks = array_merge($projects, $milestones, $ganttTasks);
		$this->prepareRecords();
		$response = [
			'statusColors' => $this->statusColors,
			'canWrite' => false,
			'canDelete' => false,
			'cantWriteOnParent' => false,
			'canAdd' => false,
			'statuses' => $this->statuses,
			'activeStatuses' => $this->activeStatuses
		];
		if (!empty($this->tree) && !empty($this->tree['children'])) {
			$response['tasks'] = $this->cleanup($this->flattenRecordTasks($this->tree['children']));
		}
		unset($projectIds, $milestones, $ganttTasks, $projects, $queryGenerator, $rootProjectIds, $projectIdsRows);
		return $response;
	}

	/**
	 * Get project data to display in view as gantt.
	 *
	 * @param int|string $id
	 *
	 * @return array - projects,milestones,tasks
	 */
	public function getById($id)
	{
		$this->getStatuses();
		$projects = $this->getProject($id);
		$projectIds = array_column($projects, 'id');
		$milestones = $this->getGanttMilestones($projectIds);
		$ganttTasks = $this->getGanttTasks($projectIds);
		$this->tasks = array_merge($projects, $milestones, $ganttTasks);
		$this->prepareRecords();
		$response = [
			'statusColors' => $this->statusColors,
			'canWrite' => false,
			'canDelete' => false,
			'cantWriteOnParent' => false,
			'canAdd' => false,
			'statuses' => $this->statuses,
			'activeStatuses' => $this->activeStatuses
		];
		if (!empty($this->tree) && !empty($this->tree['children'])) {
			$response['tasks'] = $this->cleanup($this->flattenRecordTasks($this->tree['children']));
		}
		unset($projects, $projectIds, $milestones, $ganttTasks);
		return $response;
	}

	/**
	 * Get project milestones.
	 *
	 * @param int|int[] $projectIds
	 *
	 * @return milestone[]
	 */
	public function getGanttMilestones($projectIds)
	{
		$queryGenerator = new App\QueryGenerator('ProjectMilestone');
		$queryGenerator->setFields(['id', 'parentid', 'projectid', 'projectmilestonename', 'projectmilestonedate', 'projectmilestone_no', 'projectmilestone_progress', 'projectmilestone_priority', 'projectmilestone_status', 'assigned_user_id']);
		$queryGenerator->addNativeCondition(['vtiger_projectmilestone.projectid' => $projectIds]);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$milestones = [];
		while ($row = $dataReader->read()) {
			$row['parentid'] = (int) $row['parentid'];
			$row['projectid'] = (int) $row['projectid'];
			$milestone = [
				'id' => $row['id'],
				'name' => \App\Purifier::encodeHtml($row['projectmilestonename']),
				'label' => \App\Purifier::encodeHtml($row['projectmilestonename']),
				'url' => 'index.php?module=ProjectMilestone&view=Detail&record=' . $row['id'],
				'parentId' => !empty($row['parentid']) ? $row['parentid'] : $row['projectid'],
				'module' => 'ProjectMilestone',
				'progress' => (int) $row['projectmilestone_progress'],
				'priority' => $row['projectmilestone_priority'],
				'priority_label' => \App\Language::translate($row['projectmilestone_priority'], 'ProjectMilestone'),
				'open' => true,
				'type' => 'milestone',
				'normalized_status' => $row['projectmilestone_status'],
				'status_label' => App\Language::translate($row['projectmilestone_status'], 'ProjectMilestone'),
				'canWrite' => false,
				'canDelete' => false,
				'status' => 'STATUS_ACTIVE',
				'cantWriteOnParent' => false,
				'canAdd' => false,
				'no' => $row['projectmilestone_no'],
				'assigned_user_id' => $row['assigned_user_id'],
				'assigned_user_name' => \App\Fields\Owner::getUserLabel($row['assigned_user_id']),
				'startIsMilestone' => true,
				'color' => $row['projectmilestone_status'] ? $this->statusColors['ProjectMilestone']['projectmilestone_status'][$row['projectmilestone_status']] : App\Colors::getRandomColor('projectmilestone_status_' . $row['id']),
			];
			$milestone['number'] = '<a href="' . $milestone['url'] . '" target="_blank">' . $milestone['no'] . '</a>';
			if (empty($milestone['parentId'])) {
				unset($milestone['parentId']);
			}
			if ($row['projectmilestonedate']) {
				$milestione['duration'] = 24 * 60 * 60;
				$milestone['start'] = date('Y-m-d H:i:s', strtotime($row['projectmilestonedate']));
				$milestone['start_date'] = date('Y-m-d', strtotime($row['projectmilestonedate']));
				$endDate = strtotime(date('Y-m-d', strtotime($row['projectmilestonedate'])) . ' +1 days');
				$milestone['end'] = $endDate * 1000;
				$milestone['end_date'] = date('Y-m-d', $endDate);
			}
			$milestone['style'] = [
				'base' => [
					'fill' => $milestone['color'],
					'border' => $milestone['color']
				]
			];
			unset($milestone['color']);
			$milestones[] = $milestone;
		}
		$dataReader->close();
		unset($dataReader, $queryGenerator);
		return $milestones;
	}

	/**
	 * Get project tasks.
	 *
	 * @param int|int[] $projectIds
	 *
	 * @return task[]
	 */
	public function getGanttTasks($projectIds)
	{
		$taskTime = 0;
		$queryGenerator = new App\QueryGenerator('ProjectTask');
		$queryGenerator->setFields(['id', 'projectid', 'projecttaskname', 'parentid', 'projectmilestoneid', 'projecttaskprogress', 'projecttaskpriority', 'startdate', 'targetenddate', 'projecttask_no', 'projecttaskstatus', 'estimated_work_time', 'assigned_user_id']);
		$queryGenerator->addNativeCondition(['vtiger_projecttask.projectid' => $projectIds]);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$ganttTasks = [];
		while ($row = $dataReader->read()) {
			$task = [
				'id' => $row['id'],
				'name' => \App\Purifier::encodeHtml($row['projecttaskname']),
				'label' => \App\Purifier::encodeHtml($row['projecttaskname']),
				'url' => 'index.php?module=ProjectTask&view=Detail&record=' . $row['id'],
				'parentId' => (int) ($row['parentid'] ?? 0),
				'canWrite' => false,
				'canDelete' => false,
				'cantWriteOnParent' => false,
				'canAdd' => false,
				'progress' => (int) $row['projecttaskprogress'],
				'priority' => $row['projecttaskpriority'],
				'priority_label' => \App\Language::translate($row['projecttaskpriority'], 'ProjectTask'),
				'no' => $row['projecttask_no'],
				'normalized_status' => $row['projecttaskstatus'],
				'status_label' => App\Language::translate($row['projecttaskstatus'], 'ProjectTask'),
				'color' => $row['projecttaskstatus'] ? $this->statusColors['ProjectTask']['projecttaskstatus'][$row['projecttaskstatus']] : App\Colors::getRandomColor('projecttaskstatus_' . $row['id']),
				'start_date' => date('Y-m-d', strtotime($row['startdate'])),
				'start' => date('Y-m-d H:i:s', strtotime($row['startdate'])),
				'end_date' => $row['targetenddate'],
				'assigned_user_id' => $row['assigned_user_id'],
				'assigned_user_name' => \App\Fields\Owner::getUserLabel($row['assigned_user_id']),
				'open' => true,
				'type' => 'task',
				'module' => 'ProjectTask',
				'status' => 'STATUS_ACTIVE',
			];
			$task['number'] = '<a href="' . $task['url'] . '" target="_blank">' . $task['no'] . '</a>';
			if (empty($task['parentId'])) {
				$parentId = (int) ($row['projectmilestoneid'] ?? $row['projectid']);
				if ($parentId) {
					$task['parentId'] = $parentId;
				}
			}
			$task['style'] = [
				'base' => [
					'fill' => $task['color'],
					'border' => $task['color']
				]
			];
			unset($task['color']);
			$endDate = strtotime(date('Y-m-d', strtotime($row['targetenddate'])) . ' +1 days');
			$task['duration'] = $this->calculateDuration($task['start_date'], $task['end_date']);
			$taskTime += $row['estimated_work_time'];
			$ganttTasks[] = $task;
		}
		$dataReader->close();
		unset($dataReader, $queryGenerator, $taskTime, $endDate);
		return $ganttTasks;
	}
}
