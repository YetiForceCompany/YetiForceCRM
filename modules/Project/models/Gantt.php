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
	 * @var array associative array where key is task/milestone/project id and value is an array of all parent ids
	 */
	public $taskParents = [];

	/**
	 * @var array colors for statuses
	 */
	public $statusColors = [];

	/**
	 * @var array if some task is already loaded get it from here
	 */
	private $tasksById = [];

	/**
	 * @var picklists values
	 */
	private $picklistsValues;

	/**
	 * @var statuses - with closing value
	 */
	private $statuses;

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
		$this->taskParents = $parents;
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
		$hasChild = [];
		foreach ($parents as $childId => $parentsId) {
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
		return (int) (new DateTime($startDateStr))->diff(new DateTime($endDateStr))->format('%d');
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
			if (empty($task['duration']) && isset($task['start_date'], $task['end_date'])) {
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
		if (!empty($this->statusColors)) {
			return $this->statusColors;
		}
		$configColors = \AppConfig::module('Project', 'defaultGanttColors');
		if (!empty($configColors)) {
			return $this->statusColors = $configColors;
		}
		$this->statusColors = [
			'Project' => \App\Colors::getPicklists('Project'),
			'ProjectMilestone' => App\Colors::getPicklists('ProjectMilestone'),
			'ProjectTask' => App\Colors::getPicklists('ProjectTask'),
		];
		return $this->statusColors;
	}

	/**
	 * Collect all modules picklists names and values that we can use in filters.
	 *
	 * @return array
	 */
	public function getPicklistValues()
	{
		if ($this->picklistsValues) {
			return $this->picklistsValues;
		}
		$picklists = [
			'Project' => [],
			'ProjectMilestone' => [],
			'ProjectTask' => []
		];
		foreach (App\Fields\Picklist::getModulesByName('Project') as $name) {
			$picklists['Project'][$name] = [];
			$values = array_column(array_values(App\Fields\Picklist::getValues($name)), 'picklistValue');
			foreach ($values as $index => $value) {
				$picklists['Project'][$name][] = ['value' => $value, 'label' => App\Language::translate($value, 'Project')];
			}
		}
		foreach (App\Fields\Picklist::getModulesByName('ProjectMilestone') as $name) {
			$picklists['ProjectMilestone'][$name] = [];
			$values = array_column(array_values(App\Fields\Picklist::getValues($name)), 'picklistValue');
			foreach ($values as $value) {
				$picklists['ProjectMilestone'][$name][] = ['value' => $value, 'label' => App\Language::translate($value, 'ProjectMilestone')];
			}
		}
		foreach (App\Fields\Picklist::getModulesByName('ProjectTask') as $name) {
			$picklists['ProjectTask'][$name] = [];
			$values = array_column(array_values(App\Fields\Picklist::getValues($name)), 'picklistValue');
			foreach ($values as $value) {
				$picklists['ProjectTask'][$name][] = ['value' => $value, 'label' => App\Language::translate($value, 'ProjectTask')];
			}
		}
		$this->picklistsValues = $picklists;
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
		if (isset($this->tasksById[$id])) {
			return $this->tasksById[$id];
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($id, 'Project');
		$project = [
			'id' => $id,
			'parent' => $recordModel->get('parentid'), // we must collet parent,
			'name' => \App\Purifier::encodeHtml($recordModel->get('projectname')),
			'text' => \App\Purifier::encodeHtml($recordModel->get('projectname')),
			'priority' => $recordModel->get('projectpriority'),
			'priority_label' => \App\Language::translate($recordModel->get('projectpriority'), 'Project'),
			'status' => 'STATUS_ACTIVE',
			'type' => 'project',
			'module' => 'Project',
			'open' => true,
			'canWrite' => false,
			'canDelete' => false,
			'cantWriteOnParent' => false,
			'canAdd' => false,
			'description' => \App\Purifier::encodeHtml($recordModel->get('description')),
			'no' => $recordModel->get('project_no'),
			'normalized_status' => $recordModel->get('projectstatus'),
			'status_label' => App\Language::translate($recordModel->get('projectstatus'), 'Project'),
			'assigned_user_id' => $recordModel->get('assigned_user_id'),
			'assigned_user_name' => \App\Fields\Owner::getUserLabel($recordModel->get('assigned_user_id')),
			'color' => $recordModel->get('projectstatus') ? $this->statusColors['Project']['projectstatus'][$recordModel->get('projectstatus')] : \App\Colors::getRandomColor('projectstatus_' . $id),
		];
		if (!empty($recordModel->get('startdate'))) {
			$project['start_date'] = $recordModel->get('startdate');
			$project['start'] = strtotime($project['start_date']) * 1000;
		}
		$project['end_date'] = $recordModel->get('actualenddate');
		if (empty($project['end_date']) && !empty($recordModel->get('targetenddate'))) {
			$project['end_date'] = $recordModel->get('targetenddate');
			$project['end'] = strtotime($project['end_date']) * 1000;
		}
		$this->tasksById[$id] = $project;
		unset($recordModel);
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
		$queryGenerator->setFields(['id', 'parentid']);
		$queryGenerator->addNativeCondition(['parentid' => (int) $id]);
		$childrenRows = $queryGenerator->createQuery()->createCommand()->queryAll();
		$childrenIds = array_column($childrenRows, 'id');
		$children = [];
		foreach ($childrenIds as $childrenId) {
			$child = $this->getProject($childrenId);
			$children[] = $child;
			$childChildren = $this->getProjectChildren($childrenId);
			$children = array_merge($children, $childChildren);
		}
		unset($queryGenerator, $childrenRows, $childrenIds);
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
		$projects = [$this->getProject($id)];
		return array_merge($projects, $this->getProjectChildren($id));
	}

	/**
	 * Get all projects from the system.
	 *
	 * @return array projects,milestones,tasks
	 */
	public function getAllData($viewName = null)
	{
		$this->getStatusColors();
		$queryGenerator = new App\QueryGenerator('Project');
		$queryGenerator->setFields(['id', 'parentid', 'no' => 'project_no']);
		$queryGenerator->addNativeCondition(['vtiger_project.parentid' => 0]);
		if ($viewName) {
			$query = $queryGenerator->getCustomViewQueryById($viewName);
		} else {
			$query = $queryGenerator->createQuery();
		}
		$projectIdsRows = $query->createCommand()->queryAll();
		$rootProjectIds = array_column($projectIdsRows, 'id');
		$projects = [];
		foreach ($rootProjectIds as $projectId) {
			$projects = array_merge($projects, $this->getProjects($projectId));
		}
		$projectIds = array_column($projects, 'id');
		$milestones = $this->getGanttMilestones($projectIds);
		$tasks = $this->getGanttTasks($projectIds);
		$this->tasks = array_merge($projects, $milestones, $tasks);
		$this->addRootNode();
		$this->normalizeParents();
		$this->collectChildrens();
		$this->calculateLevels();
		$this->calculateDates();
		$this->calculateDurations();
		$response = [
			'statusColors' => $this->statusColors,
			'canWrite' => false,
			'canDelete' => false,
			'cantWriteOnParent' => false,
			'canAdd' => false,
			'picklists' => $this->getPicklistValues(),
			'statuses' => $this->getStatuses(),
		];
		if (!empty($this->tree) && !empty($this->tree['children'])) {
			$response['tasks'] = $this->cleanup($this->flattenRecordTasks($this->tree['children']));
		}
		unset($projectIds, $milestones, $tasks, $projects, $queryGenerator, $rootProjectIds, $projectIdsRows);
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
		$this->getStatusColors();
		$projects = $this->getProjects($id);
		$projectIds = array_column($projects, 'id');
		$milestones = $this->getGanttMilestones($projectIds);
		$tasks = $this->getGanttTasks($projectIds);
		$this->tasks = array_merge($projects, $milestones, $tasks);
		$this->addRootNode();
		$this->normalizeParents();
		$this->collectChildrens();
		$this->calculateLevels();
		$this->calculateDates();
		$this->calculateDurations();
		$response = [
			'statusColors' => $this->statusColors,
			'canWrite' => false,
			'canDelete' => false,
			'cantWriteOnParent' => false,
			'canAdd' => false,
			'picklists' => $this->getPicklistValues(),
			'statuses' => $this->getStatuses(),
		];
		if (!empty($this->tree) && !empty($this->tree['children'])) {
			$response['tasks'] = $this->cleanup($this->flattenRecordTasks($this->tree['children']));
		}
		unset($projects, $projectIds, $milestones, $tasks);
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
			$milestone = [
				'id' => $row['id'],
				'name' => \App\Purifier::encodeHtml($row['projectmilestonename']),
				'text' => \App\Purifier::encodeHtml($row['projectmilestonename']),
				'parent' => $row['parentid'] ? $row['parentid'] : $row['projectid'],
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
			if ($row['projectmilestonedate']) {
				$endDate = strtotime($row['projectmilestonedate']);
				$milestone['end'] = $endDate * 1000;
				$milestone['end_date'] = date('Y-m-d', $endDate);
			}
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
		$tasks = [];
		while ($row = $dataReader->read()) {
			$task = [
				'id' => $row['id'],
				'name' => \App\Purifier::encodeHtml($row['projecttaskname']),
				'text' => \App\Purifier::encodeHtml($row['projecttaskname']),
				'parent' => $row['parentid'] ? $row['parentid'] : null,
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
				'start_date' => date('d-m-Y', strtotime($row['startdate'])),
				'start' => strtotime($row['startdate']) * 1000,
				'assigned_user_id' => $row['assigned_user_id'],
				'assigned_user_name' => \App\Fields\Owner::getUserLabel($row['assigned_user_id']),
				'open' => true,
				'type' => 'task',
				'module' => 'ProjectTask',
				'status' => 'STATUS_ACTIVE',
			];
			if (empty($task['parent'])) {
				$task['parent'] = $row['projectmilestoneid'] ? $row['projectmilestoneid'] : $row['projectid'];
			}
			$endDate = strtotime(date('Y-m-d', strtotime($row['targetenddate'])) . ' +1 days');
			$task['end_date'] = date('d-m-Y', $endDate);
			$task['end'] = $endDate * 1000;
			$task['duration'] = $this->calculateDuration($task['start_date'], $task['end_date']);
			$taskTime += $row['estimated_work_time'];
			$tasks[] = $task;
		}
		$dataReader->close();
		unset($dataReader, $queryGenerator, $taskTime, $endDate);
		return $tasks;
	}

	/**
	 * Get statuses.
	 *
	 * @return array
	 */
	public function getStatuses()
	{
		if (!empty($this->statuses)) {
			return $this->statuses;
		}
		$data = [];
		$closingStatuses = Settings_RealizationProcesses_Module_Model::getStatusNotModify();
		$allStatuses = $this->getPicklistValues();
		if (!empty($allStatuses['Project']['projectstatus'])) {
			foreach ($allStatuses['Project']['projectstatus'] as $status) {
				if (!empty($closingStatuses['Project']['status'])) {
					$status['closing'] = in_array($status['value'], $closingStatuses['Project']['status']);
				}
				$data['Project'][] = $status;
			}
		}
		if (!empty($allStatuses['ProjectMilestone']['projectmilestone_status'])) {
			foreach ($allStatuses['ProjectMilestone']['projectmilestone_status'] as $status) {
				if (!empty($closingStatuses['ProjectMilestone']['status'])) {
					$status['closing'] = in_array($status['value'], $closingStatuses['ProjectMilestone']['status']);
				}
				$data['ProjectMilestone'][] = $status;
			}
		}
		if (!empty($allStatuses['ProjectTask']['projecttaskstatus'])) {
			foreach ($allStatuses['ProjectTask']['projecttaskstatus'] as $status) {
				if (!empty($closingStatuses['ProjectTask']['status'])) {
					$status['closing'] = in_array($status['value'], $closingStatuses['ProjectTask']['status']);
				}
				$data['ProjectTask'][] = $status;
			}
		}
		unset($closingStatuses, $allStatuses);
		return $data;
	}
}
