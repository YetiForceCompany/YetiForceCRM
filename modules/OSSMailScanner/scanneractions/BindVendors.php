<?php

/**
 * Mail scanner action bind Vendors
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindVendors_ScannerAction extends OSSMailScanner_EmailScannerAction_Model
{

	public function process($mail)
	{
		return parent::process($mail, 'Vendors');
	}
}
