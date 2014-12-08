<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class MobileHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {

		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
		}

		if($eventName == 'vtiger.entity.aftersave') {
			// Entity has been saved, take next action
		}
	}
}

?>
