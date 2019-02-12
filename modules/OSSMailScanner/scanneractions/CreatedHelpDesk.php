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
		$prefix = App\Fields\Email::findRecordNumber($mail->get('subject'), 'HelpDesk');
		$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');
		if (!empty($exceptionsAll['crating_tickets'])) {
			$exceptions = explode(',', $exceptionsAll['crating_tickets']);
			foreach ($exceptions as $exception) {
				if (strpos($mail->get('fromaddress'), $exception) !== false) {
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
		$contactId = (int) $mail->findEmailAdress('fromaddress', 'Contacts', false);
		$parentId = (int) $mail->findEmailAdress('fromaddress', 'Accounts', false);
		$record = Vtiger_Record_Model::getCleanInstance('HelpDesk');

		$dbCommand = \App\Db::getInstance()->createCommand();
		if (empty($parentId) && !empty($contactId)) {
			$parentId = (new App\Db\Query())->select(['parentid'])->from('vtiger_contactdetails')->where(['contactid' => $contactId])->limit(1)->scalar();
		}
		if ($parentId) {
			$record->set('parent_id', $parentId);
			$serviceContracts = (new App\Db\Query())->select(['vtiger_servicecontracts.servicecontractsid', 'vtiger_servicecontracts.priority'])->from('vtiger_servicecontracts')->innerJoin('vtiger_crmentity', 'vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid')->where(['vtiger_crmentity.deleted' => 0, 'vtiger_servicecontracts.sc_related_to' => $parentId])->limit(1)->one();
			if ($serviceContracts) {
				$record->set('servicecontractsid', $serviceContracts['servicecontractsid']);
				$record->set('ticketpriorities', $serviceContracts['priority']);
			}
		}
		$accountOwner = $mail->getAccountOwner();
		$record->set('assigned_user_id', $mail->getAccountOwner());
		$record->set('ticket_title', \App\Purifier::purify($mail->get('subject')));
		$record->set('description', \App\Purifier::purifyHtml($mail->get('body')));
		$record->set('ticketstatus', 'Open');
		$record->save();
		$id = $record->getId();

		if (!empty($contactId)) {
			$relationModel = Vtiger_Relation_Model::getInstance($record->getModule(), Vtiger_Module_Model::getInstance('Contacts'));
			$relationModel->addRelation($id, $contactId);
		}

		if ($mailId = $mail->getMailCrmId()) {
			(new OSSMailView_Relation_Model())->addRelation($mailId, $id, $mail->get('udate_formated'));
			$query = (new App\Db\Query())->select(['documentsid'])->from('vtiger_ossmailview_files')->where(['ossmailviewid' => $mailId]);
			$dataReader = $query->createCommand()->query();
			while ($documentId = $dataReader->readColumn(0)) {
				$dbCommand->insert('vtiger_senotesrel', ['crmid' => $id, 'notesid' => $documentId])->execute();
			}
			$dataReader->close();
		}
		$dbCommand->update('vtiger_crmentity', ['createdtime' => $mail->get('udate_formated'), 'smcreatorid' => $accountOwner, 'modifiedby' => $accountOwner], ['crmid' => $id])->execute();

		return $id;
	}
}
