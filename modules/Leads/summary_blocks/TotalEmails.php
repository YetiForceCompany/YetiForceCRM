<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class TotalEmails
{

	public $name = 'Total emails';
	public $sequence = 1;
	public $reference = 'OSSMailView';

	public function process($instance)
	{
		$log = vglobal('log');
		$log->debug("Entering TotalEmails::process() method ...");
		$adb = PearDatabase::getInstance();
		$emails = 'SELECT COUNT(DISTINCT vtiger_ossmailview.ossmailviewid) AS mails
			FROM vtiger_ossmailview 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailview.ossmailviewid 
			INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid || vtiger_crmentityrel.crmid = vtiger_crmentity.crmid) 
			WHERE vtiger_crmentity.deleted = 0 && (vtiger_crmentityrel.crmid = ? || vtiger_crmentityrel.relcrmid = ?)';
		$result_mailview = $adb->pquery($emails, array($instance->getId(), $instance->getId()));
		$count = $adb->query_result($result_mailview, 0, 'mails');
		$log->debug("Exiting TotalEmails::process() method ...");
		return $count;
	}
}
