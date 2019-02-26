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
				$mailerContent['to'] = $emailParser->setContent(implode(',', $this->email))->parse()->getContent(true);
			}
			unset($emailParser);
			if (empty($mailerContent['to'])) {
				return false;
			}
			if ($recordModel->getModuleName() === 'Contacts' && !$recordModel->isEmpty('notifilanguage')) {
				$mailerContent['language'] = $recordModel->get('notifilanguage');
			}
			$mailerContent['template'] = $this->mailTemplate;
			$mailerContent['recordModel'] = $recordModel;
			if (!empty($this->copy_email)) {
				$mailerContent['bcc'] = $this->copy_email;
			}
			$templateRecord = Vtiger_PDF_Model::getInstanceById($this->pdfTemplate);
			$fileName = vtlib\Functions::slug($templateRecord->getName()) . '_' . time() . '.pdf';
			$pdfFile = 'cache' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $fileName;
			Vtiger_PDF_Model::exportToPdf($recordModel->getId(), $recordModel->getModuleName(), $this->pdfTemplate, $pdfFile, 'F');
			if (!file_exists($pdfFile)) {
				App\Log::error('An error occurred while generating PFD file, the file doesn\'t exist. Sending email with PDF has been blocked.');
				return false;
			}
			if (!$templateRecord->isEmpty('filename')) {
				$textParser = \App\TextParser::getInstanceByModel($recordModel);
				$fileName = \App\Fields\File::sanitizeUploadFileName($textParser->setContent($templateRecord->get('filename'))->parse()->getContent());
			}
			$mailerContent['attachments'] = [$pdfFile => $fileName];
			\App\Mailer::sendFromTemplate($mailerContent);
		}
	}
}
