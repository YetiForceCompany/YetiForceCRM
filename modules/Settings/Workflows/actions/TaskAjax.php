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

class Settings_Workflows_TaskAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('delete');
		$this->exposeMethod('changeStatus');
		$this->exposeMethod('changeStatusAllTasks');
		$this->exposeMethod('save');
	}

	public function delete(App\Request $request)
	{
		$record = $request->get('task_id');
		if (!empty($record)) {
			$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($record);
			$taskRecordModel->delete();
			$response = new Vtiger_Response();
			$response->setResult(['ok']);
			$response->emit();
		}
	}

	public function changeStatus(App\Request $request)
	{
		$record = $request->get('task_id');
		if (!empty($record)) {
			$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($record);
			$taskObject = $taskRecordModel->getTaskObject();
			if ('true' == $request->get('status')) {
				$taskObject->active = true;
			} else {
				$taskObject->active = false;
			}
			$taskRecordModel->save();
			$response = new Vtiger_Response();
			$response->setResult(['ok']);
			$response->emit();
		}
	}

	/**
	 * Change status for all workflow tasks.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function changeStatusAllTasks(App\Request $request)
	{
		$record = $request->getInteger('record');
		$status = $request->get('status');
		if (!empty($record)) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($record);
			$taskList = $workflowModel->getTasks(false);
			foreach ($taskList as $taskRecordModel) {
				$taskObject = $taskRecordModel->getTaskObject();
				if ('true' == $status) {
					$taskObject->active = true;
				} else {
					$taskObject->active = false;
				}
				$taskRecordModel->save();
			}
			$activeCount = 0;
			foreach ($taskList as $taskRecord) {
				if ($taskRecord->isActive() && $taskRecord->isEditable()) {
					++$activeCount;
				}
			}
			$response = new Vtiger_Response();
			$response->setResult(['success' => true, 'count' => $activeCount]);
			$response->emit();
		}
	}

	/**
	 * Save.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function save(App\Request $request)
	{
		$workflowId = $request->get('for_workflow');
		if (!empty($workflowId)) {
			$record = $request->get('task_id');
			if ($record) {
				$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($record);
				$taskObject = $taskRecordModel->getTaskObject();
			} else {
				$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
				$taskRecordModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, $request->get('taskType'));
				$taskObject = $taskRecordModel->getTaskObject();
				$taskObject->sequence = $taskRecordModel->getNextSequenceNumber($workflowId);
			}
			$taskObject->summary = htmlspecialchars($request->get('summary'));

			$active = $request->get('active');
			if ('true' == $active) {
				$taskObject->active = true;
			} elseif ('false' == $active) {
				$taskObject->active = false;
			}
			$checkSelectDate = $request->get('check_select_date');

			if (!empty($checkSelectDate)) {
				$trigger = [
					'days' => ('after' == $request->get('select_date_direction') ? 1 : -1) * (int) $request->get('select_date_days'),
					'field' => $request->get('select_date_field'),
				];
				$taskObject->trigger = $trigger;
			} else {
				$taskObject->trigger = null;
			}

			if (method_exists($taskObject, 'setDataFromRequest')) {
				$taskObject->setDataFromRequest($request);
			} else {
				$fieldNames = $taskObject->getFieldNames();
				$fieldNamesRequestMethods = method_exists($taskObject, 'getFieldsNamesRequestMethod') ? $taskObject->getFieldsNamesRequestMethod() : [];
				foreach ($fieldNames as $fieldName) {
					if ('field_value_mapping' == $fieldName || 'content' == $fieldName) {
						$values = \App\Json::decode($request->getRaw($fieldName));
						if (\is_array($values)) {
							foreach ($values as $index => $value) {
								$values[$index]['value'] = htmlspecialchars($value['value']);
							}

							$taskObject->{$fieldName} = \App\Json::encode($values);
						} else {
							$taskObject->{$fieldName} = $request->getRaw($fieldName);
						}
					} elseif (isset($fieldNamesRequestMethods[$fieldName])) {
						$taskObject->{$fieldName} = $request->{$fieldNamesRequestMethods[$fieldName]}($fieldName);
					} else {
						$taskObject->{$fieldName} = $request->get($fieldName);
					}
				}
			}

			$taskType = \get_class($taskObject);
			if ('VTCreateEntityTask' === $taskType && $taskObject->field_value_mapping) {
				$relationModuleModel = Vtiger_Module_Model::getInstance($taskObject->entity_type);
				$ownerFieldModels = $relationModuleModel->getFieldsByType('owner');

				$fieldMapping = \App\Json::decode($taskObject->field_value_mapping);
				foreach ($fieldMapping as $key => $mappingInfo) {
					if (\array_key_exists($mappingInfo['fieldname'], $ownerFieldModels)) {
						if ('assigned_user_id' == $mappingInfo['value']) {
							$fieldMapping[$key]['valuetype'] = 'fieldname';
						} elseif ('triggerUser' !== $mappingInfo['value']) {
							$userRecordModel = Users_Record_Model::getInstanceById($mappingInfo['value'], 'Users');
							$ownerName = $userRecordModel->get('user_name');

							if (!$ownerName) {
								$groupRecordModel = Settings_Groups_Record_Model::getInstance($mappingInfo['value']);
								$ownerName = $groupRecordModel->getName();
							}
							$fieldMapping[$key]['value'] = $ownerName;
						}
					}
				}
				$taskObject->field_value_mapping = \App\Json::encode($fieldMapping);
			}
			if ('SumFieldFromDependent' === $taskType && $taskObject->conditions) {
				$taskObject->conditions = \App\Condition::getConditionsFromRequest($taskObject->conditions);
			}
			$taskRecordModel->save();
			$response = new Vtiger_Response();
			$response->setResult(['for_workflow' => $workflowId]);
			$response->emit();
		}
	}
}
