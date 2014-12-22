<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
	require_once("VTJsonCondition.inc");
	require_once 'include/utils/ConfigReader.php';
	require_once 'includes/runtime/Cache.php';

	class VTWorkflowManager{

		static $ON_FIRST_SAVE = 1;
		static $ONCE = 2;
		static $ON_EVERY_SAVE = 3;
		static $ON_MODIFY = 4;
        // Reserving 5 & 6 for ON_DELETE and ON_SCHEDULED types.
		static $ON_SCHEDULE=6;
		static $MANUAL = 7;

		function __construct($adb){
			$this->adb = $adb;
		}

		function save($workflow){
			$adb=$this->adb;
			if(isset($workflow->id)){
				$wf=$workflow;
				if($wf->filtersavedinnew == null) $wf->filtersavedinnew = 5;
				$adb->pquery("update com_vtiger_workflows set
								module_name=?, summary=?, test=?, execution_condition=?, defaultworkflow=?, filtersavedinnew=?,
								schtypeid=?, schtime=?, schdayofmonth=?, schdayofweek=?, schannualdates=?, nexttrigger_time=? where workflow_id=?",
					array($wf->moduleName, $wf->description, $wf->test, $wf->executionCondition, $wf->defaultworkflow, $wf->filtersavedinnew,
						$wf->schtypeid, $wf->schtime, $wf->schdayofmonth, $wf->schdayofweek, $wf->schannualdates, $wf->nexttrigger_time, $wf->id));
			}else{
				$workflowId = $adb->getUniqueID("com_vtiger_workflows");
				$workflow->id = $workflowId;
				$wf=$workflow;
				if($wf->filtersavedinnew == null) $wf->filtersavedinnew = 5;

				$result=$adb->getColumnNames("com_vtiger_workflows");
				if(in_array("type",$result)) {
					$adb->pquery("insert into com_vtiger_workflows
								(workflow_id, module_name, summary, test, execution_condition, type, defaultworkflow, filtersavedinnew,
								schtypeid, schtime, schdayofmonth, schdayofweek, schannualdates, nexttrigger_time) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
							array($workflowId, $wf->moduleName, $wf->description, $wf->test,  $wf->executionCondition, $wf->type, $wf->defaultworkflow, $wf->filtersavedinnew,
							$wf->schtypeid, $wf->schtime, $wf->schdayofmonth, $wf->schdayofweek, $wf->schannualdates, $wf->nexttrigger_time));
				} else {
					$adb->pquery("insert into com_vtiger_workflows
								(workflow_id, module_name, summary, test, execution_condition, defaultworkflow,filtersavedinnew,
								schtypeid, schtime, schdayofmonth, schdayofweek, schannualdates, nexttrigger_time) values (?,?,?,?,?,?,?,?,?,?,?,?,?)",
							array($workflowId, $wf->moduleName, $wf->description, $wf->test,  $wf->executionCondition, $wf->defaultworkflow, $wf->filtersavedinnew,
							$wf->schtypeid, $wf->schtime, $wf->schdayofmonth, $wf->schdayofweek, $wf->schannualdates, $wf->nexttrigger_time));
				}
			}
		}


		function getWorkflows(){
			$adb=$this->adb;

			$result=$adb->getColumnNames("com_vtiger_workflows");
			if(in_array("defaultworkflow",$result)){
				$result = $adb->query("select workflow_id, module_name, summary, test, execution_condition,defaultworkflow, type, filtersavedinnew
									from com_vtiger_workflows ");
			}else{

				$result = $adb->query("select workflow_id, module_name, summary, test, execution_condition, type, filtersavedinnew
									from com_vtiger_workflows");
			}
			return $this->getWorkflowsForResult($result);

		}

		/**
		 * Function returns scheduled workflows
		 * @param DateTime $referenceTime
		 * @return Workflow
		 */
		function getScheduledWorkflows($referenceTime='') {
			$adb=$this->adb;
			$query = 'SELECT * FROM com_vtiger_workflows WHERE execution_condition = ?';
			$params = array(VTWorkflowManager::$ON_SCHEDULE);
			if($referenceTime != '') {
				$query .= " AND (nexttrigger_time = '' OR nexttrigger_time IS NULL OR nexttrigger_time <= ?)";
				array_push($params, $referenceTime);
			}
			$result = $adb->pquery($query, $params);
			return $this->getWorkflowsForResult($result);

		}

		/**
		 * Function to get the number of scheduled workflows
		 * @return Integer
		 */
		function getScheduledWorkflowsCount() {
			$adb=$this->adb;
			$query = 'SELECT count(*) AS count FROM com_vtiger_workflows WHERE execution_condition = ?';
			$params = array(VTWorkflowManager::$ON_SCHEDULE);
			$result = $adb->pquery($query, $params);
			return $adb->query_result($result, 0, 'count');
		}

		/**
		 * Function returns the maximum allowed scheduled workflows
		 * @return int
		 */
		function getMaxAllowedScheduledWorkflows() {
			return 10;
		}

		function getWorkflowsForModule($moduleName){
			$adb=$this->adb;
			$cache= Vtiger_Cache::getInstance();
			if($cache->getWorkflowForModule($moduleName)){
				return $this->getWorkflowsForResult($cache->getWorkflowForModule($moduleName));
			} else {
			//my changes
			$result=$adb->getColumnNames("com_vtiger_workflows");
			if(in_array(defaultworkflow,$result)){
				$result = $adb->pquery("select workflow_id, module_name, summary, test, execution_condition, defaultworkflow, type, filtersavedinnew
									from com_vtiger_workflows where module_name=?",array($moduleName));
			}
			else{
				$result = $adb->pquery("select workflow_id, module_name, summary, test, execution_condition, type, filtersavedinnew
									from com_vtiger_workflows where module_name=?",array($moduleName));
			}
				$cache->setWorkflowForModule($moduleName,$result);
			return $this->getWorkflowsForResult($result);
			}
		}

		protected function getWorkflowsForResult($result){
			$adb=$this->adb;

			$it = new SqlResultIterator($adb, $result);
			$workflows=array();
			$i=0;
			foreach($it as $row){
				$workflow = $this->getWorkflowInstance($row->type);
				$workflow->setup($row->data);

				if(!is_a($workflow, 'Workflow')) continue;

				$workflows[$i++]=$workflow;
			}
			return $workflows;
		}

		protected function getWorkflowInstance($type='basic') {
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
		function retrieve($id){
			$adb=$this->adb;
			$result = $adb->pquery("select * from com_vtiger_workflows where workflow_id=?", array($id));

			if($adb->num_rows($result)){
				$data = $adb->raw_query_result_rowdata($result, 0);
				$workflow = $this->getWorkflowInstance($data['type']);
				$workflow->setup($data);
				return $workflow;
			}else{
				return null;
			}
		}

		function delete($id){
			$adb=$this->adb;
			$adb->pquery("DELETE FROM com_vtiger_workflowtasks WHERE workflow_id IN
							(SELECT workflow_id FROM com_vtiger_workflows WHERE workflow_id=? AND (defaultworkflow IS NULL OR defaultworkflow != 1))",
						array($id));
			$adb->pquery("DELETE FROM com_vtiger_workflows WHERE workflow_id=? AND (defaultworkflow IS NULL OR defaultworkflow != 1)", array($id));
		}

		function newWorkflow($moduleName){
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
		public function serializeWorkflow($workflow){
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
			foreach($tasks as $task){
				unset($task->id);
				unset($task->workflowId);
				$exp['tasks'][] = serialize($task);
			}
			return  Zend_Json::encode($exp);
		}

		/**
		 * Import a json encoded string as a workflow object
		 *
		 * @return The Workflow instance representing the imported workflow.
		 */
		public function deserializeWorkflow($str){
			$data =  Zend_Json::decode($str);
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
			foreach($tasks as $taskStr){
				$task = $tm->unserializeTask($taskStr);
				$task->workflowId = $workflow->id;
				$tm->saveTask($task);
			}
			return $workflow;
		}
		/**
		 * Update the Next trigger timestamp for a workflow
		 */
		public function updateNexTriggerTime($workflow) {
			$nextTriggerTime = $workflow->getNextTriggerTime();
			$workflow->setNextTriggerTime($nextTriggerTime);
		}

		/**
		 * Function to get workflows modules those are supporting comments
		 * @param <String> $moduleName
		 * @return <Array> list of Workflow models
		 */
		public function getWorkflowsForModuleSupportingComments($moduleName) {
			$adb = $this->adb;
			$cache = Vtiger_Cache::getInstance();

			if($cache->getWorkflowForModuleSupportingComments($moduleName)) {
				return $cache->getWorkflowForModuleSupportingComments($moduleName);
			}

			$result = $adb->getColumnNames('com_vtiger_workflows');
			if(in_array('defaultworkflow', $result)) {
				$result = $adb->pquery('SELECT workflow_id, module_name, summary, test, execution_condition, defaultworkflow, type, filtersavedinnew
									FROM com_vtiger_workflows WHERE module_name = ? AND test LIKE (?)', array($moduleName, '%_VT_add_comment%'));
			} else {
				$result = $adb->pquery('SELECT workflow_id, module_name, summary, test, execution_condition, type, filtersavedinnew
									FROM com_vtiger_workflows where module_name = ? AND test LIKE (?)', array($moduleName, '%_VT_add_comment%'));
			}
			$workflowModels = $this->getWorkflowsForResult($result);

			$commentSupportedWorkflowModels = array();
			foreach ($workflowModels as $workflowId => $workflowModel) {
				$conditions = Zend_Json::decode($workflowModel->test);
				if (is_array($conditions)) {
					foreach ($conditions as $key => $conditionInfo) {
						if ($conditionInfo['fieldname'] === '_VT_add_comment') {
							unset($conditions[$key]);
							$workflowModel->test = Zend_Json::encode($conditions);
							$commentSupportedWorkflowModels[$workflowId] = $workflowModel;
						}
					}
				}

			}
			$cache->setWorkflowForModuleSupportingComments($moduleName, $commentSupportedWorkflowModels);
			return $commentSupportedWorkflowModels;
		}
	}

	class Workflow{
		static $SCHEDULED_HOURLY = 1;
		static $SCHEDULED_DAILY = 2;
		static $SCHEDULED_WEEKLY = 3;
		static $SCHEDULED_ON_SPECIFIC_DATE = 4;
		static $SCHEDULED_MONTHLY_BY_DATE = 5;
		static $SCHEDULED_MONTHLY_BY_WEEKDAY = 6;
		static $SCHEDULED_ANNUALLY = 7;

		function __construct(){
			$this->conditionStrategy = new VTJsonCondition();
		}

		function setup($row) {
			$this->id = $row['workflow_id'];
			$this->moduleName = $row['module_name'];
			$this->description = to_html($row['summary']);
			$this->test = $row['test'];
			$this->executionCondition = $row['execution_condition'];
			$this->schtypeid = $row['schtypeid'];
			$this->schtime = $row['schtime'];
			$this->schdayofmonth = $row['schdayofmonth'];
			$this->schdayofweek = $row['schdayofweek'];
			$this->schannualdates = $row['schannualdates'];
			if($row['defaultworkflow']){
				$this->defaultworkflow=$row['defaultworkflow'];
			}
			$this->filtersavedinnew = $row['filtersavedinnew'];
			$this->nexttrigger_time = $row['nexttrigger_time'];
		}

		function evaluate($entityCache, $id){
			if($this->test==""){
				return true;
			}else{
				$cs = $this->conditionStrategy;
				return $cs->evaluate($this->test,
									 $entityCache, $id);
			}
		}

		function isCompletedForRecord($recordId) {
			global $adb;

			$result = $adb->pquery("SELECT * FROM com_vtiger_workflow_activatedonce
							WHERE entity_id=? and workflow_id=?", array($recordId, $this->id));

			$result2=$adb->pquery("SELECT * FROM com_vtiger_workflowtasks
							INNER JOIN com_vtiger_workflowtask_queue
							ON com_vtiger_workflowtasks.task_id= com_vtiger_workflowtask_queue.task_id
							WHERE workflow_id=? AND entity_id=?",
							array($this->id,$recordId));

			if($adb->num_rows($result)===0 && $adb->num_rows($result2)===0) { // Workflow not done for specified record
				return false;
			} else {
				return true;
			}
		}

		function markAsCompletedForRecord($recordId) {
			global $adb;

			$adb->pquery("INSERT INTO com_vtiger_workflow_activatedonce (entity_id, workflow_id)
				VALUES (?,?)", array($recordId, $this->id));
		}

		function performTasks($entityData) {
			global $adb;
			$data = $entityData->getData();

			require_once('modules/com_vtiger_workflow/VTTaskManager.inc');
			require_once('modules/com_vtiger_workflow/VTTaskQueue.inc');

			$tm = new VTTaskManager($adb);
			$taskQueue = new VTTaskQueue($adb);
			$tasks = $tm->getTasksForWorkflow($this->id);

			foreach($tasks as $task){
				if($task->active) {
					$trigger = $task->trigger;
					if($trigger != null){
						$delay = strtotime($data[$trigger['field']])+$trigger['days']*86400;
					}else{
						$delay = 0;
					}
					if($task->executeImmediately==true){
						$task->doTask($entityData);
					} else {
						$hasContents = $task->hasContents($entityData);
						if ($hasContents) {
							$taskQueue->queueTask($task->id,$entityData->getId(), $delay, $task->getContents($entityData));
						}
					}
				}
			}
		}

		function executionConditionAsLabel($label=null){
			if($label==null){
				$arr = array('ON_FIRST_SAVE', 'ONCE', 'ON_EVERY_SAVE', 'ON_MODIFY', '', '', 'MANUAL');
				return $arr[$this->executionCondition-1];
			}else{
				$arr = array('ON_FIRST_SAVE'=>1, 'ONCE'=>2, 'ON_EVERY_SAVE'=>3, 'ON_MODIFY'=>4, 'MANUAL'=>7);
				$this->executionCondition = $arr[$label];
			}
		}
		function setNextTriggerTime($time) {
			if($time) {
				$db = PearDatabase::getInstance();
				$db->pquery("UPDATE com_vtiger_workflows SET nexttrigger_time=? WHERE workflow_id=?", array($time, $this->id));
				$this->nexttrigger_time = $time;
			}
		}

		function getNextTriggerTimeValue() {
			return $this->nexttrigger_time;
		}

		function getWFScheduleType(){
			return ($this->executionCondition == 6 ? $this->schtypeid : 0);
		}

		function getWFScheduleTime(){
			return $this->schtime;
		}

		function getWFScheduleDay(){
			return $this->schdayofmonth;
		}

		function getWFScheduleWeek(){
			return $this->schdayofweek;
		}

		function getWFScheduleAnnualDates(){
			return $this->schannualdates;
		}

		/**
		 * Function gets the next trigger for the workflows
		 * @global <String> $default_timezone
		 * @return type
		 */
		function getNextTriggerTime() {
			global $default_timezone;
			$admin = Users::getActiveAdminUser();
			$adminTimeZone = $admin->time_zone;
			@date_default_timezone_set($adminTimeZone);

			$scheduleType = $this->getWFScheduleType();
			$nextTime = null;

			if($scheduleType == Workflow::$SCHEDULED_HOURLY) {
				$nextTime = date("Y-m-d H:i:s",strtotime("+1 hour"));
			}

			if($scheduleType == Workflow::$SCHEDULED_DAILY) {
				$nextTime = $this->getNextTriggerTimeForDaily($this->getWFScheduleTime());
			}

			if($scheduleType == Workflow::$SCHEDULED_WEEKLY) {
				$nextTime = $this->getNextTriggerTimeForWeekly($this->getWFScheduleWeek(), $this->getWFScheduleTime());
			}

			if($scheduleType == Workflow::$SCHEDULED_ON_SPECIFIC_DATE) {
				$nextTime = date('Y-m-d H:i:s', strtotime('+10 year'));
			}

			if($scheduleType == Workflow::$SCHEDULED_MONTHLY_BY_DATE) {
				$nextTime = $this->getNextTriggerTimeForMonthlyByDate($this->getWFScheduleDay(), $this->getWFScheduleTime());
			}

			if($scheduleType == Workflow::$SCHEDULED_MONTHLY_BY_WEEKDAY) {
				$nextTime = $this->getNextTriggerTimeForMonthlyByWeekDay($this->getWFScheduleDay(), $this->getWFScheduleTime());
			}

			if($scheduleType == Workflow::$SCHEDULED_ANNUALLY) {
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
		function getNextTriggerTimeForDaily($scheduledTime) {
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
		function getNextTriggerTimeForWeekly($scheduledDaysOfWeek, $scheduledTime) {
			$weekDays = array('1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday');
			$currentTime = time();
			$currentWeekDay = date('N', $currentTime);
			if ($scheduledDaysOfWeek) {
				$scheduledDaysOfWeek = Zend_Json::decode($scheduledDaysOfWeek);
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
		function getNextTriggerTimeForMonthlyByDate($scheduledDayOfMonth, $scheduledTime){
			$currentDayOfMonth = date('j', time());
			if($scheduledDayOfMonth) {
				$scheduledDaysOfMonth = Zend_Json::decode($scheduledDayOfMonth);
				if(is_array($scheduledDaysOfMonth)) {
					// algorithm :
					//1. First sort all the days in ascending order and find the closest day which is greater than currentDayOfMonth
					//2. If found, set the next trigger date to the found value which is in the same month.
					//3. If not found, set the trigger date to the next month's first selected value.
					$nextTriggerDay = null;
					sort($scheduledDaysOfMonth);
					foreach ($scheduledDaysOfMonth as $day) {
						if($day == $currentDayOfMonth) {
							$currentTime = time();
							$schTime = strtotime($date = date('Y').'-'.date('m').'-'.$day.' '.$scheduledTime);
							if($schTime > $currentTime) {
								$nextTriggerDay = $day;
								break;
							}
						} elseif ($day > $currentDayOfMonth) {
							$nextTriggerDay = $day;
							break;
						}
					}
					if(!empty($nextTriggerDay)) {
						$firstDayofNextMonth = date('Y:m:d H:i:s', strtotime('first day of this month'));
						$nextTime = date('Y:m:d', strtotime($firstDayofNextMonth.' + '.($nextTriggerDay-1).' days'));
						$nextTime = $nextTime.' '.$scheduledTime;
					} else {
						$firstDayofNextMonth = date('Y:m:d H:i:s', strtotime('first day of next month'));
						$nextTime = date('Y:m:d', strtotime($firstDayofNextMonth.' + '.($scheduledDaysOfMonth[0]-1).' days'));
						$nextTime = $nextTime.' '.$scheduledTime;
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
		function getNextTriggerTimeForMonthlyByWeekDay($scheduledWeekDayOfMonth, $scheduledTime){
			$currentTime = time();
			$currentDayOfMonth = date('j',$currentTime);
			$scheduledTime = $this->getWFScheduleTime();
			if($scheduledWeekDayOfMonth == $currentDayOfMonth) {
				$nextTime = date("Y-m-d H:i:s",strtotime('+1 month '.$scheduledTime));
			} else {
				$monthInFullText = date('F',$currentTime);
				$yearFullNumberic = date('Y',$currentTime);
				if($scheduledWeekDayOfMonth < $currentDayOfMonth) {
					$nextMonth = date("Y-m-d H:i:s",strtotime('next month'));
					$monthInFullText = date('F',strtotime($nextMonth));
				}
				$nextTime = date("Y-m-d H:i:s",strtotime($scheduledWeekDayOfMonth.' '.$monthInFullText.' '.$yearFullNumberic.' '.$scheduledTime));
			}
			return $nextTime;
		}
		
		/**
		 * to get next trigger time
		 * @param type $annualDates
		 * @param type $scheduledTime
		 * @return <time>
		 */
		function getNextTriggerTimeForAnnualDates($annualDates, $scheduledTime){
			if($annualDates) {
				$today = date('Y-m-d');
				$annualDates = Zend_Json::decode($annualDates);
				$nextTriggerDay = null;
				// sort the dates
				sort($annualDates);
				$currentTime = time();
				$currentDayOfMonth = date('Y-m-d',$currentTime);
				foreach ($annualDates as $day) {
					if($day == $currentDayOfMonth) {
						$schTime = strtotime($day.' '.$scheduledTime);
						if($schTime > $currentTime) {
							$nextTriggerDay = $day;
							break;
						}
					}else if ($day > $today) {
						$nextTriggerDay = $day;
						break;
					}
				}
				if(!empty($nextTriggerDay)) {
					$nextTime = date('Y:m:d H:i:s', strtotime($nextTriggerDay.' '.$scheduledTime));
				} else {
					$nextTriggerDay = $annualDates[0];
					$nextTime = date('Y:m:d H:i:s', strtotime($nextTriggerDay.' '.$scheduledTime.'+1 year'));
				}
			}
			return $nextTime;
		}
	}
?>
