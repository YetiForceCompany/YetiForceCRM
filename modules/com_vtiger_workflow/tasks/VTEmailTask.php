<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/com_vtiger_workflow/VTEmailRecipientsTemplate.php');
require_once('include/simplehtmldom/simple_html_dom.php');

class VTEmailTask extends VTTask
{

	// Sending email takes more time, this should be handled via queue all the time.
	public $executeImmediately = false;

	public function getFieldNames()
	{
		return array('subject', 'content', 'recepient', 'emailcc', 'emailbcc', 'fromEmail');
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$taskContents = \App\Json::decode($this->getContents($recordModel));
		$from_email = $taskContents['fromEmail'];
		$from_name = $taskContents['fromName'];
		$to_email = $taskContents['toEmail'];
		$cc = $taskContents['ccEmail'];
		$bcc = $taskContents['bccEmail'];
		$subject = $taskContents['subject'];
		$content = $taskContents['content'];

		if (!empty($to_email)) {
			\App\Mailer::addMail([
				//'smtp_id' => 1,
				'from' => [$from_name => $from_email],
				'to' => $to_email,
				'cc' => $cc,
				'bcc' => $bcc,
				'subject' => $subject,
				'content' => $content,
			]);
		}
	}

	/**
	 * Function to get contents of this task
	 * @param <Object> $entity
	 * @return <Array> contents
	 */
	public function getContents($recordModel)
	{
		if (!$this->contents) {
			$adb = PearDatabase::getInstance();
			$current_user = vglobal('current_user');
			$taskContents = array();
			$recordId = $recordModel->getId();
			$utils = new VTWorkflowUtils();
			$adminUser = $utils->adminUser();

			$fromUserId = $recordModel->get('assigned_user_id');
			$fromModule = \App\Fields\Owner::getType($fromUserId);
			if ($fromModule === 'Groups') {
				$fromUserId = Vtiger_Util_Helper::getCreator($recordId);
			}

			if ($this->fromEmail && !($fromModule === 'Groups' && strpos($this->fromEmail, 'assigned_user_id : (Users) ') !== false)) {
				$et = new VTEmailRecipientsTemplate($this->fromEmail);
				$fromEmailDetails = $et->render($recordModel);

				if (strpos($this->fromEmail, '&lt;') && strpos($this->fromEmail, '&gt;')) {
					list($fromName, $fromEmail) = explode('&lt;', $fromEmailDetails);
					list($fromEmail, $rest) = explode('&gt;', $fromEmail);
				} else {
					$fromEmail = $fromEmailDetails;
				}
			} else {
				$userObj = CRMEntity::getInstance('Users');
				$userObj->retrieveCurrentUserInfoFromFile($fromUserId);
				if ($userObj) {
					$fromEmail = $userObj->email1;
					$fromName = $userObj->user_name;
				} else {
					$result = $adb->pquery('SELECT user_name, email1 FROM vtiger_users WHERE id = ?', array($fromUserId));
					$fromEmail = $adb->query_result($result, 0, 'email1');
					$fromName = $adb->query_result($result, 0, 'user_name');
				}
			}

			if (!$fromEmail) {
				$utils->revertUser();
				return false;
			}

			$taskContents['fromEmail'] = $fromEmail;
			$taskContents['fromName'] = $fromName;

			$et = new VTEmailRecipientsTemplate($this->recepient);
			$toEmail = $et->render($recordModel);

			$ecct = new VTEmailRecipientsTemplate($this->emailcc);
			$ccEmail = $ecct->render($recordModel);

			$ebcct = new VTEmailRecipientsTemplate($this->emailbcc);
			$bccEmail = $ebcct->render($recordModel);

			if (strlen(trim($toEmail, " \t\n,")) == 0 && strlen(trim($ccEmail, " \t\n,")) == 0 && strlen(trim($bccEmail, " \t\n,")) == 0) {
				$utils->revertUser();
				return false;
			}

			$taskContents['toEmail'] = $toEmail;
			$taskContents['ccEmail'] = $ccEmail;
			$taskContents['bccEmail'] = $bccEmail;

			$st = new VTSimpleTemplate($this->subject);
			$taskContents['subject'] = $st->render($recordModel);

			$ct = new VTSimpleTemplate($this->content);
			$taskContents['content'] = $ct->render($recordModel);
			$this->contents = $taskContents;
			$utils->revertUser();
		}
		if (is_array($this->contents)) {
			$this->contents = \App\Json::encode($this->contents);
		}
		return $this->contents;
	}
}
