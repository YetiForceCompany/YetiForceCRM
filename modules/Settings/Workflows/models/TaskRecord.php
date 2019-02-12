<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'modules/com_vtiger_workflow/include.php';
require_once 'modules/com_vtiger_workflow/VTTaskManager.php';

// Workflow Task Record Model Class

class Settings_Workflows_TaskRecord_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Task status active.
	 *
	 * @var int
	 */
	const TASK_STATUS_ACTIVE = 1;

	/**
	 * Return task record id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('task_id');
	}

	/**
	 * Return task record name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('summary');
	}

	/**
	 * Check if task is active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return $this->get('status') == self::TASK_STATUS_ACTIVE;
	}

	/**
	 * Return task object.
	 *
	 * @return VTTask
	 */
	public function getTaskObject()
	{
		return $this->task_object;
	}

	/**
	 * Set task object.
	 *
	 * @param VTTask $task
	 *
	 * @return $this
	 */
	public function setTaskObject($task)
	{
		$this->task_object = $task;

		return $this;
	}

	/**
	 * Return task manager object.
	 *
	 * @return VTTaskManager
	 */
	public function getTaskManager()
	{
		return $this->task_manager;
	}

	/**
	 * Set task manager object.
	 *
	 * @param VTTaskManager $tm
	 */
	public function setTaskManager($tm)
	{
		$this->task_manager = $tm;
	}

	/**
	 * Return edit view url.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=EditTask&type=' . $this->task_type->getName() . '&task_id=' . $this->getId() . '&for_workflow=' . $this->getWorkflow()->getId();
	}

	/**
	 * Return delete action url.
	 *
	 * @return string
	 */
	public function getDeleteActionUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&action=TaskAjax&mode=delete&task_id=' . $this->getId();
	}

	/**
	 * return change status url.
	 *
	 * @return string
	 */
	public function getChangeStatusUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&action=TaskAjax&mode=changeStatus&task_id=' . $this->getId();
	}

	/**
	 * Return workflow object.
	 *
	 * @return Workflow
	 */
	public function getWorkflow()
	{
		return $this->workflow;
	}

	/**
	 * Set workflow from instance.
	 *
	 * @param object $workflowModel
	 *
	 * @return $this
	 */
	public function setWorkflowFromInstance($workflowModel)
	{
		$this->workflow = $workflowModel;

		return $this;
	}

	/**
	 * Return task type.
	 *
	 * @return Settings_Workflows_TaskType_Model
	 */
	public function getTaskType()
	{
		if (empty($this->task_type)) {
			$taskObject = $this->getTaskObject();
			if (!empty($taskObject)) {
				$taskClass = get_class($taskObject);
				$this->task_type = Settings_Workflows_TaskType_Model::getInstanceFromClassName($taskClass);
			}
		}
		return $this->task_type;
	}

	/**
	 * Return all tasks for workflow.
	 *
	 * @param Workflow $workflowModel
	 * @param bool     $active
	 *
	 * @return VTTask[]
	 */
	public static function getAllForWorkflow($workflowModel, $active = false)
	{
		$tm = new VTTaskManager();
		$tasks = $tm->getTasksForWorkflow($workflowModel->getId());
		$taskModels = [];
		foreach ($tasks as $task) {
			if (!$active || $task->active == self::TASK_STATUS_ACTIVE) {
				$taskModels[$task->id] = self::getInstanceFromTaskObject($task, $workflowModel, $tm);
			}
		}
		return $taskModels;
	}

	/**
	 * Return instance.
	 *
	 * @param int      $taskId
	 * @param Workflow $workflowModel
	 *
	 * @return VTTask
	 */
	public static function getInstance($taskId, $workflowModel = null)
	{
		$tm = new VTTaskManager();
		$task = $tm->retrieveTask($taskId);
		if ($workflowModel === null) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($task->workflowId);
		}
		return self::getInstanceFromTaskObject($task, $workflowModel, $tm);
	}

	/**
	 * Return clean instance.
	 *
	 * @param object $workflowModel
	 * @param string $taskName
	 *
	 * @return VTTask
	 */
	public static function getCleanInstance($workflowModel, $taskName)
	{
		$tm = new VTTaskManager();
		$task = $tm->createTask($taskName, $workflowModel->getId());

		return self::getInstanceFromTaskObject($task, $workflowModel, $tm);
	}

	/**
	 * @param VTTask        $task
	 * @param Workflow      $workflowModel
	 * @param VTTaskManager $tm
	 *
	 * @return VTTask
	 */
	public static function getInstanceFromTaskObject($task, $workflowModel, $tm)
	{
		if (isset($task->id)) {
			$taskId = $task->id;
		} else {
			$taskId = false;
		}

		$summary = $task->summary;
		$status = $task->active;

		$taskModel = new self();
		$taskModel->setTaskManager($tm);

		return $taskModel->set('task_id', $taskId)->set('summary', $summary)->set('status', $status)
			->setTaskObject($task)->setWorkflowFromInstance($workflowModel);
	}

	/**
	 * Function deletes workflow task.
	 */
	public function delete()
	{
		$this->task_manager->deleteTask($this->getId());
	}

	/**
	 * Function saves workflow task.
	 */
	public function save()
	{
		$taskObject = $this->getTaskObject();
		$this->task_manager->saveTask($taskObject);
	}
}
