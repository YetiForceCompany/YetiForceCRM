<?php
namespace App;

/**
 * Privilege basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Privilege
{

	public static $isPermittedLevel;

	/**
	 * Function to check permission for a Module/Action/Record
	 * @param string $moduleName
	 * @param string $actionName
	 * @param <Number> $record
	 * @return Boolean
	 */
	public static function isPermitted($moduleName, $actionName = null, $record = false, $userId = false)
	{
		\App\Log::trace("Entering isPermitted($moduleName,$actionName,$record,$userId) method ...");
		if (!$userId) {
			$userId = \App\User::getCurrentUserId();
		}
		$userPrivileges = \App\User::getPrivilegesFile($userId);
		$permission = false;
		if (($moduleName == 'Users' || $moduleName == 'Home' || $moduleName == 'uploads') && \AppRequest::get('parenttab') != 'Settings') {
			//These modules dont have security right now
			static::$isPermittedLevel = 'SEC_MODULE_DONT_HAVE_SECURITY_RIGHT';
			\App\Log::trace('Exiting isPermitted method ... - yes');
			return true;
		}
		//Checking the Access for the Settings Module
		if ($moduleName == 'Settings' || $moduleName == 'Administration' || $moduleName == 'System' || \AppRequest::get('parenttab') == 'Settings') {
			if (!$userPrivileges['is_admin']) {
				$permission = false;
			} else {
				$permission = true;
			}
			static::$isPermittedLevel = 'SEC_ADMINISTRATION_MODULE_' . ($permission ? 'YES' : 'NO');
			\App\Log::trace('Exiting isPermitted method ... - ' . ($permission) ? 'YES' : 'NO');
			return $permission;
		}
		//Retreiving the Tabid and Action Id
		$tabid = Module::getModuleId($moduleName);
		$actionid = getActionid($actionName);
		$checkModule = $moduleName;

		if ($checkModule == 'Events') {
			$checkModule = 'Calendar';
		}

		if (Module::isModuleActive($checkModule)) {
			//Checking whether the user is admin
			if ($userPrivileges['is_admin']) {
				if ($record) {
					$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($record);
					if (!isset($recordMetaData) || $recordMetaData['deleted'] === 1) {
						static::$isPermittedLevel = 'SEC_RECORD_DOES_NOT_EXIST';
						\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_DOES_NOT_EXIST');
						return false;
					}
				}
				static::$isPermittedLevel = 'SEC_USER_IS_ADMIN';
				\App\Log::trace('Exiting isPermitted method ... - SEC_USER_IS_ADMIN');
				return true;
			}

			//If no actionid, then allow action is vtiger_tab permission is available
			if ($actionid === '' || $actionid === null) {
				if ($userPrivileges['profile_tabs_permission'][$tabid] == 0) {
					$permission = true;
				} else {
					$permission = false;
				}
				static::$isPermittedLevel = 'SEC_USER_IS_ADMIN' . ($permission ? 'YES' : 'NO');
				\App\Log::trace('Exiting isPermitted method ... - ' . static::$isPermittedLevel);
				return $permission;
			}
			//Checking for vtiger_tab permission
			if ($userPrivileges['profile_tabs_permission'][$tabid] != 0) {
				static::$isPermittedLevel = 'SEC_MODULE_PERMISSIONS_NO';
				\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_PERMISSIONS_NO');
				return false;
			}

			if ($actionid === false) {
				static::$isPermittedLevel = 'SEC_ACTION_DOES_NOT_EXIST';
				\App\Log::trace('Exiting isPermitted method ... - SEC_ACTION_DOES_NOT_EXIST');
				return false;
			}
			//Checking for Action Permission
			if (!isset($userPrivileges['profile_action_permission'][$tabid][$actionid])) {
				static::$isPermittedLevel = 'SEC_MODULE_NO_ACTION_TOOL';
				\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_NO_ACTION_TOOL');
				return false;
			}
			if (strlen($userPrivileges['profile_action_permission'][$tabid][$actionid]) < 1 && $userPrivileges['profile_action_permission'][$tabid][$actionid] == '') {
				static::$isPermittedLevel = 'SEC_MODULE_RIGHTS_TO_ACTION';
				\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_RIGHTS_TO_ACTION');
				return true;
			}

			if ($userPrivileges['profile_action_permission'][$tabid][$actionid] != 0 && $userPrivileges['profile_action_permission'][$tabid][$actionid] != '') {
				static::$isPermittedLevel = 'SEC_MODULE_NO_RIGHTS_TO_ACTION';
				\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_NO_RIGHTS_TO_ACTION');
				return false;
			}
			//Checking for view all permission
			if ($userPrivileges['profile_global_permission'][1] == 0 || $userPrivileges['profile_global_permission'][2] == 0) {
				if ($actionid == 3 || $actionid == 4) {
					static::$isPermittedLevel = 'SEC_MODULE_VIEW_ALL_PERMISSION';
					\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_VIEW_ALL_PERMISSION');
					return true;
				}
			}
			//Checking for edit all permission
			if ($userPrivileges['profile_global_permission'][2] == 0) {
				if ($actionid == 3 || $actionid == 4 || $actionid == 0 || $actionid == 1) {
					static::$isPermittedLevel = 'SEC_MODULE_EDIT_ALL_PERMISSION';
					\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_EDIT_ALL_PERMISSION');
					return true;
				}
			}
			//Checking and returning true if recorid is null
			if (empty($record)) {
				static::$isPermittedLevel = 'SEC_RECORID_IS_NULL';
				\App\Log::trace('Exiting isPermitted method ... - SEC_RECORID_IS_NULL');
				return true;
			} else {
				//If modules is Products,Vendors,Faq,PriceBook then no sharing
				if (\vtlib\Functions::getModuleOwner($moduleName) == 1) {
					static::$isPermittedLevel = 'SEC_MODULE_IS_OWNEDBY';
					\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_IS_OWNEDBY');
					return true;
				}
			}
			$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($record);
			if (!isset($recordMetaData) || $recordMetaData['deleted'] === 1) {
				static::$isPermittedLevel = 'SEC_RECORD_DOES_NOT_EXIST';
				\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_DOES_NOT_EXIST');
				return false;
			}
			if (\AppConfig::security('PERMITTED_BY_PRIVATE_FIELD') && $recordMetaData['private']) {
				$level = 'SEC_PRIVATE_RECORD_NO';
				$isPermittedPrivateRecord = false;
				$recOwnId = $recordMetaData['smownerid'];
				$recOwnType = \App\Fields\Owner::getType($recOwnId);
				if ($recOwnType === 'Users') {
					if ($userId === $recOwnId) {
						$level = 'SEC_PRIVATE_RECORD_OWNER_CURRENT_USER';
						$isPermittedPrivateRecord = true;
					}
				} elseif ($recOwnType === 'Groups') {
					if (in_array($recOwnId, $userPrivileges['groups'])) {
						$level = 'SEC_PRIVATE_RECORD_OWNER_CURRENT_GROUP';
						$isPermittedPrivateRecord = true;
					}
				}
				if (!$isPermittedPrivateRecord) {
					$shownerids = \Vtiger_SharedOwner_UIType::getSharedOwners($record, $moduleName);
					if (in_array($userId, $shownerids) || count(array_intersect($shownerids, $userPrivileges['groups'])) > 0) {
						$level = 'SEC_PRIVATE_RECORD_SHARED_OWNER';
						$isPermittedPrivateRecord = true;
					}
				}
				static::$isPermittedLevel = $level;
				\App\Log::trace('Exiting isPermitted method ... - ' . static::$isPermittedLevel);
				return $isPermittedPrivateRecord;
			}
			// Check advanced permissions
			if (\AppConfig::security('PERMITTED_BY_ADVANCED_PERMISSION')) {
				$prvAdv = PrivilegeAdvanced::checkPermissions($record, $moduleName, $userId);
				if ($prvAdv !== false) {
					if ($prvAdv === 0) {
						static::$isPermittedLevel = 'SEC_ADVANCED_PERMISSION_NO';
						\App\Log::trace('Exiting isPermitted method ... - SEC_ADVANCED_PERMISSION_NO');
						return false;
					} else {
						static::$isPermittedLevel = 'SEC_ADVANCED_PERMISSION_YES';
						\App\Log::trace('Exiting isPermitted method ... - SEC_ADVANCED_PERMISSION_YES');
						return true;
					}
				}
			}
			if (\AppConfig::security('PERMITTED_BY_SHARED_OWNERS')) {
				$shownerids = \Vtiger_SharedOwner_UIType::getSharedOwners($record, $moduleName);
				if (in_array($userId, $shownerids) || count(array_intersect($shownerids, $userPrivileges['groups'])) > 0) {
					static::$isPermittedLevel = 'SEC_RECORD_SHARED_OWNER';
					\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_SHARED_OWNER');
					return true;
				}
			}
			//Retreiving the RecordOwnerId
			$recOwnId = $recordMetaData['smownerid'];
			$recOwnType = \App\Fields\Owner::getType($recOwnId);

			if ($recOwnType == 'Users') {
				//Checking if the Record Owner is the current User
				if ($userId == $recOwnId) {
					static::$isPermittedLevel = 'SEC_RECORD_OWNER_CURRENT_USER';
					\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_OWNER_CURRENT_USER');
					return true;
				}
				if (\AppConfig::security('PERMITTED_BY_ROLES')) {
					//Checking if the Record Owner is the Subordinate User
					foreach ($userPrivileges['subordinate_roles_users'] as &$userids) {
						if (in_array($recOwnId, $userids)) {
							static::$isPermittedLevel = 'SEC_RECORD_OWNER_SUBORDINATE_USER';
							\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_OWNER_SUBORDINATE_USER');
							return true;
						}
					}
				}
			} elseif ($recOwnType == 'Groups') {
				//Checking if the record owner is the current user's group
				if (in_array($recOwnId, $userPrivileges['groups'])) {
					static::$isPermittedLevel = 'SEC_RECORD_OWNER_CURRENT_GROUP';
					\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_OWNER_CURRENT_GROUP');
					return true;
				}
			}
			if (\AppConfig::security('PERMITTED_BY_RECORD_HIERARCHY')) {
				$userPrivilegesModel = \Users_Privileges_Model::getInstanceById($userId);
				$role = $userPrivilegesModel->getRoleDetail();
				if ((($actionid == 3 || $actionid == 4) && $role->get('previewrelatedrecord') != 0 ) || (($actionid == 0 || $actionid == 1) && $role->get('editrelatedrecord') != 0 )) {
					$parentRecord = \Users_Privileges_Model::getParentRecord($record, $moduleName, $role->get('previewrelatedrecord'), $actionid);
					if ($parentRecord) {
						$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($parentRecord);
						$permissionsRoleForRelatedField = $role->get('permissionsrelatedfield');
						$permissionsRelatedField = $permissionsRoleForRelatedField == '' ? [] : explode(',', $role->get('permissionsrelatedfield'));
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
									if (\AppConfig::security('PERMITTED_BY_SHARING')) {
										$relatedPermission = static::isPermittedBySharing($recordMetaData['setype'], Module::getModuleId($recordMetaData['setype']), $actionid, $parentRecord, $userId);
									}
									break;
							}
							if ($relatedPermission) {
								static::$isPermittedLevel = 'SEC_RECORD_HIERARCHY_USER';
								\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_HIERARCHY_USER');
								return true;
							}
						}
					}
				}
			}
			if (\AppConfig::security('PERMITTED_BY_SHARING')) {
				$permission = static::isPermittedBySharing($moduleName, $tabid, $actionid, $record, $userId);
			}
			static::$isPermittedLevel = 'SEC_RECORD_BY_SHARING_' . ($permission ? 'YES' : 'NO');
		} else {
			$permission = false;
			static::$isPermittedLevel = 'SEC_MODULE_IS_INACTIVE';
		}

		\App\Log::trace('Exiting isPermitted method ... - ' . static::$isPermittedLevel);
		return $permission;
	}

	public static function isPermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId)
	{
		$sharingPrivileges = \App\User::getSharingFile($userId);
		//Retreiving the default Organisation sharing Access
		$othersPermissionId = $sharingPrivileges['defOrgShare'][$tabId];
		//Checking for Default Org Sharing permission
		if ($othersPermissionId == 0) {
			if ($actionId == 1 || $actionId == 0) {
				return static::isReadWritePermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
			} elseif ($actionId == 2) {
				return false;
			} else {
				return true;
			}
		} elseif ($othersPermissionId == 1) {
			if ($actionId == 2) {
				return false;
			} else {
				return true;
			}
		} elseif ($othersPermissionId == 2) {
			return true;
		} elseif ($othersPermissionId == 3) {
			if ($actionId == 3 || $actionId == 4) {
				return static::isReadPermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
			} elseif ($actionId == 0 || $actionId == 1) {
				return static::isReadWritePermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
			} elseif ($actionId == 2) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
		return false;
	}

	/** Function to check if the currently logged in user has Read Access due to Sharing for the specified record
	 * @param $moduleName -- Module Name:: Type varchar
	 * @param $actionId -- Action Id:: Type integer
	 * @param $recordId -- Record Id:: Type integer
	 * @param $tabId -- Tab Id:: Type integer
	 * @returns yes or no. If Yes means this action is allowed for the currently logged in user. If no means this action is not allowed for the currently logged in user
	 */
	public static function isReadPermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId)
	{
		\App\Log::trace("Entering isReadPermittedBySharing($moduleName,$tabId,$actionId,$recordId,$userId) method ...");
		$sharingPrivileges = \App\User::getSharingFile($userId);

		if (!isset($sharingPrivileges['permission'][$moduleName])) {
			return false;
		}
		$sharingPrivilegesModule = $sharingPrivileges['permission'][$moduleName];

		$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($recordId);
		$ownerId = $recordMetaData['smownerid'];
		$ownerType = \App\Fields\Owner::getType($ownerId);

		$read = $sharingPrivilegesModule['read'];
		if ($ownerType == 'Users') {
			//Checking the Read Sharing Permission Array in Role Users
			foreach ($read['ROLE'] as $userids) {
				if (in_array($ownerId, $userids)) {
					\App\Log::trace('Exiting isReadPermittedBySharing method ...');
					return true;
				}
			}
			//Checking the Read Sharing Permission Array in Groups Users
			foreach ($read['GROUP'] as $userids) {
				if (in_array($ownerId, $userids)) {
					\App\Log::trace('Exiting isReadPermittedBySharing method ...');
					return true;
				}
			}
		} else {
			if (isset($read['GROUP'][$ownerId])) {
				\App\Log::trace('Exiting isReadPermittedBySharing method ...');
				return true;
			}
		}

		//Checking for the Related Sharing Permission
		$relatedModuleArray = $sharingPrivileges['relatedModuleShare'][$tabId];
		if (is_array($relatedModuleArray)) {
			foreach ($relatedModuleArray as $parModId) {
				$parRecordOwner = PrivilegeUtil::getParentRecordOwner($tabId, $parModId, $recordId);
				if (sizeof($parRecordOwner) > 0) {
					$parModName = \vtlib\Functions::getModuleName($parModId);
					if (isset($sharingPrivileges['permission'][$parModName . '_' . $moduleName])) {
						$readRelated = $sharingPrivileges['permission'][$parModName . '_' . $moduleName]['read'];

						$relOwnerType = '';
						$relOwnerId = '';
						foreach ($parRecordOwner as $rel_type => $rel_id) {
							$relOwnerType = $rel_type;
							$relOwnerId = $rel_id;
						}
						if ($relOwnerType == 'Users') {
							//Checking in Role Users
							foreach ($readRelated['ROLE'] as $userids) {
								if (in_array($relOwnerId, $userids)) {
									\App\Log::trace('Exiting isReadPermittedBySharing method ...');
									return true;
								}
							}
							//Checking in Group Users
							foreach ($readRelated['GROUP'] as $userids) {
								if (in_array($relOwnerId, $userids)) {
									\App\Log::trace('Exiting isReadPermittedBySharing method ...');
									return true;
								}
							}
						} else {
							if (isset($readRelated['GROUP'][$relOwnerId])) {
								\App\Log::trace('Exiting isReadPermittedBySharing method ...');
								return true;
							}
						}
					}
				}
			}
		}
		\App\Log::trace('Exiting isReadPermittedBySharing method ...');
		return false;
	}

	/** Function to check if the currently logged in user has Write Access due to Sharing for the specified record
	 * @param $moduleName -- Module Name:: Type varchar
	 * @param $actionid -- Action Id:: Type integer
	 * @param $recordid -- Record Id:: Type integer
	 * @param $tabid -- Tab Id:: Type integer
	 * @returns yes or no. If Yes means this action is allowed for the currently logged in user. If no means this action is not allowed for the currently logged in user
	 */
	public static function isReadWritePermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId)
	{
		\App\Log::trace("Entering isReadWritePermittedBySharing($moduleName,$tabId,$actionId,$recordId,$userId) method ...");
		$sharingPrivileges = \App\User::getSharingFile($userId);
		if (!isset($sharingPrivileges['permission'][$moduleName])) {
			return false;
		}
		$sharingPrivilegesModule = $sharingPrivileges['permission'][$moduleName];

		$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($recordId);
		$ownerId = $recordMetaData['smownerid'];
		$ownerType = \App\Fields\Owner::getType($ownerId);

		$write = $sharingPrivilegesModule['write'];
		if ($ownerType == 'Users') {
			//Checking the Write Sharing Permission Array in Role Users
			foreach ($write['ROLE'] as $userids) {
				if (in_array($ownerId, $userids)) {
					\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');
					return true;
				}
			}
			//Checking the Write Sharing Permission Array in Groups Users
			foreach ($write['GROUP'] as $userids) {
				if (in_array($ownerId, $userids)) {
					\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');
					return true;
				}
			}
		} elseif ($ownerType == 'Groups') {
			if (isset($write['GROUP'][$ownerId])) {
				\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');
				return true;
			}
		}
		//Checking for the Related Sharing Permission
		$relatedModuleArray = $sharingPrivileges['relatedModuleShare'][$tabId];
		if (is_array($relatedModuleArray)) {
			foreach ($relatedModuleArray as $parModId) {
				$parRecordOwner = PrivilegeUtil::getParentRecordOwner($tabId, $parModId, $recordId);
				if (!empty($parRecordOwner)) {
					$parModName = \vtlib\Functions::getModuleName($parModId);
					if (isset($sharingPrivileges['permission'][$parModName . '_' . $moduleName])) {
						$writeRelated = $sharingPrivileges['permission'][$parModName . '_' . $moduleName]['write'];
						$relOwnerType = '';
						$relOwnerId = '';
						foreach ($parRecordOwner as $rel_type => $rel_id) {
							$relOwnerType = $rel_type;
							$relOwnerId = $rel_id;
						}
						if ($relOwnerType == 'Users') {
							//Checking in Role Users
							foreach ($writeRelated['ROLE'] as $userids) {
								if (in_array($relOwnerId, $userids)) {
									\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');
									return true;
								}
							}
							//Checking in Group Users
							foreach ($writeRelated['GROUP'] as $userids) {
								if (in_array($relOwnerId, $userids)) {
									\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');
									return true;
								}
							}
						} else {
							if (isset($writeRelated['GROUP'][$relOwnerId])) {
								\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');
								return true;
							}
						}
					}
				}
			}
		}
		\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');
		return false;
	}

	/**
	 * Add to global permissions update queue.
	 * @param string $moduleName Module name
	 * @param int $record If type = 1 starting number if type = 0 record ID
	 * @param int $priority
	 * @param int $type
	 */
	public static function setUpdater($moduleName, $record = false, $priority = false, $type = 1)
	{
		PrivilegeUpdater::setUpdater($moduleName, $record, $priority, $type);
	}

	public static function setAllUpdater()
	{
		PrivilegeUpdater::setAllUpdater();
	}
}
