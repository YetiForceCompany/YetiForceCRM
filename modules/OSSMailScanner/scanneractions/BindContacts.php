<?php

/**
 * Mail scanner action bind Contacts.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindContacts_ScannerAction extends OSSMailScanner_EmailScannerAction_Model
{
	public function process(OSSMail_Mail_Model $mail, $moduleName = 'Contacts')
	{
		return parent::process($mail, 'Contacts');
	}
}
