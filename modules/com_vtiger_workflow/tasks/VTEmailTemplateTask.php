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
				$emails = $emailParser->setContent($this->address_emails)->parse()->getContent(true);
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
			if ($attachments = $this->getAttachments($recordModel)) {
				$mailerContent['attachments'] = ['ids' => $attachments];
			}
			\App\Mailer::sendFromTemplate($mailerContent);
		}
	}

	/**
	 * Get attachments from document relation or multireference field.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getAttachments(Vtiger_Record_Model $recordModel): array
	{
		$documentIds = [];
		if (!empty($this->attachments)) {
			[$moduleOrFieldName, $fieldNameOrAction] = array_pad(explode('::', $this->attachments), 2, null);
			$relationListView = null;
			if ($moduleOrFieldName && $fieldNameOrAction) {
				$fieldModelList = array_intersect_key($recordModel->getModule()->getFields(), array_flip([$moduleOrFieldName]));
				$isFieldActive = false;
				foreach ($fieldModelList as $fieldModel) {
					$isFieldActive = $fieldModel->isActiveField();
				}
				if (!$recordModel->isEmpty($fieldNameOrAction) && App\Record::isExists($recordModel->get($fieldNameOrAction), $moduleOrFieldName)) {
					$relationListView = Vtiger_RelationListView_Model::getInstance(Vtiger_Record_Model::getInstanceById($recordModel->get($fieldNameOrAction), $moduleOrFieldName), 'Documents');
				} elseif ($isFieldActive && 'allAttachments' === $fieldNameOrAction) {
					$currentValue = $recordModel->get($moduleOrFieldName);
					$documentIds = $currentValue ? explode(',', $currentValue) : [];
				} elseif ($isFieldActive && 'latestAttachments' === $fieldNameOrAction
					&& false !== $recordModel->getPreviousValue($moduleOrFieldName)
				) {
					$previousValue = $recordModel->getPreviousValue($moduleOrFieldName);
					$previousAttachments = $previousValue ? explode(',', $previousValue) : [];
					$currentValue = $recordModel->get($moduleOrFieldName);
					$currentAttachments = $currentValue ? explode(',', $currentValue) : [];
					$documentIds = array_values(array_diff($currentAttachments, $previousAttachments));
				}
			} else {
				$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'Documents');
			}
			if ($relationListView) {
				$queryGenerator = $relationListView->getRelationQuery(true);
				$queryGenerator->setFields(['id']);
				$documentIds = $queryGenerator->createQuery()->column();
			}
		}
		return $documentIds;
	}
}
