<?php

/**
 * Email PDF Template Task Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radoslaw Skrzypczak <r.skrzypczak@yetiforce.com>
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
		if (!empty($this->mailTemplate) && !empty($this->pdfTemplate) && \App\Record::isExists($this->mailTemplate, 'EmailTemplates') && \Vtiger_PDF_Model::getInstanceById($this->pdfTemplate)) {
			$mailerContent = [];
			if (!empty($this->smtp)) {
				$mailerContent['smtp_id'] = $this->smtp;
			}
			$emailParser = \App\EmailParser::getInstanceByModel($recordModel);
			$emailParser->emailoptout = $this->emailoptout ? true : false;
			if ($this->email) {
				$emails = \is_array($this->email) ? implode(',', $this->email) : $this->email;
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

			$pdf = \App\Pdf\Pdf::getInstanceByTemplateId($this->pdfTemplate);
			$template = $pdf->getTemplate();
			$template->setVariable('recordId', $recordModel->getId());
			$pdf->loadTemplateData();
			$filePath = $template->getPath();
			$pdf->output($filePath, 'F');
			$fileName = ($pdf->getFileName() ?: time()) . '.pdf';

			$mailerContent['attachments'] = [$filePath => $fileName];
			\App\Mailer::sendFromTemplate($mailerContent);
		}
	}
}
