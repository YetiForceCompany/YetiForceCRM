<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'modules/Reports/Reports.php';
require_once 'modules/Reports/ReportRun.php';

class VTScheduledReport extends Reports
{

	public $db;
	public $user;
	public $isScheduled = false;
	public $scheduledInterval = null;
	public $scheduledFormat = null;
	public $scheduledRecipients = null;
	public static $SCHEDULED_HOURLY = 1;
	public static $SCHEDULED_DAILY = 2;
	public static $SCHEDULED_WEEKLY = 3;
	public static $SCHEDULED_BIWEEKLY = 4;
	public static $SCHEDULED_MONTHLY = 5;
	public static $SCHEDULED_ANNUALLY = 6;

	public function __construct($adb, $user, $reportid = "")
	{
		$this->db = $adb;
		$this->user = $user;
		$this->id = $reportid;
		parent::__construct($reportid);
	}

	public function getReportScheduleInfo()
	{
		$adb = PearDatabase::getInstance();

		if (!empty($this->id)) {
			$cachedInfo = VTCacheUtils::lookupReportScheduledInfo($this->user->id, $this->id);

			if ($cachedInfo === false) {
				$result = $adb->pquery('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', [$this->id]);

				if ($adb->numRows($result) > 0) {
					$reportScheduleInfo = $adb->rawQueryResultRowData($result, 0);

					$scheduledInterval = (!empty($reportScheduleInfo['schedule'])) ? \App\Json::decode($reportScheduleInfo['schedule']) : [];
					$scheduledRecipients = (!empty($reportScheduleInfo['recipients'])) ? \App\Json::decode($reportScheduleInfo['recipients']) : [];

					VTCacheUtils::updateReportScheduledInfo($this->user->id, $this->id, true, $reportScheduleInfo['format'], $scheduledInterval, $scheduledRecipients, $reportScheduleInfo['next_trigger_time']);

					$cachedInfo = VTCacheUtils::lookupReportScheduledInfo($this->user->id, $this->id);
				}
			}
			if ($cachedInfo) {
				$this->isScheduled = $cachedInfo['isScheduled'];
				$this->scheduledFormat = $cachedInfo['scheduledFormat'];
				$this->scheduledInterval = $cachedInfo['scheduledInterval'];
				$this->scheduledRecipients = $cachedInfo['scheduledRecipients'];
				$this->scheduledTime = $cachedInfo['scheduledTime'];
				return true;
			}
		}
		return false;
	}

	/**
	 * Get recipient emails
	 * @return array
	 */
	public function getRecipientEmails()
	{
		$recipientsInfo = $this->scheduledRecipients;

		$recipientsList = [];
		if (!empty($recipientsInfo)) {
			if (!empty($recipientsInfo['users'])) {
				$recipientsList = array_merge($recipientsList, $recipientsInfo['users']);
			}
			if (!empty($recipientsInfo['roles'])) {
				foreach ($recipientsInfo['roles'] as $roleId) {
					$roleUsers = \App\PrivilegeUtil::getUsersNameByRole($roleId);
					foreach ($roleUsers as $userId => $userName) {
						array_push($recipientsList, $userId);
					}
				}
			}
			if (!empty($recipientsInfo['rs'])) {
				foreach ($recipientsInfo['rs'] as $roleId) {
					foreach (\App\PrivilegeUtil::getUsersByRoleAndSubordinate($roleId) as $userId) {
						array_push($recipientsList, $userId);
					}
				}
			}
			if (!empty($recipientsInfo['groups'])) {
				foreach ($recipientsInfo['groups'] as $groupId) {
					$recipientsList = array_merge($recipientsList, App\PrivilegeUtil::getUsersByGroup($groupId));
				}
			}
		}
		$recipientsEmails = [];
		if (!empty($recipientsList) && count($recipientsList) > 0) {
			foreach ($recipientsList as $userId) {
				$userName = \App\Fields\Owner::getUserLabel($userId);
				$userEmail = \App\User::getUserModel($userId)->getDetail('email1');
				if (!in_array($userEmail, $recipientsEmails)) {
					$recipientsEmails[$userName] = $userEmail;
				}
			}
		}
		return $recipientsEmails;
	}

	public function sendEmail()
	{
		$recipientEmails = $this->getRecipientEmails();
		$to = [];
		foreach ($recipientEmails as $name => $email) {
			$to[$email] = $name;
		}

		$currentTime = date('Y-m-d H:i:s');
		$subject = $this->reportname . ' - ' . $currentTime . ' (' . App\Fields\DateTime::getTimeZone() . ')';

		$contents = \App\Language::translate('LBL_AUTO_GENERATED_REPORT_EMAIL', 'Reports') . '<br /><br />';
		$contents .= '<b>' . \App\Language::translate('LBL_REPORT_NAME', 'Reports') . ' :</b> ' . $this->reportname . '<br />';
		$contents .= '<b>' . \App\Language::translate('LBL_DESCRIPTION', 'Reports') . ' :</b><br />' . $this->reportdescription . '<br /><br />';

		$baseFileName = preg_replace('/[^a-zA-Z0-9_-\s]/', '', $this->reportname) . '__' . preg_replace('/[^a-zA-Z0-9_-\s]/', '', $currentTime);

		$oReportRun = ReportRun::getInstance($this->id);
		$reportFormat = $this->scheduledFormat;
		$attachments = [];
		if ($reportFormat === 'pdf' || $reportFormat === 'both') {
			$fileName = $baseFileName . '.pdf';
			$filePath = 'storage/' . $fileName;
		}
		if ($reportFormat === 'excel' || $reportFormat === 'both') {
			$fileName = $baseFileName . '.xls';
			$filePath = 'storage/' . $fileName;
			$attachments[$filePath] = $fileName;
			$oReportRun->writeReportToExcelFile($filePath);
		}
		\App\Mailer::addMail([
			'to' => $to,
			'subject' => $subject,
			'content' => $contents,
			'attachments' => $attachments,
		]);
		foreach ($attachments as $path => $attachmentName) {
			unlink($path);
		}
	}

	public function getNextTriggerTime()
	{
		$scheduleInfo = $this->scheduledInterval;

		$scheduleType = $scheduleInfo['scheduletype'];
		$scheduledMonth = $scheduleInfo['month'];
		$scheduledDayOfMonth = $scheduleInfo['date'];
		$scheduledDayOfWeek = $scheduleInfo['day'];
		$scheduledTime = $scheduleInfo['time'];
		if (empty($scheduledTime)) {
			$scheduledTime = '10:00';
		} elseif (stripos(':', $scheduledTime) === false) {
			$scheduledTime = $scheduledTime . ':00';
		}

		if ($scheduleType == VTScheduledReport::$SCHEDULED_HOURLY) {
			return date("Y-m-d H:i:s", strtotime("+1 hour"));
		}
		if ($scheduleType == VTScheduledReport::$SCHEDULED_DAILY) {
			return date("Y-m-d H:i:s", strtotime("+ 1 day " . $scheduledTime));
		}
		if ($scheduleType == VTScheduledReport::$SCHEDULED_WEEKLY) {
			$weekDays = ['0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'];

			if (date('w', time()) == $scheduledDayOfWeek) {
				return date("Y-m-d H:i:s", strtotime('+1 week ' . $scheduledTime));
			} else {
				return date("Y-m-d H:i:s", strtotime($weekDays[$scheduledDayOfWeek] . ' ' . $scheduledTime));
			}
		}
		if ($scheduleType == VTScheduledReport::$SCHEDULED_BIWEEKLY) {
			$weekDays = ['0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'];
			if (date('w', time()) == $scheduledDayOfWeek) {
				return date("Y-m-d H:i:s", strtotime('+2 weeks ' . $scheduledTime));
			} else {
				return date("Y-m-d H:i:s", strtotime($weekDays[$scheduledDayOfWeek] . ' ' . $scheduledTime));
			}
		}
		if ($scheduleType == VTScheduledReport::$SCHEDULED_MONTHLY) {
			$currentTime = time();
			$currentDayOfMonth = date('j', $currentTime);

			if ($scheduledDayOfMonth == $currentDayOfMonth) {
				return date("Y-m-d H:i:s", strtotime('+1 month ' . $scheduledTime));
			} else {
				$monthInFullText = date('F', $currentTime);
				$yearFullNumberic = date('Y', $currentTime);
				if ($scheduledDayOfMonth < $currentDayOfMonth) {
					$nextMonth = date("Y-m-d H:i:s", strtotime('next month'));
					$monthInFullText = date('F', strtotime($nextMonth));
				}
				return date("Y-m-d H:i:s", strtotime($scheduledDayOfMonth . ' ' . $monthInFullText . ' ' . $yearFullNumberic . ' ' . $scheduledTime));
			}
		}
		if ($scheduleType == VTScheduledReport::$SCHEDULED_ANNUALLY) {
			$months = [0 => 'January', 1 => 'February', 2 => 'March', 3 => 'April', 4 => 'May', 5 => 'June', 6 => 'July',
				7 => 'August', 8 => 'September', 9 => 'October', 10 => 'November', 11 => 'December'];
			$currentTime = time();
			$currentMonth = date('n', $currentTime);
			if (($scheduledMonth + 1) == $currentMonth) {
				return date("Y-m-d H:i:s", strtotime('+1 year ' . $scheduledTime));
			} else {
				$monthInFullText = $months[$scheduledMonth];
				$yearFullNumberic = date('Y', $currentTime);
				if (($scheduledMonth + 1) < $currentMonth) {
					$nextMonth = date("Y-m-d H:i:s", strtotime('next year'));
					$yearFullNumberic = date('Y', strtotime($nextMonth));
				}
				return date("Y-m-d H:i:s", strtotime($scheduledDayOfMonth . ' ' . $monthInFullText . ' ' . $yearFullNumberic . ' ' . $scheduledTime));
			}
		}
	}

	public function updateNextTriggerTime()
	{
		$adb = $this->db;
		$nextTriggerTime = $this->getNextTriggerTime(); // Compute based on the frequency set
		$adb->pquery('UPDATE vtiger_scheduled_reports SET next_trigger_time=? WHERE reportid=?', [$nextTriggerTime, $this->id]);
	}

	public static function getScheduledReports($user)
	{
		$adb = PearDatabase::getInstance();
		$currentTime = date('Y-m-d H:i:s');
		$result = $adb->pquery("SELECT * FROM vtiger_scheduled_reports
									WHERE next_trigger_time = '' || next_trigger_time <= ?", [$currentTime]);

		$scheduledReports = [];
		$noOfScheduledReports = $adb->numRows($result);
		for ($i = 0; $i < $noOfScheduledReports; ++$i) {
			$reportScheduleInfo = $adb->rawQueryResultRowData($result, $i);

			$scheduledInterval = (!empty($reportScheduleInfo['schedule'])) ? \App\Json::decode($reportScheduleInfo['schedule']) : [];
			$scheduledRecipients = (!empty($reportScheduleInfo['recipients'])) ? \App\Json::decode($reportScheduleInfo['recipients']) : [];

			$vtScheduledReport = new VTScheduledReport($adb, $user, $reportScheduleInfo['reportid']);
			$vtScheduledReport->isScheduled = true;
			$vtScheduledReport->scheduledFormat = $reportScheduleInfo['format'];
			$vtScheduledReport->scheduledInterval = $scheduledInterval;
			$vtScheduledReport->scheduledRecipients = $scheduledRecipients;
			$vtScheduledReport->scheduledTime = $reportScheduleInfo['next_trigger_time'];

			$scheduledReports[] = $vtScheduledReport;
		}
		return $scheduledReports;
	}

	public static function runScheduledReports()
	{
		$scheduledReports = self::getScheduledReports($adminUser);
		foreach ($scheduledReports as $scheduledReport) {
			$scheduledReport->sendEmail();
			$scheduledReport->updateNextTriggerTime();
		}
	}
}
