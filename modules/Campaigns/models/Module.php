<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Campaigns_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, App\QueryGenerator $queryGenerator)
	{
		if (\in_array($sourceModule, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			$subQuery = (new App\Db\Query())->select(['campaignid'])->from('vtiger_campaign_records')->where(['crmid' => $record]);
			$queryGenerator->addNativeCondition(['not in', 'vtiger_campaign.campaignid', $subQuery]);
		}
	}
}
