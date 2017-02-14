<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Import_Utils_Helper
{

	static $AUTO_MERGE_NONE = 0;
	static $AUTO_MERGE_IGNORE = 1;
	static $AUTO_MERGE_OVERWRITE = 2;
	static $AUTO_MERGE_MERGEFIELDS = 3;
	static $supportedFileExtensions = ['csv', 'vcf', 'ical', 'xml', 'ics'];
	static $supportedFileExtensionsByModule = ['Contacts' => ['csv', 'vcf'], 'Calendar' => ['csv', 'ical', 'ics'], 'Default' => ['csv', 'xml', 'zip']];

	public function getSupportedFileExtensions($moduleName = null)
	{
		if (!$moduleName) {
			return self::$supportedFileExtensions;
		} else {
			switch ($moduleName) {
				case 'Contacts':
				case 'Calendar':
					return self::$supportedFileExtensionsByModule[$moduleName];

				default:
					return self::$supportedFileExtensionsByModule['Default'];
			}
		}
	}

	public function getSupportedFileExtensionsDescription($moduleName)
	{
		$supportedFileTypes = self::getSupportedFileExtensions($moduleName);
		$description = [];

		foreach ($supportedFileTypes as $fileType) {
			$description[] = '.' . strtoupper($fileType);
		}
		return implode(', ', $description);
	}

	public static function getMaxUploadSize()
	{
		global $upload_maxsize;
		return $upload_maxsize;
	}

	public static function getImportDirectory()
	{
		global $import_dir;
		$importDir = dirname(__FILE__) . '/../../../' . $import_dir;
		return $importDir;
	}

	public static function getImportFilePath($user)
	{
		$importDirectory = self::getImportDirectory();
		return $importDirectory . "IMPORT_" . $user->id;
	}

	public static function showErrorPage($errorMessage, $errorDetails = false, $customActions = false)
	{
		$viewer = new Vtiger_Viewer();

		$viewer->assign('ERROR_MESSAGE', $errorMessage);
		$viewer->assign('ERROR_DETAILS', $errorDetails);
		$viewer->assign('CUSTOM_ACTIONS', $customActions);
		$viewer->assign('MODULE', 'Import');

		$viewer->view('ImportError.tpl', 'Import');
	}

	public static function showImportLockedError($lockInfo)
	{

		$errorMessage = vtranslate('ERR_MODULE_IMPORT_LOCKED', 'Import');
		$errorDetails = array(vtranslate('LBL_MODULE_NAME', 'Import') => \App\Module::getModuleName($lockInfo['tabid']),
			vtranslate('LBL_USER_NAME', 'Import') => \App\Fields\Owner::getUserLabel($lockInfo['userid']),
			vtranslate('LBL_LOCKED_TIME', 'Import') => $lockInfo['locked_since']);

		self::showErrorPage($errorMessage, $errorDetails);
	}

	public static function showImportTableBlockedError($moduleName, $user)
	{

		$errorMessage = vtranslate('ERR_UNIMPORTED_RECORDS_EXIST', 'Import');
		$customActions = array('LBL_CLEAR_DATA' => "location.href='index.php?module={$moduleName}&view=Import&mode=clearCorruptedData'");

		self::showErrorPage($errorMessage, '', $customActions);
	}

	public static function getAssignedToUserList($module)
	{
		$cache = Vtiger_Cache::getInstance();
		if ($cache->getUserList($module, $current_user->id)) {
			return $cache->getUserList($module, $current_user->id);
		} else {
			$userList = \App\Fields\Owner::getInstance()->getUsers(false, 'Active', $current_user->id);
			$cache->setUserList($module, $userList, $current_user->id);
			return $userList;
		}
	}

	public static function getAssignedToGroupList($module)
	{
		$cache = Vtiger_Cache::getInstance();
		if ($cache->getGroupList($module, $current_user->id)) {
			return $cache->getGroupList($module, $current_user->id);
		} else {
			$groupList = \App\Fields\Owner::getInstance()->getGroups(false);
			$cache->setGroupList($module, $groupList, $current_user->id);
			return $groupList;
		}
	}

	public static function hasAssignPrivilege($moduleName, $assignToUserId)
	{
		$assignableUsersList = self::getAssignedToUserList($moduleName);
		if (array_key_exists($assignToUserId, $assignableUsersList)) {
			return true;
		}
		$assignableGroupsList = self::getAssignedToGroupList($moduleName);
		if (array_key_exists($assignToUserId, $assignableGroupsList)) {
			return true;
		}
		return false;
	}

	public static function validateFileUpload($request)
	{
		$current_user = Users_Record_Model::getCurrentUserModel();

		$uploadMaxSize = self::getMaxUploadSize();
		$importDirectory = self::getImportDirectory();
		$temporaryFileName = self::getImportFilePath($current_user);

		if ($_FILES['import_file']['error']) {
			$request->set('error_message', self::fileUploadErrorMessage($_FILES['import_file']['error']));
			return false;
		}
		if (!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
			$request->set('error_message', vtranslate('LBL_FILE_UPLOAD_FAILED', 'Import'));
			return false;
		}
		if ($_FILES['import_file']['size'] > $uploadMaxSize) {
			$request->set('error_message', vtranslate('LBL_IMPORT_ERROR_LARGE_FILE', 'Import') .
				$uploadMaxSize . ' ' . vtranslate('LBL_IMPORT_CHANGE_UPLOAD_SIZE', 'Import'));
			return false;
		}
		if (!is_writable($importDirectory)) {
			$request->set('error_message', vtranslate('LBL_IMPORT_DIRECTORY_NOT_WRITABLE', 'Import'));
			return false;
		}

		$fileCopied = move_uploaded_file($_FILES['import_file']['tmp_name'], $temporaryFileName);
		if (!$fileCopied) {
			$request->set('error_message', vtranslate('LBL_IMPORT_FILE_COPY_FAILED', 'Import'));
			return false;
		}
		$fileReader = Import_Module_Model::getFileReader($request, $current_user);

		if ($fileReader === null) {
			$request->set('error_message', vtranslate('LBL_INVALID_FILE', 'Import'));
			return false;
		}

		$hasHeader = $fileReader->hasHeader();
		$firstRow = $fileReader->getFirstRowData($hasHeader);
		if ($firstRow === false) {
			$request->set('error_message', vtranslate('LBL_NO_ROWS_FOUND', 'Import'));
			return false;
		}
		return true;
	}

	static function fileUploadErrorMessage($error_code)
	{
		switch ($error_code) {
			case 1:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
			case 2:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			case 3:
				return 'The uploaded file was only partially uploaded';
			case 4:
				return 'No file was uploaded';
			case 6:
				return 'Missing a temporary folder';
			case 7:
				return 'Failed to write file to disk';
			case 8:
				return 'File upload stopped by extension';
			default:
				return 'Unknown upload error';
		}
	}
}
