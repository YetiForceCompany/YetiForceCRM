<?php

/**
 * Mail scanner action bind Campaigns
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindCampaigns_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{

	public function process($mail)
	{
		return parent::process($mail, 'Campaigns', 'vtiger_campaign', 'campaign_no');
	}
}
