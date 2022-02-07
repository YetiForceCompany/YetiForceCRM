<?php
/**
 * Mail scanner action bind ServiceContracts.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Mail scanner action bind ServiceContracts.
 */
class OSSMailScanner_BindServiceContracts_ScannerAction
{
	/**
	 * Process.
	 *
	 * @param OSSMail_Mail_Model $mail
	 *
	 * @return array
	 */
	public function process(OSSMail_Mail_Model $mail)
	{
		$mailId = $mail->getMailCrmId();
		$returnIds = [];
		if (!$mailId) {
			return $returnIds;
		}

		$accountNumbers = [];
		$accounts = $mail->getActionResult('Accounts');
		if ($accounts) {
			$keys = ['BindAccounts', 'BindContacts', 'BindLeads', 'BindHelpDesk'];
			foreach ($keys as $key) {
				if (isset($accounts[$key]) && \is_array($accounts[$key])) {
					$accountNumbers = array_merge($accountNumbers, $accounts[$key]);
				}
			}
		}

		if ($accountNumbers) {
			$result = (new App\Db\Query())->select(['servicecontractsid'])->from('vtiger_servicecontracts')->innerJoin('vtiger_crmentity', 'vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid')->where(['vtiger_crmentity.deleted' => 0, 'sc_related_to' => $accountNumbers, 'contract_status' => 'In Progress'])->limit(2)->column();
			if ($result && 1 === \count($result)) {
				$serviceContractsId = current($result);
				$status = (new OSSMailView_Relation_Model())->addRelation($mailId, $serviceContractsId, $mail->get('date'));
				if ($status) {
					$returnIds[] = $serviceContractsId;
				}
			}
		}
		return $returnIds;
	}
}
