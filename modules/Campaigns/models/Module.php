<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Campaigns_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule)
	{
		if ($relatedModule === 'Leads') {
			$specificQuery = 'AND vtiger_leaddetails.converted = 0';
			return $specificQuery;
		}
		return parent::getSpecificRelationQuery($relatedModule);
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery)
	{
		if (in_array($sourceModule, array('Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'))) {
			$condition = " vtiger_campaign.campaignid NOT IN (SELECT campaignid FROM vtiger_campaign_records WHERE crmid = '$record')";
			$pos = stripos($listQuery, 'where');

			if ($pos) {
				$overRideQuery = $listQuery . ' && ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
}
