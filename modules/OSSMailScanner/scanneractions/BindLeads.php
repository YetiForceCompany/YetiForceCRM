<?php

/**
 * Mail scanner action bind Leads
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindLeads_ScannerAction extends OSSMailScanner_EmailScannerAction_Model
{

	public function process(OSSMail_Mail_Model $mail, $moduleName = 'Leads')
	{
		return parent::process($mail, 'Leads');
	}
}
