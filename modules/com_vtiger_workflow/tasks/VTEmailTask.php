<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTEmailTask extends VTTask
{
	// Sending email takes more time, this should be handled via queue all the time.
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['subject', 'content', 'recepient', 'emailcc', 'emailbcc', 'fromEmail', 'smtp', 'emailoptout'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$mailerContent = [
			'smtp_id' => ($this->smtp) ? $this->smtp : App\Mail::getDefaultSmtp(),
		];
		$emailParser = \App\EmailParser::getInstanceByModel($recordModel);
		$emailParser->emailoptout = $this->emailoptout ? true : false;
		if ($this->fromEmail) {
			$fromEmailDetails = $emailParser->setContent($this->fromEmail)->parse()->getContent(true);
			if ($fromEmailDetails) {
				foreach ($fromEmailDetails as $key => $value) {
					if (\is_int($key)) {
						$mailerContent['from'] = ['email' => $value, 'name' => $value];
					} else {
						$mailerContent['from'] = ['email' => $key, 'name' => $value];
					}
				}
			}
		}
		$toEmail = $emailParser->setContent($this->recepient)->parse()->getContent(true);
		if ($toEmail) {
			$mailerContent['to'] = $toEmail;
		}
		$ccEmail = $emailParser->setContent($this->emailcc)->parse()->getContent(true);
		if ($ccEmail) {
			$mailerContent['cc'] = $ccEmail;
		}
		$bccEmail = $emailParser->setContent($this->emailbcc)->parse()->getContent(true);
		if ($bccEmail) {
			$mailerContent['bcc'] = $bccEmail;
		}
		unset($emailParser);
		if (empty($toEmail) && empty($ccEmail) && empty($bccEmail)) {
			return false;
		}
		$textParser = \App\TextParser::getInstanceByModel($recordModel);
		$mailerContent['subject'] = $textParser->setContent($this->subject)->parse()->getContent();
		$mailerContent['content'] = $textParser->setContent($this->content)->parse()->getContent();
		if (!empty($mailerContent['content'])) {
			\App\Mailer::addMail($mailerContent);
		}
		unset($textParser);
	}
}
