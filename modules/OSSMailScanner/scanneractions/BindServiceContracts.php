<?php

/**
 * Mail scanner action bind ServiceContracts
 * @package YetiForce.MailScanner
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindServiceContracts_ScannerAction
{

	public function process(OSSMail_Mail_Model $mail)
	{
		$mailId = $mail->getMailCrmId();
		$returnIds = [];
		if (!$mailId) {
			return $returnIds;
		}

		$accountnumbers = [];
		$accounts = $mail->getActionResult('Accounts');
		if (!empty($accounts)) {
			$keys = array('BindAccounts', 'BindContacts', 'BindLeads', 'BindHelpDesk');
			foreach($keys as $key) {
				$accountnumbers = array_merge($accountnumbers, $accounts[$key]);
			}
		}

		if (!empty($accountnumbers)) {
			$db = PearDatabase::getInstance();

			$query = 'SELECT servicecontractsid FROM vtiger_servicecontracts '
				. 'INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid '
				. 'WHERE vtiger_crmentity.deleted = 0 && sc_related_to IN (' . implode(',', $accountnumbers) . ') && contract_status = ?';
			$result = $db->pquery($query, ['In Progress']);
			if ($db->getRowCount($result)) {
				$serviceContractsId = $db->getSingleValue($result);

				$status = (new OSSMailView_Relation_Model())->addRelation($mailId, $serviceContractsId, $mail->get('udate_formated'));
				if ($status) {
					$returnIds[] = $serviceContractsId;
				}
			}
		}
		return $returnIds;
	}
}
