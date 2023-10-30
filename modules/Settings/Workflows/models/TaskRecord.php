<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
		return self::TASK_STATUS_ACTIVE == $this->get('status');
	}

	/**
	 * Check if record is editable.
	 *
	 * @return bool
	 */
	public function isEditable(): bool
	{
		$recordEventMode = $this->getTaskObject()->recordEventState ?? VTTask::RECORD_EVENT_ACTIVE;
		$workflowModeIterationOff = $this->getWorkflow()->getParams('iterationOff');
		return $workflowModeIterationOff && (VTTask::RECORD_EVENT_DOUBLE_MODE === $recordEventMode || VTTask::RECORD_EVENT_INACTIVE === $recordEventMode)
		|| (!$workflowModeIterationOff && (VTTask::RECORD_EVENT_DOUBLE_MODE === $recordEventMode || VTTask::RECORD_EVENT_ACTIVE === $recordEventMode));
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
		$url = 'index.php?module=Workflows&parent=Settings&view=EditTask&type=' . $this->task_type->getName() . '&for_workflow=' . $this->getWorkflow()->getId();
		if ($this->getId()) {
			$url .= '&task_id=' . $this->getId();
		}
		return $url;
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
				$taskClass = \get_class($taskObject);
				$this->task_type = Settings_Workflows_TaskType_Model::getInstanceFromClassName($taskClass);
			}
		}
		return $this->task_type;
	}

	/**
	 * Set task type model.
	 *
	 * @param Settings_Workflows_TaskType_Model $taskType
	 *
	 * @return self
	 */
	public function setTaskType(Settings_Workflows_TaskType_Model $taskType): self
	{
		$this->task_type = $taskType;
		return $this;
	}

	/**
	 * Return all tasks for workflow.
	 *
	 * @param Settings_Workflows_Record_Model $workflowModel
	 * @param bool                            $active
	 *
	 * @return VTTask[]
	 */
	public static function getAllForWorkflow(Settings_Workflows_Record_Model $workflowModel, $active = false)
	{
		$tm = new VTTaskManager();
		$tasks = $tm->getTasksForWorkflow($workflowModel->getId(), $active);
		$taskModels = [];
		foreach ($tasks as $task) {
			if (!$active || self::TASK_STATUS_ACTIVE == $task->active) {
				$taskRecord = self::getInstanceFromTaskObject($task, $workflowModel, $tm);
				if (!$active || $taskRecord->isEditable()) {
					$taskModels[$task->id] = $taskRecord;
				}
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
		if (null === $workflowModel) {
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
		$this->set('summary', $taskObject->summary)->set('status', $taskObject->active);
	}

	/**
	 * Get next task action sequence number.
	 *
	 * @param int $workflowId
	 *
	 * @return int
	 */
	public function getNextSequenceNumber(int $workflowId): int
	{
		return (new \App\Db\Query())
			->from('com_vtiger_workflowtasks')
			->where(['workflow_id' => $workflowId])
			->max('sequence') + 1;
	}
}
