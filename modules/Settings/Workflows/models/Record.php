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
// Workflow Record Model Class
require_once 'modules/com_vtiger_workflow/include.php';
require_once 'modules/com_vtiger_workflow/expression_engine/VTExpressionsManager.php';

/**
 * Class settings workflows record model.
 */
class Settings_Workflows_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Get record id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('workflow_id');
	}

	/**
	 * Get record name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('summary');
	}

	/**
	 * Get edit view url.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Get tasks list url.
	 *
	 * @return string
	 */
	public function getTasksListUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=TasksList&record=' . $this->getId();
	}

	/**
	 * Get add task url.
	 *
	 * @return string
	 */
	public function getAddTaskUrl()
	{
		return 'index.php?module=Workflows&parent=Settings&view=EditTask&for_workflow=' . $this->getId();
	}

	/**
	 * Set workflow object.
	 *
	 * @param object $wf
	 *
	 * @return $this
	 */
	protected function setWorkflowObject($wf)
	{
		$this->workflow_object = $wf;

		return $this;
	}

	/**
	 * Get workflow object.
	 *
	 * @return object
	 */
	public function getWorkflowObject()
	{
		return $this->workflow_object;
	}

	/**
	 * Get module object.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Set module model.
	 *
	 * @param string $moduleName
	 *
	 * @return $this
	 */
	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);

		return $this;
	}

	/**
	 * Get tasks array.
	 *
	 * @param bool $active
	 *
	 * @return array
	 */
	public function getTasks($active = true)
	{
		return Settings_Workflows_TaskRecord_Model::getAllForWorkflow($this, $active);
	}

	/**
	 * Get task types array.
	 *
	 * @return array
	 */
	public function getTaskTypes()
	{
		$taskTypes = [];
		foreach (Settings_Workflows_TaskType_Model::getAllForModule($this->getModule()) as $taskType) {
			$taskModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($this, $taskType->get('classname'))->setTaskType($taskType);
			if ($taskModel->isEditable()) {
				$taskTypes[] = $taskModel;
			}
		}
		return $taskTypes;
	}

	/**
	 * Check if is default record.
	 *
	 * @return bool
	 */
	public function isDefault()
	{
		$wf = $this->getWorkflowObject();
		if (!empty($wf->defaultworkflow) && 1 == $wf->defaultworkflow) {
			return true;
		}
		return false;
	}

	/**
	 * Gets params value.
	 *
	 * @param string|null $key
	 *
	 * @return mixed
	 */
	public function getParams(string $key = null)
	{
		if ($params = $this->get('params') ?: []) {
			$params = \App\Json::decode($params);
		}
		return $key ? ($params[$key] ?? null) : $params;
	}

	/**
	 * Save record to database.
	 */
	public function save()
	{
		$wm = new VTWorkflowManager();
		$wf = $this->getWorkflowObject();
		$wf->description = $this->get('summary');
		$wf->test = \App\Json::encode($this->get('conditions'));
		$wf->moduleName = $this->get('module_name');
		$wf->executionCondition = $this->get('execution_condition');
		$wf->filtersavedinnew = $this->get('filtersavedinnew');
		$wf->schtypeid = $this->get('schtypeid');
		$wf->schtime = $this->get('schtime');
		$wf->schdayofmonth = $this->get('schdayofmonth');
		$wf->schdayofweek = $this->get('schdayofweek');
		$wf->schmonth = $this->get('schmonth');
		$wf->schannualdates = $this->get('schannualdates');
		$wf->nexttrigger_time = $this->get('nexttrigger_time');
		$wf->params = $this->get('params');
		if (!isset($wf->id)) {
			$wf->sequence = $this->getNextSequenceNumber($this->get('module_name'));
		}
		$wm->save($wf);

		$this->set('workflow_id', $wf->id);
	}

	/**
	 * Delete record from database.
	 */
	public function delete()
	{
		$wm = new VTWorkflowManager();
		$wm->delete($this->getId());
	}

	/**
	 * Functions returns the Custom Entity Methods that are supported for a module.
	 *
	 * @return array
	 */
	public function getEntityMethods()
	{
		return (new VTEntityMethodManager())->methodsForModule($this->get('module_name'));
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];

		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_ACTIVATION_TASKS',
				'linkurl' => 'javascript:Settings_Workflows_List_Js.setChangeStatusTasks(this,' . $this->getId() . ',true);',
				'linkicon' => 'fas fa-check',
				'class' => 'activeTasks',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DEACTIVATION_TASKS',
				'linkurl' => 'javascript:Settings_Workflows_List_Js.setChangeStatusTasks(this,' . $this->getId() . ', false);',
				'linkicon' => 'fas fa-times',
				'class' => 'deactiveTasks',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EXPORT_RECORD',
				'linkurl' => 'index.php?module=Workflows&parent=Settings&action=ExportWorkflow&id=' . $this->getId(),
				'linkicon' => 'fas fa-upload',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'class' => 'js-edit',
				'linkicon' => 'yfi yfi-full-editing-view',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_Workflows_List_Js.deleteById(' . $this->getId() . ');',
				'linkicon' => 'fas fa-trash-alt',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Get instance.
	 *
	 * @param int $workflowId
	 *
	 * @return \Self
	 */
	public static function getInstance($workflowId)
	{
		$wm = new VTWorkflowManager();
		$wf = $wm->retrieve($workflowId);

		return self::getInstanceFromWorkflowObject($wf);
	}

	/**
	 * Get clean instance.
	 *
	 * @param string $moduleName
	 *
	 * @return \Self
	 */
	public static function getCleanInstance(string $moduleName)
	{
		$wm = new VTWorkflowManager();
		$wf = $wm->newWorkflow($moduleName);
		$wf->filtersavedinnew = 6;
		$wf->params = null;
		return self::getInstanceFromWorkflowObject($wf);
	}

	/**
	 * Get instance from workflow object.
	 *
	 * @param object $wf
	 *
	 * @return \self
	 */
	public static function getInstanceFromWorkflowObject($wf)
	{
		$workflowModel = new self();
		$workflowModel->set('summary', $wf->description ?? '');
		$workflowModel->set('conditions', !empty($wf->test) ? \App\Json::decode($wf->test) : []);
		$workflowModel->set('execution_condition', $wf->executionCondition);
		$workflowModel->set('module_name', $wf->moduleName);
		$workflowModel->set('workflow_id', $wf->id ?? false);
		$workflowModel->set('filtersavedinnew', $wf->filtersavedinnew);
		$workflowModel->set('params', $wf->params);
		$workflowModel->setWorkflowObject($wf);
		$workflowModel->setModule($wf->moduleName);

		return $workflowModel;
	}

	/**
	 * Get execution condition label.
	 *
	 * @param int $executionCondition
	 *
	 * @return string
	 */
	public function executionConditionAsLabel($executionCondition = null)
	{
		if (null === $executionCondition) {
			$executionCondition = $this->get('execution_condition');
		}
		$arr = ['ON_FIRST_SAVE', 'ONCE', 'ON_EVERY_SAVE', 'ON_MODIFY', 'ON_DELETE', 'ON_SCHEDULE', 'MANUAL', 'TRIGGER', 'BLOCK_EDIT', 'ON_RELATED'];
		return $arr[$executionCondition - 1] ?? '';
	}

	/**
	 * Check if filter is saved in new.
	 *
	 * @return bool
	 */
	public function isFilterSavedInNew()
	{
		$wf = $this->getWorkflowObject();
		if ('6' == $wf->filtersavedinnew) {
			return true;
		}
		return false;
	}

	/**
	 * Functions transforms workflow filter to advanced filter.
	 *
	 * @param mixed $conditions
	 *
	 * @return array
	 */
	public function transformToAdvancedFilterCondition($conditions = false)
	{
		if (!$conditions) {
			$conditions = $this->get('conditions');
		}
		$transformedConditions = [];

		if (!empty($conditions)) {
			foreach ($conditions as $info) {
				if (!($info['groupid'])) {
					$firstGroup[] = ['columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid'], ];
				} else {
					$secondGroup[] = ['columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid'], ];
				}
			}
		}
		$transformedConditions[1] = ['columns' => $firstGroup ?? []];
		$transformedConditions[2] = ['columns' => $secondGroup ?? []];

		return $transformedConditions;
	}

	/**
	 * Function returns valuetype of the field filter.
	 *
	 * @param mixed $fieldname
	 *
	 * @return string|bool
	 */
	public function getFieldFilterValueType($fieldname)
	{
		$conditions = $this->get('conditions');
		if (!empty($conditions) && \is_array($conditions)) {
			foreach ($conditions as $filter) {
				if ($fieldname == $filter['fieldname']) {
					return $filter['valuetype'];
				}
			}
		}
		return false;
	}

	/**
	 * Function transforms Advance filter to workflow conditions.
	 */
	public function transformAdvanceFilterToWorkFlowFilter()
	{
		$conditions = $this->get('conditions');
		$wfCondition = [];

		if (\is_array($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if ('1' == $index && empty($columns)) {
					$wfCondition[] = ['fieldname' => '', 'operation' => '', 'value' => '', 'valuetype' => '',
						'joincondition' => '', 'groupid' => '0', ];
				}
				if (!empty($columns) && \is_array($columns)) {
					foreach ($columns as $column) {
						$wfCondition[] = ['fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'] ?? '', 'valuetype' => $column['valuetype'], 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'], 'groupid' => $column['groupid'], ];
					}
				}
			}
		}
		$this->set('conditions', $wfCondition);
	}

	public function updateNextTriggerTime()
	{
		$wm = new VTWorkflowManager();
		$wf = $this->getWorkflowObject();
		$wm->updateNexTriggerTime($wf);
	}

	/**
	 * Returns array of tasks for active workflow.
	 *
	 * @return array tasks
	 */
	public function getTasksForExport()
	{
		return (new \App\Db\Query())->select(['summary', 'task'])->from('com_vtiger_workflowtasks')->where(['workflow_id' => $this->getId()])->all();
	}

	/**
	 * Function to get number of workflow.
	 *
	 * @return int
	 */
	public static function getAllAmountWorkflowsAmount()
	{
		return (new App\Db\Query())->from('com_vtiger_workflows')->count();
	}

	/**
	 * Get next workflow action sequence number.
	 *
	 * @param string $moduleName
	 *
	 * @return int
	 */
	public function getNextSequenceNumber(string $moduleName): int
	{
		return (new \App\Db\Query())
			->from('com_vtiger_workflows')
			->where(['module_name' => $moduleName])
			->max('sequence') + 1;
	}

	/**
	 * Get module relations by type.
	 *
	 * @return array
	 */
	public function getModuleRelationsByType(): array
	{
		$moduleRelations = App\Relation::getByModule($this->getModule()->getName());
		$moduleRelationsByType = [];
		foreach ($moduleRelations as $relationId => $relationInfo) {
			$relationType = \Vtiger_Relation_Model::getInstanceById($relationId)->getRelationType();
			if (\Vtiger_Relation_Model::RELATION_O2M === $relationType) {
				$moduleRelationsByType['LBL_ONE_TO_MANY_RELATIONS'][] = $relationInfo;
			} elseif (\Vtiger_Relation_Model::RELATION_M2M === $relationType) {
				$moduleRelationsByType['LBL_MANY_TO_MANY_RELATIONS'][] = $relationInfo;
			} else {
				$moduleRelationsByType['LBL_WORKFLOW_CUSTOM_RELATIONS'][] = $relationInfo;
			}
		}
		return $moduleRelationsByType;
	}
}
