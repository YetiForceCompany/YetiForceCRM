<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
	 * @param mixed $module
	 * @param mixed $fieldsName
	 * @param mixed $fieldValues
	 *
	 * @return string $fieldConcatName - the entity field name for the module
	 */
	public static function getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues)
	{
		if (false === strpos($fieldsName, ',')) {
			return $fieldValues[$fieldsName];
		}
		$accessibleFieldNames = [];
		foreach (explode(',', $fieldsName) as $field) {
			if ('Users' === $module || \App\Field::getColumnPermission($module, $field)) {
				$accessibleFieldNames[] = $fieldValues[$field];
			}
		}
		if (\count($accessibleFieldNames) > 0) {
			return implode(' ', $accessibleFieldNames);
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
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . \DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (0 !== stripos($realfilepath, $rootdirpath) || \in_array($filePathParts[0], $unsafeDirectories)) {
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
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . \DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (0 !== stripos($realfilepath, $rootdirpath) || !\in_array($filePathParts[0], $safeDirectories)) {
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
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . \DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		if (0 !== stripos($realfilepath, $rootdirpath)) {
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
		if (1 === $dataReader->count()) {
			$blockId = $dataReader->readColumn(0);
		}
		$dataReader->close();

		return $blockId;
	}

	public static function getSqlForNameInDisplayFormat($input, $module, $glue = ' ')
	{
		$entityFieldInfo = \App\Module::getEntityInfo($module);
		$fieldsName = $entityFieldInfo['fieldnameArr'];
		if (\is_array($fieldsName)) {
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
	 * Gets fields for module.
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public static function getColumnFields($module)
	{
		\App\Log::trace('Entering getColumnFields(' . $module . ') method ...');
		$columnFields = [];
		foreach (\App\Field::getModuleFieldInfosByPresence($module) as $fieldInfo) {
			$columnFields[$fieldInfo['fieldname']] = '';
		}
		\App\Log::trace('Exiting getColumnFields method ...');
		return $columnFields;
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

		if (false === $is_admin && 1 == $profileGlobalPermission[1]
			&& 1 == $profileGlobalPermission[2]) {
			foreach ($tab_seq_array as $tabid => $seq_value) {
				if (0 === $seq_value && isset($profileTabsPermission[$tabid]) && 0 === $profileTabsPermission[$tabid]) {
					$permittedModules[] = ($tabid);
				}
			}
		} else {
			foreach ($tab_seq_array as $tabid => $seq_value) {
				if (0 === $seq_value) {
					$permittedModules[] = ($tabid);
				}
			}
		}
		$homeTabid = \App\Module::getModuleId('Home');
		if (!\in_array($homeTabid, $permittedModules)) {
			$permittedModules[] = $homeTabid;
		}
		return $permittedModules;
	}
}
