<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_ScheduleReports_Model extends Vtiger_Base_Model
{

	public $scheduledFormat = 'CSV';
	static $SCHEDULED_DAILY = 1;
	static $SCHEDULED_WEEKLY = 2;
	static $SCHEDULED_MONTHLY_BY_DATE = 3;
	static $SCHEDULED_ANNUALLY = 4;
	static $SCHEDULED_ON_SPECIFIC_DATE = 5;

	public static function getInstance()
	{
		return new self();
	}

	/**
	 * Function returns the Scheduled Reports Model instance
	 * @param <Number> $recordId
	 * @return <Reports_ScehduleReports_Model>
	 */
	public static function getInstanceById($recordId)
	{
		$db = PearDatabase::getInstance();
		$scheduledReportModel = new self();

		if (!empty($recordId)) {
			$scheduledReportResult = $db->pquery('SELECT * FROM vtiger_schedulereports WHERE reportid = ?', array($recordId));
			if ($db->num_rows($scheduledReportResult) > 0) {
				$reportScheduleInfo = $db->query_result_rowdata($scheduledReportResult, 0);
				$reportScheduleInfo['schdate'] = decode_html($reportScheduleInfo['schdate']);
				$reportScheduleInfo['schdayoftheweek'] = decode_html($reportScheduleInfo['schdayoftheweek']);
				$reportScheduleInfo['schdayofthemonth'] = decode_html($reportScheduleInfo['schdayofthemonth']);
				$reportScheduleInfo['schannualdates'] = decode_html($reportScheduleInfo['schannualdates']);
				$reportScheduleInfo['recipients'] = decode_html($reportScheduleInfo['recipients']);
				$reportScheduleInfo['specificemails'] = decode_html($reportScheduleInfo['specificemails']);
				$reportScheduleInfo['scheduleFileType'] = $reportScheduleInfo['filetype'];
				$scheduledReportModel->setData($reportScheduleInfo);
			}
		}
		return $scheduledReportModel;
	}

	/**
	 * Function to save the  Scheduled Reports data
	 */
	public function saveScheduleReport()
	{
		$adb = PearDatabase::getInstance();

		$reportid = $this->get('reportid');
		$scheduleid = $this->get('scheduleid');
		$schtime = $this->get('schtime');
		$scheduleFileType = $this->get('scheduleFileType');
		if (!preg_match('/^[0-2]\d(:[0-5]\d){1,2}$/', $schtime) || substr($schtime, 0, 2) > 23) {  // invalid time format
			$schtime = '00:00';
		}
		$schtime .= ':00';

		$schdate = null;
		$schdayoftheweek = null;
		$schdayofthemonth = null;
		$schannualdates = null;
		if ($scheduleid == self::$SCHEDULED_ON_SPECIFIC_DATE) {
			$date = $this->get('schdate');
			$dateDBFormat = DateTimeField::convertToDBFormat($date);
			$nextTriggerTime = $dateDBFormat . ' ' . $schtime;
			$currentTime = Vtiger_Util_Helper::getActiveAdminCurrentDateTime();
			if ($nextTriggerTime > $currentTime) {
				$this->set('next_trigger_time', $nextTriggerTime);
			} else {
				$this->set('next_trigger_time', date('Y-m-d H:i:s', strtotime('+10 year')));
			}
			$schdate = \App\Json::encode(array($dateDBFormat));
		} else if ($scheduleid == self::$SCHEDULED_WEEKLY) {
			$schdayoftheweek = \App\Json::encode($this->get('schdayoftheweek'));
			$this->set('schdayoftheweek', $schdayoftheweek);
		} else if ($scheduleid == self::$SCHEDULED_MONTHLY_BY_DATE) {
			$schdayofthemonth = \App\Json::encode($this->get('schdayofthemonth'));
			$this->set('schdayofthemonth', $schdayofthemonth);
		} else if ($scheduleid == self::$SCHEDULED_ANNUALLY) {
			$schannualdates = \App\Json::encode($this->get('schannualdates'));
			$this->set('schannualdates', $schannualdates);
		}

		$recipients = \App\Json::encode($this->get('recipients'));
		$specificemails = \App\Json::encode($this->get('specificemails'));
		$isReportScheduled = $this->get('isReportScheduled');

		if ($scheduleid != self::$SCHEDULED_ON_SPECIFIC_DATE) {
			$nextTriggerTime = $this->getNextTriggerTime();
		}
		if ($isReportScheduled == '0' || $isReportScheduled == '' || $isReportScheduled === false) {
			$deleteScheduledReportSql = "DELETE FROM vtiger_schedulereports WHERE reportid=?";
			$adb->pquery($deleteScheduledReportSql, array($reportid));
		} else {
			$checkScheduledResult = $adb->pquery('SELECT 1 FROM vtiger_schedulereports WHERE reportid=?', array($reportid));
			$params = [
				'scheduleid' => $scheduleid,
				'recipients' => $recipients,
				'schdate' => $schdate,
				'schtime' => $schtime,
				'schdayoftheweek' => $schdayoftheweek,
				'schdayofthemonth' => $schdayofthemonth,
				'schannualdates' => $schannualdates,
				'specificemails' => $specificemails,
				'next_trigger_time' => $nextTriggerTime,
				'filetype' => $scheduleFileType,
			];
			if ($adb->num_rows($checkScheduledResult) > 0) {
				$adb->update('vtiger_schedulereports', $params, 'reportid = ?', [$reportid]);
			} else {
				$params['reportid'] = $reportid;
				$adb->insert('vtiger_schedulereports', $params);
			}
		}
	}

	public function getRecipientEmails()
	{
		$recipientsInfo = $this->get('recipients');

		if (!empty($recipientsInfo)) {
			$recipients = [];
			$recipientsInfo = \App\Json::decode($recipientsInfo);
			foreach ($recipientsInfo as $key => $recipient) {
				if (strpos($recipient, 'USER') !== false) {
					$id = explode('::', $recipient);
					$recipients['Users'][] = $id[1];
				} else if (strpos($recipient, 'GROUP') !== false) {
					$id = explode('::', $recipient);
					$recipients['Groups'][] = $id[1];
				} else if (strpos($recipient, 'ROLE') !== false) {
					$id = explode('::', $recipient);
					$recipients['Roles'][] = $id[1];
				}
			}
		}
		$recipientsList = [];
		if (!empty($recipients)) {
			if (!empty($recipients['Users'])) {
				$recipientsList = array_merge($recipientsList, $recipients['Users']);
			}

			if (!empty($recipients['Roles'])) {
				foreach ($recipients['Roles'] as $roleId) {
					$roleUsers = getRoleUsers($roleId);
					foreach ($roleUsers as $userId => $userName) {
						array_push($recipientsList, $userId);
					}
				}
			}

			if (!empty($recipients['Groups'])) {
				require_once 'include/utils/GetGroupUsers.php';
				foreach ($recipients['Groups'] as $groupId) {
					$userGroups = new GetGroupUsers();
					$userGroups->getAllUsersInGroup($groupId);
					$recipientsList = array_merge($recipientsList, $userGroups->group_users);
				}
			}
		}
		$recipientsList = array_unique($recipientsList);
		$recipientsEmails = [];
		if (!empty($recipientsList) && count($recipientsList) > 0) {
			foreach ($recipientsList as $userId) {
				if (!Vtiger_Util_Helper::isUserDeleted($userId)) {
					$userName = \App\Fields\Owner::getUserLabel($userId);
					$userEmail = \App\User::getUserModel($userId)->getDetail('email1');
					if (!in_array($userEmail, $recipientsEmails)) {
						$recipientsEmails[$userName] = $userEmail;
					}
				}
			}
		}
		//Added for specific email address.
		$specificemails = explode(',', \App\Json::decode($this->get('specificemails')));
		if (!empty($specificemails)) {
			$recipientsEmails = array_merge($recipientsEmails, $specificemails);
		}

		return $recipientsEmails;
	}

	public function sendEmail()
	{
		$recipientEmails = $this->getRecipientEmails();
		vtlib\Utils::ModuleLog('ScheduleReprots', $recipientEmails);
		$to = [];
		foreach ($recipientEmails as $name => $email) {
			$to[$email] = $name;
		}
		vimport('~modules/Report/models/Record.php');
		$reportRecordModel = Reports_Record_Model::getInstanceById($this->get('reportid'));
		$currentTime = date('Y-m-d.H.i.s');
		vtlib\Utils::ModuleLog('ScheduleReprots Send Mail Start ::', $currentTime);
		$reportname = decode_html($reportRecordModel->getName());
		$subject = $reportname;
		vtlib\Utils::ModuleLog('ScheduleReprot Name ::', $reportname);
		$baseFileName = $reportname . '__' . $currentTime;
		$fileName = $baseFileName . '.csv';

		$reportFormat = $this->scheduledFormat;
		if (!$this->isEmpty('scheduleFileType')) {
			$reportFormat = $this->get('scheduleFileType');
		}
		$oReportRun = ReportRun::getInstance($this->get('reportid'));
		$attachments = [];
		if ($reportFormat === 'CSV') {
			$fileName = $baseFileName . '.csv';
			$filePath = 'cache/mail/' . $fileName;
			$attachments[$filePath] = $fileName;
			$oReportRun->writeReportToCSVFile($filePath);
		}
		if ($reportFormat === 'EXCEL') {
			$fileName = $baseFileName . '.xls';
			$filePath = 'cache/mail/' . $fileName;
			$attachments[$filePath] = $fileName;
			$oReportRun->writeReportToExcelFile($filePath);
		}
		//Added cc to account owner
		$accountOwnerId = Users::getActiveAdminId();
		\App\Mailer::sendFromTemplate([
			'to' => $to,
			'cc' => [\App\User::getUserModel($accountOwnerId)->getDetail('email1') => \App\Fields\Owner::getUserLabel($accountOwnerId)],
			'template' => 'ScheduleReprots',
			'attachments' => $attachments,
			'reportName' => $reportRecordModel->getName(),
			'reportDescritpion' => $reportRecordModel->getDescriptionValue(),
			'reportUrl' => $reportRecordModel->getDetailViewUrl(),
		]);
		return true;
	}

	/**
	 * Function gets the next trigger for the workflows
	 * @global string $default_timezone
	 * @return type
	 */
	public function getNextTriggerTime()
	{
		require_once 'modules/com_vtiger_workflow/VTWorkflowManager.php';
		$default_timezone = vglobal('default_timezine');
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		@date_default_timezone_set($adminTimeZone);

		$scheduleType = $this->get('scheduleid');
		$nextTime = null;

		$workflow = new Workflow();
		if ($scheduleType == self::$SCHEDULED_DAILY) {
			$nextTime = $workflow->getNextTriggerTimeForDaily($this->get('schtime'));
		}
		if ($scheduleType == self::$SCHEDULED_WEEKLY) {
			$nextTime = $workflow->getNextTriggerTimeForWeekly($this->get('schdayoftheweek'), $this->get('schtime'));
		}

		if ($scheduleType == self::$SCHEDULED_ON_SPECIFIC_DATE) {
			$nextTime = date('Y-m-d H:i:s', strtotime('+10 year'));
		}

		if ($scheduleType == self::$SCHEDULED_MONTHLY_BY_DATE) {
			$nextTime = $workflow->getNextTriggerTimeForMonthlyByDate($this->get('schdayofthemonth'), $this->get('schtime'));
		}

		if ($scheduleType == self::$SCHEDULED_ANNUALLY) {
			$nextTime = $workflow->getNextTriggerTimeForAnnualDates($this->get('schannualdates'), $this->get('schtime'));
		}
		@date_default_timezone_set($default_timezone);
		return $nextTime;
	}

	public function updateNextTriggerTime()
	{
		$adb = PearDatabase::getInstance();
		$nextTriggerTime = $this->getNextTriggerTime();
		vtlib\Utils::ModuleLog('ScheduleReprot Next Trigger Time >> ', $nextTriggerTime);
		$adb->pquery('UPDATE vtiger_schedulereports SET next_trigger_time=? WHERE reportid=?', array($nextTriggerTime, $this->get('reportid')));
		vtlib\Utils::ModuleLog('ScheduleReprot', 'Next Trigger Time updated');
	}

	public static function getScheduledReports()
	{
		$default_timezone = vglobal('default_timezone');
		// set the time zone to the admin's time zone, this is needed so that the scheduled reprots will be triggered
		// at admin's time zone rather than the systems time zone. This is specially needed for Hourly and Daily scheduled reports
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		@date_default_timezone_set($adminTimeZone);
		$currentTimestamp = date("Y-m-d H:i:s");
		@date_default_timezone_set($default_timezone);
		$dataReader = (new App\Db\Query())->select(['reportid'])
				->from('vtiger_schedulereports')
				->where(['or', ['next_trigger_time' => null], ['<=', 'next_trigger_time', $currentTimestamp]])
				->createCommand()->query();
		$scheduledReports = [];
		while ($recordId = $dataReader->readColumn(0)) {
			$scheduledReports[] = self::getInstanceById($recordId);
		}
		return $scheduledReports;
	}

	public static function runScheduledReports()
	{
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$util = new VTWorkflowUtils();
		$util->adminUser();

		$currentModule = vglobal('currentModule');
		$current_language = vglobal('current_language');
		if (empty($currentModule)) {
			vglobal('currentModule', 'Reports');
		}
		if (empty($current_language))
			vglobal('current_language', 'en_us');

		$scheduledReports = self::getScheduledReports();
		foreach ($scheduledReports as $scheduledReport) {
			$status = $scheduledReport->sendEmail();
			vtlib\Utils::ModuleLog('ScheduleReprot Send Mail Status ', $status);
			if ($status)
				$scheduledReport->updateNextTriggerTime();
		}
		$util->revertUser();
		return $status;
	}

	public function getEmailContent($reportRecordModel)
	{
		$site_URL = vglobal('site_URL');
		$currentModule = vglobal('currentModule');
		$logo = $site_URL . '/storage/Logo/' . Vtiger_CompanyDetails_Model::getInstanceById()->get('logoname');

		$body = '<table width="700" cellspacing="0" cellpadding="0" border="0" align="center" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: normal; text-decoration: none; ">
			<tr>
				<td> </td>
			</tr>
			<tr>
				<td>
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td>
							<table width="100%" cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td rowspan="4" ><img height="30" src=' . $logo . '></td>
									</tr>
							</table>
							</td>
						</tr>
						<tr>
							<td>
							<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: normal; color: rgb(0, 0, 0); background-color: rgb(255, 255, 255);">
									<tr>
										<td valign="top">
										<table width="100%" cellspacing="0" cellpadding="5" border="0">
												<tr>
													<td align="right" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; color: rgb(66, 66, 253);"> </td>
												</tr>
												<tr>
													<td> </td>
												</tr>
												<tr>
													<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(0, 0, 0); font-weight: normal; text-align: justify; line-height: 20px;"> ' . vtranslate('LBL_AUTO_GENERATED_REPORT_EMAIL', $currentModule) . '</td>
												</tr>
												<tr>
													<td align="center">
													<table width="75%" cellspacing="0" cellpadding="10" border="0" style="border: 2px solid rgb(180, 180, 179); background-color: rgb(226, 226, 225); font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(0, 0, 0); font-weight: normal;">
															<tr>
																<td><b>' . vtranslate('LBL_REPORT_NAME', $currentModule) . ' </b> : <font color="#990000"><strong> <a href=' . $site_URL . '/' . $reportRecordModel->getDetailViewUrl() . '>' . $reportRecordModel->getName() . '</a></strong></font> </td>
															</tr>
															<tr>
																<td><b>' . vtranslate('LBL_DESCRIPTION', $currentModule) . ' :</b> <font color="#990000"><strong>' . $reportRecordModel->get('description') . '</strong></font> </td>
															</tr>
													</table>
													</td>
												</tr>
										</table>
										</td>
										<td width="1%" valign="top"> </td>
									</tr>
							</table>
							</td>
						</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td> </td>
			</tr>
			<tr>
				<td> </td>
			</tr>
			<tr>
				<td> </td>
			</tr>
	</table>';

		return $body;
	}
}
