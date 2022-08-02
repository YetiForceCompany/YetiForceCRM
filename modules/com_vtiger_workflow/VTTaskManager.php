<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */
Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTTask.php');
Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTTaskType.php');

/**
 * Functionality to save and retrieve Tasks from the database.
 */
class VTTaskManager
{
	/**
	 * Save the task into the database.
	 *
	 * When a new task is saved for the first time a field is added to it called
	 * id that stores the task id used in the database.
	 *
	 * @param VTTask $task The task instance to save
	 *
	 * @return The id of the task
	 */
	public function saveTask(VTTask $task)
	{
		$db = App\Db::getInstance();
		if (!empty($task->id) && is_numeric($task->id)) {
			//How do I check whether a member exists in php?
			$taskId = $task->id;
			if (isset($task->email) && !\is_array($task->email)) {
				$task->email = [$task->email];
			}
			$db->createCommand()->update('com_vtiger_workflowtasks', ['summary' => $task->summary, 'task' => serialize($task)], ['task_id' => $taskId])->execute();

			return $taskId;
		}
		$db->createCommand()->insert('com_vtiger_workflowtasks', [
			'workflow_id' => $task->workflowId,
			'summary' => $task->summary,
			'task' => serialize($task),
			'sequence' => $task->sequence,
		])->execute();

		return $db->getLastInsertID();
	}

	/**
	 * Delete task by id.
	 *
	 * @param int $taskId
	 */
	public function deleteTask($taskId)
	{
		App\Db::getInstance()->createCommand()->delete('com_vtiger_workflowtasks', ['task_id' => $taskId])->execute();
	}

	/**
	 * Create a new class instance.
	 *
	 * @param string $taskType
	 * @param int    $workflowId
	 *
	 * @return VTTask
	 */
	public function createTask($taskType, $workflowId)
	{
		$taskTypeInstance = VTTaskType::getInstanceFromTaskType($taskType);
		$taskClass = $taskTypeInstance->get('classname');
		require_once $taskTypeInstance->get('classpath');
		$task = new $taskClass();
		$task->workflowId = $workflowId;
		$task->summary = '';
		$task->active = true;

		return $task;
	}

	/**
	 * Retrieve a task from the database.
	 *
	 * @param $taskId The id of the task to retrieve
	 *
	 * @return VTTask The retrieved task
	 */
	public function retrieveTask($taskId)
	{
		$row = (new \App\Db\Query())->select(['task_id', 'workflow_id', 'task'])->from('com_vtiger_workflowtasks')->where(['task_id' => $taskId])->one();
		$task = $this->unserializeTask($row['task']);
		$task->workflowId = $row['workflow_id'];
		$task->id = $row['task_id'];

		return $task;
	}

	/**
	 * Return tasks for workflow.
	 *
	 * @param int  $workflowId
	 * @param bool $active
	 *
	 * @return array
	 */
	public function getTasksForWorkflow($workflowId, $active = true)
	{
		$cacheName = "{$workflowId}:{$active}";
		if (\App\Cache::staticHas('getTasksForWorkflow', $cacheName)) {
			return \App\Cache::staticGet('getTasksForWorkflow', $cacheName);
		}
		$dataReader = (new \App\Db\Query())->select(['task_id', 'workflow_id', 'task'])->from('com_vtiger_workflowtasks')->where(['workflow_id' => $workflowId])->orderBy(['sequence' => SORT_ASC])->createCommand()->query();
		$tasks = [];
		while ($row = $dataReader->read()) {
			$taskType = self::taskName($row['task']);
			if (!empty($taskType)) {
				require_once "tasks/$taskType.php";
			}
			$task = unserialize($row['task']);
			if ($active && !$task->active) {
				continue;
			}
			$task->workflowId = $row['workflow_id'];
			$task->id = $row['task_id'];
			$tasks[] = $task;
		}
		\App\Cache::staticSave('getTasksForWorkflow', $cacheName, $tasks);
		return $tasks;
	}

	/**
	 * Unserialize task string.
	 *
	 * @param string $str
	 *
	 * @return array|bool
	 */
	public function unserializeTask($str)
	{
		$taskType = self::taskName($str);
		if (!empty($taskType)) {
			require_once "tasks/$taskType.php";
		}
		return unserialize($str);
	}

	/**
	 * Return task name.
	 *
	 * @param string $serializedTask
	 *
	 * @return string
	 */
	public static function taskName($serializedTask)
	{
		$matches = [];
		preg_match('/"([^"]+)"/', $serializedTask, $matches);

		return $matches[1];
	}
}
