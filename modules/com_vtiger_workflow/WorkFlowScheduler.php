<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'modules/com_vtiger_workflow/WorkflowSchedulerInclude.php';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/Users/Users.php';

/**
 * Class WorkFlowScheduler.
 */
class WorkFlowScheduler
{
	/**
	 * User.
	 *
	 * @var Users
	 */
	private $user;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->user = Users::getActiveAdminUser();
	}

	/**
	 * Get workflow query.
	 *
	 * @param \Workflow $workflow
	 *
	 * @return \App\Db\Query
	 */
	public function getWorkflowQuery(\Workflow $workflow)
	{
		$conditions = \App\Json::decode(App\Purifier::decodeHtml($workflow->test));

		$moduleName = $workflow->moduleName;
		$queryGenerator = new \App\QueryGenerator($moduleName, $this->user->id);
		$queryGenerator->setFields(['id']);
		$this->addWorkflowConditionsToQueryGenerator($queryGenerator, $conditions);
		return $queryGenerator->createQuery();
	}

	/**
	 * Get eligible workflow records.
	 *
	 * @param Workflow $workflow
	 *
	 * @return string
	 */
	public function getEligibleWorkflowRecords($workflow)
	{
		$query = $this->getWorkflowQuery($workflow);

		return $query->column();
	}

	/**
	 * Queue scheduled workflow tasks.
	 */
	public function queueScheduledWorkflowTasks()
	{
		$default_timezone = AppConfig::main('default_timezone');
		$vtWorflowManager = new VTWorkflowManager();
		$taskQueue = new VTTaskQueue();

		// set the time zone to the admin's time zone, this is needed so that the scheduled workflow will be triggered
		// at admin's time zone rather than the systems time zone. This is specially needed for Hourly and Daily scheduled workflows
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		date_default_timezone_set($adminTimeZone);
		$currentTimestamp = date('Y-m-d H:i:s');
		date_default_timezone_set($default_timezone);

		$scheduledWorkflows = $vtWorflowManager->getScheduledWorkflows($currentTimestamp);
		foreach ($scheduledWorkflows as &$workflow) {
			$tm = new VTTaskManager();
			$tasks = $tm->getTasksForWorkflow($workflow->id);
			if ($tasks) {
				$records = $this->getEligibleWorkflowRecords($workflow);
				foreach ($records as &$recordId) {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
					$data = $recordModel->getData();
					foreach ($tasks as $task) {
						if ($task->active) {
							$trigger = $task->trigger;
							if ($trigger !== null) {
								$delay = strtotime($data[$trigger['field']]) + $trigger['days'] * 86400;
							} else {
								$delay = 0;
							}
							if ((bool) $task->executeImmediately === true) {
								$task->doTask($recordModel);
							} else {
								$taskQueue->queueTask($task->id, $recordModel->getId(), $delay);
							}
						}
					}
				}
			}
			$vtWorflowManager->updateNexTriggerTime($workflow);
		}
		$scheduledWorkflows = null;
	}

	/**
	 * Add workflow conditions to query generator.
	 *
	 * @param \App\QueryGenerator $queryGenerator
	 * @param array               $conditions
	 */
	public function addWorkflowConditionsToQueryGenerator(\App\QueryGenerator $queryGenerator, $conditions)
	{
		$conditionMapping = [
			'equal to' => 'e',
			'less than' => 'l',
			'greater than' => 'g',
			'does not equal' => 'n',
			'less than or equal to' => 'm',
			'greater than or equal to' => 'h',
			'is' => 'e',
			'contains' => 'c',
			'does not contain' => 'k',
			'starts with' => 's',
			'ends with' => 'ew',
			'is not' => 'n',
			'is empty' => 'y',
			'is not empty' => 'ny',
			'before' => 'l',
			'after' => 'g',
			'between' => 'bw',
			'less than days ago' => 'bw',
			'more than days ago' => 'l',
			'in less than' => 'bw',
			'in more than' => 'g',
			'days ago' => 'e',
			'days later' => 'e',
			'less than hours before' => 'bw',
			'less than hours later' => 'bw',
			'more than hours before' => 'l',
			'more than hours later' => 'g',
			'is today' => 'e',
		];
		/*
		  Algorithm :
		  1. If the query has already where condition then start a new group with and condition, else start a group
		  2. Foreach of the condition, if its a condition in the same group just append with the existing joincondition
		  3. If its a new group, then start the group with the group join.
		  4. And for the first condition in the new group, dont append any joincondition.
		 */
		if ($conditions) {
			foreach ($conditions as &$condition) {
				$sourceField = '';
				$operation = $condition['operation'];
				//Cannot handle this condition for scheduled workflows
				if ($operation === 'has changed') {
					continue;
				}
				$value = $condition['value'];
				if (in_array($operation, $this->specialDateTimeOperator())) {
					$value = $this->parseValueForDate($condition);
				}
				$groupJoin = $condition['groupjoin'];
				$operator = $conditionMapping[$operation];
				$fieldName = $condition['fieldname'];
				$value = html_entity_decode($value);
				preg_match('/(\w+) : \((\w+)\) (\w+)/', $condition['fieldname'], $matches);
				if (count($matches) != 0) {
					$sourceField = $matches[1];
					$relatedModule = $matches[2];
					$relatedFieldName = $matches[3];
				}
				if (!empty($sourceField)) {
					$queryGenerator->addRelatedCondition([
						'sourceField' => $sourceField,
						'relatedModule' => $relatedModule,
						'relatedField' => $relatedFieldName,
						'value' => $value,
						'operator' => $operator,
						'conditionGroup' => $groupJoin === 'and',
					]);
				} else {
					$queryGenerator->addCondition($fieldName, $value, $operator, $groupJoin === 'and');
				}
			}
		}
	}

	/**
	 * Special Date functions.
	 *
	 * @return array
	 */
	public function specialDateTimeOperator()
	{
		return ['less than days ago', 'more than days ago', 'in less than', 'in more than', 'days ago', 'days later',
			'less than hours before', 'less than hours later', 'more than hours later', 'more than hours before', 'is today', ];
	}

	/**
	 * Function parse the value based on the condition.
	 *
	 * @param array $condition
	 *
	 * @return string
	 */
	public function parseValueForDate($condition)
	{
		$value = $condition['value'];
		$operation = $condition['operation'];

		// based on the admin users time zone, since query generator expects datetime at user timezone
		$default_timezone = \AppConfig::main('default_timezone');
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		date_default_timezone_set($adminTimeZone);

		switch ($operation) {
			case 'less than days ago':  //between current date and (currentdate - givenValue)
				$days = $condition['value'];
				$value = date('Y-m-d', strtotime('-' . $days . ' days')) . ',' . date('Y-m-d', strtotime('+1 day'));
				break;
			case 'more than days ago':  // less than (current date - givenValue)
				$days = $condition['value'] - 1;
				$value = date('Y-m-d', strtotime('-' . $days . ' days'));
				break;
			case 'in less than':   // between current date and future date(current date + givenValue)
				$days = $condition['value'] + 1;
				$value = date('Y-m-d', strtotime('-1 day')) . ',' . date('Y-m-d', strtotime('+' . $days . ' days'));
				break;
			case 'in more than':   // greater than future date(current date + givenValue)
				$days = $condition['value'] - 1;
				$value = date('Y-m-d', strtotime('+' . $days . ' days'));
				break;
			case 'days ago':
				$days = $condition['value'];
				$value = date('Y-m-d', strtotime('-' . $days . ' days'));
				break;
			case 'days later':
				$days = $condition['value'];
				$value = date('Y-m-d', strtotime('+' . $days . ' days'));
				break;
			case 'is today':
				$value = date('Y-m-d');
				break;
			case 'less than hours before':
				$hours = $condition['value'];
				$value = date('Y-m-d H:i:s', strtotime('-' . $hours . ' hours')) . ',' . date('Y-m-d H:i:s');
				break;
			case 'less than hours later':
				$hours = $condition['value'];
				$value = date('Y-m-d H:i:s') . ',' . date('Y-m-d H:i:s', strtotime('+' . $hours . ' hours'));
				break;
			case 'more than hours later':
				$hours = $condition['value'];
				$value = date('Y-m-d H:i:s', strtotime('+' . $hours . ' hours'));
				break;
			case 'more than hours before':
				$hours = $condition['value'];
				$value = date('Y-m-d H:i:s', strtotime('-' . $hours . ' hours'));
				break;
			default:
				break;
		}
		if (in_array($operation, ['less than hours before', 'less than hours later', 'more than hours later', 'more than hours before'])) {
			$value = App\Fields\DateTime::formatToDisplay($value);
		} else {
			$value = App\Fields\Date::formatToDisplay($value);
		}
		date_default_timezone_set($default_timezone);
		return $value;
	}
}
