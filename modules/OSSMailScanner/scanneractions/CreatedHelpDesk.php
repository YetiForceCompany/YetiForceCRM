<?php
/**
 * Mail scanner action creating HelpDesk.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mail scanner action creating HelpDesk.
 */
class OSSMailScanner_CreatedHelpDesk_ScannerAction extends OSSMailScanner_BindHelpDesk_ScannerAction
{
	/** {@inheritdoc} */
	public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;
		$id = $recordId = 0;
		$this->prefix = \App\Mail\RecordFinder::getRecordNumberFromString($mail->get('subject'), 'HelpDesk');
		if (empty($this->prefix) && \Config\Modules\OSSMailScanner::$searchPrefixInBody) {
			$this->prefix = \App\Mail\RecordFinder::getRecordNumberFromString($mail->get('body'), 'HelpDesk', true);
		}
		if ($this->prefix) {
			$recordId = $this->findRecord();
		}
		$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');
		if (!empty($exceptionsAll['crating_tickets'])) {
			$exceptions = explode(',', $exceptionsAll['crating_tickets']);
			foreach ($exceptions as $exception) {
				if (false !== strpos($mail->get('from_email'), $exception)) {
					return '';
				}
			}
		}
		if (!$recordId) {
			$id = $this->add();
		}
		return $id;
	}

	/**
	 * Creating a HelpDesk from an email.
	 *
	 * @return int
	 */
	public function add()
	{
		$contactId = (int) $this->mail->findEmailAddress('from_email', 'Contacts', false);
		$parentId = (int) $this->mail->findEmailAddress('from_email', 'Accounts', false);
		$record = Vtiger_Record_Model::getCleanInstance('HelpDesk');
		if (!$contactId && !$parentId && !\Config\Modules\OSSMailScanner::$helpdeskCreateWithoutNoRelation) {
			return 0;
		}
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (empty($parentId) && !empty($contactId)) {
			$parentId = (new App\Db\Query())->select(['parentid'])->from('vtiger_contactdetails')->where(['contactid' => $contactId])->scalar();
		}
		if ($parentId) {
			$record->set('parent_id', $parentId);
			$queryGenerator = new \App\QueryGenerator('ServiceContracts');
			$queryGenerator->setFields(['id', 'contract_priority']);
			$queryGenerator->addNativeCondition(['vtiger_servicecontracts.sc_related_to' => $parentId]);
			$queryGenerator->permissions = false;
			if (($queryGenerator->getModuleField('contract_status')->getFieldParams()['isProcessStatusField'] ?? false) && ($status = \App\RecordStatus::getStates('ServiceContracts', \App\RecordStatus::RECORD_STATE_OPEN))) {
				$queryGenerator->addCondition('contract_status', $status, 'e');
			} else {
				$queryGenerator->addCondition('contract_status', 'In Progress', 'e');
			}
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			if (1 === $dataReader->count()) {
				$serviceContracts = $dataReader->read();
				$record->set('servicecontractsid', $serviceContracts['id']);
				if (App\Fields\Picklist::isExists('ticketpriorities', $serviceContracts['contract_priority'])) {
					$record->set('ticketpriorities', $serviceContracts['contract_priority']);
				}
			}
			$dataReader->close();
		}
		$accountOwner = $this->mail->getAccountOwner();
		$record->set('assigned_user_id', $accountOwner);
		$record->setFromUserValue('ticket_title', \App\TextUtils::textTruncate($this->mail->get('subject'), $record->getField('ticket_title')->getMaxValue()));
		$record->set('description', \App\TextUtils::htmlTruncate($this->mail->getContent(), $record->getField('description')->getMaxValue()));
		if (!empty(\Config\Modules\OSSMailScanner::$helpdeskCreateDefaultStatus)) {
			$record->set('ticketstatus', \Config\Modules\OSSMailScanner::$helpdeskCreateDefaultStatus);
		}
		if ($contactId) {
			$record->ext['relations'][] = [
				'relatedModule' => 'Contacts',
				'relatedRecords' => [$contactId],
			];
		}
		if ($mailId = $this->mail->getMailCrmId()) {
			$record->ext['relations'][] = [
				'reverse' => true,
				'relatedModule' => 'OSSMailView',
				'relatedRecords' => [$mailId],
				'params' => $this->mail->get('date'),
			];
		}
		$record->save();
		$id = $record->getId();
		if ($mailId) {
			$query = (new App\Db\Query())->select(['documentsid'])->from('vtiger_ossmailview_files')->where(['ossmailviewid' => $mailId]);
			$dataReader = $query->createCommand()->query();
			while ($documentId = $dataReader->readColumn(0)) {
				$dbCommand->insert('vtiger_senotesrel', ['crmid' => $id, 'notesid' => $documentId])->execute();
			}
			$dataReader->close();
		}
		$dbCommand->update('vtiger_crmentity', ['createdtime' => $this->mail->get('date'), 'smcreatorid' => $accountOwner], ['crmid' => $id])->execute();
		return $id;
	}
}
