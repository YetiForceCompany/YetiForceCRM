<?php
 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Users_Record_Model extends Vtiger_Record_Model
{
	public function getRealId()
	{
		if (App\Session::has('baseUserId') && '' != App\Session::get('baseUserId')) {
			return App\Session::get('baseUserId');
		}
		return $this->getId();
	}

	/**
	 * Function to get the Module to which the record belongs.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		if (empty($this->module)) {
			$this->module = Vtiger_Module_Model::getInstance('Users');
		}
		return $this->module;
	}

	/**
	 * Gets the value of the key . First it will check whether specified key is a property if not it
	 *  will get from normal data attribure from base class.
	 *
	 * @param string $key - property or key name
	 *
	 * @return <object>
	 */
	public function get($key)
	{
		if (property_exists($this, $key)) {
			return $this->{$key};
		}
		return parent::get($key);
	}

	/**
	 * Sets the value of the key . First it will check whether specified key is a property if not it
	 * will set from normal set from base class.
	 *
	 * @param string $key   - property or key name
	 * @param string $value
	 */
	public function set($key, $value)
	{
		if (property_exists($this, $key)) {
			$this->{$key} = $value;
		}
		parent::set($key, $value);

		return $this;
	}

	/**
	 * Function to get the Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&parent=Settings&view=' . $module->getDetailViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail View url for the Preferences page.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getPreferenceDetailViewUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=PreferenceDetail&record=' . $this->getId();
	}

	/**
	 * Function to get the Edit View url for the record.
	 *
	 * @return string - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&parent=Settings&view=' . $module->getEditViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the Edit View url for the Preferences page.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getPreferenceEditViewUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=PreferenceEdit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action url for the record.
	 *
	 * @return string - Record Delete Action Url
	 */
	public function getDeleteUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&parent=Settings&view=' . $module->getDeleteActionName() . 'User&record=' . $this->getId();
	}

	/**
	 * Function to check whether the user is an Admin user.
	 *
	 * @return bool true/false
	 */
	public function isAdminUser()
	{
		if ('on' === $this->get('is_admin') || 1 == $this->get('is_admin')) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get the module name.
	 *
	 * @return string Module Name
	 */
	public function getModuleName()
	{
		$module = $this->getModule();
		if ($module) {
			return parent::getModuleName();
		}
		//get from the class propety module_name
		return $this->get('module_name');
	}

	/**
	 * Function to save the user record model.
	 *
	 * @throws \Exception
	 */
	public function save()
	{
		$entityInstance = $this->getModule()->getEntityInstance();
		$entityInstance->column_fields['user_name'] = $this->get('user_name');
		if (!$this->isNew() && empty($this->getPreviousValue())) {
			App\Log::info('ERR_NO_DATA');
			return false;
		}
		if ($this->getPreviousValue('user_password')) {
			$this->set('date_password_change', date('Y-m-d H:i:s'));
		}
		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($this);
		$eventHandler->setModuleName($this->getModuleName());
		if ($this->getHandlerExceptions()) {
			$eventHandler->setExceptions($this->getHandlerExceptions());
		}
		$eventHandler->trigger('UserBeforeSave');
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		try {
			$this->validate();
			$this->saveToDb();
			$this->afterSaveToDb();
			(new App\BatchMethod(['method' => '\App\PrivilegeUtil::recalculateSharingRulesByUser', 'params' => [$this->getId()]]))->save();
			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
		$eventHandler->trigger('UserAfterSave');
		\App\Cache::clearOpcache();
	}

	/**
	 * Save data to the database.
	 */
	public function saveToDb()
	{
		$entityInstance = $this->getModule()->getEntityInstance();
		$db = \App\Db::getInstance();
		$valuesForSave = $this->getValuesForSave();
		foreach ($valuesForSave as $tableName => $tableData) {
			if ($this->isNew()) {
				$db->createCommand()->insert($tableName, [$entityInstance->tab_name_index[$tableName] => $this->getId()] + $tableData)->execute();
			} else {
				$db->createCommand()->update($tableName, $tableData, [$entityInstance->tab_name_index[$tableName] => $this->getId()])->execute();
			}
		}
		if (AppConfig::module('Users', 'CHECK_LAST_USERNAME') && isset($valuesForSave['vtiger_users']['user_name'])) {
			$db = \App\Db::getInstance('log');
			$db->createCommand()->insert('l_#__username_history', ['user_name' => $valuesForSave['vtiger_users']['user_name'], 'user_id' => $this->getId()])->execute();
		}
	}

	/**
	 * Prepare value to save.
	 *
	 * @return array
	 */
	public function getValuesForSave()
	{
		$forSave = [
			'vtiger_users' => [
				'date_modified' => date('Y-m-d H:i:s'),
				'reminder_next_time' => date('Y-m-d H:i'),
				'modified_user_id' => \App\User::getCurrentUserRealId(),
			],
		];
		$moduleModel = $this->getModule();
		$saveFields = $moduleModel->getFieldsForSave($this);
		if (!$this->isNew()) {
			$saveFields = array_intersect($saveFields, array_keys($this->changes));
		}
		if ($this->isNew()) {
			$this->setId(\App\Db::getInstance()->getUniqueID('vtiger_users'));
			$forSave['vtiger_users']['date_entered'] = date('Y-m-d H:i:s');
		}
		if ($this->has('changeUserPassword') || $this->isNew()) {
			$saveFields[] = 'user_password';
		}
		foreach ($saveFields as $fieldName) {
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			if ($fieldModel) {
				$uitypeModel = $fieldModel->getUITypeModel();
				$value = $this->get($fieldName);
				$uitypeModel->validate($value);
				if (null === $value || '' === $value) {
					$defaultValue = $fieldModel->getDefaultFieldValue();
					if ('' !== $defaultValue) {
						$value = $defaultValue;
					} elseif ($default = $this->getDefaultValue($fieldName)) {
						$value = $default;
					} else {
						$value = $uitypeModel->getDBValue($value, $this);
					}
					$this->set($fieldName, $value);
				}
				$forSave[$fieldModel->getTableName()][$fieldModel->getColumnName()] = $uitypeModel->convertToSave($value, $this);
			}
		}
		return $forSave;
	}

	/**
	 * Get default value.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	protected function getDefaultValue($fieldName)
	{
		switch ($fieldName) {
			case 'currency_id':
				return CurrencyField::getDBCurrencyId();
			case 'accesskey':
				return \App\Encryption::generatePassword(20, 'lbn');
			case 'language':
				return \App\Language::getLanguage();
			case 'time_zone':
				return App\Fields\DateTime::getTimeZone();
			case 'theme':
				return Vtiger_Viewer::DEFAULTTHEME;
			case 'is_admin':
				return 'off';
			default:
				break;
		}
		return false;
	}

	/**
	 * Validation of modified data.
	 *
	 * @throws \App\Exceptions\SaveRecord
	 */
	public function validate()
	{
		$checkUserExist = false;
		if ($this->isNew()) {
			$checkUserExist = true;
		} else {
			if (false !== $this->getPreviousValue('is_admin')) {
				\App\Privilege::setAllUpdater();
			}
			if (false !== $this->getPreviousValue('roleid')) {
				$checkUserExist = true;
			}
		}
		if ($checkUserExist) {
			$query = (new App\Db\Query())->from('vtiger_users')
				->leftJoin('vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id')
				->where(['vtiger_users.user_name' => $this->get('user_name'), 'vtiger_user2role.roleid' => $this->get('roleid')]);
			if (false === $this->isNew()) {
				$query->andWhere(['<>', 'vtiger_users.id', $this->getId()]);
			}
			if ($query->exists()) {
				throw new \App\Exceptions\SaveRecord('ERR_USER_EXISTS||' . $this->get('user_name'), 406);
			}
			if ($this->getId()) {
				\App\Db::getInstance()->createCommand()->delete('vtiger_module_dashboard_widgets', ['userid' => $this->getId()])->execute();
			}
			\App\Privilege::setAllUpdater();
		}
		if (!$this->isNew() && false !== $this->getPreviousValue('user_password') && App\User::getCurrentUserId() === $this->getId()) {
			$isExists = (new \App\Db\Query())->from('l_#__userpass_history')->where(['user_id' => $this->getId(), 'pass' => \App\Encryption::createHash($this->get('user_password'))])->exists();
			if ($isExists) {
				throw new \App\Exceptions\SaveRecord('ERR_PASSWORD_HAS_ALREADY_BEEN_USED', 406);
			}
		}
	}

	/**
	 * Function after save to database.
	 */
	public function afterSaveToDb()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (false !== $this->getPreviousValue('user_password') && ($this->isNew() || App\User::getCurrentUserId() === $this->getId())) {
			$dbCommand->insert('l_#__userpass_history', [
				'pass' => \App\Encryption::createHash($this->get('user_password')),
				'user_id' => $this->getId(),
				'date' => date('Y-m-d H:i:s'),
			])->execute();
		}
		if (false !== $this->getPreviousValue('language') && App\User::getCurrentUserRealId() === $this->getId()) {
			App\Session::set('language', $this->get('language'));
		}
		\App\UserPrivilegesFile::createUserPrivilegesfile($this->getId());
		\App\UserPrivilegesFile::createUserSharingPrivilegesfile($this->getId());
		if (AppConfig::performance('ENABLE_CACHING_USERS')) {
			\App\PrivilegeFile::createUsersFile();
		}
		if ($this->getPreviousValue('sync_caldav') || $this->isNew()) {
			$dbCommand->update('vtiger_activity', ['dav_status' => 1])->execute();
		}
		if ($this->getPreviousValue('sync_carddav') || $this->isNew()) {
			$dbCommand->update('vtiger_contactdetails', ['dav_status' => 1])->execute();
			$dbCommand->update('vtiger_ossemployees', ['dav_status' => 1])->execute();
		}
	}

	/**
	 * Static Function to get the instance of the User Record model for the current user.
	 *
	 * @return Users_Record_Model instance
	 */
	protected static $currentUserModels = [];

	public static function getCurrentUserModel()
	{
		$currentUser = \App\User::getCurrentUserModel();
		if ($currentUser->getId()) {
			// Optimization to avoid object creation every-time
			// Caching is per-id as current_user can get swapped at runtime (ex. workflow)
			$currentUserModel = null;
			if (isset(static::$currentUserModels[$currentUser->getId()])) {
				$currentUserModel = static::$currentUserModels[$currentUser->getId()];
			}
			if (!$currentUserModel) {
				static::$currentUserModels[$currentUser->getId()] = $currentUserModel = static::getInstanceFromUserObject($currentUser);
			}

			return $currentUserModel;
		}
		return new self();
	}

	/**
	 * Static Function to get the instance of the User Record model from the given Users object.
	 *
	 * @param mixed $currentUser
	 *
	 * @return Users_Record_Model instance
	 */
	public static function getInstanceFromUserObject($currentUser)
	{
		$userDetails = array_map('\App\Purifier::decodeHtml', $currentUser->getDetails());
		$userModel = new self();
		foreach ($userDetails as $key => $value) {
			$userModel->{$key} = $value;
		}
		return $userModel->setData($userDetails)->setModule('Users')->setId($currentUser->getId());
	}

	/**
	 * Static Function to get the instance of all the User Record models.
	 *
	 * @param bool $onlyActive
	 *
	 * @return <Array> - List of Users_Record_Model instances
	 */
	public static function getAll($onlyActive = true)
	{
		$query = (new \App\Db\Query())
			->select(['id'])
			->from('vtiger_users');
		if ($onlyActive) {
			$query->where(['status' => 'Active']);
		}
		$users = [];
		$dataReader = $query->createCommand()->query();
		while ($userId = $dataReader->readColumn(0)) {
			$userModel = self::getInstanceFromUserObject(\App\User::getUserModel($userId));
			$users[(int) $userModel->getId()] = $userModel;
		}
		$dataReader->close();

		return $users;
	}

	/**
	 * Function returns the Subordinate users.
	 *
	 * @return <Array>
	 */
	public function getSubordinateUsers()
	{
		$privilegesModel = $this->get('privileges');

		if (empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}

		$subordinateUsers = [];
		$subordinateRoleUsers = $privilegesModel->get('subordinate_roles_users');
		if ($subordinateRoleUsers) {
			foreach ($subordinateRoleUsers as $users) {
				foreach ($users as $user) {
					$subordinateUsers[$user] = $privilegesModel->getDisplayName();
				}
			}
		}
		return $subordinateUsers;
	}

	/**
	 * Function returns the Users Current Role.
	 *
	 * @return string
	 */
	public function getRole()
	{
		$privilegesModel = $this->get('privileges');

		if (empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}
		return $privilegesModel->get('roleid');
	}

	public function getRoleDetail()
	{
		$roleDetail = $this->get('roleDetail');
		if (!empty($roleDetail)) {
			return $this->get('roleDetail');
		}
		$privileges = $this->get('privileges');
		if (empty($privileges)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}
		$roleModel = Settings_Roles_Record_Model::getInstanceById($this->get('privileges')->get('roleid'));
		$this->set('roleDetail', $roleModel);

		return $roleModel;
	}

	/**
	 * Function returns the Users Current Role.
	 *
	 * @return string
	 */
	public function getProfiles()
	{
		$userProfiles = $this->get('profiles');
		if (empty($userProfiles)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$userProfiles = $privilegesModel->get('profiles');
			$this->set('profiles', $userProfiles);
		}
		$profiles = [];
		if (!empty($userProfiles)) {
			foreach ($userProfiles as $profile) {
				$profiles[$profile] = Settings_Profiles_Record_Model::getInstanceById($profile);
			}
		}
		return $profiles;
	}

	public function getGroups()
	{
		if (empty($this->get('groups'))) {
			if ($this->isAdminUser()) {
				$userGroups = App\PrivilegeUtil::getAllGroupsByUser($this->getId());
			} else {
				$userGroups = $this->getPrivileges()->get('groups');
			}
			$this->set('groups', $userGroups);
		}
		return $this->get('groups');
	}

	public function getParentRoles()
	{
		if (empty($this->get('parentRoles'))) {
			if ($this->isAdminUser()) {
				$userParentRoles = \App\PrivilegeUtil::getParentRole($this->getRole());
			} else {
				$privilegesModel = $this->getPrivileges();
				$userParentRoles = $privilegesModel->get('parent_roles');
			}
			$this->set('parentRoles', $userParentRoles);
		}
		return $this->get('parentRoles');
	}

	/**
	 * Function to get privillage model.
	 *
	 * @return $privillage model
	 */
	public function getPrivileges()
	{
		$privilegesModel = $this->get('privileges');
		if (empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}
		return $privilegesModel;
	}

	/**
	 * Function to get user default activity view.
	 *
	 * @return string
	 */
	public function getActivityView()
	{
		return $this->get('activity_view');
	}

	/**
	 * Function to get the Day Starts picklist values.
	 *
	 * @return array
	 */
	public function getDayStartsPicklistValues()
	{
		$hourFormats = $this->getField('hour_format')->getPicklistValues();
		$startHour = $this->getField('start_hour')->getPicklistValues();
		$defaultValues = [
			'00:00' => '12:00 AM', '01:00' => '01:00 AM', '02:00' => '02:00 AM', '03:00' => '03:00 AM', '04:00' => '04:00 AM', '05:00' => '05:00 AM',
			'06:00' => '06:00 AM', '07:00' => '07:00 AM', '08:00' => '08:00 AM', '09:00' => '09:00 AM', '10:00' => '10:00 AM', '11:00' => '11:00 AM', '12:00' => '12:00 PM',
			'13:00' => '01:00 PM', '14:00' => '02:00 PM', '15:00' => '03:00 PM', '16:00' => '04:00 PM', '17:00' => '05:00 PM', '18:00' => '06:00 PM', '19:00' => '07:00 PM',
			'20:00' => '08:00 PM', '21:00' => '09:00 PM', '22:00' => '10:00 PM', '23:00' => '11:00 PM',
		];
		$picklistDependencyData = [];
		foreach ($hourFormats as $value) {
			if (24 == $value) {
				$picklistDependencyData['hour_format'][$value]['start_hour'] = $startHour;
			} else {
				$picklistDependencyData['hour_format'][$value]['start_hour'] = $defaultValues;
			}
		}
		if (empty($picklistDependencyData['hour_format']['__DEFAULT__']['start_hour'])) {
			$picklistDependencyData['hour_format']['__DEFAULT__']['start_hour'] = $defaultValues;
		}
		return $picklistDependencyData;
	}

	/**
	 * Function to get user groups.
	 *
	 * @param int $userId
	 *
	 * @return array - groupId's
	 */
	public static function getUserGroups($userId)
	{
		return App\PrivilegeUtil::getUserGroups($userId);
	}

	/**
	 * Function returns the users activity reminder in seconds.
	 *
	 * @return string
	 */

	/**
	 * Function returns the users activity reminder in seconds.
	 *
	 * @return string
	 */
	public function getCurrentUserActivityReminderInSeconds()
	{
		$activityReminder = $this->reminder_interval;
		$activityReminderInSeconds = '';
		if ('None' != $activityReminder) {
			preg_match('/([0-9]+)[\s]([a-zA-Z]+)/', $activityReminder, $matches);
			if ($matches) {
				$number = $matches[1];
				$string = $matches[2];
				if ($string) {
					switch ($string) {
						case 'Minute':
						case 'Minutes':
							$activityReminderInSeconds = $number * 60;
							break;
						case 'Hour':
							$activityReminderInSeconds = $number * 60 * 60;
							break;
						case 'Day':
							$activityReminderInSeconds = $number * 60 * 60 * 24;
							break;
						default:
							$activityReminderInSeconds = '';
					}
				}
			}
		}
		return $activityReminderInSeconds;
	}

	/**
	 * Function to get the users count.
	 *
	 * @param bool $onlyActive - If true it returns count of only acive users else only inactive users
	 *
	 * @return int number of users
	 */
	public static function getCount($onlyActive = false)
	{
		$query = (new App\Db\Query())->from('vtiger_users');
		if ($onlyActive) {
			$query->where(['status' => 'Active']);
		}
		return $query->count();
	}

	/**
	 * Funtion to get Duplicate Record Url.
	 *
	 * @return string
	 */
	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&parent=Settings&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true';
	}

	/**
	 * Function to get instance of user model by name.
	 *
	 * @param string $userName
	 *
	 * @return Users_Record_Model
	 */
	public static function getInstanceByName($userName)
	{
		$id = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['or', ['user_name' => $userName], ['user_name' => strtolower($userName)]])->scalar();
		if ($id) {
			return self::getInstanceById($id, 'Users');
		}
		return false;
	}

	/**
	 * Function to get instance of user model by id from file.
	 *
	 * @param int $userId
	 *
	 * @return Users_Record_Model
	 */
	public static function getInstanceFromFile($userId)
	{
		if (empty($userId)) {
			\App\Log::error('No user id: ' . $userId);

			return false;
		}
		$valueMap = \App\User::getPrivilegesFile($userId);
		$instance = new self();
		$instance->setData($valueMap['user_info']);

		return $instance;
	}

	/**
	 * Function to delete the current Record Model.
	 */
	public function delete()
	{
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		try {
			$db->createCommand()->update('vtiger_users', [
				'status' => 'Inactive',
				'date_modified' => date('Y-m-d H:i:s'),
				'modified_user_id' => App\User::getCurrentUserRealId(),
			], ['id' => $this->getId()])->execute();
			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	public function getActiveAdminUsers()
	{
		$dataReader = (new App\Db\Query())->select(['id'])->from('vtiger_users')->where(['status' => 'Active', 'is_admin' => 'on'])->createCommand()->query();
		$users = [];
		while ($id = $dataReader->readColumn(0)) {
			$users[$id] = self::getInstanceFromUserObject(\App\User::getUserModel($id));
		}
		return $users;
	}

	/**
	 * Function to delete user permanemtly from CRM and
	 * assign all record which are assigned to that user
	 * and not transfered to other user to other user.
	 *
	 * @param mixed $userId
	 * @param mixed $newOwnerId
	 */
	public static function deleteUserPermanently($userId, $newOwnerId)
	{
		$db = App\Db::getInstance();
		$db->createCommand()->update('vtiger_crmentity', ['smcreatorid' => $newOwnerId, 'smownerid' => $newOwnerId], ['smcreatorid' => $userId, 'setype' => 'ModComments'])->execute();
		//update history details in vtiger_modtracker_basic
		$db->createCommand()->update('vtiger_modtracker_basic', ['whodid' => $newOwnerId], ['whodid' => $userId])->execute();
		//update comments details in vtiger_modcomments
		$db->createCommand()->update('vtiger_modcomments', ['userid' => $newOwnerId], ['userid' => $userId])->execute();
		$db->createCommand()->delete('vtiger_users', ['id' => $userId])->execute();
		\App\PrivilegeUtil::deleteRelatedSharingRules($userId, 'Users');
		$fileName = "user_privileges/sharing_privileges_{$userId}.php";
		if (file_exists($fileName)) {
			unlink($fileName);
		}
		$fileName = "user_privileges/user_privileges_{$userId}.php";
		if (file_exists($fileName)) {
			unlink($fileName);
		}
	}

	/**
	 * Function to get the Display Name for the record.
	 *
	 * @return string - Entity Display Name for the record
	 */
	public function getDisplayName()
	{
		return \App\Purifier::encodeHtml(\vtlib\Deprecated::getFullNameFromArray($this->getModuleName(), $this->getData()));
	}

	public function getSwitchUsersUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=SwitchUsers&id=' . $this->getId();
	}

	public function getLocks()
	{
		if ($this->has('locks')) {
			return $this->get('locks');
		}
		require 'user_privileges/locks.php';
		if ($this->getId() && array_key_exists($this->getId(), $locks)) {
			$this->set('locks', $locks[$this->getId()]);

			return $locks[$this->getId()];
		}
		return [];
	}

	public function getBodyLocks()
	{
		$return = '';
		foreach ($this->getLocks() as $lock) {
			switch ($lock) {
				case 'copy':
					$return .= ' oncopy = "return false"';
					break;
				case 'cut':
					$return .= ' oncut = "return false"';
					break;
				case 'paste':
					$return .= ' onpaste = "return false"';
					break;
				case 'contextmenu':
					$return .= ' oncontextmenu = "return false"';
					break;
				case 'selectstart':
					$return .= ' onselectstart = "return false" onselect = "return false"';
					break;
				case 'drag':
					$return .= ' ondragstart = "return false" ondrag = "return false"';
					break;
				default:
					break;
			}
		}
		return $return;
	}

	public function getHeadLocks()
	{
		$return = 'function lockFunction() {return false;}';
		foreach ($this->getLocks() as $lock) {
			switch ($lock) {
				case 'copy':
					$return .= ' document.oncopy = lockFunction;';
					break;
				case 'cut':
					$return .= ' document.oncut = lockFunction;';
					break;
				case 'paste':
					$return .= ' document.onpaste = lockFunction;';
					break;
				case 'contextmenu':
					$return .= ' document.oncontextmenu = function(event) {if(event.button==2){return false;}}; document.oncontextmenu = lockFunction;';
					break;
				case 'selectstart':
					$return .= ' document.onselectstart = lockFunction; document.onselect = lockFunction;';
					break;
				case 'drag':
					$return .= ' document.ondragstart = lockFunction; document.ondrag = lockFunction;';
					break;
				default:
					break;
			}
		}
		return $return;
	}

	/**
	 * Encrypt user password.
	 *
	 * @param string $password User password
	 *
	 * @return string Encrypted password
	 */
	public function encryptPassword($password)
	{
		return password_hash($password, PASSWORD_BCRYPT, ['cost' => AppConfig::security('USER_ENCRYPT_PASSWORD_COST')]);
	}

	/**
	 * Verify user assword.
	 *
	 * @param string $password
	 *
	 * @return bool
	 */
	public function verifyPassword($password)
	{
		return password_verify($password, $this->get('user_password'));
	}

	/**
	 * Slower logon for security purposes.
	 *
	 * @param string $password
	 */
	public function fakeEncryptPassword($password)
	{
		$this->getAuthDetail();
		\Settings_Password_Record_Model::getUserPassConfig();
		password_verify($password, $this->encryptPassword($password));
	}

	/**
	 * The function to log on to the system.
	 *
	 * @param string $password The password of the user to authenticate
	 *
	 * @return bool true if the user is authenticated, false otherwise
	 */
	public function doLogin($password)
	{
		$userName = $this->get('user_name');
		$row = (new App\Db\Query())->select(['id', 'deleted'])->from('vtiger_users')->where(['or', ['user_name' => $userName], ['user_name' => strtolower($userName)]])->limit(1)->one();
		if (!$row || 0 !== (int) $row['deleted']) {
			$this->fakeEncryptPassword($password);
			\App\Log::info('User not found: ' . $userName, 'UserAuthentication');

			return false;
		}
		$this->set('id', $row['id']);
		$userRecordModel = static::getInstanceFromFile($row['id']);
		if ('Active' !== $userRecordModel->get('status')) {
			\App\Log::info('Inactive user :' . $userName, 'UserAuthentication');

			return false;
		}
		$result = $userRecordModel->doLoginByAuthMethod($password);
		if (null !== $result) {
			return $result;
		}
		if (null === $result && $userRecordModel->verifyPassword($password)) {
			\App\Session::set('UserAuthMethod', 'PASSWORD');

			return true;
		}
		\App\Log::info('Invalid password. User: ' . $userName, 'UserAuthentication');

		return false;
	}

	/**
	 * User authorization based on authorization methods.
	 *
	 * @param string $password
	 *
	 * @return null|bool
	 */
	protected function doLoginByAuthMethod($password)
	{
		$auth = $this->getAuthDetail();
		if ('true' === $auth['ldap']['active']) {
			$authMethod = new Users_Ldap_Authmethod($this);
			return $authMethod->process($auth['ldap'], $password);
		}
		return null;
	}

	/**
	 * Get authorization detail.
	 *
	 * @return array
	 */
	protected function getAuthDetail()
	{
		if (\App\Cache::has('getAuthMethods', 'config')) {
			return \App\Cache::get('getAuthMethods', 'config');
		}
		$dataReader = (new \App\Db\Query())->from('yetiforce_auth')->createCommand()->query();
		$auth = [];
		while ($row = $dataReader->read()) {
			$auth[$row['type']][$row['param']] = $row['value'];
		}
		$dataReader->close();
		\App\Cache::save('getAuthMethods', 'config', $auth);

		return $auth;
	}

	/**
	 * Verify  password change.
	 *
	 * @param App\User $userModel
	 *
	 * @return bool
	 */
	public function verifyPasswordChange(App\User $userModel)
	{
		$passConfig = \Settings_Password_Record_Model::getUserPassConfig();
		$time = (int) $passConfig['change_time'];
		if (1 === (int) $userModel->getDetail('force_password_change')) {
			\App\Session::set('ShowUserPasswordChange', 2);
		}
		if (0 === $time) {
			return false;
		}
		if (strtotime("-$time day") > strtotime($userModel->getDetail('date_password_change'))) {
			$time += (int) $passConfig['lock_time'];
			if (strtotime("-$time day") > strtotime($userModel->getDetail('date_password_change'))) {
				return true;
			}
			\App\Session::set('ShowUserPasswordChange', 1);
		}
		return false;
	}

	/**
	 * Return user favourite users.
	 *
	 * @return array
	 */
	public function getFavouritesUsers()
	{
		if (\App\Cache::has('UsersFavourite', $this->getId())) {
			$favouriteUsers = \App\Cache::get('UsersFavourite', $this->getId());
		} else {
			$query = new \App\Db\Query();
			$favouriteUsers = $query->select(['fav_element_id', 'pinned_id' => 'fav_element_id'])
				->from('u_#__users_pinned')
				->where(['owner_id' => $this->getId()])
				->createCommand()
				->queryAllByGroup();
			\App\Cache::save('UsersFavourite', $this->getId(), $favouriteUsers, \App\Cache::LONG);
		}
		return $favouriteUsers;
	}
}
