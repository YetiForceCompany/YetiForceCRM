<?php

/**
 * Mail scanner action bind Project
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindProject_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{

	public function process($mail)
	{
		return parent::process($mail, 'Project', 'vtiger_project', 'project_no');
	}
}
