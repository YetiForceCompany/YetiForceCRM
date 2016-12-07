<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ***************************************************************************** */

class CustomerPortal
{

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{

		require_once('include/utils/utils.php');
		$adb = PearDatabase::getInstance();

		if ($eventType == 'module.postinstall') {
			$portalModules = array("HelpDesk", "Faq", "Products", "Services", "Documents",
				"Contacts", "Accounts", "Project", "ProjectTask", "ProjectMilestone", "Assets");

			$query = "SELECT max(sequence) AS max_tabseq FROM vtiger_customerportal_tabs";
			$res = $adb->pquery($query, array());
			$tabseq = $adb->query_result($res, 0, 'max_tabseq');
			$i = ++$tabseq;
			foreach ($portalModules as $module) {
				$tabIdResult = $adb->pquery('SELECT tabid FROM vtiger_tab WHERE name=?', array($module));
				$tabId = $adb->query_result($tabIdResult, 0, 'tabid');
				if ($tabId) {
					++$i;
					$adb->query("INSERT INTO vtiger_customerportal_tabs (tabid,visible,sequence) VALUES ($tabId,1,$i)");
					$adb->query("INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES ($tabId,'showrelatedinfo',1)");
				}
			}

			$adb->query("INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (0,'userid',1)");
			$adb->query("INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (0,'defaultassignee',1)");

			// Mark the module as Standard module
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($moduleName));
			Settings_Vtiger_Module_Model::addSettingsField('LBL_OTHER_SETTINGS', [
				'name' => 'LBL_CUSTOMER_PORTAL',
				'iconpath' => 'adminIcon-customer-portal',
				'description' => 'PORTAL_EXTENSION_DESCRIPTION',
				'linkto' => 'index.php?module=CustomerPortal&action=index&parenttab=Settings'
			]);
		} else if ($eventType == 'module.disabled') {

		} else if ($eventType == 'module.enabled') {

		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}
}
