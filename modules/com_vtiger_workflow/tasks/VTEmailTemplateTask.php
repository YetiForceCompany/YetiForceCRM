<?php

/**
 * Email Template Task Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class VTEmailTemplateTask extends VTTask
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
		return ['template', 'email', 'relations_email', 'emailoptout', 'smtp', 'copy_email', 'address_emails', 'attachments'];
	}

	/**
	 * Execute task.
	 *
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
			$mailerContent['to'] = [];
			if ($this->email) {
				$email = \is_array($this->email) ? implode(',', $this->email) : $this->email;
				$mailerContent['to'] = $emailParser->setContent($email)->parse()->getContent(true);
			}
			if ($this->address_emails) {
				$emails = $emailParser->setContent($this->address_emails)->getContent(true);
				foreach ($emails as $email) {
					$mailerContent['to'][] = $email;
				}
			}
			if ($this->relations_email && '-' !== $this->relations_email) {
				[$relatedModule,$relatedFieldName,$onlyFirst] = array_pad(explode('::', $this->relations_email), 3, false);
				$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModule);
				$relationListView->setFields(['id', $relatedFieldName]);
				$relationListView->set('search_key', $relatedFieldName);
				$relationListView->set('operator', 'ny');
				if ($onlyFirst) {
					$relationListView->getQueryGenerator()->setLimit(1);
				}
				foreach ($relationListView->getAllEntries() as $relatedRecordModel) {
					$mailerContent['to'][] = $relatedRecordModel->get($relatedFieldName);
				}
			}
			unset($emailParser);
			if (empty($mailerContent['to'])) {
				return false;
			}
			if ('Contacts' === $recordModel->getModuleName() && !$recordModel->isEmpty('notifilanguage')) {
				$mailerContent['language'] = $recordModel->get('notifilanguage');
			}
			$mailerContent['template'] = $this->template;
			$mailerContent['recordModel'] = $recordModel;
			if (!empty($this->copy_email)) {
				$mailerContent['bcc'] = $this->copy_email;
			}
			if (!empty($this->attachments)) {
				$attachmentsInfo = explode('::', $this->attachments);
				$ids = [];
				$relationListView = null;
				if (\count($attachmentsInfo) > 1) {
					if (!$recordModel->isEmpty($attachmentsInfo[1]) && App\Record::isExists($recordModel->get($attachmentsInfo[1]), $attachmentsInfo[0])) {
						$relationListView = Vtiger_RelationListView_Model::getInstance(Vtiger_Record_Model::getInstanceById($recordModel->get($attachmentsInfo[1]), $attachmentsInfo[0]), 'Documents');
					}
				} else {
					$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'Documents');
				}
				if ($relationListView) {
					$queryGenerator = $relationListView->getRelationQuery(true);
					$queryGenerator->setFields(['id']);
					$ids = $queryGenerator->createQuery()->column();
					$mailerContent['attachments'] = ['ids' => $ids];
				}
			}
			\App\Mailer::sendFromTemplate($mailerContent);
		}
	}
}
