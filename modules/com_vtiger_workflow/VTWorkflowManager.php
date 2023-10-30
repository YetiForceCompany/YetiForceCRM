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
require_once 'VTJsonCondition.php';
require_once 'include/runtime/Cache.php';
Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/Workflow.php');

class VTWorkflowManager
{
	/**
	 * On first save.
	 *
	 * @var int
	 */
	public static $ON_FIRST_SAVE = 1;

	/**
	 * Once.
	 *
	 * @var int
	 */
	public static $ONCE = 2;

	/**
	 * On every save.
	 *
	 * @var int
	 */
	public static $ON_EVERY_SAVE = 3;

	/**
	 * On modify.
	 *
	 * @var int
	 */
	public static $ON_MODIFY = 4;

	/**
	 * On delete.
	 *
	 * @var int
	 */
	public static $ON_DELETE = 5;

	/**
	 * On schedule.
	 *
	 * @var int
	 */
	public static $ON_SCHEDULE = 6;

	/**
	 * Manual.
	 *
	 * @var int
	 */
	public static $MANUAL = 7;

	/**
	 * Trigger.
	 *
	 * @var int
	 */
	public static $TRIGGER = 8;

	/**
	 * Block edit.
	 *
	 * @var int
	 */
	public static $BLOCK_EDIT = 9;

	/**
	 * On related.
	 *
	 * @var int
	 */
	public static $ON_RELATED = 10;

	/**
	 * Save workflow data.
	 *
	 * @param Workflow $workflow
	 */
	public function save(Workflow $workflow)
	{
		if (isset($workflow->id)) {
			$wf = $workflow;
			if (null === $wf->filtersavedinnew) {
				$wf->filtersavedinnew = 5;
			}
			App\Db::getInstance()->createCommand()->update('com_vtiger_workflows', [
				'module_name' => $wf->moduleName,
				'summary' => $wf->description,
				'test' => $wf->test,
				'execution_condition' => $wf->executionCondition,
				'defaultworkflow' => empty($wf->defaultworkflow) ? null : $wf->defaultworkflow,
				'filtersavedinnew' => $wf->filtersavedinnew,
				'schtypeid' => $wf->schtypeid,
				'schtime' => $wf->schtime,
				'schdayofmonth' => $wf->schdayofmonth,
				'schdayofweek' => $wf->schdayofweek,
				'schannualdates' => $wf->schannualdates,
				'nexttrigger_time' => empty($wf->nexttrigger_time) ? null : $wf->nexttrigger_time,
				'params' => empty($wf->params) ? null : $wf->params,
			], ['workflow_id' => $wf->id])->execute();
		} else {
			$db = App\Db::getInstance();
			$wf = $workflow;
			if (null === $wf->filtersavedinnew) {
				$wf->filtersavedinnew = 5;
			}

			$db->createCommand()->insert('com_vtiger_workflows', [
				'module_name' => $wf->moduleName,
				'summary' => $wf->description,
				'test' => $wf->test,
				'execution_condition' => $wf->executionCondition,
				'type' => $wf->type,
				'defaultworkflow' => empty($wf->defaultworkflow) ? null : $wf->defaultworkflow,
				'filtersavedinnew' => $wf->filtersavedinnew,
				'schtypeid' => $wf->schtypeid,
				'schtime' => $wf->schtime,
				'schdayofmonth' => $wf->schdayofmonth,
				'schdayofweek' => $wf->schdayofweek,
				'schannualdates' => $wf->schannualdates,
				'nexttrigger_time' => empty($wf->nexttrigger_time) ? null : $wf->nexttrigger_time,
				'params' => empty($wf->params) ? null : $wf->params,
				'sequence' => (int) $wf->sequence,
			])->execute();
			$wf->id = $db->getLastInsertID('com_vtiger_workflows_workflow_id_seq');
		}
	}

	/**
	 * Return workflows.
	 *
	 * @return Workflow[]
	 */
	public function getWorkflows()
	{
		$query = (new \App\Db\Query())
			->select(['workflow_id', 'module_name', 'summary', 'test', 'execution_condition', 'defaultworkflow', 'type', 'filtersavedinnew'])
			->from('com_vtiger_workflows');

		return $this->getWorkflowsForResult($query->all());
	}

	/**
	 * Function returns scheduled workflows.
	 *
	 * @param object $referenceTime DateTime
	 *
	 * @return Workflow
	 */
	public function getScheduledWorkflows($referenceTime = false)
	{
		$query = (new \App\Db\Query())->from('com_vtiger_workflows')->where(['execution_condition' => self::$ON_SCHEDULE]);
		if ($referenceTime) {
			$query->andWhere(['or', ['nexttrigger_time' => null], ['<=', 'nexttrigger_time', $referenceTime]]);
		}
		return $this->getWorkflowsForResult($query->all());
	}

	/**
	 * Return workflows for module.
	 *
	 * @param string $moduleName
	 * @param string $executionCondition
	 *
	 * @return Workflow[]
	 */
	public function getWorkflowsForModule($moduleName, $executionCondition = false)
	{
		if (\App\Cache::has('WorkflowsForModule', $moduleName)) {
			$rows = \App\Cache::get('WorkflowsForModule', $moduleName);
		} else {
			$rows = (new \App\Db\Query())
				->select(['workflow_id', 'module_name', 'summary', 'test', 'execution_condition', 'defaultworkflow', 'type', 'filtersavedinnew', 'params'])
				->from('com_vtiger_workflows')
				->where(['module_name' => $moduleName])
				->orderBy(['sequence' => SORT_ASC])
				->all();
			\App\Cache::save('WorkflowsForModule', $moduleName, $rows);
		}
		if ($executionCondition) {
			foreach ($rows as $key => &$row) {
				if ($row['execution_condition'] !== $executionCondition) {
					unset($rows[$key]);
				}
			}
		}
		return $this->getWorkflowsForResult($rows);
	}

	/**
	 * Return workflows for provided rows.
	 *
	 * @param array $rows
	 *
	 * @return Workflow[]
	 */
	protected function getWorkflowsForResult($rows)
	{
		$workflows = [];
		foreach ($rows as &$row) {
			$workflow = $this->getWorkflowInstance($row['type']);
			$workflow->setup($row);
			if (!is_a($workflow, 'Workflow')) {
				continue;
			}

			$workflows[] = $workflow;
		}
		return $workflows;
	}

	/**
	 * Return workflow instance.
	 *
	 * @param string $type
	 *
	 * @return \workflowClass
	 */
	protected function getWorkflowInstance($type = 'basic')
	{
		require_once 'modules/com_vtiger_workflow/VTWorkflowManager.php';
		return new Workflow();
	}

	/**
	 * Retrieve a workflow from the database.
	 *
	 * Returns null if the workflow doesn't exist.
	 *
	 * @param The id of the workflow
	 * @param mixed $id
	 *
	 * @return A workflow object
	 */
	public function retrieve($id)
	{
		$data = (new App\Db\Query())->from('com_vtiger_workflows')->where(['workflow_id' => $id])->one();
		if ($data) {
			$workflow = $this->getWorkflowInstance($data['type']);
			$workflow->setup($data);

			return $workflow;
		}
		return null;
	}

	/**
	 * Delete workflow.
	 *
	 * @param int $id
	 */
	public function delete($id)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$subQuery = (new \App\Db\Query())->select(['workflow_id'])->from('com_vtiger_workflows')->where(['workflow_id' => $id])->andWhere(['or', ['defaultworkflow' => null], ['<>', 'defaultworkflow', 1]]);
		$dbCommand->delete('com_vtiger_workflowtasks', ['workflow_id' => $subQuery])->execute();
		$dbCommand->delete('com_vtiger_workflows', ['and', ['workflow_id' => $id], ['or', ['defaultworkflow' => null], ['<>', 'defaultworkflow', 1]]])->execute();
	}

	/**
	 * Create new workflow in module.
	 *
	 * @param string $moduleName
	 *
	 * @return Workflow
	 */
	public function newWorkflow($moduleName)
	{
		$workflow = $this->getWorkflowInstance();
		$workflow->moduleName = $moduleName;
		$workflow->executionCondition = self::$ON_EVERY_SAVE;
		$workflow->type = 'basic';

		return $workflow;
	}

	/**
	 * Update the Next trigger timestamp for a workflow.
	 *
	 * @param Workflow $workflow
	 */
	public function updateNexTriggerTime(Workflow $workflow)
	{
		$workflow->setNextTriggerTime($workflow->getNextTriggerTime());
	}
}
