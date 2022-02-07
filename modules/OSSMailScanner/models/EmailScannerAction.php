<?php

/**
 * Base for action creating relations on the basis of mail address.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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

		$crmIds = $mail->findEmailAddress('from_email', $moduleName, true);
		$crmidsToaddress = $mail->findEmailAddress('to_email', $moduleName, true);
		$crmidsCcaddress = $mail->findEmailAddress('cc_email', $moduleName, true);
		$crmidsBccaddress = $mail->findEmailAddress('bcc_email', $moduleName, true);
		$crmidsReplyToaddress = $mail->findEmailAddress('reply_toaddress', $moduleName, true);
		$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsToaddress);
		$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsCcaddress);
		$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsBccaddress);
		$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsReplyToaddress);
		$returnIds = [];

		if (!empty($crmIds)) {
			$relationModel = new OSSMailView_Relation_Model();
			foreach ($crmIds as $crmId) {
				$status = $relationModel->addRelation($mailId, $crmId, $mail->get('date'));
				if ($status) {
					$returnIds[] = $crmId;
				}
			}
		}
		return $returnIds;
	}
}
