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
	 * @param array $entityData
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

		$dataReader = (new \App\Db\Query())->select(['s_#__picklist_dependency_data.*'])->from('s_#__picklist_dependency')->innerJoin('s_#__picklist_dependency_data', 's_#__picklist_dependency_data.id = s_#__picklist_dependency.id')
			->where(['tabid' => $tabId])->andWhere(['and', ['like', 'conditions', $pickListFieldName], ['like', 'conditions', $oldValue]])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$change = false;
			$conditions = \App\Json::decode($row['conditions']);
			$rules = $conditions['rules'];

			foreach ($rules as &$data) {
				$values = explode('##', $data['value']);
				if ($data['fieldname'] === "{$pickListFieldName}:{$moduleName}" && \in_array($oldValue, $values)) {
					$key = array_search($oldValue, $values);
					$values[$key] = $newValue;
					$data['value'] = implode('##', $values);
					$change = true;
				}
			}
			if ($change) {
				$conditions['rules'] = $rules;
				$conditions = \App\Json::encode($conditions);
				$dbCommand->update('s_#__picklist_dependency_data', ['conditions' => $conditions], [
					'id' => $row['id'],
					'source_id' => $row['source_id']
				])->execute();
			}
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
			if (false !== $arrayKey) {
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
						if (false !== $arrayKey) {
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
			if (isset($unserializeTask->field_value_mapping)) {
				$fieldMapping = \App\Json::decode($unserializeTask->field_value_mapping);
				if (!empty($fieldMapping)) {
					foreach ($fieldMapping as $key => $condition) {
						if ($condition['fieldname'] == $pickListFieldName) {
							$value = $condition['value'];
							$explodedValueArray = explode(',', $value);
							$arrayKey = array_search($oldValue, $explodedValueArray);
							if (false !== $arrayKey) {
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
				if ('VTCreateEventTask' == $className) {
					if ('activitystatus' == $pickListFieldName) {
						$pickListFieldName = 'status';
					} elseif ('activitytype' == $pickListFieldName) {
						$pickListFieldName = 'eventType';
					}
				} elseif ('VTCreateTodoTask' == $className) {
					if ('activitystatus' == $pickListFieldName) {
						$pickListFieldName = 'status';
					} elseif ('taskpriority' == $pickListFieldName) {
						$pickListFieldName = 'priority';
					}
				}
				if (isset($unserializeTask->{$pickListFieldName})) {
					$value = $unserializeTask->{$pickListFieldName};
					$explodedValueArray = explode(',', $value);
					$arrayKey = array_search($oldValue, $explodedValueArray);
					if (false !== $arrayKey) {
						$explodedValueArray[$arrayKey] = $newValue;
					}
					$value = implode(',', $explodedValueArray);
					$unserializeTask->{$pickListFieldName} = $value;
					$serializeTask = serialize($unserializeTask);
					$dbCommand->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
				}
			}
		}
		$dataReader->close();
		if ('Calendar' === $moduleName && ('activitytype' === $pickListFieldName || 'activitystatus' === $pickListFieldName)) {
			$this->updateUserData($pickListFieldName, $oldValue, $newValue);
		}
	}

	/**
	 * Function to perform operation after picklist delete.
	 *
	 * @param array $entityData
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
				if (false !== $arrayKey) {
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
								if (false !== $arrayKey) {
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
				if (isset($unserializeTask->field_value_mapping)) {
					$fieldMapping = \App\Json::decode($unserializeTask->field_value_mapping);
					if (!empty($fieldMapping)) {
						foreach ($fieldMapping as $key => $condition) {
							if ($condition['fieldname'] == $pickListFieldName) {
								$value = $condition['value'];
								$explodedValueArray = explode(',', $value);
								foreach ($valueToDelete as $value) {
									$arrayKey = array_search($value, $explodedValueArray);
									if (false !== $arrayKey) {
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
					if ('VTCreateEventTask' == $className) {
						if ('activitystatus' == $pickListFieldName) {
							$pickListFieldName = 'status';
						} elseif ('activitytype' == $pickListFieldName) {
							$pickListFieldName = 'eventType';
						}
					} elseif ('VTCreateTodoTask' == $className) {
						if ('activitystatus' == $pickListFieldName) {
							$pickListFieldName = 'status';
						} elseif ('taskpriority' == $pickListFieldName) {
							$pickListFieldName = 'priority';
						}
					}
					if (isset($unserializeTask->{$pickListFieldName})) {
						$value = $unserializeTask->{$pickListFieldName};
						$explodedValueArray = explode(',', $value);
						foreach ($valueToDelete as $value) {
							$arrayKey = array_search($value, $explodedValueArray);
							if (false !== $arrayKey) {
								$explodedValueArray[$arrayKey] = $replaceValue;
							}
						}
						$value = implode(',', $explodedValueArray);
						$unserializeTask->{$pickListFieldName} = $value;
						$serializeTask = serialize($unserializeTask);
						$dbCommand->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
					}
				}
			}
			$dataReader->close();
		}
		if ('Calendar' === $moduleName && ('activitytype' === $pickListFieldName || 'activitystatus' === $pickListFieldName)) {
			$this->updateUserData($pickListFieldName, $valueToDelete, $replaceValue);
		}
	}

	/**
	 * Update users data.
	 *
	 * @param string          $pickListFieldName
	 * @param string|string[] $oldValue
	 * @param string          $newValue
	 *
	 * @return void
	 */
	public function updateUserData($pickListFieldName, $oldValue, $newValue)
	{
		if ('activitytype' === $pickListFieldName) {
			$defaultFieldName = 'defaultactivitytype';
		} else {
			$defaultFieldName = 'defaulteventstatus';
		}
		$dataReader = (new App\Db\Query())->select(['id'])
			->from('vtiger_users')
			->where([$defaultFieldName => $oldValue])
			->createCommand()->query();
		while ($userId = $dataReader->readColumn(0)) {
			$record = Vtiger_Record_Model::getInstanceById($userId, 'Users');
			$record->set($defaultFieldName, $newValue);
			$record->save();
		}
		$dataReader->close();
	}
}
