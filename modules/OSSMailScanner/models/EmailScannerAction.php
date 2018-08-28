<?php

/**
 * Base for action creating relations on the basis of mail address.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_EmailScannerAction_Model
{
	public function process(OSSMail_Mail_Model $mail, $moduleName)
	{
		$mailId = $mail->getMailCrmId();
		if (!$mailId) {
			return 0;
		}

		$crmIds = $mail->findEmailAdress('fromaddress', $moduleName, true);
		$crmidsToaddress = $mail->findEmailAdress('toaddress', $moduleName, true);
		$crmidsCcaddress = $mail->findEmailAdress('ccaddress', $moduleName, true);
		$crmidsBccaddress = $mail->findEmailAdress('bccaddress', $moduleName, true);
		$crmidsReplyToaddress = $mail->findEmailAdress('reply_toaddress', $moduleName, true);
		$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsToaddress);
		$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsCcaddress);
		$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsBccaddress);
		$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsReplyToaddress);
		$returnIds = [];

		if (!empty($crmIds)) {
			$relationModel = new OSSMailView_Relation_Model();
			foreach ($crmIds as $crmId) {
				$status = $relationModel->addRelation($mailId, $crmId, $mail->get('udate_formated'));
				if ($status) {
					$returnIds[] = $crmId;
				}
			}
		}
		return $returnIds;
	}
}
