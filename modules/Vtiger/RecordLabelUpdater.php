<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once 'include/events/VTEventHandler.inc';

class Vtiger_RecordLabelUpdater_Handler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		$adb = PearDatabase::getInstance();

		if ($eventName == 'vtiger.entity.aftersave') {
			$labelInfo = getEntityName($data->getModuleName(), $data->getId(), true);

			if ($labelInfo) {
				$label = decode_html($labelInfo[$data->getId()]);
				$adb->pquery('UPDATE u_yf_crmentity_label SET label=? WHERE crmid=?', array($label, $data->getId()));
			}
		}
	}
}
