<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'modules/Emails/mail.php';

class HelpDeskHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $log, $adb;

		if($eventName == 'vtiger.entity.aftersave.final') {
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'HelpDesk') {
				$ticketId = $entityData->getId();
				$adb->pquery('UPDATE vtiger_ticketcf SET from_portal=0 WHERE ticketid=?', array($ticketId));
			}
		}
	}
}