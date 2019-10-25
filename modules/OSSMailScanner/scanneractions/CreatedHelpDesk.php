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
class OSSMailScanner_CreatedHelpDesk_ScannerAction
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
		$id = 0;
		$prefix = \App\Mail\RecordFinder::getRecordNumberFromString($mail->get('subject'), 'HelpDesk');
		$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');
		if (!empty($exceptionsAll['crating_tickets'])) {
			$exceptions = explode(',', $exceptionsAll['crating_tickets']);
			foreach ($exceptions as $exception) {
				if (false !== strpos($mail->get('from_email'), $exception)) {
					return '';
				}
			}
		}
		$exists = false;
		if ($prefix) {
			$exists = (new App\Db\Query())->select(['ticketid'])->from('vtiger_troubletickets')->where(['ticket_no' => $prefix])->limit(1)->exists();
		}
		if (!$exists) {
			$id = $this->add($mail);
		}
		return $id;
	}

	/**
	 * Tworzenie zgłoszenia z maila.
	 *
	 * @param OSSMail_Mail_Model $mail
	 *
	 * @return int
	 */
	public function add(OSSMail_Mail_Model $mail)
	{
		$contactId = (int) $mail->findEmailAdress('from_email', 'Contacts', false);
		$parentId = (int) $mail->findEmailAdress('from_email', 'Accounts', false);
		$record = Vtiger_Record_Model::getCleanInstance('HelpDesk');

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
		$accountOwner = $mail->getAccountOwner();
		$record->set('assigned_user_id', $mail->getAccountOwner());
		$maxLengthSubject = $record->getField('ticket_title')->get('maximumlength');
		$subject = \App\Purifier::purify($mail->get('subject'));
		$record->setFromUserValue('ticket_title', $maxLengthSubject ? \App\TextParser::textTruncate($subject, $maxLengthSubject, false) : $subject);
		$maxLengthDescription = $record->getField('description')->get('maximumlength');
		$description = \App\Purifier::purifyHtml($mail->get('body'));
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

		if ($mailId = $mail->getMailCrmId()) {
			(new OSSMailView_Relation_Model())->addRelation($mailId, $id, $mail->get('date'));
			$query = (new App\Db\Query())->select(['documentsid'])->from('vtiger_ossmailview_files')->where(['ossmailviewid' => $mailId]);
			$dataReader = $query->createCommand()->query();
			while ($documentId = $dataReader->readColumn(0)) {
				$dbCommand->insert('vtiger_senotesrel', ['crmid' => $id, 'notesid' => $documentId])->execute();
			}
			$dataReader->close();
		}
		$dbCommand->update('vtiger_crmentity', ['createdtime' => $mail->get('date'), 'smcreatorid' => $accountOwner, 'modifiedby' => $accountOwner], ['crmid' => $id])->execute();
		return $id;
	}
}
