<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Services_Record_Model extends Products_Record_Model
{

	/**
	 * Function to get acive status of record
	 */
	public function getActiveStatusOfRecord()
	{
		$activeStatus = $this->get('discontinued');
		if ($activeStatus) {
			return $activeStatus;
		}
		$recordId = $this->getId();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT discontinued FROM vtiger_service WHERE serviceid = ?', array($recordId));
		$activeStatus = $db->query_result($result, 'discontinued');
		return $activeStatus;
	}
}
