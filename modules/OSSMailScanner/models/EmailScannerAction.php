<?php

/**
 * Base for action creating relations on the basis of mail address
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_EmailScannerAction_Model extends OSSMailScanner_BaseScannerAction_Model
{

	public function process($mail, $moduleName)
	{
		$db = PearDatabase::getInstance();
		$mailId = $mail->getMailCrmId();
		if (!$mailId) {
			return 0;
		}

		$crmIds = $mail->findEmailAdress('fromaddress', $moduleName, true);
		$crmidsToaddress = $mail->findEmailAdress('toaddress', $moduleName, true);
		$crmidsCcaddress = $mail->findEmailAdress('ccaddress', $moduleName, true);
		$crmidsBccaddress = $mail->findEmailAdress('bccaddress', $moduleName, true);
		$crmidsReplyToaddress = $mail->findEmailAdress('reply_toaddress', $moduleName, true);
		$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsToaddress);
		$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsCcaddress);
		$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsBccaddress);
		$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsReplyToaddress);
		$returnIds = [];

		if (!empty($crmIds)) {
			foreach ($crmIds as $crmId) {
				$result = $db->pquery('SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid=? AND crmid=?', [$mailId, $crmId]);
				if ($db->getRowCount($result)) {
					continue;
				}
				$db->insert('vtiger_ossmailview_relation', [
					'ossmailviewid' => $mailId,
					'crmid' => $crmId,
					'date' => $mail->get('udate_formated')
				]);
				$returnIds[] = $crmId;
			}
		}
		return $returnIds;
	}
}
