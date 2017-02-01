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
	public $tab_name = Array('vtiger_users', 'vtiger_attachments', 'vtiger_user2role', 'vtiger_asteriskextensions');
	public $tab_name_index = Array('vtiger_users' => 'id', 'vtiger_attachments' => 'attachmentsid', 'vtiger_user2role' => 'userid', 'vtiger_asteriskextensions' => 'userid');
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
	public $homeorder_array = array('HDB', 'ALVT', 'CVLVT', 'HLT', 'GRT', 'MNL', 'LTFAQ', 'UA', 'PA');
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
	public $DEFAULT_PASSWORD_CRYPT_TYPE = 'PHP5.3MD5'; //'BLOWFISH', /* before PHP5.3*/ MD5;
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
		if (AppRequest::has('sorder'))
			$sorder = $this->db->sql_escape_string(AppRequest::get('sorder'));
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

		if (AppRequest::has('order_by'))
			$order_by = $this->db->sql_escape_string(AppRequest::get('order_by'));
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

	protected function get_user_hash($input)
	{
		return strtolower(md5($input));
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
	public function encrypt_password($user_password, $crypt_type = '')
	{
		// encrypt the password.
		$salt = substr($this->column_fields["user_name"], 0, 2);

		// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4923
		if ($crypt_type == '') {
			// Try to get the crypt_type which is in database for the user
			$crypt_type = $this->get_user_crypt_type();
		}

		// For more details on salt format look at: http://in.php.net/crypt
		if ($crypt_type == 'MD5') {
			$salt = '$1$' . $salt . '$';
		} elseif ($crypt_type == 'BLOWFISH') {
			$salt = '$2$' . $salt . '$';
		} elseif ($crypt_type == 'PHP5.3MD5') {
			//only change salt for php 5.3 or higher version for backward
			//compactibility.
			//crypt API is lot stricter in taking the value for salt.
			$salt = '$1$' . str_pad($salt, 9, '0');
		}

		$encrypted_password = crypt($user_password, $salt);
		return $encrypted_password;
	}

	/** Function to authenticate the current user with the given password
	 * @param $password -- password::Type varchar
	 * @returns true if authenticated or false if not authenticated
	 */
	public function authenticate_user($password)
	{
		$usr_name = $this->column_fields["user_name"];

		$query = "SELECT * from $this->table_name where user_name=? && user_hash=?";
		$params = array($usr_name, $password);
		$result = $this->db->requirePsSingleResult($query, $params, false);

		if (empty($result)) {
			\App\Log::error("SECURITY: failed login by $usr_name");
			return false;
		}

		return true;
	}

	/** Function for validation check
	 *
	 */
	public function validation_check($validate, $md5, $alt = '')
	{
		$validate = base64_decode($validate);
		if (file_exists($validate) && $handle = fopen($validate, 'rb', true)) {
			$buffer = fread($handle, filesize($validate));
			if (md5($buffer) == $md5 || (!empty($alt) && md5($buffer) == $alt)) {
				return 1;
			}
			return -1;
		} else {
			return -1;
		}
	}

	/** Function for authorization check
	 *
	 */
	public function authorization_check($validate, $authkey, $i)
	{
		$validate = base64_decode($validate);
		$authkey = base64_decode($authkey);
		if (file_exists($validate) && $handle = fopen($validate, 'rb', true)) {
			$buffer = fread($handle, filesize($validate));
			if (substr_count($buffer, $authkey) < $i)
				return -1;
		}else {
			return -1;
		}
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
		if (!$userInfo || $userInfo['deleted'] !== 0) {
			\App\Log::error('User not found: ' . $userName);
			return false;
		}
		\App\Log::trace('Start of authentication for user: ' . $userName);
		if ($userInfo['status'] !== 'Active') {
			\App\Log::trace("Authentication failed. User: $userName");
			return false;
		}
		$this->column_fields['id'] = $userInfo['id'];
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
				if ($port != 636) {
					//ldap_start_tls($ds);
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

		//Default authentication
		\App\Log::trace('Using integrated/SQL authentication');
		$encryptedPassword = $this->encrypt_password($userPassword, $userInfo['crypt_type']);
		if ($encryptedPassword === $userInfo['user_password']) {
			\App\Log::trace("Authentication OK. User: $userName");
			return true;
		}
		\App\Log::trace("Authentication failed. User: $userName");
		return false;
	}

	/**
	 * Load a user based on the user_name in $this
	 * @return -- this if load was successul and null if load failed.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function load_user($user_password)
	{
		$usr_name = $this->column_fields["user_name"];
		if (isset($_SESSION['loginattempts'])) {
			$_SESSION['loginattempts'] += 1;
		} else {
			$_SESSION['loginattempts'] = 1;
		}
		if ($_SESSION['loginattempts'] > 5) {
			\App\Log::warning("SECURITY: " . $usr_name . " has attempted to login " . $_SESSION['loginattempts'] . " times.");
		}
		\App\Log::trace("Starting user load for $usr_name");

		if (!isset($this->column_fields["user_name"]) || $this->column_fields["user_name"] == "" || !isset($user_password) || $user_password == "")
			return null;

		$authCheck = false;
		$authCheck = $this->doLogin($user_password);

		if (!$authCheck) {
			\App\Log::warning("User authentication for $usr_name failed");
			return null;
		}

		// Get the fields for the user
		$query = "SELECT * from $this->table_name where user_name='$usr_name'";
		$result = $this->db->requireSingleResult($query, false);

		$row = $this->db->fetchByAssoc($result);
		$this->column_fields = $row;
		$this->id = $row['id'];

		$user_hash = $this->get_user_hash($user_password);

		// If there is no user_hash is not present or is out of date, then create a new one.
		if (!isset($row['user_hash']) || $row['user_hash'] != $user_hash) {
			$query = "UPDATE $this->table_name SET user_hash=? where id=?";
			$this->db->pquery($query, array($user_hash, $row['id']), true, "Error setting new hash for {$row['user_name']}: ");
		}
		$this->loadPreferencesFromDB($row['user_preferences']);


		if ($row['status'] != "Inactive")
			$this->authenticated = true;

		unset($_SESSION['loginattempts']);
		return $this;
	}

	/**
	 * Get crypt type to use for password for the user.
	 * Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4923
	 */
	public function get_user_crypt_type()
	{

		$crypt_res = null;
		$crypt_type = $this->DEFAULT_PASSWORD_CRYPT_TYPE;

		// For backward compatability, we need to make sure to handle this case.
		$adb = PearDatabase::getInstance();
		$table_cols = $adb->getColumnNames("vtiger_users");
		if (!in_array("crypt_type", $table_cols)) {
			return $crypt_type;
		}

		if (isset($this->id)) {
			// Get the type of crypt used on password before actual comparision
			$qcrypt_sql = "SELECT crypt_type from $this->table_name where id=?";
			$crypt_res = $this->db->pquery($qcrypt_sql, array($this->id), true);
		} else if (isset($this->column_fields["user_name"])) {
			$qcrypt_sql = "SELECT crypt_type from $this->table_name where user_name=?";
			$crypt_res = $this->db->pquery($qcrypt_sql, array($this->column_fields["user_name"]));
		} else {
			$crypt_type = $this->DEFAULT_PASSWORD_CRYPT_TYPE;
		}

		if ($crypt_res && $this->db->num_rows($crypt_res)) {
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
	public function change_password($userPassword, $newPassword, $dieOnError = true)
	{
		$userName = $this->column_fields['user_name'];
		$currentUser = \App\User::getCurrentUserModel();
		\App\Log::trace('Starting password change for ' . $userName);

		if (empty($newPassword)) {
			$this->error_string = vtranslate('ERR_PASSWORD_CHANGE_FAILED_1') . $user_name . vtranslate('ERR_PASSWORD_CHANGE_FAILED_2');
			return false;
		}

		if (!$currentUser->isAdmin()) {
			if (!$this->verifyPassword($userPassword)) {
				\App\Log::warning('Incorrect old password for ' . $userName);
				$this->error_string = vtranslate('ERR_PASSWORD_INCORRECT_OLD');
				return false;
			}
		}
		$userHash = $this->get_user_hash($newPassword);

		//set new password
		$crypt_type = $this->DEFAULT_PASSWORD_CRYPT_TYPE;
		$encryptedNewPassword = $this->encrypt_password($newPassword, $crypt_type);

		\App\Db::getInstance()->createCommand()->update($this->table_name, [
			'user_password' => $encryptedNewPassword,
			'confirm_password' => $encryptedNewPassword,
			'user_hash' => $userHash,
			'crypt_type' => $crypt_type,
			], ['id' => $this->id])->execute();

		// Fill up the post-save state of the instance.
		if (empty($this->column_fields['user_hash'])) {
			$this->column_fields['user_hash'] = $userHash;
		}

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
		$encryptedPassword = $this->encrypt_password($password, $row['crypt_type']);
		if ($encryptedPassword !== $row['user_password']) {
			return false;
		}
		return true;
	}

	public function is_authenticated()
	{
		return $this->authenticated;
	}

	/** gives the user id for the specified user name
	 * @param $user_name -- user name:: Type varchar
	 * @returns user id
	 */
	public function retrieve_user_id($userName)
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

	/** Function to return the column name array
	 *
	 */
	public function getColumnNames_User()
	{

		$mergeflds = array("FIRSTNAME", "LASTNAME", "USERNAME", "SECONDARYEMAIL", "TITLE", "OFFICEPHONE", "DEPARTMENT",
			"MOBILE", "OTHERPHONE", "FAX", "EMAIL",
			"HOMEPHONE", "OTHEREMAIL", "PRIMARYADDRESS",
			"CITY", "STATE", "POSTALCODE", "COUNTRY");
		return $mergeflds;
	}

	public function fill_in_additional_list_fields()
	{
		$this->fill_in_additional_detail_fields();
	}

	public function fill_in_additional_detail_fields()
	{
		$query = "SELECT u1.first_name, u1.last_name from vtiger_users u1, vtiger_users u2 where u1.id = u2.reports_to_id && u2.id = ? and u1.deleted=0";
		$result = $this->db->pquery($query, array($this->id), true, "Error filling in additional detail vtiger_fields");

		$row = $this->db->fetchByAssoc($result);
		\App\Log::trace("additional detail query results: $row");

		if ($row != null) {
			$this->reports_to_name = stripslashes(\vtlib\Deprecated::getFullNameFromArray('Users', $row));
		} else {
			$this->reports_to_name = '';
		}
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

	/** Function to save the user information into the database
	 * @param $module -- module name:: Type varchar
	 * @param $fileid
	 */
	public function saveentity($module, $fileid = '')
	{
		$db = PearDatabase::getInstance();
		$insertion_mode = $this->mode;
		if (empty($this->column_fields['time_zone'])) {
			$dbDefaultTimeZone = DateTimeField::getDBTimeZone();
			$this->column_fields['time_zone'] = $dbDefaultTimeZone;
			$this->time_zone = $dbDefaultTimeZone;
		}
		$defaults = [
			'currency_id' => CurrencyField::getDBCurrencyId(),
			'date_format' => 'yyyy-mm-dd',
			'start_hour' => '08:00',
			'end_hour' => '16:00',
			'dayoftheweek' => 'Monday',
			'activity_view' => 'Today',
			'callduration' => 10,
			'othereventduration' => 30,
			'hour_format' => 24,
			'activity_view' => 'This Month',
			'calendarsharedtype' => 'public',
			'default_record_view' => 'Summary',
			'status' => 'Active',
			'internal_mailer' => 1,
			'defaulteventstatus' => 'PLL_PLANNED',
			'defaultactivitytype' => 'Meeting',
			'calendarsharedtype' => 'private',
			'truncate_trailing_zeros' => 0,
			'no_of_currency_decimals' => 2,
			'currency_grouping_pattern' => '123,456,789',
			'currency_symbol_placement' => '1.0$',
			'truncate_trailing_zeros' => 0,
			'reminder_interval' => '15 Minutes',
			'rowheight' => 'medium',
			'lead_view' => 'Today',
		];
		foreach ($defaults as $column => $value) {
			if ($this->column_fields[$column] == '') {
				$this->column_fields[$column] = $value;
			}
		}

		if (empty($this->column_fields['currency_decimal_separator']) && $this->column_fields['currency_decimal_separator'] != ' ') {
			$this->column_fields['currency_decimal_separator'] = '.';
		}
		if (empty($this->column_fields['currency_grouping_separator']) && $this->column_fields['currency_grouping_separator'] != ' ') {
			$this->column_fields['currency_grouping_separator'] = ' ';
		}

		foreach ($this->tab_name as $table_name) {
			if ($table_name == 'vtiger_attachments') {
				$this->insertIntoAttachment($this->id, $module);
			} else {
				$this->insertIntoEntityTable($table_name, $module);
			}
		}
		if (Settings_Roles_Record_Model::getInstanceById($this->column_fields['roleid']) === null) {
			$roleid = Settings_Roles_Record_Model::getInstanceByName($this->column_fields['roleid']);
			if ($roleid) {
				$this->column_fields['roleid'] = $roleid->getId();
			} else {
				$roles = Settings_Roles_Record_Model::getAll();
				$this->column_fields['roleid'] = key($roles);
			}
		}
		if ($insertion_mode != 'edit') {
			$this->createAccessKey();
		}

		require_once('modules/Users/CreateUserPrivilegeFile.php');
		createUserPrivilegesfile($this->id);
		unset($_SESSION['next_reminder_interval']);
		unset($_SESSION['next_reminder_time']);

		if (AppConfig::performance('ENABLE_CACHING_USERS')) {
			\App\PrivilegeFile::createUsersFile();
		}
	}

	public function createAccessKey()
	{
		$adb = PearDatabase::getInstance();


		\App\Log::trace("Entering Into function createAccessKey()");
		$updateQuery = "update vtiger_users set accesskey=? where id=?";
		$insertResult = $adb->pquery($updateQuery, array(vtws_generateRandomAccessKey(16), $this->id));
		\App\Log::trace("Exiting function createAccessKey()");
	}

	/** Function to insert values in the specifed table for the specified module
	 * @param $table_name -- table name:: Type varchar
	 * @param $module -- module:: Type varchar
	 */
	public function insertIntoEntityTable($table_name, $module, $fileid = '')
	{

		\App\Log::trace("function insertIntoEntityTable " . $module . ' vtiger_table name ' . $table_name);
		$adb = PearDatabase::getInstance();
		$db = \App\Db::getInstance();
		$current_user = vglobal('current_user');
		$insertion_mode = $this->mode;
		//Checkin whether an entry is already is present in the vtiger_table to update
		if ($insertion_mode === 'edit') {
			$check_query = "SELECT * FROM %s WHERE %s = ?";
			$check_query = sprintf($check_query, $table_name, $this->tab_name_index[$table_name]);
			$check_result = $this->db->pquery($check_query, array($this->id));

			$num_rows = $this->db->num_rows($check_result);

			if ($num_rows <= 0) {
				$insertion_mode = '';
			}
		}

		// We will set the crypt_type based on the insertion_mode
		$crypt_type = '';

		$params = [];
		if ($insertion_mode === 'edit') {
			$tabid = \App\Module::getModuleId($module);
			$sql = "select * from vtiger_field where tabid=? and tablename=? and displaytype in (1,3,5) and vtiger_field.presence in (0,2)";
			$paramsField = array($tabid, $table_name);
		} else {
			$column = $this->tab_name_index[$table_name];
			if ($column === 'id' && $table_name === 'vtiger_users') {
				$this->column_fields['id'] = $this->id = $db->getUniqueID("vtiger_users");
			}
			$params[$column] = $this->id;
			$tabid = \App\Module::getModuleId($module);
			$sql = "select * from vtiger_field where tabid=? and tablename=? and displaytype in (1,3,4,5) and vtiger_field.presence in (0,2)";
			$paramsField = array($tabid, $table_name);
			$crypt_type = $this->DEFAULT_PASSWORD_CRYPT_TYPE;
		}

		$result = $this->db->pquery($sql, $paramsField);
		$noofrows = $this->db->num_rows($result);
		for ($i = 0; $i < $noofrows; $i++) {
			$fieldname = $this->db->query_result($result, $i, "fieldname");
			$columname = $this->db->query_result($result, $i, "columnname");
			$uitype = $this->db->query_result($result, $i, "uitype");
			$typeofdata = $adb->query_result($result, $i, "typeofdata");

			$typeofdata_array = explode("~", $typeofdata);
			$datatype = $typeofdata_array[0];

			if (isset($this->column_fields[$fieldname])) {
				if ($uitype == 56) {
					if ($this->column_fields[$fieldname] === 'on' || $this->column_fields[$fieldname] == 1) {
						$fldvalue = 1;
					} else {
						$fldvalue = 0;
					}
				} elseif ($uitype == 15) {
					if ($this->column_fields[$fieldname] == \App\Language::translate('LBL_NOT_ACCESSIBLE')) {
						//If the value in the request is Not Accessible for a picklist, the existing value will be replaced instead of Not Accessible value.
						$sql = "select $columname from  $table_name where " . $this->tab_name_index[$table_name] . "=?";
						$res = $adb->pquery($sql, array($this->id));
						$pick_val = $adb->query_result($res, 0, $columname);
						$fldvalue = $pick_val;
					} else {
						$fldvalue = $this->column_fields[$fieldname];
					}
				} elseif ($uitype == 5 || $uitype == 6 || $uitype == 23) {
					if (isset($current_user->date_format)) {
						$fldvalue = getValidDBInsertDateValue($this->column_fields[$fieldname]);
					} else {
						$fldvalue = $this->column_fields[$fieldname];
					}
				} elseif ($uitype == 33) {
					if (is_array($this->column_fields[$fieldname])) {
						$field_list = implode(' |##| ', $this->column_fields[$fieldname]);
					} else {
						$field_list = $this->column_fields[$fieldname];
					}
					$fldvalue = $field_list;
				} elseif ($uitype == 99) {
					$plain_text = $this->column_fields[$fieldname];
					$fldvalue = $this->encrypt_password($plain_text, $crypt_type);
					// Update the plain-text value with encrypted value and dependent fields
					$this->column_fields[$fieldname] = $fldvalue;
					$this->column_fields['crypt_type'] = $crypt_type;
					$this->column_fields['user_hash'] = $this->get_user_hash($plain_text);
				} else {
					$fldvalue = $this->column_fields[$fieldname];
					$fldvalue = stripslashes($fldvalue);
				}
				$fldvalue = \vtlib\Functions::fromHTML($fldvalue, ($insertion_mode == 'edit') ? true : false);
			} else {
				$fldvalue = '';
			}
			if ($uitype == 31) {
				$themeList = array_keys(Vtiger_Util_Helper::getAllSkins());
				if (!in_array($fldvalue, $themeList) || $fldvalue == '') {
					global $default_theme;
					if (!empty($default_theme) && in_array($default_theme, $themeList)) {
						$fldvalue = $default_theme;
					} else {
						$fldvalue = $themeList[0];
					}
				}
				if ($current_user->id == $this->id) {
					$_SESSION['vtiger_authenticated_user_theme'] = $fldvalue;
				}
			} elseif ($uitype == 32) {
				$languageList = vtlib\Language::getAll();
				$languageList = array_keys($languageList);
				if (!in_array($fldvalue, $languageList) || $fldvalue == '') {
					$default_language = vglobal('default_language');
					if (!empty($default_language) && in_array($default_language, $languageList)) {
						$fldvalue = vglobal('default_language');
					} else {
						$fldvalue = $languageList[0];
					}
				}
				if ($current_user->id == $this->id) {
					Vtiger_Session::set('language', $fldvalue);
				}
			}
			if ($fldvalue == '') {
				//$fldvalue = $this->get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype);
			}
			$params[$columname] = $fldvalue;
		}
		if ($table_name === 'vtiger_users') {
			$params['date_modified'] = date('Y-m-d H:i:s');
		}
		if ($insertion_mode == 'edit') {
			//Check done by Don. If update is empty the the query fails
			if ($params) {
				$db->createCommand()
					->update($table_name, $params, [$this->tab_name_index[$table_name] => $this->id])->execute();
			}
		} else {
			if ($table_name === 'vtiger_users') {
				if (!isset($params['crypt_type'])) {
					$params['crypt_type'] = $crypt_type;
				}
				if (!isset($params['user_hash'])) {
					$params['user_hash'] = $this->column_fields['user_hash'];
				}
			}
			$db->createCommand()->insert($table_name, $params)->execute();
		}
	}

	/** Function to insert values into the attachment table
	 * @param $id -- entity id:: Type integer
	 * @param $module -- module:: Type varchar
	 */
	public function insertIntoAttachment($id, $module)
	{

		\App\Log::trace("Entering into insertIntoAttachment($id,$module) method.");

		foreach ($_FILES as $fileindex => $files) {
			if ($files['name'] != '' && $files['size'] > 0) {
				$files['original_name'] = AppRequest::get($fileindex . '_hidden');
				$this->uploadAndSaveFile($id, $module, $files);
			}
		}

		\App\Log::trace("Exiting from insertIntoAttachment($id,$module) method.");
	}

	/** Function to retreive the user info of the specifed user id The user info will be available in $this->column_fields array
	 * @param $record -- record id:: Type integer
	 * @param $module -- module:: Type varchar
	 */
	public function retrieve_entity_info($record, $module)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering into retrieve_entity_info($record, $module) method.");

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
		if (!$currency) {
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
		\App\Log::trace('Exit from retrieve_entity_info() method.');
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
		$uploadFilePath = \vtlib\Functions::initStorageFileDirectory($module);
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
		$success = move_uploaded_file($fileTmpName, $uploadFilePath . $currentId . "_" . $binFile);
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

	/** Function to save the user information into the database
	 * @param $module -- module name:: Type varchar
	 *
	 */
	public function save($module_name, $fileid = '')
	{
		$adb = PearDatabase::getInstance();
		$db = App\Db::getInstance();
		if ($this->mode !== 'edit') {
			if ((new \App\Db\Query())->from('vtiger_users')
					->where(['or', ['user_name' => $this->column_fields['user_name']], ['email1' => $this->column_fields['email1']]])
					->exists()) {
				throw new \Exception('LBL_USER_EXISTS');
			}
			\App\Privilege::setAllUpdater();
		} else {// update dashboard widgets when changing users role
			$oldRole = (new App\Db\Query())->select('roleid')
				->from('vtiger_user2role')
				->where(['userid' => $this->id])
				->scalar();
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->id);
			if ($this->column_fields['is_admin'] != $privilegesModel->get('is_admin')) {
				\App\Privilege::setAllUpdater();
			}
			if ($oldRole != $this->column_fields['roleid']) {
				$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['userid' => $this->id])->execute();
				\App\Privilege::setAllUpdater();
			}
		}
		//Save entity being called with the modulename as parameter
		$this->saveentity($module_name);

		// Added for Reminder Popup support
		$query_prev_interval = $adb->pquery("SELECT reminder_interval from vtiger_users where id=?", array($this->id));
		$prev_reminder_interval = $adb->query_result($query_prev_interval, 0, 'reminder_interval');

		//$this->saveHomeStuffOrder($this->id);
		// Added for Reminder Popup support
		$this->resetReminderInterval($prev_reminder_interval);
		//Creating the Privileges Flat File
		if (isset($this->column_fields['roleid']) && $this->mode === 'edit') {
			$this->updateUser2RoleMapping();
		}
		//After adding new user, set the default activity types for new user
		Vtiger_Util_Helper::setCalendarDefaultActivityTypesForUser($this->id);

		require_once('modules/Users/CreateUserPrivilegeFile.php');
		createUserPrivilegesfile($this->id);
		createUserSharingPrivilegesfile($this->id);
	}

	/**
	 * Function to update user to vtiger_role mapping based on the userid
	 * @param $roleid -- Role Id:: Type varchar
	 * @param $userid User Id:: Type integer
	 */
	public function updateUser2RoleMapping()
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_user2role', ['roleid' => $this->column_fields['roleid']], ['userid' => $this->id])
			->execute();
	}

	/**
	 * gives the order in which the modules have to be displayed in the home page for the specified user id
	 * @param $id -- user id:: Type integer
	 * @returns the customized home page order in $return_array
	 */
	public function getHomeStuffOrder($id)
	{
		$adb = PearDatabase::getInstance();
		if (!is_array($this->homeorder_array)) {
			$this->homeorder_array = array('UA', 'PA', 'ALVT', 'HDB', 'CVLVT', 'HLT',
				'GRT', 'MNL', 'LTFAQ');
		}
		$return_array = [];
		$homeorder = [];
		if ($id != '') {
			$qry = " select distinct(vtiger_homedefault.hometype) from vtiger_homedefault inner join vtiger_homestuff  on vtiger_homestuff.stuffid=vtiger_homedefault.stuffid where vtiger_homestuff.visible=0 and vtiger_homestuff.userid=?";
			$res = $adb->pquery($qry, array($id));
			$rows_res = $adb->num_rows($res);
			for ($q = 0; $q < $rows_res; $q++) {
				$homeorder[] = $adb->query_result($res, $q, "hometype");
			}
			$countHomeorderArray = count($this->homeorder_array);
			for ($i = 0; $i < $countHomeorderArray; $i++) {
				if (in_array($this->homeorder_array[$i], $homeorder)) {
					$return_array[$this->homeorder_array[$i]] = $this->homeorder_array[$i];
				} else {
					$return_array[$this->homeorder_array[$i]] = '';
				}
			}
		} else {
			$countHomeorderArray = count($this->homeorder_array);
			for ($i = 0; $i < $countHomeorderArray; $i++) {
				if (in_array($this->homeorder_array[$i], $this->default_widgets)) {
					$return_array[$this->homeorder_array[$i]] = $this->homeorder_array[$i];
				} else {
					$return_array[$this->homeorder_array[$i]] = '';
				}
			}
		}
		return $return_array;
	}

	public function getDefaultHomeModuleVisibility($home_string, $inVal)
	{
		$homeModComptVisibility = 1;
		if ($inVal == 'postinstall') {
			if (AppRequest::get($home_string) != '') {
				$homeModComptVisibility = 0;
			} else if (in_array($home_string, $this->default_widgets)) {
				$homeModComptVisibility = 0;
			}
		}
		return $homeModComptVisibility;
	}

	public function insertUserdetails($inVal)
	{
		$adb = PearDatabase::getInstance();
		$uid = $this->id;
		$s1 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('ALVT', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s1, 1, 'Default', $uid, $visibility, 'Top Accounts'));

		$s2 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('HDB', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s2, 2, 'Default', $uid, $visibility, 'Home Page Dashboard'));

		$s5 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('CVLVT', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s5, 5, 'Default', $uid, $visibility, 'Key Metrics'));

		$s6 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('HLT', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s6, 6, 'Default', $uid, $visibility, 'Top Trouble Tickets'));

		$s7 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('UA', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s7, 7, 'Default', $uid, $visibility, 'Upcoming Activities'));

		$s8 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('GRT', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s8, 8, 'Default', $uid, $visibility, 'My Group Allocation'));

		$s11 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('MNL', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s11, 11, 'Default', $uid, $visibility, 'My New Leads'));

		$s13 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('PA', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s13, 13, 'Default', $uid, $visibility, 'Pending Activities'));
		;

		$s14 = $adb->getUniqueID("vtiger_homestuff");
		$visibility = $this->getDefaultHomeModuleVisibility('LTFAQ', $inVal);
		$sql = "insert into vtiger_homestuff values(?,?,?,?,?,?)";
		$res = $adb->pquery($sql, array($s14, 14, 'Default', $uid, $visibility, 'My Recent FAQs'));

		$sql = "insert into vtiger_homedefault values(" . $s1 . ",'ALVT',5,'Accounts')";
		$adb->pquery($sql, []);

		$sql = "insert into vtiger_homedefault values(" . $s2 . ",'HDB',5,'Dashboard')";
		$adb->pquery($sql, []);

		$sql = "insert into vtiger_homedefault values(" . $s5 . ",'CVLVT',5,'NULL')";
		$adb->pquery($sql, []);

		$sql = "insert into vtiger_homedefault values(" . $s6 . ",'HLT',5,'HelpDesk')";
		$adb->pquery($sql, []);

		$sql = "insert into vtiger_homedefault values(" . $s7 . ",'UA',5,'Calendar')";
		$adb->pquery($sql, []);

		$sql = "insert into vtiger_homedefault values(" . $s8 . ",'GRT',5,'NULL')";
		$adb->pquery($sql, []);

		$sql = "insert into vtiger_homedefault values(" . $s11 . ",'MNL',5,'Leads')";
		$adb->pquery($sql, []);

		$sql = "insert into vtiger_homedefault values(" . $s13 . ",'PA',5,'Calendar')";
		$adb->pquery($sql, []);

		$sql = "insert into vtiger_homedefault values(" . $s14 . ",'LTFAQ',5,'Faq')";
		$adb->pquery($sql, []);
	}

	/** function to save the order in which the modules have to be displayed in the home page for the specified user id
	 * @param $id -- user id:: Type integer
	 */
	public function saveHomeStuffOrder($id)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering in function saveHomeOrder($id)");

		if ($this->mode == 'edit') {
			$countHomeorderArray = count($this->homeorder_array);
			for ($i = 0; $i < $countHomeorderArray; $i++) {
				if (AppRequest::get($this->homeorder_array[$i]) != '') {
					$save_array[] = $this->homeorder_array[$i];
					$qry = " update vtiger_homestuff,vtiger_homedefault set vtiger_homestuff.visible=0 where vtiger_homestuff.stuffid=vtiger_homedefault.stuffid and vtiger_homestuff.userid = ? and vtiger_homedefault.hometype= ?"; //To show the default Homestuff on the the Home Page
					$result = $adb->pquery($qry, [$id, $this->homeorder_array[$i]]);
				} else {

					$qry = "update vtiger_homestuff,vtiger_homedefault set vtiger_homestuff.visible=1 where vtiger_homestuff.stuffid=vtiger_homedefault.stuffid and vtiger_homestuff.userid= ? and vtiger_homedefault.hometype=?"; //To hide the default Homestuff on the the Home Page
					$result = $adb->pquery($qry, [$id, $this->homeorder_array[$i]]);
				}
			}
			if ($save_array != "")
				$homeorder = implode(',', $save_array);
		}
		else {
			$this->insertUserdetails('postinstall');
		}
		\App\Log::trace("Exiting from function saveHomeOrder($id)");
	}

	/**
	 * Function to reset the Reminder Interval setup and update the time for next reminder interval
	 * @param $prev_reminder_interval -- Last Reminder Interval on which the reminder popup's were triggered.
	 */
	public function resetReminderInterval($prev_reminder_interval)
	{
		$adb = PearDatabase::getInstance();
		if ($prev_reminder_interval != $this->column_fields['reminder_interval']) {
			unset($_SESSION['next_reminder_interval']);
			unset($_SESSION['next_reminder_time']);
			$set_reminder_next = date('Y-m-d H:i');
			// NOTE date_entered has CURRENT_TIMESTAMP constraint, so we need to reset when updating the table
			$adb->pquery("UPDATE vtiger_users SET reminder_next_time=?, date_entered=? WHERE id=?", array($set_reminder_next, $this->column_fields['date_entered'], $this->id));
		}
	}

	public function filterInactiveFields($module)
	{
		
	}

	public function deleteImage()
	{
		$sql1 = 'SELECT attachmentsid FROM vtiger_salesmanattachmentsrel WHERE smid = ?';
		$res1 = $this->db->pquery($sql1, array($this->id));
		if ($this->db->num_rows($res1) > 0) {
			$attachmentId = $this->db->query_result($res1, 0, 'attachmentsid');

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
		$this->mark_deleted($id);
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
	public function mark_deleted($id)
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
		$db = PearDatabase::getInstance();
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
				$result = $db->query("SELECT id FROM vtiger_users WHERE is_admin = 'on' AND status = 'Active' limit 1");
				$adminId = 1;
				while (($id = $db->getSingleValue($result)) !== false) {
					$adminId = $id;
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
