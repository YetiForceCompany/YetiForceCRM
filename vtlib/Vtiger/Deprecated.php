<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

namespace vtlib;

/**
 * Functions that need-rewrite / to be eliminated.
 */
class Deprecated
{
	public static function getFullNameFromArray($module, $fieldValues)
	{
		$entityInfo = \App\Module::getEntityInfo($module);
		$fieldsName = $entityInfo['fieldname'];
		return self::getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues);
	}

	/**
	 * this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname).
	 *
	 * @param1 $module - name of the module
	 * @param2 $fieldsName - fieldname with respect to module (ex : 'Accounts' - 'accountname', 'Contacts' - 'lastname','firstname')
	 * @param3 $fieldValues - array of fieldname and its value
	 *
	 * @return string $fieldConcatName - the entity field name for the module
	 */
	public static function getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues)
	{
		if (strpos($fieldsName, ',') === false) {
			return $fieldValues[$fieldsName];
		} else {
			$accessibleFieldNames = [];
			foreach (explode(',', $fieldsName) as $field) {
				if ($module === 'Users' || \App\Field::getColumnPermission($module, $field)) {
					$accessibleFieldNames[] = $fieldValues[$field];
				}
			}
			if (count($accessibleFieldNames) > 0) {
				return implode(' ', $accessibleFieldNames);
			}
		}
		return '';
	}

	/** Function to check the file access is made within web root directory and whether it is not from unsafe directories */
	public static function checkFileAccessForInclusion($filepath)
	{
		$unsafeDirectories = ['storage', 'cache', 'test'];
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (stripos($realfilepath, $rootdirpath) !== 0 || in_array($filePathParts[0], $unsafeDirectories)) {
			\App\Log::error(__METHOD__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
			throw new \App\Exceptions\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/** Function to check the file deletion within the deletable (safe) directories */
	public static function checkFileAccessForDeletion($filepath)
	{
		$safeDirectories = ['storage', 'cache', 'test'];
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (stripos($realfilepath, $rootdirpath) !== 0 || !in_array($filePathParts[0], $safeDirectories)) {
			\App\Log::error(__METHOD__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
			throw new \App\Exceptions\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/** Function to check the file access is made within web root directory. */
	public static function checkFileAccess($filepath)
	{
		if (!self::isFileAccessible($filepath)) {
			\App\Log::error(__METHOD__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($filepath, true));
			throw new \App\Exceptions\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/**
	 * function to return whether the file access is made within vtiger root directory
	 * and it exists.
	 *
	 * @param string $filepath relative path to the file which need to be verified
	 *
	 * @return bool true if file is a valid file within vtiger root directory, false otherwise
	 */
	public static function isFileAccessible($filepath)
	{
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		if (stripos($realfilepath, $rootdirpath) !== 0) {
			return false;
		}
		return true;
	}

	/**
	 * This function is used to get the blockid of the settings block for a given label.
	 *
	 * @param string $label
	 *
	 * @return int
	 */
	public static function getSettingsBlockId($label)
	{
		$blockId = 0;
		$dataReader = (new \App\Db\Query())->select(['blockid'])
			->from('vtiger_settings_blocks')
			->where(['label' => $label])
			->createCommand()->query();
		if ($dataReader->count() === 1) {
			$blockId = $dataReader->readColumn(0);
		}
		$dataReader->close();

		return $blockId;
	}

	public static function getSqlForNameInDisplayFormat($input, $module, $glue = ' ')
	{
		$entityFieldInfo = \App\Module::getEntityInfo($module);
		$fieldsName = $entityFieldInfo['fieldnameArr'];
		if (is_array($fieldsName)) {
			foreach ($fieldsName as &$value) {
				$formattedNameList[] = $input[$value];
			}
			$formattedNameListString = implode(",'" . $glue . "',", $formattedNameList);
		} else {
			$formattedNameListString = $input[$fieldsName];
		}
		return 'CONCAT(' . $formattedNameListString . ')';
	}

	/**
	 * This function returns no value but handles the delete functionality of each entity.
	 * Input Parameter are $module - module name, $return_module - return module name, $focus - module object, $record - entity id, $return_id - return entity id.
	 */
	public static function deleteEntity($destinationModule, $sourceModule, \CRMEntity $focus, $destinationRecordId, $sourceRecordId, $relatedName = false)
	{
		\App\Log::trace("Entering deleteEntity method ($destinationModule, $sourceModule, $destinationRecordId, $sourceRecordId)");
		if ($destinationModule != $sourceModule && !empty($sourceModule) && !empty($sourceRecordId)) {
			$eventHandler = new \App\EventHandler();
			$eventHandler->setModuleName($sourceModule);
			$eventHandler->setParams([
				'CRMEntity' => $focus,
				'sourceModule' => $sourceModule,
				'sourceRecordId' => $sourceRecordId,
				'destinationModule' => $destinationModule,
				'destinationRecordId' => $destinationRecordId,
			]);
			$eventHandler->trigger('EntityBeforeUnLink');

			$focus->unlinkRelationship($destinationRecordId, $sourceModule, $sourceRecordId, $relatedName);
			$focus->trackUnLinkedInfo($sourceRecordId);

			$eventHandler->trigger('EntityAfterUnLink');
		} else {
			$currentUserPrivilegesModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if (!$currentUserPrivilegesModel->isPermitted($destinationModule, 'Delete', $destinationRecordId)) {
				throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
			}
			\Vtiger_Record_Model::getInstanceById($destinationRecordId, $destinationModule)->delete();
		}
		\App\Log::trace('Exiting deleteEntity method ...');
	}

	/**
	 * Function to related two records of different entity types.
	 */
	public static function relateEntities(\CRMEntity $focus, $sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds, $relatedName = false)
	{
		\App\Log::trace("Entering relateEntities method ($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds)");
		if (!is_array($destinationRecordIds)) {
			$destinationRecordIds = [$destinationRecordIds];
		}

		$data = [
			'CRMEntity' => $focus,
			'sourceModule' => $sourceModule,
			'sourceRecordId' => $sourceRecordId,
			'destinationModule' => $destinationModule,
		];
		$eventHandler = new \App\EventHandler();
		$eventHandler->setModuleName($sourceModule);
		foreach ($destinationRecordIds as &$destinationRecordId) {
			$data['destinationRecordId'] = $destinationRecordId;
			$eventHandler->setParams($data);
			$eventHandler->trigger('EntityBeforeLink');
			$focus->saveRelatedModule($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId, $relatedName);
			\CRMEntity::trackLinkedInfo($sourceRecordId);
			$eventHandler->trigger('EntityAfterLink');
		}
		\App\Log::trace('Exiting relateEntities method ...');
	}

	public static function getColumnFields($module)
	{
		\App\Log::trace('Entering getColumnFields(' . $module . ') method ...');

		// Lookup in cache for information
		$cachedModuleFields = \VTCacheUtils::lookupFieldInfoModule($module);

		if ($cachedModuleFields === false) {
			$fieldsInfo = Functions::getModuleFieldInfos($module);
			if (!empty($fieldsInfo)) {
				foreach ($fieldsInfo as $resultrow) {
					// Update information to cache for re-use
					\VTCacheUtils::updateFieldInfo(
						$resultrow['tabid'], $resultrow['fieldname'], $resultrow['fieldid'], $resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'], $resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
					);
				}
			}
			// For consistency get information from cache
			$cachedModuleFields = \VTCacheUtils::lookupFieldInfoModule($module);
		}

		$column_fld = [];
		if ($cachedModuleFields) {
			foreach ($cachedModuleFields as $fieldinfo) {
				$column_fld[$fieldinfo['fieldname']] = '';
			}
		}

		\App\Log::trace('Exiting getColumnFields method ...');

		return $column_fld;
	}

	/**
	 * Function to get the permitted module id Array with presence as 0.
	 *
	 * @global Users $current_user
	 *
	 * @return array Array of accessible tabids
	 */
	public static function getPermittedModuleIdList()
	{
		$permittedModules = [];
		require 'user_privileges/user_privileges_' . \App\User::getCurrentUserId() . '.php';
		include 'user_privileges/tabdata.php';

		if ($is_admin === false && $profileGlobalPermission[1] == 1 &&
			$profileGlobalPermission[2] == 1) {
			foreach ($tab_seq_array as $tabid => $seq_value) {
				if ($seq_value === 0 && isset($profileTabsPermission[$tabid]) && $profileTabsPermission[$tabid] === 0) {
					$permittedModules[] = ($tabid);
				}
			}
		} else {
			foreach ($tab_seq_array as $tabid => $seq_value) {
				if ($seq_value === 0) {
					$permittedModules[] = ($tabid);
				}
			}
		}
		$homeTabid = \App\Module::getModuleId('Home');
		if (!in_array($homeTabid, $permittedModules)) {
			$permittedModules[] = $homeTabid;
		}
		return $permittedModules;
	}
}
