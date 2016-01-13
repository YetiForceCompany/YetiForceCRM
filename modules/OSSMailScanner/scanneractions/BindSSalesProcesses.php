<?php

/**
 * Mail scanner action bind SalesProcesses
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindSSalesProcesses_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{

	public function process($mail)
	{
		return parent::process($mail, 'SSalesProcesses', 'u_yf_ssalesprocesses', 'ssalesprocesses_no');
	}
}
