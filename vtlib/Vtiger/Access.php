<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

namespace vtlib;

/**
 * Provides API to control Access like Sharing, Tools etc. for vtiger CRM Module.
 */
class Access
{
	/**
	 * Recalculate sharing access rules.
	 */
	public static function syncSharingAccess()
	{
		\App\Log::trace('Recalculating sharing rules ... ', __METHOD__);
		\App\UserPrivilegesFile::recalculateAll();
		\App\Log::trace('DONE', __METHOD__);
	}

	/**
	 * Enable or Disable sharing access control to module.
	 *
	 * @param ModuleBasic $moduleInstance
	 * @param bool true to enable sharing access, false disable sharing access
	 * @param mixed $enable
	 */
	public static function allowSharing(ModuleBasic $moduleInstance, $enable = true)
	{
		$ownedBy = $enable ? 0 : 1;
		\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['ownedby' => $ownedBy], ['tabid' => $moduleInstance->id])->execute();
		\App\Log::trace(($enable ? 'Enabled' : 'Disabled') . ' sharing access control ... DONE', __METHOD__);
	}

	/**
	 * Initialize sharing access.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public static function initSharing(ModuleBasic $moduleInstance)
	{
		$query = (new \App\Db\Query())->select(['share_action_id'])->from('vtiger_org_share_action_mapping')
			->where(['share_action_name' => ['Public: Read Only', 'Public: Read, Create/Edit', 'Public: Read, Create/Edit, Delete', 'Private']]);
		$actionIds = $query->column();
		$insertedData = [];
		foreach ($actionIds as $id) {
			$insertedData[] = [$id, $moduleInstance->id];
		}
		\App\Db::getInstance()->createCommand()
			->batchInsert('vtiger_org_share_action2tab', ['share_action_id', 'tabid'], $insertedData)
			->execute();
		\App\Log::trace('Setting up sharing access options ... DONE', __METHOD__);
	}

	/**
	 * Delete sharing access setup for module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public static function deleteSharing(ModuleBasic $moduleInstance)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_org_share_action2tab', ['tabid' => $moduleInstance->id])->execute();
		\App\Log::trace('Deleting sharing access ... DONE', __METHOD__);
	}

	/**
	 * Set default sharing for a module.
	 *
	 * @param ModuleBasic $moduleInstance
	 * @param string Permission text should be one of ['Public_ReadWriteDelete', 'Public_ReadOnly', 'Public_ReadWrite', 'Private']
	 * @param mixed $permissionText
	 */
	public static function setDefaultSharing(ModuleBasic $moduleInstance, $permissionText = 'Public_ReadWriteDelete')
	{
		$permissionText = strtolower($permissionText);

		if ('public_readonly' === $permissionText) {
			$permission = 0;
		} elseif ('public_readwrite' === $permissionText) {
			$permission = 1;
		} elseif ('public_readwritedelete' === $permissionText) {
			$permission = 2;
		} elseif ('private' === $permissionText) {
			$permission = 3;
		} else {
			$permission = 2;
		} // public_readwritedelete is default

		$editstatus = 0; // 0 or 1

		$ruleId = (new \App\Db\Query())->select(['ruleid'])->from('vtiger_def_org_share')->where(['tabid' => $moduleInstance->id])->scalar();
		if ($ruleId) {
			\App\Db::getInstance()->createCommand()->update('vtiger_def_org_share', ['permission' => $permission], ['ruleid' => $ruleId])->execute();
		} else {
			\App\Db::getInstance()->createCommand()->insert('vtiger_def_org_share', ['tabid' => $moduleInstance->id, 'permission' => $permission, 'editstatus' => $editstatus])->execute();
		}

		self::syncSharingAccess();
	}

	/**
	 * Enable tool for module.
	 *
	 * @param ModuleBasic $moduleInstance
	 * @param string Tool (action name) like Import, Export
	 * @param bool true to enable tool, false to disable
	 * @param int (optional) profile id to use, false applies to all profile
	 * @param mixed $toolAction
	 * @param mixed $flag
	 * @param mixed $profileid
	 */
	public static function updateTool(ModuleBasic $moduleInstance, $toolAction, $flag, $profileid = false)
	{
		$actionId = \App\Module::getActionId($toolAction);
		if ($actionId) {
			$permission = (true === $flag) ? '0' : '1';

			$profileids = [];
			if ($profileid) {
				$profileids[] = $profileid;
			} else {
				$profileids = Profile::getAllIds();
			}

			\App\Log::trace(($flag ? 'Enabling' : 'Disabling') . " $toolAction for Profile [", __METHOD__);
			$db = \App\Db::getInstance();
			foreach ($profileids as &$useProfileId) {
				$isExists = (new \App\Db\Query())->from('vtiger_profile2utility')
					->where(['profileid' => $useProfileId, 'tabid' => $moduleInstance->id, 'activityid' => $actionId])
					->exists();
				if ($isExists) {
					$db->createCommand()->update('vtiger_profile2utility', ['permission' => $permission], ['profileid' => $useProfileId, 'tabid' => $moduleInstance->id, 'activityid' => $actionId])->execute();
				} else {
					$db->createCommand()->insert('vtiger_profile2utility', ['profileid' => $useProfileId, 'tabid' => $moduleInstance->id, 'activityid' => $actionId, 'permission' => $permission])->execute();
				}
				\App\Log::trace("$useProfileId,", __METHOD__);
			}
			\App\Log::trace('] ... DONE', __METHOD__);
		}
	}

	/**
	 * Delete tool (actions) of the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public static function deleteTools(ModuleBasic $moduleInstance)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_profile2utility', ['tabid' => $moduleInstance->id])->execute();
		\App\Log::trace('Deleting tools ... DONE', __METHOD__);
	}
}
