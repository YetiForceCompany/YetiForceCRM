<?php
/**
 * Mail scanner action bind Campaigns.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mail scanner action bind Campaigns.
 */
class OSSMailScanner_BindCampaigns_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName = 'Campaigns';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $tableName = 'vtiger_campaign';

	/**
	 * Table column.
	 *
	 * @var string
	 */
	public $tableColumn = 'campaign_no';

	/**
	 * Process.
	 *
	 * @param OSSMail_Mail_Model $mail
	 *
	 * @return array
	 */
	public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;
		$campaignIds = $this->findAndBind();
		if (0 == $mail->get('type') && false !== $campaignIds && 0 != $campaignIds) {
			$crmIds = [];
			$crmidsToaddress = $mail->findEmailAddress('to_email', false, true);
			$crmidsCcaddress = $mail->findEmailAddress('cc_email', false, true);
			$crmidsBccaddress = $mail->findEmailAddress('bcc_email', false, true);
			$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsToaddress);
			$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsCcaddress);
			$crmIds = OSSMailScanner_Record_Model::mergeArray($crmIds, $crmidsBccaddress);
			$dbCommand = \App\Db::getInstance()->createCommand();
			foreach ($campaignIds as $campaignId) {
				foreach ($crmIds as $recordId) {
					$dbCommand->update('vtiger_campaign_records', ['campaignrelstatusid' => 1], ['campaignid' => $campaignId, 'crmid' => $recordId])->execute();
				}
			}
		}
		return $campaignIds;
	}
}
