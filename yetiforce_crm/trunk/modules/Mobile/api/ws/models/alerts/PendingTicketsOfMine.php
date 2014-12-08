<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/../Alert.php';

/** Pending Ticket Alert */
class Mobile_WS_AlertModel_PendingTicketsOfMine extends Mobile_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'Pending Ticket Alert';
		$this->moduleName = 'HelpDesk';
		$this->refreshRate= 1 * (24* 60 * 60); // 1 day
		$this->description='Alert sent when ticket assigned is not yet closed';
	}
	
	function query() {
		$sql = "SELECT crmid FROM vtiger_troubletickets INNER JOIN 
				vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_troubletickets.ticketid 
				WHERE vtiger_crmentity.deleted=0 AND vtiger_crmentity.smownerid=? AND 
				vtiger_troubletickets.status <> 'Closed'";
		return $sql;
	}
	
	function queryParameters() {
		return array($this->getUser()->id);
	}
}