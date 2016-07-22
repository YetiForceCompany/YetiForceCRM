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

class Vtiger_RecordLabelUpdater_Handler extends VTEventHandler
{

	function handleEvent($eventName, $data)
	{
		$db = PearDatabase::getInstance();

		if ($eventName == 'vtiger.entity.aftersave') {
			$module = $data->getModuleName();
			if ($module != "Users") {
				$labelInfo = vtlib\Functions::computeCRMRecordLabels($module, $data->getId(), true);
				if (count($labelInfo) > 0) {
					$label = decode_html($labelInfo[$data->getId()]['name']);
					$search = decode_html($labelInfo[$data->getId()]['search']);
					if ($data->focus->mode == 'edit') {
						$db->pquery('UPDATE vtiger_crmentity 
						INNER JOIN vtiger_crmentity_search ON vtiger_crmentity_search.crmid = vtiger_crmentity.crmid
						INNER JOIN vtiger_crmentity_label ON vtiger_crmentity_label.crmid = vtiger_crmentity.crmid
						SET vtiger_crmentity_label.label = ?, vtiger_crmentity_search.searchlabel = ? WHERE vtiger_crmentity.crmid = ?', [$label, $search, $data->getId()]);
					} else {
						$db->insert('vtiger_crmentity_label', ['crmid' => $data->getId(), 'label' => $label]);
						$db->insert('vtiger_crmentity_search', ['crmid' => $data->getId(), 'searchlabel' => $search]);
					}
				}
			}
		}
	}
}
