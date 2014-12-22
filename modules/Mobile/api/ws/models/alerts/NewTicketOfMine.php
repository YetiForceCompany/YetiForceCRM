<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/PendingTicketsOfMine.php';

/** New Ticket */
class Mobile_WS_AlertModel_NewTicketOfMine extends Mobile_WS_AlertModel_PendingTicketsOfMine {
	function __construct() {
		parent::__construct();
		$this->name = 'New Ticket Alert';
		$this->moduleName = 'HelpDesk';
		$this->refreshRate= 1 * (60 * 60); // 1 hour
		$this->description='Alert sent when a ticket is assigned to you';
	}
	
	function query() {
		$sql = parent::query();
		$sql .= " ORDER BY crmid DESC";
		return $sql;
	}
	
	function countQuery() {
		return str_replace("ORDER BY crmid DESC", "", $this->query());
	}
	
	function executeCount() {
		global $adb;
		$result = $adb->pquery($this->countQuery(), $this->queryParameters());
		return $adb->num_rows($result);
	}
}