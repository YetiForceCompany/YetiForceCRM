<?php

/**
 * Mail scanner action bind Competition.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindCompetition_ScannerAction extends OSSMailScanner_EmailScannerAction_Model
{
	public function process(OSSMail_Mail_Model $mail, $moduleName = 'Competition')
	{
		return parent::process($mail, 'Competition');
	}
}
