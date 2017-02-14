<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
require_once('VTJsonCondition.php');
require_once 'include/utils/ConfigReader.php';
require_once 'include/runtime/Cache.php';

class VTWorkflowManager
{

	static $ON_FIRST_SAVE = 1;
	static $ONCE = 2;
	static $ON_EVERY_SAVE = 3;
	static $ON_MODIFY = 4;
	static $ON_DELETE = 5;
	static $ON_SCHEDULE = 6;
	static $MANUAL = 7;
	static $TRIGGER = 8;
	static $BLOCK_EDIT = 9;
	static $ON_RELATED = 10;

	public function __construct($adb = false)
	{
		$this->adb = $adb;
	}

	public function save($workflow)
	{
		if (isset($workflow->id)) {
			$wf = $workflow;
			if ($wf->filtersavedinnew == null)
				$wf->filtersavedinnew = 5;
			App\Db::getInstance()->createCommand()->update('com_vtiger_workflows', [
				'module_name' => $wf->moduleName,
				'summary' => $wf->description,
				'test' => $wf->test,
				'execution_condition' => $wf->executionCondition,
				'defaultworkflow' => $wf->defaultworkflow,
				'filtersavedinnew' => $wf->filtersavedinnew,
				'schtypeid' => $wf->schtypeid,
				'schtime' => $wf->schtime,
				'schdayofmonth' => $wf->schdayofmonth,
				'schdayofweek' => $wf->schdayofweek,
				'schannualdates' => $wf->schannualdates,
				'nexttrigger_time' => empty($wf->nexttrigger_time) ? null : $wf->nexttrigger_time
				], ['workflow_id' => $wf->id])->execute();
		} else {
			$db = App\Db::getInstance();
			$wf = $workflow;
			if ($wf->filtersavedinnew == null)
				$wf->filtersavedinnew = 5;
			$db->createCommand()->insert('com_vtiger_workflows', [
				'module_name' => $wf->moduleName,
				'summary' => $wf->description,
				'test' => $wf->test,
				'execution_condition' => $wf->executionCondition,
				'type' => $wf->type,
				'defaultworkflow' => $wf->defaultworkflow,
				'filtersavedinnew' => $wf->filtersavedinnew,
				'schtypeid' => $wf->schtypeid,
				'schtime' => $wf->schtime,
				'schdayofmonth' => $wf->schdayofmonth,
				'schdayofweek' => $wf->schdayofweek,
				'schannualdates' => $wf->schannualdates,
				'nexttrigger_time' => empty($wf->nexttrigger_time) ? null : $wf->nexttrigger_time
			])->execute();
			$wf->id = $db->getLastInsertID('com_vtiger_workflows_workflow_id_seq');
		}
	}

	public function getWorkflows()
	{
		$query = (new \App\Db\Query())
			->select(['workflow_id', 'module_name', 'summary', 'test', 'execution_condition', 'defaultworkflow', 'type', 'filtersavedinnew'])
			->from('com_vtiger_workflows');
		return $this->getWorkflowsForResult($query->all());
	}

	/**
	 * Function returns scheduled workflows
	 * @param DateTime $referenceTime
	 * @return Workflow
	 */
	public function getScheduledWorkflows($referenceTime = false)
	{
		$query = (new \App\Db\Query())->from('com_vtiger_workflows')->where(['execution_condition' => VTWorkflowManager::$ON_SCHEDULE]);
		if ($referenceTime) {
			$query->andWhere(['or', ['nexttrigger_time' => null], ['<=', 'nexttrigger_time', $referenceTime]]);
		}
		return $this->getWorkflowsForResult($query->all());
	}

	/**
	 * Function to get the number of scheduled workflows
	 * @return Integer
	 */
	public function getScheduledWorkflowsCount()
	{
		$adb = $this->adb;
		$query = 'SELECT count(*) AS count FROM com_vtiger_workflows WHERE execution_condition = ?';
		$params = array(VTWorkflowManager::$ON_SCHEDULE);
		$result = $adb->pquery($query, $params);
		return $adb->query_result($result, 0, 'count');
	}

	/**
	 * Function returns the maximum allowed scheduled workflows
	 * @return int
	 */
	public function getMaxAllowedScheduledWorkflows()
	{
		return 10;
	}

	public function getWorkflowsForModule($moduleName, $executionCondition = false)
	{
		if (\App\Cache::has('WorkflowsForModule', $moduleName)) {
			$rows = \App\Cache::get('WorkflowsForModule', $moduleName);
		} else {
			$rows = (new \App\Db\Query())
					->select(['workflow_id', 'module_name', 'summary', 'test', 'execution_condition', 'defaultworkflow', 'type', 'filtersavedinnew'])
					->from('com_vtiger_workflows')
					->where(['module_name' => $moduleName])->all();
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

	protected function getWorkflowsForResult($rows)
	{
		$workflows = [];
		foreach ($rows as &$row) {
			$workflow = $this->getWorkflowInstance($row['type']);
			$workflow->setup($row);
			if (!is_a($workflow, 'Workflow'))
				continue;

			$workflows[] = $workflow;
		}
		return $workflows;
	}

	protected function getWorkflowInstance($type = 'basic')
	{
		$configReader = new ConfigReader('modules/com_vtiger_workflow/config.inc', 'workflowConfig');
		$workflowTypeConfig = $configReader->getConfig($type);
		$workflowClassPath = $workflowTypeConfig['classpath'];
		$workflowClass = $workflowTypeConfig['class'];

		require_once $workflowClassPath;
		$workflow = new $workflowClass();
		return $workflow;
	}

	/**
	 * Retrieve a workflow from the database
	 *
	 * Returns null if the workflow doesn't exist.
	 *
	 * @param The id of the workflow
	 * @return A workflow object.
	 */
	public function retrieve($id)
	{
		$data = (new App\Db\Query())->from('com_vtiger_workflows')->where(['workflow_id' => $id])->one();
		if ($data) {
			$workflow = $this->getWorkflowInstance($data['type']);
			$workflow->setup($data);
			return $workflow;
		} else {
			return null;
		}
	}

	public function delete($id)
	{
		$adb = $this->adb;
		$adb->pquery("DELETE FROM com_vtiger_workflowtasks WHERE workflow_id IN
							(SELECT workflow_id FROM com_vtiger_workflows WHERE workflow_id=? AND (defaultworkflow IS NULL OR defaultworkflow != 1))", array($id));
		$adb->pquery("DELETE FROM com_vtiger_workflows WHERE workflow_id=? AND (defaultworkflow IS NULL OR defaultworkflow != 1)", array($id));
	}

	public function newWorkflow($moduleName)
	{
		$workflow = $this->getWorkflowInstance();
		$workflow->moduleName = $moduleName;
		$workflow->executionCondition = self::$ON_EVERY_SAVE;
		$workflow->type = 'basic';
		return $workflow;
	}

	/**
	 * Export a workflow as a json encoded string
	 *
	 * @param $workflow The workflow instance to export.
	 */
	public function serializeWorkflow($workflow)
	{
		$exp = array();
		$exp['moduleName'] = $workflow->moduleName;
		$exp['description'] = $workflow->description;
		$exp['test'] = $workflow->test;
		$exp['executionCondition'] = $workflow->executionCondition;
		$exp['schtypeid'] = $workflow->schtypeid;
		$exp['schtime'] = $workflow->schtime;
		$exp['schdayofmonth'] = $workflow->schdayofmonth;
		$exp['schdayofweek'] = $workflow->schdayofweek;
		$exp['schannualdates'] = $workflow->schannualdates;
		$exp['tasks'] = array();
		$tm = new VTTaskManager($this->adb);
		$tasks = $tm->getTasksForWorkflow($workflow->id);
		foreach ($tasks as $task) {
			unset($task->id);
			unset($task->workflowId);
			$exp['tasks'][] = serialize($task);
		}
		return \App\Json::encode($exp);
	}

	/**
	 * Import a json encoded string as a workflow object
	 *
	 * @return The Workflow instance representing the imported workflow.
	 */
	public function deserializeWorkflow($str)
	{
		$data = \App\Json::decode($str);
		$workflow = $this->newWorkflow($data['moduleName']);
		$workflow->description = $data['description'];
		$workflow->test = $data['test'];
		$workflow->executionCondition = $data['executionCondition'];
		$workflow->schtypeid = $data['schtypeid'];
		$workflow->schtime = $data['schtime'];
		$workflow->schdayofmonth = $data['schdayofmonth'];
		$workflow->schdayofweek = $data['schdayofweek'];
		$workflow->schannualdates = $data['schannualdates'];
		$this->save($workflow);
		$tm = new VTTaskManager($this->adb);
		$tasks = $data['tasks'];
		foreach ($tasks as $taskStr) {
			$task = $tm->unserializeTask($taskStr);
			$task->workflowId = $workflow->id;
			$tm->saveTask($task);
		}
		return $workflow;
	}

	/**
	 * Update the Next trigger timestamp for a workflow
	 */
	public function updateNexTriggerTime($workflow)
	{
		$nextTriggerTime = $workflow->getNextTriggerTime();
		$workflow->setNextTriggerTime($nextTriggerTime);
	}

	/**
	 * Function to get workflows modules those are supporting comments
	 * @param <String> $moduleName
	 * @return <Array> list of Workflow models
	 */
	public function getWorkflowsForModuleSupportingComments($moduleName)
	{
		if (App\Cache::staticHas('WorkflowsForModuleSupportingComments', $moduleName)) {
			return App\Cache::staticGet('WorkflowsForModuleSupportingComments', $moduleName);
		}
		$query = (new \App\Db\Query())
			->select(['workflow_id', 'module_name', 'summary', 'test', 'execution_condition', 'defaultworkflow', 'type', 'filtersavedinnew'])
			->from('com_vtiger_workflows')
			->where(['module_name' => $moduleName])
			->andWhere(['like', 'test', '_VT_add_comment']);
		$workflowModels = $this->getWorkflowsForResult($query->all());

		$commentSupportedWorkflowModels = array();
		foreach ($workflowModels as $workflowId => &$workflowModel) {
			$conditions = \App\Json::decode($workflowModel->test);
			if (is_array($conditions)) {
				foreach ($conditions as $key => $conditionInfo) {
					if ($conditionInfo['fieldname'] === '_VT_add_comment') {
						unset($conditions[$key]);
						$workflowModel->test = \App\Json::encode($conditions);
						$commentSupportedWorkflowModels[$workflowId] = $workflowModel;
					}
				}
			}
		}
		App\Cache::staticSave('WorkflowsForModuleSupportingComments', $moduleName, $commentSupportedWorkflowModels);
		return $commentSupportedWorkflowModels;
	}
}

class Workflow
{

	static $SCHEDULED_HOURLY = 1;
	static $SCHEDULED_DAILY = 2;
	static $SCHEDULED_WEEKLY = 3;
	static $SCHEDULED_ON_SPECIFIC_DATE = 4;
	static $SCHEDULED_MONTHLY_BY_DATE = 5;
	static $SCHEDULED_MONTHLY_BY_WEEKDAY = 6;
	static $SCHEDULED_ANNUALLY = 7;

	public function __construct()
	{
		$this->conditionStrategy = new VTJsonCondition();
	}

	public function setup($row)
	{
		$this->id = isset($row['workflow_id']) ? $row['workflow_id'] : '';
		$this->moduleName = isset($row['module_name']) ? $row['module_name'] : '';
		$this->description = \App\Purifier::toHtml(isset($row['summary']) ? $row['summary'] : '');
		$this->test = isset($row['test']) ? $row['test'] : '';
		$this->executionCondition = isset($row['execution_condition']) ? $row['execution_condition'] : '';
		$this->schtypeid = isset($row['schtypeid']) ? $row['schtypeid'] : '';
		$this->schtime = isset($row['schtime']) ? $row['schtime'] : '';
		$this->schdayofmonth = isset($row['schdayofmonth']) ? $row['schdayofmonth'] : '';
		$this->schdayofweek = isset($row['schdayofweek']) ? $row['schdayofweek'] : '';
		$this->schannualdates = isset($row['schannualdates']) ? $row['schannualdates'] : '';
		if (isset($row['defaultworkflow'])) {
			$this->defaultworkflow = $row['defaultworkflow'];
		}
		$this->filtersavedinnew = isset($row['filtersavedinnew']) ? $row['filtersavedinnew'] : '';
		$this->nexttrigger_time = isset($row['nexttrigger_time']) ? $row['nexttrigger_time'] : '';
	}

	/**
	 * Evaluate
	 * @param Vtiger_Record_Model $recordModel
	 * @return boolean
	 */
	public function evaluate($recordModel)
	{
		if ($this->test == "") {
			return true;
		} else {
			$cs = $this->conditionStrategy;
			return $cs->evaluate($this->test, $recordModel);
		}
	}

	public function isCompletedForRecord($recordId)
	{
		$isExistsActivateDonce = (new \App\Db\Query())->from('com_vtiger_workflow_activatedonce')->where(['entity_id' => $recordId, 'workflow_id' => $this->id])->exists();
		$isExistsWorkflowTasks = (new \App\Db\Query())->from('com_vtiger_workflowtasks')
				->innerJoin('com_vtiger_workflowtask_queue', 'com_vtiger_workflowtasks.task_id= com_vtiger_workflowtask_queue.task_id')
				->where(['entity_id' => $recordId, 'workflow_id' => $this->id])->exists();

		if (!$isExistsActivateDonce && !$isExistsWorkflowTasks) { // Workflow not done for specified record
			return false;
		} else {
			return true;
		}
	}

	public function markAsCompletedForRecord($recordId)
	{
		\App\Db::getInstance()->createCommand()
			->insert('com_vtiger_workflow_activatedonce', [
				'entity_id' => $recordId,
				'workflow_id' => $this->id
			])->execute();
	}

	/**
	 * Perform tasks
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function performTasks($recordModel)
	{
		require_once('modules/com_vtiger_workflow/VTTaskManager.php');
		require_once('modules/com_vtiger_workflow/VTTaskQueue.php');

		$tm = new VTTaskManager();
		$taskQueue = new VTTaskQueue();
		$tasks = $tm->getTasksForWorkflow($this->id);
		foreach ($tasks as &$task) {
			if ($task->active) {
				$trigger = $task->trigger;
				if ($trigger != null) {
					$delay = strtotime($recordModel->get($trigger['field'])) + $trigger['days'] * 86400;
				} else {
					$delay = 0;
				}
				if ($task->executeImmediately == true) {
					$task->doTask($recordModel);
				} else {
					$hasContents = $task->hasContents($recordModel);
					if ($hasContents) {
						$taskQueue->queueTask($task->id, $recordModel->getId(), $delay, $task->getContents($recordModel));
					}
				}
			}
		}
	}

	public function executionConditionAsLabel($label = null)
	{
		if ($label == null) {
			$arr = ['ON_FIRST_SAVE', 'ONCE', 'ON_EVERY_SAVE', 'ON_MODIFY', 'ON_DELETE', 'ON_SCHEDULE', 'MANUAL', 'TRIGGER', 'BLOCK_EDIT', 'ON_RELATED'];
			return $arr[$this->executionCondition - 1];
		} else {
			$arr = ['ON_FIRST_SAVE' => 1, 'ONCE' => 2, 'ON_EVERY_SAVE' => 3, 'ON_MODIFY' => 4,
				'ON_DELETE' => 5, 'ON_SCHEDULE' => 6, 'MANUAL' => 7, 'TRIGGER' => 8, 'BLOCK_EDIT' => 9, 'ON_RELATED' => 10];
			$this->executionCondition = $arr[$label];
		}
	}

	public function setNextTriggerTime($time)
	{
		if ($time) {
			$db = PearDatabase::getInstance();
			$db->pquery("UPDATE com_vtiger_workflows SET nexttrigger_time=? WHERE workflow_id=?", array($time, $this->id));
			$this->nexttrigger_time = $time;
		}
	}

	public function getNextTriggerTimeValue()
	{
		return $this->nexttrigger_time;
	}

	public function getWFScheduleType()
	{
		return ($this->executionCondition == 6 ? $this->schtypeid : 0);
	}

	public function getWFScheduleTime()
	{
		return $this->schtime;
	}

	public function getWFScheduleDay()
	{
		return $this->schdayofmonth;
	}

	public function getWFScheduleWeek()
	{
		return $this->schdayofweek;
	}

	public function getWFScheduleAnnualDates()
	{
		return $this->schannualdates;
	}

	/**
	 * Function gets the next trigger for the workflows
	 * @global <String> $default_timezone
	 * @return type
	 */
	public function getNextTriggerTime()
	{
		$default_timezone = vglobal('default_timezone');
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		@date_default_timezone_set($adminTimeZone);

		$scheduleType = $this->getWFScheduleType();
		$nextTime = null;

		if ($scheduleType == Workflow::$SCHEDULED_HOURLY) {
			$nextTime = date("Y-m-d H:i:s", strtotime("+1 hour"));
		}

		if ($scheduleType == Workflow::$SCHEDULED_DAILY) {
			$nextTime = $this->getNextTriggerTimeForDaily($this->getWFScheduleTime());
		}

		if ($scheduleType == Workflow::$SCHEDULED_WEEKLY) {
			$nextTime = $this->getNextTriggerTimeForWeekly($this->getWFScheduleWeek(), $this->getWFScheduleTime());
		}

		if ($scheduleType == Workflow::$SCHEDULED_ON_SPECIFIC_DATE) {
			$nextTime = date('Y-m-d H:i:s', strtotime('+10 year'));
		}

		if ($scheduleType == Workflow::$SCHEDULED_MONTHLY_BY_DATE) {
			$nextTime = $this->getNextTriggerTimeForMonthlyByDate($this->getWFScheduleDay(), $this->getWFScheduleTime());
		}

		if ($scheduleType == Workflow::$SCHEDULED_MONTHLY_BY_WEEKDAY) {
			$nextTime = $this->getNextTriggerTimeForMonthlyByWeekDay($this->getWFScheduleDay(), $this->getWFScheduleTime());
		}

		if ($scheduleType == Workflow::$SCHEDULED_ANNUALLY) {
			$nextTime = $this->getNextTriggerTimeForAnnualDates($this->getWFScheduleAnnualDates(), $this->getWFScheduleTime());
		}
		@date_default_timezone_set($default_timezone);
		return $nextTime;
	}

	/**
	 * get next trigger time for daily
	 * @param type $schTime
	 * @return time
	 */
	public function getNextTriggerTimeForDaily($scheduledTime)
	{
		$now = strtotime(date("Y-m-d H:i:s"));
		$todayScheduledTime = strtotime(date("Y-m-d H:i:s", strtotime($scheduledTime)));
		if ($now > $todayScheduledTime) {
			$nextTime = date("Y-m-d H:i:s", strtotime('+1 day ' . $scheduledTime));
		} else {
			$nextTime = date("Y-m-d H:i:s", $todayScheduledTime);
		}
		return $nextTime;
	}

	/**
	 * get next trigger Time For weekly
	 * @param type $scheduledDaysOfWeek
	 * @param type $scheduledTime
	 * @return <time>
	 */
	public function getNextTriggerTimeForWeekly($scheduledDaysOfWeek, $scheduledTime)
	{
		$weekDays = array('1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday');
		$currentTime = time();
		$currentWeekDay = date('N', $currentTime);
		if ($scheduledDaysOfWeek) {
			$scheduledDaysOfWeek = \App\Json::decode($scheduledDaysOfWeek);
			if (is_array($scheduledDaysOfWeek)) {
				// algorithm :
				//1. First sort all the weekdays(stored as 0,1,2,3 etc in db) and find the closest weekday which is greater than currentWeekDay
				//2. If found, set the next trigger date to the next weekday value in the same week.
				//3. If not found, set the trigger date to the next first value.
				$nextTriggerWeekDay = null;
				sort($scheduledDaysOfWeek);
				foreach ($scheduledDaysOfWeek as $index => $weekDay) {
					if ($weekDay == $currentWeekDay) { //if today is the weekday selected
						$scheduleWeekDayInTime = strtotime(date('Y-m-d', strtotime($weekDays[$currentWeekDay])) . ' ' . $scheduledTime);
						if ($currentTime < $scheduleWeekDayInTime) { //if the scheduled time is greater than current time, selected today
							$nextTriggerWeekDay = $weekDay;
							break;
						} else {
							//current time greater than scheduled time, get the next weekday
							if (count($scheduledDaysOfWeek) == 1) { //if only one weekday selected, then get next week
								$nextTime = date('Y-m-d', strtotime('next ' . $weekDays[$weekDay])) . ' ' . $scheduledTime;
							} else {
								$nextWeekDay = $scheduledDaysOfWeek[$index + 1]; // its the last day of the week i.e. sunday
								if (empty($nextWeekDay)) {
									$nextWeekDay = $scheduledDaysOfWeek[0];
								}
								$nextTime = date('Y-m-d', strtotime('next ' . $weekDays[$nextWeekDay])) . ' ' . $scheduledTime;
							}
						}
					} else if ($weekDay > $currentWeekDay) {
						$nextTriggerWeekDay = $weekDay;
						break;
					}
				}

				if ($nextTime == null) {
					if (!empty($nextTriggerWeekDay)) {
						$nextTime = date("Y-m-d H:i:s", strtotime($weekDays[$nextTriggerWeekDay] . ' ' . $scheduledTime));
					} else {
						$nextTime = date("Y-m-d H:i:s", strtotime($weekDays[$scheduledDaysOfWeek[0]] . ' ' . $scheduledTime));
					}
				}
			}
		}
		return $nextTime;
	}

	/**
	 * get next triggertime for monthly
	 * @param type $scheduledDayOfMonth
	 * @param type $scheduledTime
	 * @return <time>
	 */
	public function getNextTriggerTimeForMonthlyByDate($scheduledDayOfMonth, $scheduledTime)
	{
		$currentDayOfMonth = date('j', time());
		if ($scheduledDayOfMonth) {
			$scheduledDaysOfMonth = \App\Json::decode($scheduledDayOfMonth);
			if (is_array($scheduledDaysOfMonth)) {
				// algorithm :
				//1. First sort all the days in ascending order and find the closest day which is greater than currentDayOfMonth
				//2. If found, set the next trigger date to the found value which is in the same month.
				//3. If not found, set the trigger date to the next month's first selected value.
				$nextTriggerDay = null;
				sort($scheduledDaysOfMonth);
				foreach ($scheduledDaysOfMonth as $day) {
					if ($day == $currentDayOfMonth) {
						$currentTime = time();
						$schTime = strtotime($date = date('Y') . '-' . date('m') . '-' . $day . ' ' . $scheduledTime);
						if ($schTime > $currentTime) {
							$nextTriggerDay = $day;
							break;
						}
					} elseif ($day > $currentDayOfMonth) {
						$nextTriggerDay = $day;
						break;
					}
				}
				if (!empty($nextTriggerDay)) {
					$firstDayofNextMonth = date('Y:m:d H:i:s', strtotime('first day of this month'));
					$nextTime = date('Y:m:d', strtotime($firstDayofNextMonth . ' + ' . ($nextTriggerDay - 1) . ' days'));
					$nextTime = $nextTime . ' ' . $scheduledTime;
				} else {
					$firstDayofNextMonth = date('Y:m:d H:i:s', strtotime('first day of next month'));
					$nextTime = date('Y:m:d', strtotime($firstDayofNextMonth . ' + ' . ($scheduledDaysOfMonth[0] - 1) . ' days'));
					$nextTime = $nextTime . ' ' . $scheduledTime;
				}
			}
		}
		return $nextTime;
	}

	/**
	 * to get next trigger time for weekday of the month
	 * @param type $scheduledWeekDayOfMonth
	 * @param type $scheduledTime
	 * @return <time>
	 */
	public function getNextTriggerTimeForMonthlyByWeekDay($scheduledWeekDayOfMonth, $scheduledTime)
	{
		$currentTime = time();
		$currentDayOfMonth = date('j', $currentTime);
		$scheduledTime = $this->getWFScheduleTime();
		if ($scheduledWeekDayOfMonth == $currentDayOfMonth) {
			$nextTime = date("Y-m-d H:i:s", strtotime('+1 month ' . $scheduledTime));
		} else {
			$monthInFullText = date('F', $currentTime);
			$yearFullNumberic = date('Y', $currentTime);
			if ($scheduledWeekDayOfMonth < $currentDayOfMonth) {
				$nextMonth = date("Y-m-d H:i:s", strtotime('next month'));
				$monthInFullText = date('F', strtotime($nextMonth));
			}
			$nextTime = date("Y-m-d H:i:s", strtotime($scheduledWeekDayOfMonth . ' ' . $monthInFullText . ' ' . $yearFullNumberic . ' ' . $scheduledTime));
		}
		return $nextTime;
	}

	/**
	 * to get next trigger time
	 * @param type $annualDates
	 * @param type $scheduledTime
	 * @return <time>
	 */
	public function getNextTriggerTimeForAnnualDates($annualDates, $scheduledTime)
	{
		if ($annualDates) {
			$today = date('Y-m-d');
			$annualDates = \App\Json::decode($annualDates);
			$nextTriggerDay = null;
			// sort the dates
			sort($annualDates);
			$currentTime = time();
			$currentDayOfMonth = date('Y-m-d', $currentTime);
			foreach ($annualDates as $day) {
				if ($day == $currentDayOfMonth) {
					$schTime = strtotime($day . ' ' . $scheduledTime);
					if ($schTime > $currentTime) {
						$nextTriggerDay = $day;
						break;
					}
				} else if ($day > $today) {
					$nextTriggerDay = $day;
					break;
				}
			}
			if (!empty($nextTriggerDay)) {
				$nextTime = date('Y:m:d H:i:s', strtotime($nextTriggerDay . ' ' . $scheduledTime));
			} else {
				$nextTriggerDay = $annualDates[0];
				$nextTime = date('Y:m:d H:i:s', strtotime($nextTriggerDay . ' ' . $scheduledTime . '+1 year'));
			}
		}
		return $nextTime;
	}
}
