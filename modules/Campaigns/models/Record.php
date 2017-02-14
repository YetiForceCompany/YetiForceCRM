<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Campaigns_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function to get selected ids list of related module for send email
	 * @param string $relatedModuleName
	 * @param <array> $excludedIds
	 * @return <array> List of selected ids
	 */
	public function getSelectedIdsList($relatedModuleName, $excludedIds = false)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT vtiger_campaign_records.crmid FROM vtiger_campaign_records
					INNER JOIN vtiger_crmentity ON vtiger_campaign_records.crmid = vtiger_crmentity.crmid && vtiger_crmentity.deleted = ?
					WHERE campaignid = ?';
		if ($excludedIds) {
			$query .= ' && vtiger_campaign_records.crmid NOT IN (' . implode(',', $excludedIds) . ')';
		}

		$result = $db->pquery($query, array(0, $this->getId()));
		$selectedIdsList = [];
		while (($crmid = $db->getSingleValue($result)) !== false) {
			$selectedIdsList[] = $crmid;
		}
		return $selectedIdsList;
	}
}
