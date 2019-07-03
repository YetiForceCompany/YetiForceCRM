<?php

/**
 * Email PDF Template Task Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class VTSendPdf extends VTTask
{
	/** @var bool Sending email takes more time, this should be handled via queue all the time. */
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['pdfTemplate', 'mailTemplate', 'email', 'emailoptout', 'smtp', 'copy_email'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (!empty($this->mailTemplate) && !empty($this->pdfTemplate)) {
			$mailerContent = [];
			if (!empty($this->smtp)) {
				$mailerContent['smtp_id'] = $this->smtp;
			}
			$emailParser = \App\EmailParser::getInstanceByModel($recordModel);
			$emailParser->emailoptout = $this->emailoptout ? true : false;
			if ($this->email) {
				$emails = is_array($this->email) ? implode(',', $this->email) : $this->email;
				$mailerContent['to'] = $emailParser->setContent($emails)->parse()->getContent(true);
			}
			unset($emailParser);
			if (empty($mailerContent['to'])) {
				return false;
			}
			if ('Contacts' === $recordModel->getModuleName() && !$recordModel->isEmpty('notifilanguage')) {
				$mailerContent['language'] = $recordModel->get('notifilanguage');
			}
			$mailerContent['template'] = $this->mailTemplate;
			$mailerContent['recordModel'] = $recordModel;
			if (!empty($this->copy_email)) {
				$mailerContent['bcc'] = $this->copy_email;
			}

			$filePath = 'cache' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR;
			$tmpFileName = tempnam($filePath, 'PDF' . time());
			$filePath .= basename($tmpFileName);
			Vtiger_PDF_Model::exportToPdf($recordModel->getId(), $this->pdfTemplate, $filePath, 'F');
			$templateRecord = Vtiger_PDF_Model::getInstanceById($this->pdfTemplate);
			if (!file_exists($filePath)) {
				App\Log::error('An error occurred while generating PFD file, the file doesn\'t exist. Sending email with PDF has been blocked.');
				return false;
			}
			if (!$templateRecord->isEmpty('filename')) {
				$fileName = \App\Fields\File::sanitizeUploadFileName($templateRecord->parseVariables($templateRecord->get('filename'))) . '.pdf';
			} else {
				$fileName = time() . '.pdf';
			}
			$mailerContent['attachments'] = [$filePath => $fileName];
			\App\Mailer::sendFromTemplate($mailerContent);
		}
	}
}
