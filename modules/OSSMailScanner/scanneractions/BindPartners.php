<?php

/**
 * Mail scanner action bind Partners
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindPartners_ScannerAction extends OSSMailScanner_EmailScannerAction_Model
{

	public function process(OSSMail_Mail_Model $mail, $moduleName = 'Partners')
	{
		return parent::process($mail, 'Partners');
	}
}
