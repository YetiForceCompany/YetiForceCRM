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

	/**
	 * Gets the value of the key . First it will check whether specified key is a property if not it
	 *  will get from normal data attribure from base class
	 * @param <string> $key - property or key name
	 * @return <object>
	 */
	public function get($key)
	{
		if (property_exists($this, $key)) {
			return $this->$key;
		}
		return parent::get($key);
	}

	/**
	 * Sets the value of the key . First it will check whether specified key is a property if not it
	 * will set from normal set from base class
	 * @param <string> $key - property or key name
	 * @param <string> $value
	 */
	public function set($key, $value)
	{
		if (property_exists($this, $key)) {
			$this->$key = $value;
		}
		parent::set($key, $value);
		return $this;
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&parent=Settings&view=' . $module->getDetailViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail View url for the Preferences page
	 * @return <String> - Record Detail View Url
	 */
	public function getPreferenceDetailViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=PreferenceDetail&record=' . $this->getId();
	}

	/**
	 * Function to get the url for the Profile page
	 * @return <String> - Profile Url
	 */
	public function getProfileUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=Users&view=ChangePassword&mode=Profile';
	}

	/**
	 * Function to get the Edit View url for the record
	 * @return <String> - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&parent=Settings&view=' . $module->getEditViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the Edit View url for the Preferences page
	 * @return <String> - Record Detail View Url
	 */
	public function getPreferenceEditViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=PreferenceEdit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return <String> - Record Delete Action Url
	 */
	public function getDeleteUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&parent=Settings&view=' . $module->getDeleteActionName() . 'User&record=' . $this->getId();
	}

	/**
	 * Function to check whether the user is an Admin user
	 * @return <Boolean> true/false
	 */
	public function isAdminUser()
	{
		$adminStatus = $this->get('is_admin');
		if ($adminStatus == 'on') {
			return true;
		}
		return false;
	}

	/**
	 * Function to get the module name
	 * @return <String> Module Name
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
	 * Function to save the current Record Model
	 */
	public function save()
	{
		parent::save();
	}

	/**
	 * Function to get all the Home Page components list
	 * @return <Array> List of the Home Page components
	 */
	public function getHomePageComponents()
	{
		$entity = $this->getEntity();
		$homePageComponents = $entity->getHomeStuffOrder($this->getId());
		return $homePageComponents;
	}

	/**
	 * Static Function to get the instance of the User Record model for the current user
	 * @return Users_Record_Model instance
	 */
	protected static $currentUserModels = array();

	public static function getCurrentUserModel()
	{
		//TODO : Remove the global dependency
		$currentUser = vglobal('current_user');
		if (!empty($currentUser)) {

			// Optimization to avoid object creation every-time
			// Caching is per-id as current_user can get swapped at runtime (ex. workflow)
			$currentUserModel = NULL;
			if (isset(self::$currentUserModels[$currentUser->id])) {
				$currentUserModel = self::$currentUserModels[$currentUser->id];
				if (isset($currentUser->column_fields['modifiedtime']) && $currentUser->column_fields['modifiedtime'] != $currentUserModel->get('modifiedtime')) {
					$currentUserModel = NULL;
				}
			}
			if (!$currentUserModel) {
				$currentUserModel = self::getInstanceFromUserObject($currentUser);
				self::$currentUserModels[$currentUser->id] = $currentUserModel;
			}
			return $currentUserModel;
		}
		return new self();
	}

	/**
	 * Static Function to get the instance of the User Record model from the given Users object
	 * @return Users_Record_Model instance
	 */
	public static function getInstanceFromUserObject($userObject)
	{
		$objectProperties = get_object_vars($userObject);
		$userModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$userModel->$properName = $propertyValue;
		}
		return $userModel->setData($userObject->column_fields)->setModule('Users')->setEntity($userObject);
	}

	/**
	 * Static Function to get the instance of all the User Record models
	 * @return <Array> - List of Users_Record_Model instances
	 */
	public static function getAll($onlyActive = true)
	{
		$db = PearDatabase::getInstance();

		$sql = 'SELECT id FROM vtiger_users';
		$params = array();
		if ($onlyActive) {
			$sql .= ' WHERE status = ?';
			$params[] = 'Active';
		}
		$result = $db->pquery($sql, $params);

		$noOfUsers = $db->num_rows($result);
		$users = array();
		if ($noOfUsers > 0) {
			$focus = new Users();
			for ($i = 0; $i < $noOfUsers; ++$i) {
				$userId = $db->query_result($result, $i, 'id');
				$focus->id = $userId;
				$focus->retrieve_entity_info($userId, 'Users');

				$userModel = self::getInstanceFromUserObject($focus);
				$users[$userModel->getId()] = $userModel;
			}
		}
		return $users;
	}

	/**
	 * Function returns the Subordinate users
	 * @return <Array>
	 */
	function getSubordinateUsers()
	{
		$privilegesModel = $this->get('privileges');

		if (empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}

		$subordinateUsers = array();
		$subordinateRoleUsers = $privilegesModel->get('subordinate_roles_users');
		if ($subordinateRoleUsers) {
			foreach ($subordinateRoleUsers as $role => $users) {
				foreach ($users as $user) {
					$subordinateUsers[$user] = $privilegesModel->getDisplayName();
				}
			}
		}
		return $subordinateUsers;
	}

	/**
	 * Function returns the Users Parent Role
	 * @return <String>
	 */
	function getParentRoleSequence()
	{
		$privilegesModel = $this->get('privileges');

		if (empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}

		return $privilegesModel->get('parent_role_seq');
	}

	/**
	 * Function returns the Users Current Role
	 * @return <String>
	 */
	function getRole()
	{
		$privilegesModel = $this->get('privileges');

		if (empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}

		return $privilegesModel->get('roleid');
	}

	function getRoleDetail()
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
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_role WHERE roleid = ?', [$this->get('privileges')->get('roleid')]);
		$this->set('roleDetail', $db->fetch_array($result));
		return $this->get('roleDetail');
	}

	/**
	 * Function returns List of Accessible Users for a Module
	 * @param <String> $module
	 * @return <Array of Users_Record_Model>
	 */
	public function getAccessibleUsersForModule($module)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$curentUserPrivileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if ($currentUser->isAdminUser() || $curentUserPrivileges->hasGlobalWritePermission()) {
			$users = $this->getAccessibleUsers("", $module);
		} else {
			$sharingAccessModel = Settings_SharingAccess_Module_Model::getInstance($module);
			if ($sharingAccessModel && $sharingAccessModel->isPrivate()) {
				$users = $this->getAccessibleUsers('private', $module);
			} else {
				$users = $this->getAccessibleUsers("", $module);
			}
		}
		return $users;
	}

	/**
	 * Function returns List of Accessible Users for a Module
	 * @param <String> $module
	 * @return <Array of Users_Record_Model>
	 */
	public function getAccessibleGroupForModule($module)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$curentUserPrivileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if ($currentUser->isAdminUser() || $curentUserPrivileges->hasGlobalWritePermission()) {
			$groups = $this->getAccessibleGroups("", $module);
		} else {
			$sharingAccessModel = Settings_SharingAccess_Module_Model::getInstance($module);
			if ($sharingAccessModel && $sharingAccessModel->isPrivate()) {
				$groups = $this->getAccessibleGroups('private', $module);
			} else {
				$groups = $this->getAccessibleGroups("", $module);
			}
		}
		return $groups;
	}

	/**
	 * Function to get Images Data
	 * @return <Array> list of Image names and paths
	 */
	public function getImageDetails()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$imageDetails = array();
		$recordId = $this->getId();

		if ($recordId) {
			$query = "SELECT vtiger_attachments.* FROM vtiger_attachments
            LEFT JOIN vtiger_salesmanattachmentsrel ON vtiger_salesmanattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
            WHERE vtiger_salesmanattachmentsrel.smid=?";

			$result = $db->pquery($query, array($recordId));

			$imageId = $db->query_result($result, 0, 'attachmentsid');
			$imagePath = $db->query_result($result, 0, 'path');
			$imageName = $db->query_result($result, 0, 'name');

			//decode_html - added to handle UTF-8 characters in file names
			$imageOriginalName = decode_html($imageName);

			$imageDetails[] = array(
				'id' => $imageId,
				'orgname' => $imageOriginalName,
				'path' => $imagePath . $imageId,
				'name' => $imageName
			);
		}
		return $imageDetails;
	}

	/**
	 * Function to get all the accessible users
	 * @return <Array>
	 */
	public function getAccessibleUsers($private = "", $module = false)
	{
		$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($this->getRole());
		$accessibleUser = Vtiger_Cache::get('vtiger-' . $this->getRole() . '-' . $currentUserRoleModel->get('allowassignedrecordsto'), 'accessibleusers');
		if (empty($accessibleUser)) {
			if ($currentUserRoleModel->get('allowassignedrecordsto') == '1' || $private == 'Public') {
				$accessibleUser = get_user_array(false, "ACTIVE", "", $private, $module);
			} else if ($currentUserRoleModel->get('allowassignedrecordsto') == '2') {
				$accessibleUser = $this->getSameLevelUsersWithSubordinates();
			} else if ($currentUserRoleModel->get('allowassignedrecordsto') == '3') {
				$accessibleUser = $this->getRoleBasedSubordinateUsers();
			} else if ($currentUserRoleModel->get('allowassignedrecordsto') == '4') {
				$accessibleUser[$this->getId()] = $this->getName();
			}
			Vtiger_Cache::set('vtiger-' . $this->getRole() . '-' . $currentUserRoleModel->get('allowassignedrecordsto'), 'accessibleusers', $accessibleUser);
		}
		return $accessibleUser;
	}

	/**
	 * Function to get same level and subordinates Users
	 * @return <array> Users
	 */
	public function getSameLevelUsersWithSubordinates()
	{
		$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($this->getRole());
		$sameLevelRoles = $currentUserRoleModel->getSameLevelRoles();
		$sameLevelUsers = $this->getAllUsersOnRoles($sameLevelRoles);
		$subordinateUsers = $this->getRoleBasedSubordinateUsers();
		foreach ($subordinateUsers as $userId => $userName) {
			$sameLevelUsers[$userId] = $userName;
		}
		return $sameLevelUsers;
	}

	/**
	 * Function to get subordinates Users
	 * @return <array> Users
	 */
	public function getRoleBasedSubordinateUsers()
	{
		$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($this->getRole());
		$childernRoles = $currentUserRoleModel->getAllChildren();
		$users = $this->getAllUsersOnRoles($childernRoles);
		$currentUserDetail = array($this->getId() => $this->getDisplayName());
		$users = $currentUserDetail + $users;
		return $users;
	}

	/**
	 * Function to get the users based on Roles
	 * @param type $roles
	 * @return <array>
	 */
	public function getAllUsersOnRoles($roles)
	{
		$db = PearDatabase::getInstance();
		$roleIds = array();
		foreach ($roles as $key => $role) {
			$roleIds[] = $role->getId();
		}

		if (empty($roleIds)) {
			return array();
		}

		$sql = 'SELECT userid FROM vtiger_user2role WHERE roleid IN (' . generateQuestionMarks($roleIds) . ')';
		$result = $db->pquery($sql, $roleIds);
		$noOfUsers = $db->num_rows($result);
		$userIds = array();
		$subUsers = array();
		if ($noOfUsers > 0) {
			for ($i = 0; $i < $noOfUsers; ++$i) {
				$userIds[] = $db->query_result($result, $i, 'userid');
			}
			$entityData = Vtiger_Functions::getEntityModuleSQLColumnString('Users');
			$query = 'SELECT id, ' . $entityData['colums'] . ' FROM vtiger_users WHERE status = ? AND id IN (' . generateQuestionMarks($userIds) . ')';
			$result = $db->pquery($query, array('ACTIVE', $userIds));
			while ($row = $db->fetch_array($result)) {
				$colums = [];
				foreach (explode(',', $entityData['fieldname']) as &$fieldname) {
					$colums[] = $row[$fieldname];
				}
				$subUsers[$row['id']] = implode(' ', $colums);
			}
		}
		return $subUsers;
	}

	/**
	 * Function to get all the accessible groups
	 * @return <Array>
	 */
	public function getAccessibleGroups($private = "", $module = false)
	{
		//TODO:Remove dependence on $_REQUEST for the module name in the below API
		$accessibleGroups = Vtiger_Cache::get('vtiger-' . $private, 'accessiblegroups');
		if (!$accessibleGroups) {
			$accessibleGroups = get_group_array(false, "ACTIVE", "", $private, $module);
			Vtiger_Cache::set('vtiger-' . $private, 'accessiblegroups', $accessibleGroups);
		}
		return get_group_array(false, "ACTIVE", "", $private);
	}

	/**
	 * Function to get privillage model
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
	 * Function to get user default activity view
	 * @return <String>
	 */
	public function getActivityView()
	{
		$activityView = $this->get('activity_view');
		return $activityView;
	}

	/**
	 * Function to delete corresponding image
	 * @param <type> $imageId
	 */
	public function deleteImage($imageId)
	{
		$db = PearDatabase::getInstance();

		$checkResult = $db->pquery('SELECT smid FROM vtiger_salesmanattachmentsrel WHERE attachmentsid = ?', array($imageId));
		$smId = $db->query_result($checkResult, 0, 'smid');

		if ($this->getId() === $smId) {
			$db->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid = ?', array($imageId));
			$db->pquery('DELETE FROM vtiger_salesmanattachmentsrel WHERE attachmentsid = ?', array($imageId));
			return true;
		}
		return false;
	}

	/**
	 * Function to get the Day Starts picklist values
	 * @param type $name Description
	 */
	public static function getDayStartsPicklistValues($stucturedValues)
	{
		$fieldModel = $stucturedValues['LBL_CALENDAR_SETTINGS'];
		$hour_format = $fieldModel['hour_format']->getPicklistValues();
		$start_hour = $fieldModel['start_hour']->getPicklistValues();

		$defaultValues = array('00:00' => '12:00 AM', '01:00' => '01:00 AM', '02:00' => '02:00 AM', '03:00' => '03:00 AM', '04:00' => '04:00 AM', '05:00' => '05:00 AM',
			'06:00' => '06:00 AM', '07:00' => '07:00 AM', '08:00' => '08:00 AM', '09:00' => '09:00 AM', '10:00' => '10:00 AM', '11:00' => '11:00 AM', '12:00' => '12:00 PM',
			'13:00' => '01:00 PM', '14:00' => '02:00 PM', '15:00' => '03:00 PM', '16:00' => '04:00 PM', '17:00' => '05:00 PM', '18:00' => '06:00 PM', '19:00' => '07:00 PM',
			'20:00' => '08:00 PM', '21:00' => '09:00 PM', '22:00' => '10:00 PM', '23:00' => '11:00 PM');

		$picklistDependencyData = array();
		foreach ($hour_format as $value) {
			if ($value == 24) {
				$picklistDependencyData['hour_format'][$value]['start_hour'] = $start_hour;
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
	 * Function to get user groups
	 * @param type $userId
	 * @return <array> - groupId's
	 */
	public static function getUserGroups($userId)
	{
		$db = PearDatabase::getInstance();
		$groupIds = array();
		$query = "SELECT groupid FROM vtiger_users2group WHERE userid=?";
		$result = $db->pquery($query, array($userId));
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$groupId = $db->query_result($result, $i, 'groupid');
			$groupIds[] = $groupId;
		}
		return $groupIds;
	}
	/**
	 * Function returns the users activity reminder in seconds
	 * @return string
	 */

	/**
	 * Function returns the users activity reminder in seconds
	 * @return string
	 */
	function getCurrentUserActivityReminderInSeconds()
	{
		$activityReminder = $this->reminder_interval;
		$activityReminderInSeconds = '';
		if ($activityReminder != 'None') {
			preg_match('/([0-9]+)[\s]([a-zA-Z]+)/', $activityReminder, $matches);
			if ($matches) {
				$number = $matches[1];
				$string = $matches[2];
				if ($string) {
					switch ($string) {
						case 'Minute':
						case 'Minutes': $activityReminderInSeconds = $number * 60;
							break;
						case 'Hour' : $activityReminderInSeconds = $number * 60 * 60;
							break;
						case 'Day' : $activityReminderInSeconds = $number * 60 * 60 * 24;
							break;
						default : $activityReminderInSeconds = '';
					}
				}
			}
		}
		return $activityReminderInSeconds;
	}

	/**
	 * Function to get the users count
	 * @param <Boolean> $onlyActive - If true it returns count of only acive users else only inactive users
	 * @return <Integer> number of users
	 */
	public static function getCount($onlyActive = false)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT 1 FROM vtiger_users ';
		$params = array();

		if ($onlyActive) {
			$query.= ' WHERE status=? ';
			array_push($params, 'active');
		}

		$result = $db->pquery($query, $params);

		$numOfUsers = $db->num_rows($result);
		return $numOfUsers;
	}

	/**
	 * Funtion to get Duplicate Record Url
	 * @return <String>
	 */
	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&parent=Settings&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true';
	}

	/**
	 * Function to get instance of user model by name
	 * @param <String> $userName
	 * @return <Users_Record_Model>
	 */
	public static function getInstanceByName($userName)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT id FROM vtiger_users WHERE user_name = ?', array($userName));

		if ($db->num_rows($result)) {
			return Users_Record_Model::getInstanceById($db->query_result($result, 0, 'id'), 'Users');
		}
		return false;
	}

	/**
	 * Function to delete the current Record Model
	 */
	public function delete()
	{
		$this->getModule()->deleteRecord($this);
	}

	public function isAccountOwner()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT is_owner FROM vtiger_users WHERE id = ?';
		$isOwner = $db->query_result($db->pquery($query, array($this->getId())), 0, 'is_owner');
		if ($isOwner == 1) {
			return true;
		}
		return false;
	}

	public function getActiveAdminUsers()
	{
		$db = PearDatabase::getInstance();

		$sql = 'SELECT id FROM vtiger_users WHERE status=? AND is_admin=?';
		$result = $db->pquery($sql, array('ACTIVE', 'on'));

		$noOfUsers = $db->num_rows($result);
		$users = array();
		if ($noOfUsers > 0) {
			$focus = new Users();
			for ($i = 0; $i < $noOfUsers; ++$i) {
				$userId = $db->query_result($result, $i, 'id');
				$focus->id = $userId;
				$focus->retrieve_entity_info($userId, 'Users');

				$userModel = self::getInstanceFromUserObject($focus);
				$users[$userModel->getId()] = $userModel;
			}
		}
		return $users;
	}

	/**
	 * Function to get the user hash
	 * @param type $userId
	 * @return boolean
	 */
	public function getUserHash()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT user_hash FROM vtiger_users WHERE id = ?';
		$result = $db->pquery($query, array($this->getId()));
		if ($db->num_rows($result) > 0) {
			return $db->query_result($result, 0, 'user_hash');
		}
	}
	/*
	 * Function to delete user permanemtly from CRM and
	 * assign all record which are assigned to that user
	 * and not transfered to other user to other user
	 * 
	 * @param User Ids of user to be deleted and user
	 * to whom records should be assigned
	 */

	public function deleteUserPermanently($userId, $newOwnerId)
	{
		$db = PearDatabase::getInstance();

		$sql = 'UPDATE vtiger_crmentity SET smcreatorid=?,smownerid=? WHERE smcreatorid=? AND setype=?';
		$db->pquery($sql, array($newOwnerId, $newOwnerId, $userId, 'ModComments'));

		//update history details in vtiger_modtracker_basic 
		$sql = 'update vtiger_modtracker_basic set whodid=? where whodid=?';
		$db->pquery($sql, array($newOwnerId, $userId));

		//update comments details in vtiger_modcomments 
		$sql = 'update vtiger_modcomments set userid=? where userid=?';
		$db->pquery($sql, array($newOwnerId, $userId));

		$sql = 'DELETE FROM vtiger_users WHERE id=?';
		$db->pquery($sql, array($userId));
	}

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName()
	{
		return getFullNameFromArray($this->getModuleName(), $this->getData());
	}

	public function getUsersAndGroupForModuleList($module, $view)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$userEntityInfo = Vtiger_Functions::getEntityModuleInfo('Users');
		$table = $userEntityInfo['tablename'];
		$columnsName = explode(',', $userEntityInfo['fieldname']);

		$queryGenerator = new QueryGenerator($module, $currentUser);
		$queryGenerator->initForCustomViewById($view);
		$queryGenerator->setFields(['assigned_user_id']);
		$queryGenerator->addCustomColumn('vtiger_groups.groupname');
		foreach ($columnsName as &$column) {
			$queryGenerator->addCustomColumn($table . '.' . $column);
		}
		$listQuery = $queryGenerator->getQuery('SELECT DISTINCT');
		$result = $db->query($listQuery);

		$users = $group = [];
		while ($row = $db->fetch_array($result)) {
			if (isset($row['groupname'])) {
				$group[$row['smownerid']] = $row['groupname'];
			} else {
				$name = '';
				foreach ($columnsName as &$column) {
					$name .= $row[$column] . ' ';
				}
				$users[$row['smownerid']] = trim($name);
			}
		}
		return [ 'users' => $users, 'group' => $group];
	}
}
