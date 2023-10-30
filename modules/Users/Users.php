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
 * Contributor(s): YetiForce S.A.
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
 * Contributor(s): YetiForce S.A.
 * ****************************************************************************** */

require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/Users/UserTimeZonesArray.php';

// User is used to store customer information.

/** Main class for the user module.
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
	public $table_name = 'vtiger_users';
	public $table_index = 'id';

	public $list_mode;
	public $popup_type;
	public $search_fields = [
		'Name' => ['vtiger_users' => 'last_name'],
		'Email' => ['vtiger_users' => 'email1'],
	];
	public $search_fields_name = [
		'First Name' => 'first_name',
		'Name' => 'last_name',
		'Email' => 'email1',
	];
	public $module_name = 'Users';
	public $object_name = 'User';
	public $user_preferences;
	public $encodeFields = ['first_name', 'last_name', 'description'];
	// This is used to retrieve related fields from form posts.
	public $additional_column_fields = ['reports_to_name'];

	public $list_fields_name = [
		'First Name' => 'first_name',
		'Last Name' => 'last_name',
		'Role Name' => 'roleid',
		'User Name' => 'user_name',
		'Status' => 'status',
		'Admin' => 'is_admin',
		'Login Method' => 'login_method',
		'FL_FORCE_PASSWORD_CHANGE' => 'force_password_change',
		'FL_DATE_PASSWORD_CHANGE' => 'date_password_change',
	];
	//Default Fields for Email Templates -- Pavani
	public $emailTemplate_defaultFields = ['first_name', 'last_name', 'title', 'department', 'phone_home', 'phone_mobile', 'signature', 'email1'];
	public $popup_fields = ['last_name'];
	// This is the list of fields that are in the lists.
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	public $record_id;
	public $new_schema = true;
	//Default Widgests
	public $default_widgets = ['CVLVT', 'UA'];

	/** constructor function for the main user class.
	 *
	 */
	public function __construct()
	{
		$this->column_fields = vtlib\Deprecated::getColumnFields('Users');
		$this->column_fields['currency_name'] = '';
		$this->column_fields['currency_code'] = '';
		$this->column_fields['currency_symbol'] = '';
		$this->column_fields['conv_rate'] = '';
	}

	/**
	 * Function to check whether the user is an Admin user.
	 *
	 * @return bool true/false
	 */
	public function isAdminUser()
	{
		return isset($this->is_admin) && 'on' === $this->is_admin;
	}

	/** Function to get the current user information from the user_privileges file.
	 * @param $userid -- user id:: Type integer
	 * @returns user info in $this->column_fields array:: Type array
	 */
	public function retrieveCurrentUserInfoFromFile($userid)
	{
		$userPrivileges = App\User::getPrivilegesFile($userid);
		$userInfo = $userPrivileges['user_info'];
		foreach ($this->column_fields as $field => $value_iter) {
			if (isset($userInfo[$field])) {
				$this->{$field} = $userInfo[$field];
				$this->column_fields[$field] = $userInfo[$field];
			}
		}
		$this->id = $userid;

		return $this;
	}

	/** {@inheritdoc} */
	public function retrieveEntityInfo(int $record, string $module)
	{
		\App\Log::trace("Entering into retrieveEntityInfo($record, $module) method.");
		if ('' == $record) {
			\App\Log::error('record is empty. returning null');
			return null;
		}
		$result = [];
		$fields = \App\Field::getModuleFieldInfos($module);
		foreach ($fields as $fieldName => $fieldRow) {
			$tableName = $fieldRow['tablename'];
			if (empty($result[$tableName]) && isset($this->tab_name_index[$tableName])) {
				$result[$tableName] = (new \App\Db\Query())->from($tableName)->where([$this->tab_name_index[$tableName] => $record])->one();
				if (empty($result[$tableName])) {
					throw new \App\Exceptions\NoPermittedToRecord('ERR_RECORD_NOT_FOUND||' . $record);
				}
			}
			if (isset($result[$tableName][$fieldRow['columnname']])) {
				$this->{$fieldName} = $this->column_fields[$fieldName] = $result[$tableName][$fieldRow['columnname']];
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
		if ('' === $this->column_fields['no_of_currency_decimals']) {
			$this->column_fields['no_of_currency_decimals'] = $this->no_of_currency_decimals = App\User::getCurrentUserId() ? (int) App\User::getCurrentUserModel()->getDetail('no_of_currency_decimals') : 2;
		}
		if ('' == $this->column_fields['currency_grouping_pattern'] && '' == $this->column_fields['currency_symbol_placement']) {
			$this->column_fields['currency_grouping_pattern'] = $this->currency_grouping_pattern = '123,456,789';
			$this->column_fields['currency_decimal_separator'] = $this->currency_decimal_separator = '.';
			$this->column_fields['currency_grouping_separator'] = $this->currency_grouping_separator = ' ';
			$this->column_fields['currency_symbol_placement'] = $this->currency_symbol_placement = '1.0$';
		}
		$this->id = $record;
		\App\Log::trace('Exit from retrieveEntityInfo() method.');

		return $this;
	}

	/**
	 * Transform owner ship and delete.
	 *
	 * @param int   $userId
	 * @param array $transformToUserId
	 */
	public function transformOwnerShipAndDelete($userId, $transformToUserId)
	{
		$eventHandler = new App\EventHandler();
		$eventHandler->setParams(['userId' => $userId, 'transformToUserId' => $transformToUserId]);
		$eventHandler->setModuleName('Users');
		$eventHandler->trigger('UsersBeforeDelete');

		App\Fields\Owner::transferOwnership($userId, $transformToUserId);
		//updating the vtiger_users table
		App\Db::getInstance()->createCommand()
			->update('vtiger_users', [
				'status' => 'Inactive',
				'deleted' => 1,
				'date_modified' => date('Y-m-d H:i:s'),
				'modified_user_id' => App\User::getCurrentUserRealId(),
			], ['id' => $userId])->execute();

		$eventHandler->trigger('UsersAfterDelete');
	}

	/**
	 * Function to get the user if of the active admin user.
	 *
	 * @return int - Active Admin User ID
	 */
	public static function getActiveAdminId()
	{
		$cache = Vtiger_Cache::getInstance();
		if ($cache->getAdminUserId()) {
			return $cache->getAdminUserId();
		}
		if (App\Config::performance('ENABLE_CACHING_USERS')) {
			$users = \App\PrivilegeFile::getUser('id');
			foreach ($users as $id => $user) {
				if ('Active' == $user['status'] && 'on' == $user['is_admin']) {
					$adminId = $id;
				}
			}
		} else {
			$adminId = 1;
			$result = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['is_admin' => 'on', 'status' => 'Active'])->scalar();
			if ($result) {
				$adminId = $result;
			}
		}
		$cache->setAdminUserId($adminId);

		return $adminId;
	}

	/**
	 * Function to get the active admin user object.
	 *
	 * @return Users - Active Admin User Instance
	 */
	public static function getActiveAdminUser()
	{
		$adminId = self::getActiveAdminId();
		$user = CRMEntity::getInstance('Users');
		$user->retrieveCurrentUserInfoFromFile($adminId);

		return $user;
	}

	public function createAccessKey()
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_users', [
				'accesskey' => \App\Encryption::generatePassword(20, 'lbn'),
			], ['id' => $this->id])
			->execute();
		\App\UserPrivilegesFile::createUserPrivilegesfile($this->id);
	}
}
