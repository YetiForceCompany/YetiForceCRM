<?php

/**
 * Mail scanner action bind ServiceContracts
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindServiceContracts_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{

	public function process($mail)
	{
		$mailId = $mail->getMailCrmId();
		$returnIds = [];
		if (!$mailId) {
			return $returnIds;
		}

		$accounts = $mail->getActionResult('Accounts');
		if (!empty($accounts)) {
			$db = PearDatabase::getInstance();

			$query = 'SELECT servicecontractsid FROM vtiger_servicecontracts '
				. 'INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid '
				. 'WHERE vtiger_crmentity.deleted = 0 AND sc_related_to IN (' . implode(',', $accounts) . ') AND contract_status = ?';

			$result = $db->pquery($query, ['In Progress']);
			if ($db->getRowCount($result)) {
				$serviceContractsId = $db->getSingleValue($result);

				$result = $db->pquery('SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid=? AND crmid=?', [$mailId, $serviceContractsId]);
				if ($db->getRowCount($result) == 0) {
					$db->insert('vtiger_ossmailview_relation', [
						'ossmailviewid' => $mailId,
						'crmid' => $serviceContractsId,
						'date' => $mail->get('udate_formated')
					]);
					$returnIds[] = $serviceContractsId;
				}
			}
		}
		return $returnIds;
	}
}
