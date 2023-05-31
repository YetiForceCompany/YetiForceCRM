<?php
/**
 * Base mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

use App\Mail\RecordFinder;

/**
 * Base mail scanner action class.
 */
class CreatedHelpDesk extends CreatedMail
{
	/** {@inheritdoc} */
	public static $priority = 5;

	/** {@inheritdoc} */
	public function process(): void
	{
		if ($this->checkExceptions() || ($prefix = RecordFinder::getRecordNumberFromString($this->message->getSubject(), 'HelpDesk')) && \App\Record::getIdByRecordNumber($prefix, 'HelpDesk')) {
			return;
		}
		$owner = $this->account->getSource()->get('assigned_user_id');
		$fromEmail = $this->message->getEmail('from');
		$contactId = current(\App\Utils::flatten(RecordFinder::getInstance()->setFields($this->getEmailsFields('Contacts'))->findByEmail($fromEmail)));
		$parentId = current(\App\Utils::flatten(RecordFinder::getInstance()->setFields($this->getEmailsFields('Accounts'))->findByEmail($fromEmail)));
		if (!$parentId) {
			$parentId = current(\App\Utils::flatten(RecordFinder::getInstance()->setFields($this->getEmailsFields('Vendors'))->findByEmail($fromEmail)));
		}
		if (!$parentId && $contactId) {
			$parentId = \App\Record::getParentRecord($contactId, 'Contacts');
		}
		$recordModel = \Vtiger_Record_Model::getCleanInstance('HelpDesk');
		$this->loadServiceContracts($recordModel, $parentId);
		$recordModel->set('assigned_user_id', $owner);
		$recordModel->set('created_user_id', \App\User::getCurrentUserRealId());
		$recordModel->setFromUserValue('ticket_title', \App\TextUtils::textTruncate($this->message->getSubject(), $recordModel->getField('ticket_title')->getMaxValue(), false));

		$mailId = $this->message->getMailCrmId($this->account->getSource()->getId());
		$this->message->getBody();
		if ($mailId) {
			$this->attachments = $this->message->processData['CreatedMail']['attachments'] ?? (new \App\Db\Query())->select(['crmid' => 'documentsid'])->from('vtiger_ossmailview_files')->where(['ossmailviewid' => $mailId])->all();
		} elseif ($this->message->hasAttachments()) {
			$this->message->saveAttachments([
				'assigned_user_id' => $owner,
				'modifiedby' => $owner,
			]);
		}
		$recordModel->set('description', \App\TextUtils::htmlTruncate($this->message->getBody(true), $recordModel->getField('description')->getMaxValue()));
		$recordModel->set('ticketstatus', \Config\Modules\OSSMailScanner::$helpdeskCreateDefaultStatus);

		if ($contactId) {
			$recordModel->ext['relations'][] = [
				'relatedModule' => 'Contacts',
				'relatedRecords' => [$contactId],
			];
		}
		if ($mailId) {
			$recordModel->ext['relations'][] = [
				'reverse' => true,
				'relatedModule' => 'OSSMailView',
				'relatedRecords' => [$mailId],
				'params' => $this->message->getDate(),
			];
		}

		foreach ($this->attachments as $file) {
			$recordModel->ext['relations'][] = [
				'relatedModule' => 'Documents',
				'relatedRecords' => [$file['crmid']],
			];
		}

		$recordModel->save();
		$this->message->setProcessData($this->getName(), ['crmid' => $recordModel->getId()]);
	}

	/**
	 * Find service contracts and init data.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param int|bool            $parentId
	 *
	 * @return void
	 */
	private function loadServiceContracts(\Vtiger_Record_Model $recordModel, $parentId)
	{
		if (!$parentId) {
			return;
		}
		$recordModel->set('parent_id', $parentId);
		$queryGenerator = new \App\QueryGenerator('ServiceContracts');
		$queryGenerator->setFields(['id', 'contract_priority']);
		$queryGenerator->addNativeCondition(['vtiger_servicecontracts.sc_related_to' => $parentId]);
		$queryGenerator->permissions = false;
		$queryGenerator->addCondition('contract_status', 'In Progress', 'e');
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		if (1 === $dataReader->count()) {
			$serviceContracts = $dataReader->read();
			$recordModel->set('servicecontractsid', $serviceContracts['id']);
			if (\App\Fields\Picklist::isExists('ticketpriorities', $serviceContracts['contract_priority'])) {
				$recordModel->set('ticketpriorities', $serviceContracts['contract_priority']);
			}
		}
		$dataReader->close();
		unset($dataReader, $queryGenerator);
	}
}
