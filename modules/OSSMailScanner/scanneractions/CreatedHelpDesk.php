<?php

/**
 * Mail scanner action creating HelpDesk
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_CreatedHelpDesk_ScannerAction extends OSSMailScanner_BaseScannerAction_Model
{

	public function process($mail)
	{
		$id = 0;
		$accountOwner = $mail->getAccountOwner();
		$prefix = $this->findEmailPrefix('HelpDesk', $mail->get('subject'));

		$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');
		if (!empty($exceptionsAll['crating_tickets'])) {
			$exceptions = explode(',', $exceptionsAll['crating_tickets']);
			foreach ($exceptions as $exception) {
				if (strpos($mail->get('fromaddress'), $exception) !== FALSE) {
					return '';
				}
			}
		}

		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT ticketid FROM vtiger_troubletickets where ticket_no = ?;', [$prefix]);
		if ($db->getRowCount($result) == 0) {
			$contactId = $mail->findEmailAdress('fromaddress', 'Contacts', false);
			$parentId = $mail->findEmailAdress('fromaddress', 'Accounts', false);

			$record = Vtiger_Record_Model::getCleanInstance('HelpDesk');
			$record->set('assigned_user_id', $accountOwner);
			$record->set('ticket_title', $mail->get('subject'));
			if (!empty($parentId) && $parentId != '0') {
				$record->set('parent_id', $parentId);

				$query = 'SELECT vtiger_servicecontracts.servicecontractsid, vtiger_servicecontracts.priority FROM vtiger_servicecontracts '
					. 'INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid '
					. 'WHERE vtiger_crmentity.deleted = ? AND vtiger_servicecontracts.sc_related_to = ?';
				$result = $db->pquery($query, [0, $parentId]);
				if ($db->getRowCount($result)) {
					$serviceContracts = $db->getRow($result);
					$record->set('servicecontractsid', $serviceContracts['servicecontractsid']);
					$record->set('ticketpriorities', $serviceContracts['priority']);
				}
			}
			$record->set('description', strip_tags($mail->get('body')));
			$record->set('ticketstatus', 'Open');
			$record->set('mode', 'new');
			$record->set('id', '');
			$record->save();
			$id = $record->getId();

			if (!empty($contactId) && $contactId != '0') {
				$relationModel = Vtiger_Relation_Model::getInstance($record->getModule(), Vtiger_Module_Model::getInstance('Contacts'));
				$relationModel->addRelation($id, $contactId);
			}

			$mailId = $mail->getMailCrmId();
			if ($mailId) {
				$status = OSSMailView_Relation_Model::addRelation($mailId, $id, $mail->get('udate_formated'));
				if ($status) {
					$returnIds[] = $crmid;
				}
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
		}
		return $id;
	}
}
