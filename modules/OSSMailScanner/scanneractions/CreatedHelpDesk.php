<?php
/**
 * Mail scanner action creating HelpDesk.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mail scanner action creating HelpDesk.
 */
class OSSMailScanner_CreatedHelpDesk_ScannerAction extends OSSMailScanner_BindHelpDesk_ScannerAction
{
	/**
	 * Process.
	 *
	 * @param OSSMail_Mail_Model $mail
	 *
	 * @return string
	 */
	public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;
		$id = $recordId = 0;
		$this->prefix = App\Fields\Email::findRecordNumber($mail->get('subject'), 'HelpDesk');
		if (empty($this->prefix) && \Config\Modules\OSSMailScanner::$SEARCH_PREFIX_IN_BODY) {
			$this->prefix = \App\Fields\Email::findRecordNumber($mail->get('body'), 'HelpDesk', true);
		}
		$recordId = $this->getNewestRecord();
		$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');
		if (!empty($exceptionsAll['crating_tickets'])) {
			$exceptions = explode(',', $exceptionsAll['crating_tickets']);
			foreach ($exceptions as $exception) {
				if (false !== strpos($mail->get('fromaddress'), $exception)) {
					return '';
				}
			}
		}
		$exists = false;
		if ($recordId) {
			$exists = (new App\Db\Query())->select(['ticketid'])->from('vtiger_troubletickets')->where(['ticketid' => $recordId])->limit(1)->exists();
		}
		if (!$exists) {
			$id = $this->add($mail);
		}
		return $id;
	}

	/**
	 * Create record from mail
	 *
	 * @return int
	 */
	public function add()
	{
		$contactId = (int) $this->mail->findEmailAdress('fromaddress', 'Contacts', false);
		$parentId = (int) $this->mail->findEmailAdress('fromaddress', 'Accounts', false);
		$record = Vtiger_Record_Model::getCleanInstance('HelpDesk');
		if(!$contactId && !$parentId && \App\Process::$requestMode !== 'WebUI' && !\App\Config::module('OSSMailScanner', 'CREATE_TICKET_WITHOUT_CONTACT')){
			return 0;
		}
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (empty($parentId) && !empty($contactId)) {
			$parentId = (new App\Db\Query())->select(['parentid'])->from('vtiger_contactdetails')->where(['contactid' => $contactId])->limit(1)->scalar();
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
		$maxLengthSubject = $record->getField('ticket_title')->get('maximumlength');
		$subject = \App\Purifier::purify($this->mail->get('subject'));
		$record->setFromUserValue('ticket_title', $maxLengthSubject ? \App\TextParser::textTruncate($subject, $maxLengthSubject, false) : $subject);
		$maxLengthDescription = $record->getField('description')->get('maximumlength');
		$description = \App\Purifier::purifyHtml($this->mail->get('body'));
		$record->set('description', $maxLengthDescription ? \App\TextParser::htmlTruncate($description, $maxLengthDescription, false) : $description);
		$record->set('ticketstatus', 'Open');
		if ($contactId) {
			$record->ext['relationsEmail']['Contacts'] = $contactId;
		}
		$record->save();
		$id = $record->getId();

		if (!empty($contactId)) {
			$relationModel = Vtiger_Relation_Model::getInstance($record->getModule(), Vtiger_Module_Model::getInstance('Contacts'));
			$relationModel->addRelation($id, $contactId);
		}

		if ($mailId = $this->mail->getMailCrmId()) {
			(new OSSMailView_Relation_Model())->addRelation($mailId, $id, $this->mail->get('udate_formated'));
			$query = (new App\Db\Query())->select(['documentsid'])->from('vtiger_ossmailview_files')->where(['ossmailviewid' => $mailId]);
			$dataReader = $query->createCommand()->query();
			while ($documentId = $dataReader->readColumn(0)) {
				$dbCommand->insert('vtiger_senotesrel', ['crmid' => $id, 'notesid' => $documentId])->execute();
			}
			$dataReader->close();
		}
		$dbCommand->update('vtiger_crmentity', ['createdtime' => $this->mail->get('udate_formated'), 'smcreatorid' => $accountOwner, 'modifiedby' => $accountOwner], ['crmid' => $id])->execute();
		return $id;
	}
}
