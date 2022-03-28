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

/**
 * Workflow class.
 */
class Workflow
{
	/**
	 * Scheduled hourly.
	 *
	 * @var int
	 */
	public static $SCHEDULED_HOURLY = 1;

	/**
	 * Scheduled daily.
	 *
	 * @var int
	 */
	public static $SCHEDULED_DAILY = 2;

	/**
	 * Scheduled weekly.
	 *
	 * @var int
	 */
	public static $SCHEDULED_WEEKLY = 3;

	/**
	 * Scheduled on specific date.
	 *
	 * @var int
	 */
	public static $SCHEDULED_ON_SPECIFIC_DATE = 4;

	/**
	 * Scheduled monthly by date.
	 *
	 * @var int
	 */
	public static $SCHEDULED_MONTHLY_BY_DATE = 5;

	/**
	 * Scheduled monthly by weekday.
	 *
	 * @var int
	 */
	public static $SCHEDULED_MONTHLY_BY_WEEKDAY = 6;

	/**
	 * Scheduled annually.
	 *
	 * @var int
	 */
	public static $SCHEDULED_ANNUALLY = 7;

	/**
	 * Scheduled hourly.
	 *
	 * @var int
	 */
	public static $SCHEDULED_30_MINUTES = 8;
	/**
	 * Scheduled hourly.
	 *
	 * @var int
	 */
	public static $SCHEDULED_15_MINUTES = 9;

	/**
	 * Scheduled hourly.
	 *
	 * @var int
	 */
	public static $SCHEDULED_5_MINUTES = 10;

	/**
	 * Scheduled closest working day.
	 *
	 * @var int
	 */
	public static $SCHEDULED_WORKINGDAY_DAY = 11;

	/**
	 * Scheduled first working day in week.
	 *
	 * @var int
	 */
	public static $SCHEDULED_WORKINGDAY_WEEK = 12;

	/**
	 * Scheduled first working day in month.
	 *
	 * @var int
	 */
	public static $SCHEDULED_WORKINGDAY_MONTH = 13;

	/**
	 * Scheduled list.
	 *
	 * @var int[]
	 */
	public static $SCHEDULED_LIST = [
		10 => 'LBL_5_MINUTES',
		9 => 'LBL_15_MINUTES',
		8 => 'LBL_30_MINUTES',
		1 => 'LBL_HOURLY',
		2 => 'LBL_DAILY',
		3 => 'LBL_WEEKLY',
		4 => 'LBL_SPECIFIC_DATE',
		5 => 'LBL_MONTHLY_BY_DATE',
		6 => 'LBL_MONTHLY_BY_WEEKDAY',
		7 => 'LBL_YEARLY',
		11 => 'LBL_WORKINGDAY_DAY',
		12 => 'LBL_WORKINGDAY_WEEK',
		13 => 'LBL_WORKINGDAY_MONTH',
	];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->conditionStrategy = new VTJsonCondition();
	}

	/**
	 * Setup workflow.
	 *
	 * @param array $row
	 */
	public function setup($row)
	{
		$this->id = $row['workflow_id'] ?? '';
		$this->moduleName = $row['module_name'] ?? '';
		$this->description = $row['summary'] ?? '';
		$this->test = $row['test'] ?? '';
		$this->executionCondition = $row['execution_condition'] ?? '';
		$this->schtypeid = $row['schtypeid'] ?? '';
		$this->schtime = $row['schtime'] ?? '';
		$this->schdayofmonth = $row['schdayofmonth'] ?? '';
		$this->schdayofweek = $row['schdayofweek'] ?? '';
		$this->schannualdates = $row['schannualdates'] ?? '';
		if (isset($row['defaultworkflow'])) {
			$this->defaultworkflow = $row['defaultworkflow'];
		}
		$this->filtersavedinnew = $row['filtersavedinnew'] ?? '';
		$this->nexttrigger_time = $row['nexttrigger_time'] ?? '';
		$this->params = $row['params'] ?? '';
	}

	/**
	 * Evaluate.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	public function evaluate($recordModel)
	{
		if ('' == $this->test) {
			return true;
		}
		return $this->conditionStrategy->evaluate($this->test, $recordModel);
	}

	/**
	 * Check if workfow is completed for record.
	 *
	 * @param int $recordId
	 *
	 * @return bool
	 */
	public function isCompletedForRecord($recordId)
	{
		$isExistsActivateDonce = (new \App\Db\Query())->from('com_vtiger_workflow_activatedonce')->where(['entity_id' => $recordId, 'workflow_id' => $this->id])->exists();
		$isExistsWorkflowTasks = (new \App\Db\Query())->from('com_vtiger_workflowtasks')
			->innerJoin('com_vtiger_workflowtask_queue', 'com_vtiger_workflowtasks.task_id= com_vtiger_workflowtask_queue.task_id')
			->where(['entity_id' => $recordId, 'workflow_id' => $this->id])->exists();

		if (!$isExistsActivateDonce && !$isExistsWorkflowTasks) { // Workflow not done for specified record
			return false;
		}
		return true;
	}

	/**
	 * Mark workflow as completed for record.
	 *
	 * @param int $recordId
	 */
	public function markAsCompletedForRecord($recordId)
	{
		\App\Db::getInstance()->createCommand()
			->insert('com_vtiger_workflow_activatedonce', [
				'entity_id' => $recordId,
				'workflow_id' => $this->id,
			])->execute();
	}

	/**
	 * Perform tasks.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param array|null          $tasks
	 */
	public function performTasks(Vtiger_Record_Model $recordModel, ?array $tasks = null)
	{
		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		require_once 'modules/com_vtiger_workflow/VTTaskQueue.php';

		$tm = new VTTaskManager();
		$taskQueue = new VTTaskQueue();
		foreach ($tm->getTasksForWorkflow($this->id) as $task) {
			if ($task->active && (null === $tasks || \in_array($task->id, $tasks))) {
				$trigger = $task->trigger;
				if (null !== $trigger) {
					$delay = strtotime($recordModel->get($trigger['field'])) + $trigger['days'] * 86400;
				} else {
					$delay = 0;
				}
				if (true === (bool) $task->executeImmediately) {
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

	/**
	 * Execution condition as label.
	 *
	 * @param string $label
	 *
	 * @return string
	 */
	public function executionConditionAsLabel($label = null)
	{
		if (null === $label) {
			$arr = ['ON_FIRST_SAVE', 'ONCE', 'ON_EVERY_SAVE', 'ON_MODIFY', 'ON_DELETE', 'ON_SCHEDULE', 'MANUAL', 'TRIGGER', 'BLOCK_EDIT', 'ON_RELATED'];

			return $arr[$this->executionCondition - 1];
		}
		$arr = ['ON_FIRST_SAVE' => 1, 'ONCE' => 2, 'ON_EVERY_SAVE' => 3, 'ON_MODIFY' => 4,
			'ON_DELETE' => 5, 'ON_SCHEDULE' => 6, 'MANUAL' => 7, 'TRIGGER' => 8, 'BLOCK_EDIT' => 9, 'ON_RELATED' => 10, ];
		$this->executionCondition = $arr[$label];
	}

	/**
	 * Sets next trigger time.
	 *
	 * @param timestamp $time
	 */
	public function setNextTriggerTime($time)
	{
		if ($time) {
			\App\Db::getInstance()->createCommand()->update('com_vtiger_workflows', ['nexttrigger_time' => $time], ['workflow_id' => $this->id])->execute();
			$this->nexttrigger_time = $time;
		}
	}

	/**
	 * Return next trigger timestamp.
	 *
	 * @return timestamp
	 */
	public function getNextTriggerTimeValue()
	{
		return $this->nexttrigger_time;
	}

	/**
	 * Return schedule type.
	 *
	 * @return int
	 */
	public function getWFScheduleType()
	{
		return 6 == $this->executionCondition ? $this->schtypeid : 0;
	}

	/**
	 * Return workflow schedule timestamp.
	 *
	 * @return timestamp
	 */
	public function getWFScheduleTime()
	{
		return $this->schtime;
	}

	/**
	 * Return workflow schedule timestamp in user format.
	 *
	 * @return string
	 */
	public function getWFScheduleTimeUserFormat()
	{
		return (new DateTimeField($this->schtime))->getDisplayTime();
	}

	/**
	 * Return workflow schedule day.
	 *
	 * @return int
	 */
	public function getWFScheduleDay()
	{
		return $this->schdayofmonth;
	}

	/**
	 * Return workflow schedule week.
	 *
	 * @return int
	 */
	public function getWFScheduleWeek()
	{
		return $this->schdayofweek;
	}

	/**
	 * Return workflow schedule annual dates.
	 *
	 * @return bool
	 */
	public function getWFScheduleAnnualDates()
	{
		return $this->schannualdates;
	}

	/**
	 * Function gets the next trigger for the workflows.
	 *
	 * @global string $default_timezone
	 *
	 * @return timestamp
	 */
	public function getNextTriggerTime()
	{
		$default_timezone = \App\Config::main('default_timezone');
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		date_default_timezone_set($adminTimeZone);
		$nextTime = null;
		switch ($this->getWFScheduleType()) {
			case self::$SCHEDULED_5_MINUTES:
				$nextTime = date('Y-m-d H:i:s', strtotime('+5 minutes'));
			break;
			case self::$SCHEDULED_15_MINUTES:
					$nextTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
				break;
			case self::$SCHEDULED_30_MINUTES:
				$nextTime = date('Y-m-d H:i:s', strtotime('+30 minutes'));
			break;
			case self::$SCHEDULED_HOURLY:
					$nextTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
				break;
			case self::$SCHEDULED_DAILY:
					$nextTime = $this->getNextTriggerTimeForDaily($this->getWFScheduleTime());
				break;
			case self::$SCHEDULED_WEEKLY:
					$nextTime = $this->getNextTriggerTimeForWeekly($this->getWFScheduleWeek(), $this->getWFScheduleTime());
				break;
			case self::$SCHEDULED_ON_SPECIFIC_DATE:
					$nextTime = date('Y-m-d H:i:s', strtotime('+10 year'));
				break;
			case self::$SCHEDULED_MONTHLY_BY_DATE:
					$nextTime = $this->getNextTriggerTimeForMonthlyByDate($this->getWFScheduleDay(), $this->getWFScheduleTime());
				break;
			case self::$SCHEDULED_MONTHLY_BY_WEEKDAY:
					$nextTime = $this->getNextTriggerTimeForMonthlyByWeekDay($this->getWFScheduleDay(), $this->getWFScheduleTime());
				break;
			case self::$SCHEDULED_ANNUALLY:
					$nextTime = $this->getNextTriggerTimeForAnnualDates($this->getWFScheduleAnnualDates(), $this->getWFScheduleTime());
				break;
			case self::$SCHEDULED_WORKINGDAY_DAY:
					$nextTime = $this->getNextTriggerTimeForDaily($this->getWFScheduleTime());
					$firstWorkingDay = new DateTime($nextTime);
					$nextTime = \App\Fields\Date::getWorkingDayFromDate($firstWorkingDay, '+0 day') . ' ' . $this->getWFScheduleTime();
				break;
			case self::$SCHEDULED_WORKINGDAY_WEEK:
					$firstDayNextWeek = new DateTime('monday next week');
					$nextTime = \App\Fields\Date::getWorkingDayFromDate($firstDayNextWeek, '+0 day') . ' ' . $this->getWFScheduleTime();
				break;
			case self::$SCHEDULED_WORKINGDAY_MONTH:
					$firstDayNextMonth = new DateTime('first day of next month');
					$nextTime = \App\Fields\Date::getWorkingDayFromDate($firstDayNextMonth, '+0 day') . ' ' . $this->getWFScheduleTime();
				break;
		}
		date_default_timezone_set($default_timezone);
		return $nextTime;
	}

	/**
	 * get next trigger time for daily.
	 *
	 * @param type  $schTime
	 * @param mixed $scheduledTime
	 *
	 * @return time
	 */
	public function getNextTriggerTimeForDaily($scheduledTime)
	{
		$now = strtotime(date('Y-m-d H:i:s'));
		$todayScheduledTime = strtotime(date('Y-m-d H:i:s', strtotime($scheduledTime)));
		if ($now > $todayScheduledTime) {
			$nextTime = date('Y-m-d H:i:s', strtotime('+1 day ' . $scheduledTime));
		} else {
			$nextTime = date('Y-m-d H:i:s', $todayScheduledTime);
		}
		return $nextTime;
	}

	/**
	 * get next trigger Time For weekly.
	 *
	 * @param json $scheduledDaysOfWeek
	 * @param time $scheduledTime
	 *
	 * @return time
	 */
	public function getNextTriggerTimeForWeekly($scheduledDaysOfWeek, $scheduledTime)
	{
		$weekDays = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday'];
		$currentTime = time();
		$currentWeekDay = date('N', $currentTime);
		if ($scheduledDaysOfWeek) {
			$scheduledDaysOfWeek = \App\Json::decode($scheduledDaysOfWeek);
			if (\is_array($scheduledDaysOfWeek)) {
				/*
				  algorithm :
				  1. First sort all the weekdays(stored as 0,1,2,3 etc in db) and find the closest weekday which is greater than currentWeekDay
				  2. If found, set the next trigger date to the next weekday value in the same week.
				  3. If not found, set the trigger date to the next first value.
				 */
				$nextTriggerWeekDay = null;
				sort($scheduledDaysOfWeek);
				foreach ($scheduledDaysOfWeek as $index => $weekDay) {
					if ($weekDay == $currentWeekDay) { //if today is the weekday selected
						$scheduleWeekDayInTime = strtotime(date('Y-m-d', strtotime($weekDays[$currentWeekDay])) . ' ' . $scheduledTime);
						if ($currentTime < $scheduleWeekDayInTime) { //if the scheduled time is greater than current time, selected today
							$nextTriggerWeekDay = $weekDay;
							break;
						}
						//current time greater than scheduled time, get the next weekday
							if (1 == \count($scheduledDaysOfWeek)) { //if only one weekday selected, then get next week
								$nextTime = date('Y-m-d', strtotime('next ' . $weekDays[$weekDay])) . ' ' . $scheduledTime;
							} else {
								$nextWeekDay = $scheduledDaysOfWeek[$index + 1]; // its the last day of the week i.e. sunday
								if (empty($nextWeekDay)) {
									$nextWeekDay = $scheduledDaysOfWeek[0];
								}
								$nextTime = date('Y-m-d', strtotime('next ' . $weekDays[$nextWeekDay])) . ' ' . $scheduledTime;
							}
					} elseif ($weekDay > $currentWeekDay) {
						$nextTriggerWeekDay = $weekDay;
						break;
					}
				}

				if (!isset($nextTime)) {
					if (!empty($nextTriggerWeekDay)) {
						$nextTime = date('Y-m-d H:i:s', strtotime($weekDays[$nextTriggerWeekDay] . ' ' . $scheduledTime));
					} else {
						$nextTime = date('Y-m-d H:i:s', strtotime($weekDays[$scheduledDaysOfWeek[0]] . ' ' . $scheduledTime));
					}
				}
			}
		}
		return $nextTime;
	}

	/**
	 * get next triggertime for monthly.
	 *
	 * @param int $scheduledDayOfMonth
	 * @param int $scheduledTime
	 *
	 * @return time
	 */
	public function getNextTriggerTimeForMonthlyByDate($scheduledDayOfMonth, $scheduledTime)
	{
		$currentDayOfMonth = date('j', time());
		if ($scheduledDayOfMonth) {
			$scheduledDaysOfMonth = \App\Json::decode($scheduledDayOfMonth);
			if (\is_array($scheduledDaysOfMonth)) {
				/*
				  algorithm :
				  1. First sort all the days in ascending order and find the closest day which is greater than currentDayOfMonth
				  2. If found, set the next trigger date to the found value which is in the same month.
				  3. If not found, set the trigger date to the next month's first selected value.
				 */
				$nextTriggerDay = null;
				sort($scheduledDaysOfMonth);
				foreach ($scheduledDaysOfMonth as $day) {
					if ($day == $currentDayOfMonth) {
						$currentTime = time();
						$schTime = strtotime(date('Y') . '-' . date('m') . '-' . $day . ' ' . $scheduledTime);
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
	 * to get next trigger time for weekday of the month.
	 *
	 * @param int       $scheduledWeekDayOfMonth
	 * @param timestamp $scheduledTime
	 *
	 * @return time
	 */
	public function getNextTriggerTimeForMonthlyByWeekDay($scheduledWeekDayOfMonth, $scheduledTime)
	{
		$currentTime = time();
		$currentDayOfMonth = date('j', $currentTime);
		$scheduledTime = $this->getWFScheduleTime();
		if ($scheduledWeekDayOfMonth == $currentDayOfMonth) {
			$nextTime = date('Y-m-d H:i:s', strtotime('+1 month ' . $scheduledTime));
		} else {
			$monthInFullText = date('F', $currentTime);
			$yearFullNumberic = date('Y', $currentTime);
			if ($scheduledWeekDayOfMonth < $currentDayOfMonth) {
				$nextMonth = date('Y-m-d H:i:s', strtotime('first day of next month'));
				$monthInFullText = date('F', strtotime($nextMonth));
			}
			$nextTime = date('Y-m-d H:i:s', strtotime($scheduledWeekDayOfMonth . ' ' . $monthInFullText . ' ' . $yearFullNumberic . ' ' . $scheduledTime));
		}
		return $nextTime;
	}

	/**
	 * to get next trigger time.
	 *
	 * @param json      $annualDates
	 * @param timestamp $scheduledTime
	 *
	 * @return time
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
				} elseif ($day > $today) {
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
