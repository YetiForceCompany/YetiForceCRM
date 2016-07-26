<?php namespace includes;

/**
 * Privileges basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Privileges
{

	protected static $globalSearchCache = [];

	public static function globalSearchByModule($moduleName, $userId = false)
	{
		if (!$userId) {
			$user = \Users_Record_Model::getCurrentUserModel();
			$userId = $user->getId();
		}
		$users = self::getGlobalSearchUsers();
		if (isset($users[$userId]) && in_array($moduleName, $users[$userId])) {
			return true;
		}
		return false;
	}

	public static function globalSearchById($record, $moduleName, $userId = false)
	{
		if (self::globalSearchByModule($moduleName, $userId)) {
			return true;
		}
		return self::isPermitted($moduleName, 'DetailView', $record, $userId);
	}

	public static function updateGlobalSearch($record, $moduleName)
	{
		$adb = \PearDatabase::getInstance();
		$glabalPrivileges = '';
		$currentUser = vglobal('current_user');
		$user = new \Users();
		$users = \includes\fields\Owner::getUsersIds();
		foreach ($users as &$userId) {
			vglobal('current_user', $user->retrieveCurrentUserInfoFromFile($userId));
			if (self::globalSearchById($record, $moduleName, $userId)) {
				$glabalPrivileges .= ',' . $userId; //sprintf("%'.05d", $userId)
			}
		}
		if (!empty($glabalPrivileges)) {
			$glabalPrivileges .= ',';
		}
		$adb->update('u_yf_crmentity_search_label', ['userid' => $glabalPrivileges], 'crmid = ?', [$record]);
		vglobal('current_user', $currentUser);
	}

	protected static $globalSearchUsersCache = [];

	public static function getGlobalSearchUsers()
	{
		if (empty(self::$globalSearchUsersCache)) {
			$adb = \PearDatabase::getInstance();
			$query = 'SELECT `userid`,`searchunpriv` FROM `vtiger_user2role` LEFT JOIN `vtiger_role` ON vtiger_role.roleid = vtiger_user2role.roleid WHERE vtiger_role.`searchunpriv` <> \'\'';
			$result = $adb->query($query);
			while ($row = $adb->getRow($result)) {
				self::$globalSearchUsersCache[$row['userid']] = explode(',', $row['searchunpriv']);
			}
		}
		return self::$globalSearchUsersCache;
	}

	protected static $isPermittedLevel = [];

	/**
	 * Function to check permission for a Module/Action/Record
	 * @param <String> $moduleName
	 * @param <String> $actionName
	 * @param <Number> $record
	 * @return Boolean
	 */
	public static function isPermitted($moduleName, $actionName = null, $record = false, $userId = false)
	{
		$log = \LoggerManager::getInstance();
		$log->debug("Entering isPermitted($moduleName,$actionName,$record) method ...");
		if (!$userId) {
			$current_user = vglobal('current_user');
			$userId = $current_user->id;
		}
		$userPrivileges = \Vtiger_Util_Helper::getUserPrivilegesFile($userId);
		$permission = false;
		if (($moduleName == 'Users' || $moduleName == 'Home' || $moduleName == 'uploads') && \AppRequest::get('parenttab') != 'Settings') {
			//These modules dont have security right now
			self::$isPermittedLevel = 'SEC_MODULE_DONT_HAVE_SECURITY_RIGHT';
			$log->debug('Exiting isPermitted method ...');
			return true;
		}
		//Checking the Access for the Settings Module
		if ($moduleName == 'Settings' || $moduleName == 'Administration' || $moduleName == 'System' || \AppRequest::get('parenttab') == 'Settings') {
			if (!$userPrivileges['is_admin']) {
				$permission = false;
			} else {
				$permission = true;
			}
			self::$isPermittedLevel = 'SEC_ADMINISTRATION_MODULE_' . $permission;
			$log->debug('Exiting isPermitted method ...');
			return $permission;
		}
		//Retreiving the Tabid and Action Id
		$tabid = \vtlib\Functions::getModuleId($moduleName);
		$actionid = getActionid($actionName);
		$checkModule = $moduleName;

		if ($checkModule == 'Events') {
			$checkModule = 'Calendar';
		}

		if (vtlib_isModuleActive($checkModule)) {
			//Checking whether the user is admin
			if ($userPrivileges['is_admin']) {
				self::$isPermittedLevel = 'SEC_USER_IS_ADMIN';
				$log->debug('Exiting isPermitted method ...');
				return true;
			}

			//If no actionid, then allow action is vtiger_tab permission is available
			if ($actionid === '' || $actionid === null) {
				if ($userPrivileges['profile_tabs_permission'][$tabid] == 0) {
					$permission = true;
				} else {
					$permission = false;
				}
				self::$isPermittedLevel = 'SEC_USER_IS_ADMIN' . $permission;
				$log->debug('Exiting isPermitted method ...');
				return $permission;
			}
			//Checking for vtiger_tab permission
			if ($userPrivileges['profile_tabs_permission'][$tabid] != 0) {
				self::$isPermittedLevel = 'SEC_MODULE_PERMISSIONS_NO';
				$log->debug('Exiting isPermitted method ... - no');
				return false;
			}

			if ($actionid === false) {
				self::$isPermittedLevel = 'SEC_ACTION_DOES_NOT_EXIST';
				$log->debug('Exiting isPermitted method ... - no');
				return false;
			}
			//Checking for Action Permission
			if (!isset($userPrivileges['profile_action_permission'][$tabid][$actionid])) {
				self::$isPermittedLevel = 'SEC_MODULE_NO_ACTION_TOOL';
				$log->debug('Exiting isPermitted method ... - no');
				return false;
			}
			if (strlen($userPrivileges['profile_action_permission'][$tabid][$actionid]) < 1 && $userPrivileges['profile_action_permission'][$tabid][$actionid] == '') {
				self::$isPermittedLevel = 'SEC_MODULE_RIGHTS_TO_ACTION';
				$log->debug('Exiting isPermitted method ...');
				return true;
			}

			if ($userPrivileges['profile_action_permission'][$tabid][$actionid] != 0 && $userPrivileges['profile_action_permission'][$tabid][$actionid] != '') {
				self::$isPermittedLevel = 'SEC_MODULE_NO_RIGHTS_TO_ACTION';
				$log->debug('Exiting isPermitted method ... - no');
				return false;
			}
			//Checking for view all permission
			if ($userPrivileges['profile_global_permission'][1] == 0 || $userPrivileges['profile_global_permission'][2] == 0) {
				if ($actionid == 3 || $actionid == 4) {
					self::$isPermittedLevel = 'SEC_MODULE_VIEW_ALL_PERMISSION';
					$log->debug('Exiting isPermitted method ...');
					return true;
				}
			}
			//Checking for edit all permission
			if ($userPrivileges['profile_global_permission'][2] == 0) {
				if ($actionid == 3 || $actionid == 4 || $actionid == 0 || $actionid == 1) {
					self::$isPermittedLevel = 'SEC_MODULE_EDIT_ALL_PERMISSION';
					$log->debug('Exiting isPermitted method ...');
					return true;
				}
			}
			//Checking and returning true if recorid is null
			if ($record == '') {
				self::$isPermittedLevel = 'SEC_RECORID_IS_NULL';
				$log->debug('Exiting isPermitted method ...');
				return true;
			}

			//If modules is Products,Vendors,Faq,PriceBook then no sharing
			if ($record != '') {
				if (getTabOwnedBy($moduleName) == 1) {
					self::$isPermittedLevel = 'SEC_MODULE_IS_OWNEDBY';
					$log->debug('Exiting isPermitted method ...');
					return true;
				}
			}

			$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($record);
			if (!isset($recordMetaData) || $recordMetaData['deleted'] == 1) {
				self::$isPermittedLevel = 'SEC_RECORD_DOES_NOT_EXIST';
				$log->debug('Exiting isPermitted method ... - no');
				return false;
			}

			//Retreiving the RecordOwnerId
			$recOwnType = '';
			$recOwnId = '';
			$recordOwnerArr = getRecordOwnerId($record);
			$shownerids = \Vtiger_SharedOwner_UIType::getSharedOwners($record, $moduleName);
			foreach ($recordOwnerArr as $type => $id) {
				$recOwnType = $type;
				$recOwnId = $id;
			}
			if (in_array($userId, $shownerids) || count(array_intersect($shownerids, $userPrivileges['groups'])) > 0) {
				self::$isPermittedLevel = 'SEC_RECORD_SHARED_OWNER';
				$log->debug('Exiting isPermitted method ... - Shared Owner');
				return true;
			}
			if ($recOwnType == 'Users') {
				//Checking if the Record Owner is the current User
				if ($userId == $recOwnId) {
					self::$isPermittedLevel = 'SEC_RECORD_OWNER_CURRENT_USER';
					$log->debug('Exiting isPermitted method ...');
					return true;
				}

				//Checking if the Record Owner is the Subordinate User
				foreach ($userPrivileges['subordinate_roles_users'] as $roleid => $userids) {
					if (in_array($recOwnId, $userids)) {
						self::$isPermittedLevel = 'SEC_RECORD_OWNER_SUBORDINATE_USER';
						$log->debug('Exiting isPermitted method ...');
						return true;
					}
				}
			} elseif ($recOwnType == 'Groups') {
				//Checking if the record owner is the current user's group
				if (in_array($recOwnId, $userPrivileges['groups'])) {
					self::$isPermittedLevel = 'SEC_RECORD_OWNER_CURRENT_GROUP';
					$log->debug('Exiting isPermitted method ...');
					return true;
				}
			}
			$userPrivilegesModel = \Users_Privileges_Model::getInstanceById($userId);
			$role = $userPrivilegesModel->getRoleDetail();
			if ((($actionid == 3 || $actionid == 4) && $role->get('previewrelatedrecord') != 0 ) || (($actionid == 0 || $actionid == 1) && $role->get('editrelatedrecord') != 0 )) {
				$parentRecord = \Users_Privileges_Model::getParentRecord($record, $moduleName, $role->get('previewrelatedrecord'), $actionid);
				if ($parentRecord) {
					$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($parentRecord);
					$permissionsRoleForRelatedField = $role->get('permissionsrelatedfield');
					$permissionsRelatedField = empty($permissionsRoleForRelatedField) ? [] : explode(',', $role->get('permissionsrelatedfield'));
					$relatedPermission = false;
					foreach ($permissionsRelatedField as &$row) {
						switch ($row) {
							case 0:
								$relatedPermission = $recordMetaData['smownerid'] == $userId || in_array($recordMetaData['smownerid'], $userPrivileges['groups']);
								break;
							case 1:
								$relatedPermission = in_array($userId, \Vtiger_SharedOwner_UIType::getSharedOwners($parentRecord, $recordMetaData['setype']));
								break;
							case 2:
								$permission = isPermittedBySharing($recordMetaData['setype'], getTabid($recordMetaData['setype']), $actionid, $parentRecord);
								$relatedPermission = $permission == 'yes' ? true : false;
								break;
						}
						if ($relatedPermission) {
							self::$isPermittedLevel = 'SEC_RECORD_HIERARCHY_USER';
							$log->debug('Exiting isPermitted method ... - Parent Record Owner');
							return true;
						}
					}
				}
			}
			$permission = isPermittedBySharing($moduleName, $tabid, $actionid, $record) == 'yes' ? true : false;
			self::$isPermittedLevel = 'SEC_RECORD_BY_SHARING_' . $permission;
			$log->debug('Exiting isPermitted method ... - isPermittedBySharing');
		} else {
			$permission = false;
			self::$isPermittedLevel = 'SEC_MODULE_IS_INACTIVE';
		}

		$log->debug('Exiting isPermitted method ...');
		return $permission;
	}
}
