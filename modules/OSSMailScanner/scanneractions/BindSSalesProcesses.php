<?php

/**
 * Mail scanner action bind SalesProcesses
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindSSalesProcesses_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{

	public $moduleName = 'SSalesProcesses';
	public $tableName = 'u_yf_ssalesprocesses';
	public $tableColumn = 'ssalesprocesses_no';

	public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;
		return parent::findAndBind();
	}
}
