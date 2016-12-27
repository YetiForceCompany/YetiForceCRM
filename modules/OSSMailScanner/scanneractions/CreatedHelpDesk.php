<?php

/**
 * Mail scanner action creating HelpDesk
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_CreatedHelpDesk_ScannerAction
{

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
		$create = true;
		$db = PearDatabase::getInstance();
		if ($prefix !== false) {
			$result = $db->pquery('SELECT ticketid FROM vtiger_troubletickets where ticket_no = ? LIMIT 1', [$prefix]);
			$create = $db->getRowCount($result) == 0;
		}
		if ($create) {
			$id = $this->add($mail);
		}
		return $id;
	}

	public function add(OSSMail_Mail_Model $mail)
	{
		$contactId = $mail->findEmailAdress('fromaddress', 'Contacts', false);
		$parentId = $mail->findEmailAdress('fromaddress', 'Accounts', false);
		$record = Vtiger_Record_Model::getCleanInstance('HelpDesk');

		$db = PearDatabase::getInstance();
		if (empty($parentId) && !empty($contactId)) {
			$resultAccount = $db->pquery('SELECT parentid FROM vtiger_contactdetails where contactid = ? LIMIT 1', [$contactId]);
			if ($db->getRowCount($resultAccount)) {
				$parentId = $db->getSingleValue($resultAccount);
			}
		}
		if (!empty($parentId)) {
			$record->set('parent_id', $parentId);

			$query = 'SELECT vtiger_servicecontracts.servicecontractsid, vtiger_servicecontracts.priority FROM vtiger_servicecontracts '
				. 'INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid '
				. 'WHERE vtiger_crmentity.deleted = ? && vtiger_servicecontracts.sc_related_to = ? LIMIT 1';
			$result = $db->pquery($query, [0, $parentId]);
			if ($db->getRowCount($result)) {
				$serviceContracts = $db->getRow($result);
				$record->set('servicecontractsid', $serviceContracts['servicecontractsid']);
				$record->set('ticketpriorities', $serviceContracts['priority']);
			}
		}

		$accountOwner = $mail->getAccountOwner();
		$record->set('assigned_user_id', $mail->getAccountOwner());
		$record->set('ticket_title', $mail->get('subject'));
		$record->set('description', \App\Purifier::purifyHtml($mail->get('body')));
		$record->set('ticketstatus', 'Open');
		$record->set('id', '');
		$record->save();
		$id = $record->getId();

		if (!empty($contactId) && $contactId != '0') {
			$relationModel = Vtiger_Relation_Model::getInstance($record->getModule(), Vtiger_Module_Model::getInstance('Contacts'));
			$relationModel->addRelation($id, $contactId);
		}

		if ($mailId = $mail->getMailCrmId()) {
			(new OSSMailView_Relation_Model())->addRelation($mailId, $id, $mail->get('udate_formated'));
			$result = $db->pquery('SELECT documentsid FROM vtiger_ossmailview_files WHERE ossmailviewid = ?;', [$mailId]);
			while ($documentId = $db->getSingleValue($result)) {
				$db->insert('vtiger_senotesrel', [
					'crmid' => $id,
					'notesid' => $documentId
				]);
			}
		}
		$db->update('vtiger_crmentity', [
			'createdtime' => $mail->get('udate_formated'),
			'smcreatorid' => $accountOwner,
			'modifiedby' => $accountOwner
			], 'crmid = ?', [$id]
		);
		return $id;
	}
}
