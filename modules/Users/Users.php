<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
/* * *******************************************
 * With modifications by
 * Daniel Jabbour
 * iWebPress Incorporated, www.iwebpress.com
 * djabbour - a t - iwebpress - d o t - com
 * ****************************************** */
/* * *******************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Users/Users.php,v 1.10 2005/04/19 14:40:48 ray Exp $
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
require_once('include/utils/UserInfoUtil.php');
require_once 'include/utils/CommonUtils.php';
require_once 'include/Webservices/Utils.php';
require_once('modules/Users/UserTimeZonesArray.php');

// User is used to store customer information.
/** Main class for the user module
 *
 */
class Users extends CRMEntity
{

	// Stored fields
	public $id;
	public $authenticated = false;
	public $error_string;
	public $is_admin;
	public $deleted;
	public $tab_name = ['vtiger_users', 'vtiger_user2role'];
	public $tab_name_index = ['vtiger_users' => 'id', 'vtiger_user2role' => 'userid'];
	public $table_name = "vtiger_users";
	public $table_index = 'id';
	// This is the list of fields that are in the lists.
	public $list_link_field = 'last_name';
	public $list_mode;
	public $popup_type;
	public $search_fields = Array(
		'Name' => Array('vtiger_users' => 'last_name'),
		'Email' => Array('vtiger_users' => 'email1')
	);
	public $search_fields_name = Array(
		'Name' => 'last_name',
		'Email' => 'email1'
	);
	public $module_name = "Users";
	public $object_name = "User";
	public $user_preferences;
	public $encodeFields = Array("first_name", "last_name", "description");
	// This is used to retrieve related fields from form posts.
	public $additional_column_fields = Array('reports_to_name');
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = Array(
		'First Name' => Array('vtiger_users' => 'first_name'),
		'Last Name' => Array('vtiger_users' => 'last_name'),
		'Role Name' => Array('vtiger_user2role' => 'roleid'),
		'User Name' => Array('vtiger_users' => 'user_name'),
		'Status' => Array('vtiger_users' => 'status'),
		'Admin' => Array('vtiger_users' => 'is_admin')
	);
	public $list_fields_name = Array(
		'First Name' => 'first_name',
		'Last Name' => 'last_name',
		'Role Name' => 'roleid',
		'User Name' => 'user_name',
		'Status' => 'status',
		'Admin' => 'is_admin'
	);
	//Default Fields for Email Templates -- Pavani
	public $emailTemplate_defaultFields = array('first_name', 'last_name', 'title', 'department', 'phone_home', 'phone_mobile', 'signature', 'email1');
	public $popup_fields = array('last_name');
	// This is the list of fields that are in the lists.
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	public $record_id;
	public $new_schema = true;
	//Default Widgests
	public $default_widgets = array('CVLVT', 'UA');

	/** constructor function for the main user class
	  instantiates the Logger class and PearDatabase Class
	 *
	 */
	public function __construct()
	{
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Users');
		$this->column_fields['currency_name'] = '';
		$this->column_fields['currency_code'] = '';
		$this->column_fields['currency_symbol'] = '';
		$this->column_fields['conv_rate'] = '';
	}

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	public function getSortOrder()
	{

		\App\Log::trace("Entering getSortOrder() method ...");
		if (\App\Request::_has('sorder'))
			$sorder = $this->db->sqlEscapeString(\App\Request::_get('sorder'));
		else
			$sorder = (($_SESSION['USERS_SORT_ORDER'] != '') ? ($_SESSION['USERS_SORT_ORDER']) : ($this->default_sort_order));
		\App\Log::trace("Exiting getSortOrder method ...");
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'subject')
	 */
	public function getOrderBy()
	{

		\App\Log::trace("Entering getOrderBy() method ...");

		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (\App\Request::_has('order_by'))
			$order_by = $this->db->sqlEscapeString(\App\Request::_get('order_by'));
		else
			$order_by = (($_SESSION['USERS_ORDER_BY'] != '') ? ($_SESSION['USERS_ORDER_BY']) : ($use_default_order_by));
		\App\Log::trace("Exiting getOrderBy method ...");
		return $order_by;
	}
	// Mike Crowe Mod --------------------------------------------------------

	/** Function to set the user preferences in the session
	 * @param $name -- name:: Type varchar
	 * @param $value -- value:: Type varchar
	 *
	 */
	public function setPreference($name, $value)
	{
		if (!isset($this->user_preferences)) {
			if (isset($_SESSION["USER_PREFERENCES"]))
				$this->user_preferences = $_SESSION["USER_PREFERENCES"];
			else
				$this->user_preferences = [];
		}
		if (!array_key_exists($name, $this->user_preferences) || $this->user_preferences[$name] != $value) {
			\App\Log::trace("Saving To Preferences:" . $name . "=" . $value);
			$this->user_preferences[$name] = $value;
			$this->savePreferecesToDB();
		}
		$_SESSION[$name] = $value;
	}

	/** Function to save the user preferences to db
	 *
	 */
	public function savePreferecesToDB()
	{
		$data = base64_encode(serialize($this->user_preferences));
		$query = "UPDATE $this->table_name SET user_preferences=? where id=?";
		$result = & $this->db->pquery($query, array($data, $this->id));
		\App\Log::trace("SAVING: PREFERENCES SIZE " . strlen($data) . "ROWS AFFECTED WHILE UPDATING USER PREFERENCES:" . $this->db->getAffectedRowCount($result));
		$_SESSION["USER_PREFERENCES"] = $this->user_preferences;
	}

	/** Function to load the user preferences from db
	 *
	 */
	public function loadPreferencesFromDB($value)
	{

		if (isset($value) && !empty($value)) {
			\App\Log::trace("LOADING :PREFERENCES SIZE " . strlen($value));
			$this->user_preferences = unserialize(base64_decode($value));
			$_SESSION = array_merge($this->user_preferences, $_SESSION);
			\App\Log::trace("Finished Loading");
			$_SESSION["USER_PREFERENCES"] = $this->user_preferences;
		}
	}

	/**
	 * @return string encrypted password for storage in DB and comparison against DB password.
	 * @param string $user_name - Must be non null and at least 2 characters
	 * @param string $user_password - Must be non null and at least 1 character.
	 * @desc Take an unencrypted username and password and return the encrypted password
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function encryptPassword($user_password, $crypt_type = 'PHP5.3MD5')
	{
		// encrypt the password.
		$salt = substr($this->column_fields["user_name"], 0, 2);
		// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4923
		if ($crypt_type === '') {
			// Try to get the crypt_type which is in database for the user
			$crypt_type = $this->getCryptType();
		}
		// For more details on salt format look at: http://in.php.net/crypt
		if ($crypt_type === 'MD5') {
			$salt = '$1$' . $salt . '$';
		} elseif ($crypt_type === 'BLOWFISH') {
			$salt = '$2$' . $salt . '$';
		} elseif ($crypt_type === 'PHP5.3MD5') {
			//only change salt for php 5.3 or higher version for backward
			//compactibility.
			//crypt API is lot stricter in taking the value for salt.
			$salt = '$1$' . str_pad($salt, 9, '0');
		}
		return crypt($user_password, $salt);
	}

	/**
	 * Checks the config.php AUTHCFG value for login type and forks off to the proper module
	 * @param string $userPassword - The password of the user to authenticate
	 * @return bool true if the user is authenticated, false otherwise
	 */
	public function doLogin($userPassword)
	{
		$userName = $this->column_fields['user_name'];
		$userInfo = (new App\Db\Query())->select(['id', 'deleted', 'user_password', 'crypt_type', 'status'])->from($this->table_name)->where(['user_name' => $userName])->one();
		$encryptedPassword = $this->encryptPassword($userPassword, empty($userInfo['crypt_type']) ? 'PHP5.3MD5' : $userInfo['crypt_type']);
		if (!$userInfo || (int) $userInfo['deleted'] !== 0) {
			\App\Log::error('User not found: ' . $userName);
			return false;
		}
		\App\Log::trace('Start of authentication for user: ' . $userName);
		if ($userInfo['status'] !== 'Active') {
			\App\Log::trace("Authentication failed. User: $userName");
			return false;
		}
		$this->column_fields['id'] = (int) $userInfo['id'];
		if (\App\Cache::has('Authorization', 'config')) {
			$auth = \App\Cache::get('Authorization', 'config');
		} else {
			$dataReader = (new \App\Db\Query())->from('yetiforce_auth')->createCommand()->query();
			$auth = [];
			while ($row = $dataReader->read()) {
				$auth[$row['type']][$row['param']] = $row['value'];
			}
			\App\Cache::save('Authorization', 'config', $auth);
		}
		if ($auth['ldap']['active'] == 'true') {
			\App\Log::trace('Start LDAP authentication');
			$users = explode(',', $auth['ldap']['users']);
			if (in_array($userInfo['id'], $users)) {
				$bind = false;
				$port = $auth['ldap']['port'] == '' ? 389 : $auth['ldap']['port'];
				$ds = @ldap_connect($auth['ldap']['server'], $port);
				if (!$ds) {
					\App\Log::error('Error LDAP authentication: Could not connect to LDAP server.');
				}
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3); // Try version 3.  Will fail and default to v2.
				ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
				ldap_set_option($ds, LDAP_OPT_TIMELIMIT, 5);
				ldap_set_option($ds, LDAP_OPT_TIMEOUT, 5);
				ldap_set_option($ds, LDAP_OPT_NETWORK_TIMEOUT, 5);
				$parser = parse_url($auth['ldap']['server']);
				if ($parser['scheme'] === 'tls') {
					ldap_start_tls($ds);
				}
				$bind = @ldap_bind($ds, $userName . $auth['ldap']['domain'], $userPassword);
				if (!$bind) {
					\App\Log::error('LDAP authentication: LDAP bind failed.');
				}
				return $bind;
			} else {
				\App\Log::trace($userName . ' user does not belong to the LDAP');
			}
			\App\Log::trace('End LDAP authentication');
		}
		if ($encryptedPassword === $userInfo['user_password']) {
			\App\Log::trace("Authentication OK. User: $userName");
			return true;
		}
		\App\Log::trace("Authentication failed. User: $userName");
		return false;
	}

	/**
	 * Get crypt type to use for password for the user.
	 * Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4923
	 */
	public function getCryptType()
	{
		$crypt_res = null;
		$crypt_type = AppConfig::module('Users', 'PASSWORD_CRYPT_TYPE');
		if (isset($this->id)) {
			// Get the type of crypt used on password before actual comparision
			$qcrypt_sql = "SELECT crypt_type from $this->table_name where id=?";
			$crypt_res = $this->db->pquery($qcrypt_sql, array($this->id), true);
		} else if (isset($this->column_fields['user_name'])) {
			$qcrypt_sql = "SELECT crypt_type from $this->table_name where user_name=?";
			$crypt_res = $this->db->pquery($qcrypt_sql, array($this->column_fields["user_name"]));
		}
		if ($crypt_res && $this->db->numRows($crypt_res)) {
			$crypt_row = $this->db->fetchByAssoc($crypt_res);
			$crypt_type = $crypt_row['crypt_type'];
		}
		return $crypt_type;
	}

	/**
	 * @param string $user name - Must be non null and at least 1 character.
	 * @param string $userPassword - Must be non null and at least 1 character.
	 * @param string $newPassword - Must be non null and at least 1 character.
	 * @return boolean - If passwords pass verification and query succeeds, return true, else return false.
	 * @desc Verify that the current password is correct and write the new password to the DB.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): Contributor(s): YetiForce.com
	 */
	public function changePassword($userPassword, $newPassword, $dieOnError = true)
	{
		$userName = $this->column_fields['user_name'];
		\App\Log::trace('Starting password change for ' . $userName);
		if (empty($newPassword)) {
			$this->error_string = \App\Language::translate('ERR_PASSWORD_CHANGE_FAILED_1') . $userName . \App\Language::translate('ERR_PASSWORD_CHANGE_FAILED_2');
			return false;
		}
		if (empty($this->column_fields['is_admin']) && $userPassword) {
			if (!$this->verifyPassword($userPassword)) {
				\App\Log::warning('Incorrect old password for ' . $userName);
				$this->error_string = \App\Language::translate('ERR_PASSWORD_INCORRECT_OLD');
				return false;
			}
		}
		//set new password
		$crypt_type = AppConfig::module('Users', 'PASSWORD_CRYPT_TYPE');
		$encryptedNewPassword = $this->encryptPassword($newPassword, $crypt_type);

		\App\Db::getInstance()->createCommand()->update($this->table_name, [
			'user_password' => $encryptedNewPassword,
			'confirm_password' => $encryptedNewPassword,
			'crypt_type' => $crypt_type,
			], ['id' => $this->id])->execute();
		$this->column_fields['user_password'] = $encryptedNewPassword;
		$this->column_fields['confirm_password'] = $encryptedNewPassword;

		\App\Log::trace('Ending password change for ' . $userName);
		return true;
	}

	/**
	 * Function verifies if given password is correct
	 * @param string $password
	 * @return boolean
	 */
	public function verifyPassword($password)
	{
		$row = (new \App\Db\Query())->select(['user_name', 'user_password', 'crypt_type'])->from($this->table_name)->where(['id' => $this->id])->one();
		$encryptedPassword = $this->encryptPassword($password, $row['crypt_type']);
		if ($encryptedPassword !== $row['user_password']) {
			return false;
		}
		return true;
	}

	public function isAuthenticated()
	{
		return $this->authenticated;
	}

	/**
	 * Function to check whether the user is an Admin user
	 * @return boolean true/false
	 */
	public function isAdminUser()
	{
		return (isset($this->is_admin) && $this->is_admin === 'on');
	}

	/** gives the user id for the specified user name
	 * @param $user_name -- user name:: Type varchar
	 * @returns user id
	 */
	public function retrieveUserId($userName)
	{
		if (AppConfig::performance('ENABLE_CACHING_USERS')) {
			$users = \App\PrivilegeFile::getUser('userName');
			if (isset($users[$userName]) && $users[$userName]['deleted'] == '0') {
				return $users[$userName]['id'];
			}
		}
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT id,deleted from vtiger_users where user_name=?', array($userName));
		$row = $adb->getRow($result);
		if ($row && $row['deleted'] == '0') {
			return $row['id'];
		}
		return false;
	}

	/** Function to get the current user information from the user_privileges file
	 * @param $userid -- user id:: Type integer
	 * @returns user info in $this->column_fields array:: Type array
	 *
	 */
	public function retrieveCurrentUserInfoFromFile($userid)
	{
		$userPrivileges = App\User::getPrivilegesFile($userid);
		$userInfo = $userPrivileges['user_info'];
		foreach ($this->column_fields as $field => $value_iter) {
			if (isset($userInfo[$field])) {
				$this->$field = $userInfo[$field];
				$this->column_fields[$field] = $userInfo[$field];
			}
		}
		$this->id = $userid;
		return $this;
	}

	/** Function to retreive the user info of the specifed user id The user info will be available in $this->column_fields array
	 * @param $record -- record id:: Type integer
	 * @param $module -- module:: Type varchar
	 */
	public function retrieveEntityInfo($record, $module)
	{

		\App\Log::trace("Entering into retrieveEntityInfo($record, $module) method.");

		if ($record == '') {
			\App\Log::error('record is empty. returning null');
			return null;
		}
		$result = [];
		foreach ($this->tab_name_index as $tableName => $index) {
			$result[$tableName] = (new \App\Db\Query())
					->from($tableName)
					->where([$index => $record])->one();
		}
		$fields = vtlib\Functions::getModuleFieldInfos($module);
		foreach ($fields as $fieldName => &$fieldRow) {
			if (isset($result[$fieldRow['tablename']][$fieldRow['columnname']])) {
				$value = $result[$fieldRow['tablename']][$fieldRow['columnname']];
				$this->column_fields[$fieldName] = $value;
				$this->$fieldName = $value;
			}
		}
		$this->column_fields['record_id'] = $record;
		$this->column_fields['record_module'] = $module;

		if (!empty($this->column_fields['currency_id'])) {
			$currency = (new \App\Db\Query())->from('vtiger_currency_info')->where(['id' => $this->column_fields['currency_id'], 'deleted' => 0])->one();
		}
		if (empty($currency)) {
			$currency = (new \App\Db\Query())->from('vtiger_currency_info')->where(['id' => 1])->one();
		}
		$currencyArray = ['$' => '&#36;', '&euro;' => '&#8364;', '&pound;' => '&#163;', '&yen;' => '&#165;'];
		if (isset($currencyArray[$currency['currency_symbol']])) {
			$currencySymbol = $currencyArray[$currency['currency_symbol']];
		} else {
			$currencySymbol = $currency['currency_symbol'];
		}
		$this->column_fields['currency_name'] = $this->currency_name = $currency['currency_name'];
		$this->column_fields['currency_code'] = $this->currency_code = $currency['currency_code'];
		$this->column_fields['currency_symbol'] = $this->currency_symbol = $currencySymbol;
		$this->column_fields['conv_rate'] = $this->conv_rate = $currency['conversion_rate'];
		if ($this->column_fields['no_of_currency_decimals'] === '') {
			$this->column_fields['no_of_currency_decimals'] = $this->no_of_currency_decimals = getCurrencyDecimalPlaces();
		}
		if ($this->column_fields['currency_grouping_pattern'] == '' && $this->column_fields['currency_symbol_placement'] == '') {
			$this->column_fields['currency_grouping_pattern'] = $this->currency_grouping_pattern = '123,456,789';
			$this->column_fields['currency_decimal_separator'] = $this->currency_decimal_separator = '.';
			$this->column_fields['currency_grouping_separator'] = $this->currency_grouping_separator = ' ';
			$this->column_fields['currency_symbol_placement'] = $this->currency_symbol_placement = '1.0$';
		}
		$this->id = $record;
		\App\Log::trace('Exit from retrieveEntityInfo() method.');
		return $this;
	}

	/** Function to upload the file to the server and add the file details in the attachments table
	 * @param string $id
	 * @param string $module
	 * @param array $fileDetails
	 * @return boolean
	 */
	public function uploadAndSaveFile($id, $module, $fileDetails)
	{
		\App\Log::trace("Entering into uploadAndSaveFile($id,$module,$fileDetails) method.");
		$currentUserId = \App\User::getCurrentUserId();
		$dateVar = date('Y-m-d H:i:s');
		$db = App\Db::getInstance();
		//to get the owner id
		$ownerid = $this->column_fields['assigned_user_id'];
		if (!isset($ownerid) || $ownerid == '')
			$ownerid = $currentUserId;
		$fileInstance = \App\Fields\File::loadFromRequest($fileDetails);
		if (!$fileInstance->validate('image')) {
			\App\Log::trace('Skip the save attachment process.');
			return false;
		}
		$binFile = $fileInstance->getSanitizeName();
		$fileName = ltrim(basename(" " . $binFile)); //allowed filename like UTF-8 characters
		$fileType = $fileDetails['type'];
		$fileTmpName = $fileDetails['tmp_name'];
		$uploadFilePath = \App\Fields\File::initStorageFileDirectory($module);
		$db->createCommand()->insert('vtiger_crmentity', [
			'smcreatorid' => $currentUserId,
			'smownerid' => $ownerid,
			'setype' => $module . ' Attachment',
			'description' => $this->column_fields['description'],
			'createdtime' => $dateVar,
			'modifiedtime' => $dateVar
		])->execute();
		$currentId = $db->getLastInsertID('vtiger_crmentity_crmid_seq');
		//upload the file in server
		$success = move_uploaded_file($fileTmpName, $uploadFilePath . $currentId);
		if ($success) {
			$db->createCommand()->insert('vtiger_attachments', [
				'attachmentsid' => $currentId,
				'name' => $fileName,
				'description' => $this->column_fields['description'],
				'type' => $fileType,
				'path' => $uploadFilePath,
			])->execute();
			if ($id != '') {
				$db->createCommand()->delete('vtiger_salesmanattachmentsrel', ['smid' => $id])->execute();
			}
			$db->createCommand()->insert('vtiger_salesmanattachmentsrel', ['smid' => $id, 'attachmentsid' => $currentId])->execute();
			//we should update the imagename in the users table
			$db->createCommand()->update('vtiger_users', ['imagename' => $id], ['id' => $currentId])->execute();
			\App\Log::trace("Exiting from uploadAndSaveFile($id,$module,$fileDetails) method.");
			return true;
		}
		\App\Log::trace("Exiting from uploadAndSaveFile($id,$module,$fileDetails) method.");
		return false;
	}

	public function filterInactiveFields($module)
	{
		
	}

	public function deleteImage()
	{
		$sql1 = 'SELECT attachmentsid FROM vtiger_salesmanattachmentsrel WHERE smid = ?';
		$res1 = $this->db->pquery($sql1, array($this->id));
		if ($this->db->numRows($res1) > 0) {
			$attachmentId = $this->db->queryResult($res1, 0, 'attachmentsid');

			$sql2 = "DELETE FROM vtiger_crmentity WHERE crmid=? && setype='Users Attachments'";
			$this->db->pquery($sql2, array($attachmentId));

			$sql3 = 'DELETE FROM vtiger_salesmanattachmentsrel WHERE smid=? && attachmentsid=?';
			$this->db->pquery($sql3, array($this->id, $attachmentId));

			$sql2 = "UPDATE vtiger_users SET imagename='' WHERE id=?";
			$this->db->pquery($sql2, array($this->id));

			$sql4 = 'DELETE FROM vtiger_attachments WHERE attachmentsid=?';
			$this->db->pquery($sql4, array($attachmentId));
		}
	}

	/** Function to delete an entity with given Id */
	public function trash($module, $id)
	{
		$this->markDeleted($id);
	}

	/**
	 * Transform owner ship and delete
	 * @param int $userId
	 * @param array $transformToUserId
	 */
	public function transformOwnerShipAndDelete($userId, $transformToUserId)
	{
		$eventHandler = new App\EventHandler();
		$eventHandler->setParams(['userId' => $userId, 'transformToUserId' => $transformToUserId]);
		$eventHandler->setModuleName('Users');
		$eventHandler->trigger('UsersBeforeDelete');

		vtws_transferOwnership($userId, $transformToUserId);
		//updating the vtiger_users table;
		App\Db::getInstance()->createCommand()
			->update('vtiger_users', [
				'status' => 'Inactive',
				'deleted' => 1,
				'date_modified' => date('Y-m-d H:i:s'),
				'modified_user_id' => App\User::getCurrentUserRealId()
				], ['id' => $userId])->execute();

		$eventHandler->trigger('UsersAfterDelete');
	}

	/**
	 * This function should be overridden in each module.  It marks an item as deleted.
	 * @param <type> $id
	 */
	public function markDeleted($id)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$date_var = date('Y-m-d H:i:s');
		$query = "UPDATE vtiger_users set status=?,date_modified=?,modified_user_id=? where id=?";
		$adb->pquery($query, array('Inactive', $adb->formatDate($date_var, true),
			$current_user->id, $id), true, "Error marking record deleted: ");
	}

	/**
	 * Function to get the user if of the active admin user.
	 * @return Integer - Active Admin User ID
	 */
	public static function getActiveAdminId()
	{
		$cache = Vtiger_Cache::getInstance();
		if ($cache->getAdminUserId()) {
			return $cache->getAdminUserId();
		} else {
			if (AppConfig::performance('ENABLE_CACHING_USERS')) {
				$users = \App\PrivilegeFile::getUser('id');
				foreach ($users as $id => $user) {
					if ($user['status'] == 'Active' && $user['is_admin'] == 'on') {
						$adminId = $id;
						continue;
					}
				}
			} else {
				$adminId = 1;
				$result = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['is_admin' => 'on', 'status' => 'Active'])->limit(1)->scalar();
				if ($result) {
					$adminId = $result;
				}
			}
			$cache->setAdminUserId($adminId);
			return $adminId;
		}
	}

	/**
	 * Function to get the active admin user object
	 * @return Users - Active Admin User Instance
	 */
	public static function getActiveAdminUser()
	{
		$adminId = self::getActiveAdminId();
		$user = CRMEntity::getInstance('Users');
		$user->retrieveCurrentUserInfoFromFile($adminId);
		return $user;
	}
}
