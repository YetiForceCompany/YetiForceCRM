<?php

/**
 * Mail scanner action bind Campaigns
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindCampaigns_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{

	public function process(OSSMail_Mail_Model $mail)
	{
		$campaignIds = parent::process($mail, 'Campaigns', 'vtiger_campaign', 'campaign_no');
		if ($mail->get('type') == 0 && $campaignIds !== false && $campaignIds != 0) {
			$crmIds = [];
			$crmidsToaddress = $mail->findEmailAdress('toaddress', false, true);
			$crmidsCcaddress = $mail->findEmailAdress('ccaddress', false, true);
			$crmidsBccaddress = $mail->findEmailAdress('bccaddress', false, true);
			$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsToaddress);
			$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsCcaddress);
			$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsBccaddress);
			$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsReplyToaddress);
			$returnIds = [];
			
			$db = PearDatabase::getInstance();
			foreach ($campaignIds as $campaignId) {
				foreach ($crmIds as $recordId) {
					$db->update('vtiger_campaign_records', [
						'campaignrelstatusid' => 1
						], 'campaignid = ? AND crmid = ?', [$campaignId, $recordId]
					);
				}
			}
		}
		return $campaignIds;
	}
}
