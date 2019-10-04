<?php

namespace App\Db;

/**
 * Class that repaire structure and data in database.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Fixer
{
	/**
	 * Add missing entries in vtiger_profile2field.
	 */
	public static function profileField(): int
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$i = 0;
		$profileIds = \vtlib\Profile::getAllIds();
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($profileIds as $profileId) {
			$subQuery = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_profile2field')->where(['profileid' => $profileId]);
			$query = (new \App\Db\Query())->select(['tabid', 'fieldid'])->from('vtiger_field')->where(['not in', 'vtiger_field.fieldid', $subQuery]);
			$data = $query->createCommand()->queryAllByGroup(2);
			foreach ($data as $tabId => $fieldIds) {
				foreach ($fieldIds as $fieldId) {
					$isExists = (new \App\Db\Query())->from('vtiger_profile2field')->where(['profileid' => $profileId, 'fieldid' => $fieldId])->exists();
					if (!$isExists) {
						$dbCommand->insert('vtiger_profile2field', ['profileid' => $profileId, 'tabid' => $tabId, 'fieldid' => $fieldId, 'visible' => 0, 'readonly' => 0])->execute();
						++$i;
					}
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
		return $i;
	}

	/**
	 * Add missing entries in vtiger_profile2utility.
	 */
	public static function baseModuleTools(): int
	{
		$i = 0;
		$missing = $curentProfile2utility = [];
		foreach ((new \App\Db\Query())->from('vtiger_profile2utility')->all() as $row) {
			$curentProfile2utility[$row['tabid']][$row['activityid']] = true;
		}
		$profileIds = \vtlib\Profile::getAllIds();
		$moduleIds = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['isentitytype' => 1])->column();
		$baseActionIds = array_map('App\Module::getActionId', \Settings_ModuleManager_Module_Model::$baseModuleTools);
		$exceptions = \Settings_ModuleManager_Module_Model::getBaseModuleToolsExceptions();
		foreach ($profileIds as $profileId) {
			foreach ($moduleIds as $moduleId) {
				foreach ($baseActionIds as $actionId) {
					if (!isset($curentProfile2utility[$moduleId][$actionId])) {
						$missing[] = ['profileid' => $profileId, 'tabid' => $moduleId, 'activityid' => $actionId];
					}
				}
			}
		}
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($missing as $row) {
			if (isset($exceptions[$row['tabid']]['allowed'])) {
				if (!isset($exceptions[$row['tabid']]['allowed'][$row['activityid']])) {
					continue;
				}
			} elseif (isset($exceptions[$row['tabid']]['notAllowed']) && (false === $exceptions[$row['tabid']]['notAllowed'] || isset($exceptions[$row['tabid']]['notAllowed'][$row['activityid']]))) {
				continue;
			}
			$dbCommand->insert('vtiger_profile2utility', ['profileid' => $row['profileid'], 'tabid' => $row['tabid'], 'activityid' => $row['activityid'], 'permission' => 1])->execute();
			++$i;
		}
		\Settings_SharingAccess_Module_Model::recalculateSharingRules();
		return $i;
	}

	/**
	 * Add missing entries in vtiger_profile2standardpermissions.
	 */
	public static function baseModuleActions(): int
	{
		$i = 0;
		$curentProfile = [];
		foreach ((new \App\Db\Query())->from('vtiger_profile2standardpermissions')->all() as $row) {
			$curentProfile[$row['profileid']][$row['tabid']][$row['operation']] = $row['permissions'];
		}
		$moduleIds = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['isentitytype' => 1])->column();
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach (\vtlib\Profile::getAllIds() as $profileId) {
			foreach ($moduleIds as $moduleId) {
				foreach (\Vtiger_Action_Model::$standardActions as $actionId => $actionName) {
					if (!isset($curentProfile[$profileId][$moduleId][$actionId])) {
						$dbCommand->insert('vtiger_profile2standardpermissions', ['profileid' => $profileId, 'tabid' => $moduleId, 'operation' => $actionId, 'permissions' => 1])->execute();
						++$i;
					}
				}
			}
		}
		\Settings_SharingAccess_Module_Model::recalculateSharingRules();
		return $i;
	}
}
