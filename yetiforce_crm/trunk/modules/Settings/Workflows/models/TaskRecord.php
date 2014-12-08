<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Workflow Task Record Model Class
 */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

class Settings_Workflows_TaskRecord_Model extends Settings_Vtiger_Record_Model {

	const TASK_STATUS_ACTIVE = 1;

	public function getId() {
		return $this->get('task_id');
	}

	public function getName() {
		return $this->get('summary');
	}

	public function isActive() {
		return $this->get('status') == self::TASK_STATUS_ACTIVE;
	}

	public function getTaskObject() {
		return $this->task_object;
	}

	public function setTaskObject($task) {
		$this->task_object = $task;
		return $this;
	}

	public function getTaskManager() {
		return $this->task_manager;
	}

	public function setTaskManager($tm) {
		$this->task_manager = $tm;
	}

	public function getEditViewUrl() {
		return 'index.php?module=Workflows&parent=Settings&view=EditTask&type='.$this->task_type->getName().'&task_id='.$this->getId().'&for_workflow='.$this->getWorkflow()->getId();
	}

	public function getDeleteActionUrl() {
		return 'index.php?module=Workflows&parent=Settings&action=TaskAjax&mode=Delete&task_id='.$this->getId();
	}

	public function getChangeStatusUrl() {
		return 'index.php?module=Workflows&parent=Settings&action=TaskAjax&mode=ChangeStatus&task_id='.$this->getId();
	}

	public function getWorkflow() {
		return $this->workflow;
	}

	public function setWorkflowFromInstance($workflowModel) {
		$this->workflow = $workflowModel;
		return $this;
	}

	public function getTaskType() {
		if(!$this->task_type) {
			$taskObject = $this->getTaskObject();
			$taskClass = get_class($taskObject);
			$this->task_type = Settings_Workflows_TaskType_Model::getInstanceFromClassName($taskClass);
		}
		return $this->task_type;
	}

	public static function getAllForWorkflow($workflowModel, $active=false) {
		$db = PearDatabase::getInstance();

		$tm = new VTTaskManager($db);
		$tasks = $tm->getTasksForWorkflow($workflowModel->getId());
		$taskModels = array();
		foreach($tasks as $task) {
			if(!$active || $task->active == self::TASK_STATUS_ACTIVE) {
				$taskModels[$task->id] = self::getInstanceFromTaskObject($task, $workflowModel, $tm);
			}
		}
		return $taskModels;
	}

	public static function getInstance($taskId, $workflowModel=null) {
		$db = PearDatabase::getInstance();
		$tm = new VTTaskManager($db);
		$task = $tm->retrieveTask($taskId);
		if($workflowModel == null) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($task->workflowId);
		}
		return self::getInstanceFromTaskObject($task, $workflowModel, $tm);
	}

	public static function getCleanInstance($workflowModel, $taskName) {
		$db = PearDatabase::getInstance();
		$tm = new VTTaskManager($db);
		$task = $tm->createTask($taskName, $workflowModel->getId());
		return self::getInstanceFromTaskObject($task, $workflowModel, $tm);
	}

	public static function getInstanceFromTaskObject($task, $workflowModel, $tm) {
		$taskId = $task->id;
		$summary = $task->summary;
		$status = $task->active;

		$taskModel = new self();
		$taskModel->setTaskManager($tm);
		return $taskModel->set('task_id', $taskId)->set('summary', $summary)->set('status', $status)
				->setTaskObject($task)->setWorkflowFromInstance($workflowModel);
	}

	/**
	 * Function deletes workflow task
	 */
	public function delete() {
		$this->task_manager->deleteTask($this->getId());
	}

	/**
	 * Function saves workflow task
	 */
	public function save() {
		$taskObject = $this->getTaskObject();
		$this->task_manager->saveTask($taskObject);
	}
}
