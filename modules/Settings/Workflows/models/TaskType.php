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

// Workflow Task Type Model Class
require_once 'modules/com_vtiger_workflow/VTTaskManager.php';

/**
 * Settings Workflows TaskType Model.
 */
class Settings_Workflows_TaskType_Model extends \App\Base
{
	/**
	 * Get record id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Get task name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('tasktypename');
	}

	/**
	 * Get task label.
	 *
	 * @return string
	 */
	public function getLabel()
	{
		return $this->get('label');
	}

	/**
	 * Get template path.
	 *
	 * @return string
	 */
	public function getTemplatePath()
	{
		return \App\Layout::getTemplatePath('Tasks/' . $this->getName() . '.tpl', 'Settings:Workflows');
	}

	/**
	 * Get edit view url.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return '?module=Workflows&parent=Settings&view=EditTask&type=' . $this->getName();
	}

	/**
	 * Create instance from class name.
	 *
	 * @param VTTask $taskClass
	 *
	 * @return $this
	 */
	public static function getInstanceFromClassName($taskClass)
	{
		$row = (new \App\Db\Query())->from('com_vtiger_workflow_tasktypes')->where(['classname' => $taskClass])->one();
		$taskTypeObject = VTTaskType::getInstance($row);

		return self::getInstanceFromTaskTypeObject($taskTypeObject);
	}

	/**
	 * Get all tasks for module.
	 *
	 * @param object $moduleModel
	 *
	 * @return array
	 */
	public static function getAllForModule(vtlib\ModuleBasic $moduleModel)
	{
		$taskTypes = VTTaskType::getAll($moduleModel->getName());
		$taskTypeModels = [];
		foreach ($taskTypes as $taskTypeObject) {
			$taskTypeModels[] = self::getInstanceFromTaskTypeObject($taskTypeObject);
		}
		return $taskTypeModels;
	}

	/**
	 * Get task type instance.
	 *
	 * @param string $taskType
	 *
	 * @return object
	 */
	public static function getInstance($taskType)
	{
		$taskTypeObject = VTTaskType::getInstanceFromTaskType($taskType);

		return self::getInstanceFromTaskTypeObject($taskTypeObject);
	}

	/**
	 * Get instance from task type object.
	 *
	 * @param object $taskTypeObject
	 *
	 * @return \self
	 */
	public static function getInstanceFromTaskTypeObject($taskTypeObject)
	{
		return new self($taskTypeObject->data);
	}

	/**
	 * Get task base module object.
	 *
	 * @return object
	 */
	public function getTaskBaseModule()
	{
		$taskTypeName = $this->get('tasktypename');
		switch ($taskTypeName) {
			case 'VTCreateTodoTask':
			case 'VTCreateEventTask':
				return Vtiger_Module_Model::getInstance('Calendar');
			default:
				break;
		}
	}
}
