<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
include_once 'vtlib/Vtiger/Mailer.php';

class Emails_Mailer_Model extends Vtiger_Mailer {

	public static function getInstance() {
		return new self();
	}

	/**
	 * Function returns error from phpmailer
	 * @return <String>
	 */
	function getError() {
		return $this->ErrorInfo;
	}
}
