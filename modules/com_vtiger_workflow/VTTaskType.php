<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/**
 * VTTaskType class.
 */
class VTTaskType
{
	/**
	 * Data array.
	 *
	 * @var array
	 */
	public $data;

	/**
	 * Return value for $data key.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->data[$key];
	}

	/**
	 * Set value for $data key.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;

		return $this;
	}

	/**
	 * Replace $data array.
	 *
	 * @param array $valueMap
	 *
	 * @return $this
	 */
	public function setData($valueMap)
	{
		$this->data = $valueMap;

		return $this;
	}

	/**
	 * Return instance of self with new $data array.
	 *
	 * @param array $values
	 *
	 * @return VTTaskType
	 */
	public static function getInstance($values)
	{
		$instance = new self();

		return $instance->setData($values);
	}

	/**
	 * Registers new task type in database.
	 *
	 * @param array $taskType
	 */
	public static function registerTaskType($taskType)
	{
		$db = \App\Db::getInstance();
		$modules = \App\Json::encode($taskType['modules']);
		$db->createCommand()->insert('com_vtiger_workflow_tasktypes', [
			'tasktypename' => $taskType['name'],
			'label' => $taskType['label'],
			'classname' => $taskType['classname'],
			'classpath' => $taskType['classpath'],
			'templatepath' => $taskType['templatepath'],
			'modules' => $modules,
			'sourcemodule' => $taskType['sourcemodule'],
		])->execute();
	}

	/**
	 * return all task types.
	 *
	 * @param string $moduleName
	 *
	 * @return VTTaskType[]
	 */
	public static function getAll($moduleName = '')
	{
		$query = (new App\Db\Query())->from('com_vtiger_workflow_tasktypes');
		$dataReader = $query->createCommand()->query();
		while ($rawData = $dataReader->read()) {
			$taskName = $rawData['tasktypename'];
			$moduleslist = $rawData['modules'];
			$sourceModule = $rawData['sourcemodule'];
			$modules = \App\Json::decode($moduleslist);
			$includeModules = $modules['include'];
			$excludeModules = $modules['exclude'];

			if (!empty($sourceModule) && (null === \App\Module::getModuleId($sourceModule) || !\App\Module::isModuleActive($sourceModule))) {
				continue;
			}

			if (empty($includeModules) && empty($excludeModules)) {
				$taskTypeInstances[$taskName] = self::getInstance($rawData);
				continue;
			}
			if (!empty($includeModules)) {
				if (\in_array($moduleName, $includeModules)) {
					$taskTypeInstances[$taskName] = self::getInstance($rawData);
				}
				continue;
			}
			if (!empty($excludeModules)) {
				if (!(\in_array($moduleName, $excludeModules))) {
					$taskTypeInstances[$taskName] = self::getInstance($rawData);
				}
				continue;
			}
		}
		return $taskTypeInstances;
	}

	/**
	 * Return instance from task type name.
	 *
	 * @param string $taskType
	 *
	 * @return VTTaskType
	 */
	public static function getInstanceFromTaskType($taskType)
	{
		$row = (new App\Db\Query())->from('com_vtiger_workflow_tasktypes')->where(['tasktypename' => $taskType])->one();
		$taskTypes['name'] = $row['tasktypename'];
		$taskTypes['label'] = $row['label'];
		$taskTypes['classname'] = $row['classname'];
		$taskTypes['classpath'] = $row['classpath'];
		$taskTypes['templatepath'] = $row['templatepath'];
		$taskTypes['sourcemodule'] = $row['sourcemodule'];
		return self::getInstance($taskTypes);
	}
}
