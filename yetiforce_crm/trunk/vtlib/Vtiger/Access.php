<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
include_once('include/utils/UserInfoUtil.php');
include_once('vtlib/Vtiger/Utils.php');
include_once('vtlib/Vtiger/Profile.php');

/**
 * Provides API to control Access like Sharing, Tools etc. for vtiger CRM Module
 * @package vtlib
 */
class Vtiger_Access {

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim=true) {
		Vtiger_Utils::Log($message, $delim);
	}

	/**
	 * Get unique id for sharing access record.
	 * @access private
	 */
	static function __getDefaultSharingAccessId() {
		global $adb;
		return $adb->getUniqueID('vtiger_def_org_share');
	}

	/**
	 * Recalculate sharing access rules.
	 * @internal This function could take up lot of resource while execution
	 * @access private
	 */
	static function syncSharingAccess() {
		self::log("Recalculating sharing rules ... ", false);
		RecalculateSharingRules();
		self::log("DONE");
	}

	/**
	 * Enable or Disable sharing access control to module
	 * @param Vtiger_Module Instance of the module to use
	 * @param Boolean true to enable sharing access, false disable sharing access
	 * @access private
	 */
	static function allowSharing($moduleInstance, $enable=true) {
		global $adb;
		$ownedby = $enable? 0 : 1;
		$adb->pquery("UPDATE vtiger_tab set ownedby=? WHERE tabid=?", Array($ownedby, $moduleInstance->id));
		self::log(($enable? "Enabled" : "Disabled") . " sharing access control ... DONE");
	}

	/**
	 * Initialize sharing access.
	 * @param Vtiger_Module Instance of the module to use
	 * @access private
	 * @internal This method is called from Vtiger_Module during creation.
	 */
	static function initSharing($moduleInstance) {
		global $adb;

		$result = $adb->query("SELECT share_action_id from vtiger_org_share_action_mapping WHERE share_action_name in
			('Public: Read Only', 'Public: Read, Create/Edit', 'Public: Read, Create/Edit, Delete', 'Private')");

		for($index = 0; $index < $adb->num_rows($result); ++$index) {
			$actionid = $adb->query_result($result, $index, 'share_action_id');
			$adb->pquery("INSERT INTO vtiger_org_share_action2tab(share_action_id,tabid) VALUES(?,?)", Array($actionid, $moduleInstance->id));
		}
		self::log("Setting up sharing access options ... DONE");
	}

	/**
	 * Delete sharing access setup for module
	 * @param Vtiger_Module Instance of module to use
	 * @access private
	 * @internal This method is called from Vtiger_Module during deletion.
	 */
	static function deleteSharing($moduleInstance) {
		global $adb;
		$adb->pquery("DELETE FROM vtiger_org_share_action2tab WHERE tabid=?", Array($moduleInstance->id));
		self::log("Deleting sharing access ... DONE");
	}

	/**
	 * Set default sharing for a module
	 * @param Vtiger_Module Instance of the module
	 * @param String Permission text should be one of ['Public_ReadWriteDelete', 'Public_ReadOnly', 'Public_ReadWrite', 'Private']
	 * @access private
	 */
	static function setDefaultSharing($moduleInstance, $permission_text='Public_ReadWriteDelete') {
		global $adb;

		$permission_text = strtolower($permission_text);

		if($permission_text == 'public_readonly')             $permission = 0;
		else if($permission_text == 'public_readwrite')       $permission = 1;
		else if($permission_text == 'public_readwritedelete') $permission = 2;
		else if($permission_text == 'private')                $permission = 3;
		else $permission = 2; // public_readwritedelete is default

		$editstatus = 0; // 0 or 1

		$result = $adb->pquery("SELECT * FROM vtiger_def_org_share WHERE tabid=?", Array($moduleInstance->id));
		if($adb->num_rows($result)) {
			$ruleid = $adb->query_result($result, 0, 'ruleid');
			$adb->pquery("UPDATE vtiger_def_org_share SET permission=? WHERE ruleid=?", Array($permission, $ruleid));
		} else {
			$ruleid = self::__getDefaultSharingAccessId();
			$adb->pquery("INSERT INTO vtiger_def_org_share (ruleid,tabid,permission,editstatus) VALUES(?,?,?,?)",
				Array($ruleid,$moduleInstance->id,$permission,$editstatus));
		}

		self::syncSharingAccess();
	}

	/**
	 * Enable tool for module.
	 * @param Vtiger_Module Instance of module to use
	 * @param String Tool (action name) like Import, Export, Merge
	 * @param Boolean true to enable tool, false to disable
	 * @param Integer (optional) profile id to use, false applies to all profile.
	 * @access private
	 */
	static function updateTool($moduleInstance, $toolAction, $flag, $profileid=false) {
		global $adb;

		$result = $adb->pquery("SELECT actionid FROM vtiger_actionmapping WHERE actionname=?", Array($toolAction));
		if($adb->num_rows($result)) {
			$actionid = $adb->query_result($result, 0, 'actionid');
			$permission = ($flag == true)? '0' : '1';

			$profileids = Array();
			if($profileid) {
				$profileids[] = $profileid;
			} else {
				$profileids = Vtiger_Profile::getAllIds();
			}

			self::log( ($flag? 'Enabling':'Disabling') . " $toolAction for Profile [", false);

			foreach($profileids as $useprofileid) {
				$result = $adb->pquery("SELECT permission FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?",
					Array($useprofileid, $moduleInstance->id, $actionid));
				if($adb->num_rows($result)) {
					$curpermission = $adb->query_result($result, 0, 'permission');
					if($curpermission != $permission) {
						$adb->pquery("UPDATE vtiger_profile2utility set permission=? WHERE profileid=? AND tabid=? AND activityid=?",
							Array($permission, $useprofileid, $moduleInstance->id, $actionid));
					}
				} else {
					$adb->pquery("INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES(?,?,?,?)",
					   	Array($useprofileid, $moduleInstance->id, $actionid, $permission));
				}

				self::log("$useprofileid,", false);
			}
			self::log("] ... DONE");
		}
	}

	/**
	 * Delete tool (actions) of the module
	 * @param Vtiger_Module Instance of module to use
	 */
	static function deleteTools($moduleInstance) {
		global $adb;
		$adb->pquery("DELETE FROM vtiger_profile2utility WHERE tabid=?", Array($moduleInstance->id));
		self::log("Deleting tools ... DONE");
	}
}
?>
