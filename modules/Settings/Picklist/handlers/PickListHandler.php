<?php

class Settings_Picklist_PickListHandler_Handler
{
	/**
	 * PicklistAfterRename handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function picklistAfterRename(App\EventHandler $eventHandler)
	{
		$this->operationsAfterPicklistRename($eventHandler->getParams());
	}

	/**
	 * PicklistAfterDelete handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function picklistAfterDelete(App\EventHandler $eventHandler)
	{
		$this->operationsAfterPicklistDelete($eventHandler->getParams());
	}

	/**
	 * Function to perform operation after picklist rename.
	 *
	 * @param type $entityData
	 */
	public function operationsAfterPicklistRename($entityData)
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		$pickListFieldName = $entityData['fieldname'];
		$oldValue = $entityData['oldvalue'];
		$newValue = $entityData['newvalue'];
		$moduleName = $entityData['module'];
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$tabId = $moduleModel->getId();
		//update picklist dependency values
		$dataReader = (new \App\Db\Query())->select(['id', 'targetvalues'])->from('vtiger_picklist_dependency')
			->where(['targetfield' => $pickListFieldName, 'tabid' => $tabId])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$value = App\Purifier::decodeHtml($row['targetvalues']);
			$explodedValueArray = \App\Json::decode($value);
			$arrayKey = array_search($oldValue, $explodedValueArray);
			if ($arrayKey !== false) {
				$explodedValueArray[$arrayKey] = $newValue;
			}
			$value = \App\Json::encode($explodedValueArray);
			$dbCommand->update('vtiger_picklist_dependency', ['targetvalues' => $value], [
				'id' => $row['id'],
				'tabid' => $tabId,
			])->execute();
		}
		$dataReader->close();
		//update advancefilter values
		$dataReader = (new \App\Db\Query())->select(['id', 'value'])
			->from('u_#__cv_condition')
			->where(['field_name' => $pickListFieldName, 'module_name' => $moduleName])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$value = $row['value'];
			$explodedValueArray = explode(',', $value);
			$arrayKey = array_search($oldValue, $explodedValueArray);
			if ($arrayKey !== false) {
				$explodedValueArray[$arrayKey] = $newValue;
			}
			$value = implode(',', $explodedValueArray);
			$dbCommand->update('u_#__cv_condition', ['value' => $value], ['id' => $row['id']])->execute();
		}
		$dataReader->close();
		//update Workflows values
		$dataReader = (new \App\Db\Query())->select(['workflow_id', 'test'])->from('com_vtiger_workflows')->where([
			'and',
			['module_name' => $moduleName],
			['<>', 'test', ''],
			['not', ['test' => null]],
			['<>', 'test', 'null'],
			['like', 'test', $oldValue],
		])->createCommand()->query();

		while ($row = $dataReader->read()) {
			$condition = App\Purifier::decodeHtml($row['test']);
			$decodedArrayConditions = \App\Json::decode($condition);
			if (!empty($decodedArrayConditions)) {
				foreach ($decodedArrayConditions as $key => $condition) {
					if ($condition['fieldname'] == $pickListFieldName) {
						$value = $condition['value'];
						$explodedValueArray = explode(',', $value);
						$arrayKey = array_search($oldValue, $explodedValueArray);
						if ($arrayKey !== false) {
							$explodedValueArray[$arrayKey] = $newValue;
						}
						$value = implode(',', $explodedValueArray);
						$condition['value'] = $value;
					}
					$decodedArrayConditions[$key] = $condition;
				}
				$condtion = \App\Json::encode($decodedArrayConditions);
				$dbCommand->update('com_vtiger_workflows', ['test' => $condtion], ['workflow_id' => $row['workflow_id']])
					->execute();
			}
		}
		$dataReader->close();
		//update workflow task
		$dataReader = (new \App\Db\Query())->select(['task', 'task_id', 'workflow_id'])
			->from('com_vtiger_workflowtasks')
			->where(['like', 'task', $oldValue])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$task = $row['task'];
			$taskComponents = explode(':', $task);
			$classNameWithDoubleQuotes = $taskComponents[2];
			$className = str_replace('"', '', $classNameWithDoubleQuotes);
			require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
			require_once 'modules/com_vtiger_workflow/tasks/' . $className . '.php';
			$unserializeTask = unserialize($task);
			if (array_key_exists('field_value_mapping', $unserializeTask)) {
				$fieldMapping = \App\Json::decode($unserializeTask->field_value_mapping);
				if (!empty($fieldMapping)) {
					foreach ($fieldMapping as $key => $condition) {
						if ($condition['fieldname'] == $pickListFieldName) {
							$value = $condition['value'];
							$explodedValueArray = explode(',', $value);
							$arrayKey = array_search($oldValue, $explodedValueArray);
							if ($arrayKey !== false) {
								$explodedValueArray[$arrayKey] = $newValue;
							}
							$value = implode(',', $explodedValueArray);
							$condition['value'] = $value;
						}
						$fieldMapping[$key] = $condition;
					}
					$updatedTask = \App\Json::encode($fieldMapping);
					$unserializeTask->field_value_mapping = $updatedTask;
					$serializeTask = serialize($unserializeTask);
					$dbCommand->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
				}
			} else {
				if ($className == 'VTCreateEventTask') {
					if ($pickListFieldName == 'activitystatus') {
						$pickListFieldName = 'status';
					} elseif ($pickListFieldName == 'activitytype') {
						$pickListFieldName = 'eventType';
					}
				} elseif ($className == 'VTCreateTodoTask') {
					if ($pickListFieldName == 'activitystatus') {
						$pickListFieldName = 'status';
					} elseif ($pickListFieldName == 'taskpriority') {
						$pickListFieldName = 'priority';
					}
				}
				if (array_key_exists($pickListFieldName, $unserializeTask)) {
					$value = $unserializeTask->$pickListFieldName;
					$explodedValueArray = explode(',', $value);
					$arrayKey = array_search($oldValue, $explodedValueArray);
					if ($arrayKey !== false) {
						$explodedValueArray[$arrayKey] = $newValue;
					}
					$value = implode(',', $explodedValueArray);
					$unserializeTask->$pickListFieldName = $value;
					$serializeTask = serialize($unserializeTask);
					$dbCommand->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
				}
			}
		}
		$dataReader->close();
	}

	/**
	 * Function to perform operation after picklist delete.
	 *
	 * @param type $entityData
	 */
	public function operationsAfterPicklistDelete($entityData)
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		$pickListFieldName = $entityData['fieldname'];
		$valueToDelete = $entityData['valuetodelete'];
		$replaceValue = $entityData['replacevalue'];
		$moduleName = $entityData['module'];
		//update advancefilter values
		$dataReader = (new \App\Db\Query())->select(['id', 'value'])->from('u_#__cv_condition')
			->where(['field_name' => $pickListFieldName, 'module_name' => $moduleName])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$value = $row['value'];
			$explodedValueArray = explode(',', $value);
			foreach ($valueToDelete as $value) {
				$arrayKey = array_search($value, $explodedValueArray);
				if ($arrayKey !== false) {
					$explodedValueArray[$arrayKey] = $replaceValue;
				}
			}
			$value = implode(',', $explodedValueArray);
			$dbCommand->update('u_#__cv_condition', ['value' => $value], ['id' => $row['id']])->execute();
		}
		$dataReader->close();
		foreach ($valueToDelete as $value) {
			//update Workflows values
			$dataReader = (new \App\Db\Query())->select(['workflow_id', 'test'])->from('com_vtiger_workflows')->where([
				'and',
				['module_name' => $moduleName],
				['<>', 'test', ''],
				['not', ['test' => null]],
				['<>', 'test', 'null'],
				['like', 'test', $value],
			])->createCommand()->query();
			while ($row = $dataReader->read()) {
				$condition = App\Purifier::decodeHtml($row['test']);
				$decodedArrayConditions = \App\Json::decode($condition);
				if (!empty($decodedArrayConditions)) {
					foreach ($decodedArrayConditions as $key => $condition) {
						if ($condition['fieldname'] == $pickListFieldName) {
							$value = $condition['value'];
							$explodedValueArray = explode(',', $value);
							foreach ($valueToDelete as $value) {
								$arrayKey = array_search($value, $explodedValueArray);
								if ($arrayKey !== false) {
									$explodedValueArray[$arrayKey] = $replaceValue;
								}
							}
							$value = implode(',', $explodedValueArray);
							$condition['value'] = $value;
						}
						$decodedArrayConditions[$key] = $condition;
					}
					$condtion = \App\Json::encode($decodedArrayConditions);
					$dbCommand->update('com_vtiger_workflows', ['test' => $condtion], ['workflow_id' => $row['workflow_id']])->execute();
				}
			}
			$dataReader->close();
		}
		foreach ($valueToDelete as $value) {
			//update workflow task
			$dataReader = (new \App\Db\Query())->select(['task', 'workflow_id', 'task_id'])->from('com_vtiger_workflowtasks')->where(['like', 'task', $value])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$task = $row['task'];
				$taskComponents = explode(':', $task);
				$classNameWithDoubleQuotes = $taskComponents[2];
				$className = str_replace('"', '', $classNameWithDoubleQuotes);
				require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
				require_once 'modules/com_vtiger_workflow/tasks/' . $className . '.php';
				$unserializeTask = unserialize($task);
				if (array_key_exists('field_value_mapping', $unserializeTask)) {
					$fieldMapping = \App\Json::decode($unserializeTask->field_value_mapping);
					if (!empty($fieldMapping)) {
						foreach ($fieldMapping as $key => $condition) {
							if ($condition['fieldname'] == $pickListFieldName) {
								$value = $condition['value'];
								$explodedValueArray = explode(',', $value);
								foreach ($valueToDelete as $value) {
									$arrayKey = array_search($value, $explodedValueArray);
									if ($arrayKey !== false) {
										$explodedValueArray[$arrayKey] = $replaceValue;
									}
								}
								$value = implode(',', $explodedValueArray);
								$condition['value'] = $value;
							}
							$fieldMapping[$key] = $condition;
						}
						$updatedTask = \App\Json::encode($fieldMapping);
						$unserializeTask->field_value_mapping = $updatedTask;
						$serializeTask = serialize($unserializeTask);
						$dbCommand->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
					}
				} else {
					if ($className == 'VTCreateEventTask') {
						if ($pickListFieldName == 'activitystatus') {
							$pickListFieldName = 'status';
						} elseif ($pickListFieldName == 'activitytype') {
							$pickListFieldName = 'eventType';
						}
					} elseif ($className == 'VTCreateTodoTask') {
						if ($pickListFieldName == 'activitystatus') {
							$pickListFieldName = 'status';
						} elseif ($pickListFieldName == 'taskpriority') {
							$pickListFieldName = 'priority';
						}
					}
					if (array_key_exists($pickListFieldName, $unserializeTask)) {
						$value = $unserializeTask->$pickListFieldName;
						$explodedValueArray = explode(',', $value);
						foreach ($valueToDelete as $value) {
							$arrayKey = array_search($value, $explodedValueArray);
							if ($arrayKey !== false) {
								$explodedValueArray[$arrayKey] = $replaceValue;
							}
						}
						$value = implode(',', $explodedValueArray);
						$unserializeTask->$pickListFieldName = $value;
						$serializeTask = serialize($unserializeTask);
						$dbCommand->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
					}
				}
			}
			$dataReader->close();
		}
	}
}
