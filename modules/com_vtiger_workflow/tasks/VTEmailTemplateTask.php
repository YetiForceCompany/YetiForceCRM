<?php

/**
 * Email Template Task Class
 * @package YetiForce.WorkflowTask
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class VTEmailTemplateTask extends VTTask
{

	// Sending email takes more time, this should be handled via queue all the time.
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['template', 'attachments', 'email', 'copy_email'];
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (is_numeric() && $this->template > 0) {
			if (strpos($this->email, '=') === false) {
				$email = $recordModel->get($this->email);
			} else {
				$emaildata = explode('=', $this->email);
				$parentRecord = $recordModel->get($emaildata[0]);
				if (is_numeric($parentRecord) && !empty($parentRecord)) {
					$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecord, $emaildata[1]);
					$email = $parentRecordModel->get($emaildata[2]);
					$emailMod = $emaildata[1];
					if ($emaildata[1] === 'Contacts') {
						$notifilanguage = $parentRecordModel->get('notifilanguage');
					}
				}
			}
			if (!empty($email)) {
				\App\Mailer::sendFromTemplate([
					'template' => $this->template,
					'moduleName' => $recordModel->getModuleName(),
					'recordId' => $recordModel->getId(),
					'to' => $email,
					'cc' => $this->copy_email,
					'language' => $notifilanguage,
					'to_email_mod' => $emailMod
				]);
			}
		}
	}
}
