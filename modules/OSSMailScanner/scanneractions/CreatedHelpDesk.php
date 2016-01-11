<?php

/**
 * Mail scanner action creating HelpDesk
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_CreatedHelpDesk_ScannerAction extends OSSMailScanner_BaseScannerAction_Model
{

	public function process()
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

		$db = PearDatabase::getInstance($mail);
		$result = $db->pquery('SELECT ticketid FROM vtiger_troubletickets where ticket_no = ?;', [$prefix]);
		if ($db->getRowCount($result) == 0) {
			$contactId = $mail->findEmailAdress('fromaddress', 'Contacts', false);
			$parentId = $mail->findEmailAdress('fromaddress', 'Accounts', false);

			$rekord = Vtiger_Record_Model::getCleanInstance('HelpDesk');
			$rekord->set('assigned_user_id', $accountOwner);
			$rekord->set('ticket_title', $mail->get('subject'));
			if (!empty($parentId) && $parentId != '0') {
				$rekord->set('parent_id', $parentId);

				$query = 'SELECT vtiger_servicecontracts.servicecontractsid, vtiger_servicecontracts.priority FROM vtiger_servicecontracts '
					. 'INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid '
					. 'WHERE vtiger_crmentity.deleted = ? AND vtiger_servicecontracts.sc_related_to = ?';
				$servicecontracts = $db->pquery($query, [0, $parentId]);
				if ($db->getRowCount($result) > 1) {
					$serviceContracts = $db->getRow($result);
					$rekord->set('servicecontractsid', $serviceContracts['servicecontractsid']);
					$rekord->set('ticketpriorities', $serviceContracts['priority']);
				}
			}
			$rekord->set('description', strip_tags($mail->get('body')));
			$rekord->set('ticketstatus', 'Open');
			$record->set('mode', 'new');
			$record->set('id', '');
			$rekord->save();
			$id = $rekord->getId();

			if (!empty($contactId) && $contactId != '0') {
				$rekord->set('contact_id', $mail->get('$contactId'));
			}


			$mailId = $mail->getMailCrmId();
			if ($mailId) {
				/*
				$db->insert('vtiger_crmentityrel', [
					'recordid' => $cbrecord,
					'semodule' => $cbmodule,
					'date_start' => $cbdate,
					'time_start' => $cbtime,
					'status' => $status,
				]);
				 */
				//$db->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?, module=?, relcrmid=?, relmodule=?", Array($mailId, 'OSSMailView', $id, 'HelpDesk'));
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
