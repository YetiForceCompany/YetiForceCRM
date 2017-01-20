<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */
namespace vtlib;

/**
 * Provides API to control Access like Sharing, Tools etc. for vtiger CRM Module
 * @package vtlib
 */
class Access
{

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim = true)
	{
		Utils::Log($message, $delim);
	}

	/**
	 * Recalculate sharing access rules.
	 * @internal This function could take up lot of resource while execution
	 * @access private
	 */
	static function syncSharingAccess()
	{
		self::log("Recalculating sharing rules ... ", false);
		RecalculateSharingRules();
		self::log("DONE");
	}

	/**
	 * Enable or Disable sharing access control to module
	 * @param Module Instance of the module to use
	 * @param Boolean true to enable sharing access, false disable sharing access
	 * @access private
	 */
	static function allowSharing($moduleInstance, $enable = true)
	{
		$ownedBy = $enable ? 0 : 1;
		\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['ownedby' => $ownedBy], ['tabid' => $moduleInstance->id])->execute();
		self::log(($enable ? 'Enabled' : 'Disabled') . ' sharing access control ... DONE');
	}

	/**
	 * Initialize sharing access.
	 * @param Module Instance of the module to use
	 * @access private
	 * @internal This method is called from Module during creation.
	 */
	static function initSharing($moduleInstance)
	{
		$query = (new \App\Db\Query)->select(['share_action_id'])->from('vtiger_org_share_action_mapping')
			->where(['share_action_name' => ['Public: Read Only', 'Public: Read, Create/Edit', 'Public: Read, Create/Edit, Delete', 'Private']]);
		$actionIds = $query->column();
		$insertedData = [];
		foreach ($actionIds as $id) {
			$insertedData [] = [$id, $moduleInstance->id];
		}
		\App\Db::getInstance()->createCommand()
			->batchInsert('vtiger_org_share_action2tab', ['share_action_id', 'tabid'], $insertedData)
			->execute();
		self::log("Setting up sharing access options ... DONE");
	}

	/**
	 * Delete sharing access setup for module
	 * @param Module Instance of module to use
	 * @access private
	 * @internal This method is called from Module during deletion.
	 */
	static function deleteSharing($moduleInstance)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_org_share_action2tab', ['tabid' => $moduleInstance->id])->execute();
		self::log("Deleting sharing access ... DONE");
	}

	/**
	 * Set default sharing for a module
	 * @param Module Instance of the module
	 * @param String Permission text should be one of ['Public_ReadWriteDelete', 'Public_ReadOnly', 'Public_ReadWrite', 'Private']
	 * @access private
	 */
	static function setDefaultSharing($moduleInstance, $permissionText = 'Public_ReadWriteDelete')
	{
		$permissionText = strtolower($permissionText);

		if ($permissionText === 'public_readonly')
			$permission = 0;
		else if ($permissionText === 'public_readwrite')
			$permission = 1;
		else if ($permissionText === 'public_readwritedelete')
			$permission = 2;
		else if ($permissionText === 'private')
			$permission = 3;
		else
			$permission = 2; // public_readwritedelete is default

		$editstatus = 0; // 0 or 1

		$ruleId = (new \App\Db\Query)->select(['ruleid'])->from('vtiger_def_org_share')->where(['tabid' => $moduleInstance->id])->scalar();
		if ($ruleId) {
			\App\Db::getInstance()->createCommand()->update('vtiger_def_org_share', ['permission' => $permission], ['ruleid' => $ruleId])->execute();
		} else {
			\App\Db::getInstance()->createCommand()->insert('vtiger_def_org_share', ['tabid' => $moduleInstance->id, 'permission' => $permission, 'editstatus' => $editstatus])->execute();
		}

		self::syncSharingAccess();
	}

	/**
	 * Enable tool for module.
	 * @param Module Instance of module to use
	 * @param String Tool (action name) like Import, Export, Merge
	 * @param Boolean true to enable tool, false to disable
	 * @param Integer (optional) profile id to use, false applies to all profile.
	 * @access private
	 */
	static function updateTool($moduleInstance, $toolAction, $flag, $profileid = false)
	{
		$actionId = getActionid($toolAction);
		if ($actionId) {
			$permission = ($flag === true) ? 0 : 1;

			$profileids = [];
			if ($profileid) {
				$profileids[] = $profileid;
			} else {
				$profileids = Profile::getAllIds();
			}

			self::log(($flag ? 'Enabling' : 'Disabling') . " $toolAction for Profile [", false);
			$db = \App\Db::getInstance();
			foreach ($profileids as &$useProfileId) {
				$curpermission = (new \App\Db\Query)->select('permission')->from('vtiger_profile2utility')
					->where(['profileid' => $useProfileId, 'tabid' => $moduleInstance->id, 'activityid' => $actionId])
					->scalar();
				if ($curpermission) {
					if ($curpermission !== $permission) {
						$db->createCommand()->update('vtiger_profile2utility', ['permission' => $permission], ['profileid' => $useProfileId, 'tabid' => $moduleInstance->id, 'activityid' => $actionId])->execute();
					}
				} else {
					$db->createCommand()->insert('vtiger_profile2utility', ['profileid' => $useProfileId, 'tabid' => $moduleInstance->id, 'activityid' => $actionId, 'permission' => $permission])->execute();
				}

				self::log("$useProfileId,", false);
			}
			self::log("] ... DONE");
		}
	}

	/**
	 * Delete tool (actions) of the module
	 * @param Module Instance of module to use
	 */
	static function deleteTools($moduleInstance)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_profile2utility', ['tabid' => $moduleInstance->id])->execute();
		self::log("Deleting tools ... DONE");
	}
}
