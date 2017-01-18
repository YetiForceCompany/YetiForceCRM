<?php

/**
 * Email Template Task Class
 * @package YetiForce.WorkflowTask
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class VTEmailTemplateTask extends VTTask
{

	/** @var bool Sending email takes more time, this should be handled via queue all the time. */
	public $executeImmediately = true;

	/**
	 * Get field names
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['template', 'email', 'emailoptout', 'smtp', 'copy_email'];
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (!empty($this->template)) {
			$mailerContent = [];
			if (!empty($this->smtp)) {
				$mailerContent['smtp_id'] = $this->smtp;
			}
			$emailParser = \App\EmailParser::getInstanceByModel($recordModel);
			$emailParser->emailoptout = $this->emailoptout ? true : false;
			if ($this->email) {
				$email = is_array($this->email) ? implode(',', $this->email) : $this->email;
				$mailerContent['to'] = $emailParser->setContent($email)->parse()->getContent(true);
			}
			unset($emailParser);
			if (empty($mailerContent['to'])) {
				return false;
			}
			if ($recordModel->getModuleName() === 'Contacts' && !$recordModel->isEmpty('notifilanguage')) {
				$mailerContent['language'] = $recordModel->get('notifilanguage');
			}
			$mailerContent['template'] = $this->template;
			$mailerContent['recordModel'] = $recordModel;
			if (!empty($this->copy_email)) {
				$mailerContent['bcc'] = $this->copy_email;
			}
			\App\Mailer::sendFromTemplate($mailerContent);
		}
	}
}
