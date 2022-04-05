<?php

namespace App;

/**
 * Privilege basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Privilege
{
	public static $isPermittedLevel;

	/**
	 * Interpreter for privilege.
	 *
	 * @var string
	 */
	private static $interpreter;

	/**
	 * Sets interpreter.
	 *
	 * @param string $className
	 *
	 * @return void
	 */
	public static function setPermissionInterpreter(string $className)
	{
		static::$interpreter = $className;
	}

	/**
	 * Invokes function to check permission .
	 *
	 * @param string   $moduleName
	 * @param string   $actionName
	 * @param bool|int $record
	 * @param mixed    $userId
	 *
	 * @return bool
	 */
	public static function isPermitted($moduleName, $actionName = null, $record = false, $userId = false)
	{
		if (!empty(static::$interpreter) && class_exists(static::$interpreter)) {
			return (static::$interpreter)::isPermitted($moduleName, $actionName, $record, $userId);
		}
		return static::checkPermission($moduleName, $actionName, $record, $userId);
	}

	/**
	 * Function to check permission for a Module/Action/Record.
	 *
	 * @param string   $moduleName
	 * @param string   $actionName
	 * @param bool|int $record
	 * @param mixed    $userId
	 *
	 * @return bool
	 */
	public static function checkPermission($moduleName, $actionName = null, $record = false, $userId = false)
	{
		\App\Log::trace("Entering isPermitted($moduleName,$actionName,$record,$userId) method ...");
		if (!$userId) {
			$userId = \App\User::getCurrentUserId();
		}
		$userId = (int) $userId;
		$userPrivileges = \App\User::getPrivilegesFile($userId);
		$permission = false;
		$tabId = Module::getModuleId($moduleName);
		if ('Settings' !== Request::_get('parent')) {
			if ('Users' === $moduleName && $record == \App\User::getCurrentUserId()) {
				static::$isPermittedLevel = 'SEC_IS_CURRENT_USER';
				\App\Log::trace('Exiting isPermitted method ... - yes');
				return true;
			}
		} elseif (false === $tabId) {
			$permission = $userPrivileges['is_admin'] ? true : false;
			static::$isPermittedLevel = 'SEC_ADMINISTRATION_MODULE_' . ($permission ? 'YES' : 'NO');
			\App\Log::trace('Exiting isPermitted method ... - ' . ($permission) ? 'YES' : 'NO');
			return $permission;
		}
		if (!Module::isModuleActive($moduleName)) {
			static::$isPermittedLevel = 'SEC_MODULE_IS_INACTIVE';
			\App\Log::trace('Exiting isPermitted method ... - yes');
			return false;
		}
		$actionId = Module::getActionId($actionName);
		//Checking whether the user is admin
		if ($userPrivileges['is_admin']) {
			if ($record && 'Users' !== $moduleName) {
				$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($record);
				if (empty($recordMetaData)) {
					static::$isPermittedLevel = 'SEC_RECORD_DOES_NOT_EXIST';
					\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_DOES_NOT_EXIST');
					return false;
				}
				if (0 !== $recordMetaData['deleted'] && (1 === $actionId || 0 === $actionId || 17 === $actionId)) {
					switch ($recordMetaData['deleted']) {
						case 1:
							static::$isPermittedLevel = 'SEC_RECORD_DELETED';
							\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_DELETED');
							break;
						case 2:
							static::$isPermittedLevel = 'SEC_RECORD_ARCHIVED';
							\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_ARCHIVED');
							break;
						default:
							break;
					}
					return false;
				}
			}
			static::$isPermittedLevel = 'SEC_USER_IS_ADMIN';
			\App\Log::trace('Exiting isPermitted method ... - SEC_USER_IS_ADMIN');
			return true;
		}
		//If no actionid, then allow action is vtiger_tab permission is available
		if ('' === $actionId || null === $actionId) {
			if (isset($userPrivileges['profile_tabs_permission'][$tabId]) && 0 == $userPrivileges['profile_tabs_permission'][$tabId]) {
				$permission = true;
			} else {
				$permission = false;
			}
			static::$isPermittedLevel = 'SEC_NO_ACTION_MODULE_PERMISSIONS' . ($permission ? 'YES' : 'NO');
			\App\Log::trace('Exiting isPermitted method ... - ' . static::$isPermittedLevel);
			return $permission;
		}
		//Checking for vtiger_tab permission
		if (!isset($userPrivileges['profile_tabs_permission'][$tabId]) || 0 != $userPrivileges['profile_tabs_permission'][$tabId]) {
			static::$isPermittedLevel = 'SEC_MODULE_PERMISSIONS_NO';
			\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_PERMISSIONS_NO');
			return false;
		}
		if (false === $actionId) {
			static::$isPermittedLevel = 'SEC_ACTION_DOES_NOT_EXIST';
			\App\Log::trace('Exiting isPermitted method ... - SEC_ACTION_DOES_NOT_EXIST');
			return false;
		}
		//Checking for Action Permission
		if (!isset($userPrivileges['profile_action_permission'][$tabId][$actionId])) {
			static::$isPermittedLevel = 'SEC_MODULE_NO_ACTION_TOOL';
			\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_NO_ACTION_TOOL');
			return false;
		}
		if (\strlen($userPrivileges['profile_action_permission'][$tabId][$actionId]) < 1 && '' === $userPrivileges['profile_action_permission'][$tabId][$actionId]) {
			static::$isPermittedLevel = 'SEC_MODULE_RIGHTS_TO_ACTION';
			\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_RIGHTS_TO_ACTION');
			return true;
		}
		if (0 != $userPrivileges['profile_action_permission'][$tabId][$actionId] && '' != $userPrivileges['profile_action_permission'][$tabId][$actionId]) {
			static::$isPermittedLevel = 'SEC_MODULE_NO_RIGHTS_TO_ACTION';
			\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_NO_RIGHTS_TO_ACTION');
			return false;
		}
		//Checking for view all permission
		if ((0 == $userPrivileges['profile_global_permission'][1] || 0 == $userPrivileges['profile_global_permission'][2]) && (3 == $actionId || 4 == $actionId)) {
			static::$isPermittedLevel = 'SEC_MODULE_VIEW_ALL_PERMISSION';
			\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_VIEW_ALL_PERMISSION');
			return true;
		}
		//Checking for edit all permission
		if (0 == $userPrivileges['profile_global_permission'][2] && (3 == $actionId || 4 == $actionId || 0 == $actionId || 1 == $actionId)) {
			static::$isPermittedLevel = 'SEC_MODULE_EDIT_ALL_PERMISSION';
			\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_EDIT_ALL_PERMISSION');
			return true;
		}
		//Checking and returning true if recorid is null
		if (empty($record)) {
			static::$isPermittedLevel = 'SEC_RECORD_ID_IS_NULL';
			\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_ID_IS_NULL');
			return true;
		}
		//If modules is Products,Vendors,Faq,PriceBook then no sharing
		if (1 === Module::getModuleOwner($tabId)) {
			static::$isPermittedLevel = 'SEC_MODULE_IS_OWNEDBY';
			\App\Log::trace('Exiting isPermitted method ... - SEC_MODULE_IS_OWNEDBY');
			return true;
		}

		$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($record);
		if (empty($recordMetaData)) {
			static::$isPermittedLevel = 'SEC_RECORD_DOES_NOT_EXIST';
			\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_DOES_NOT_EXIST');
			return false;
		}
		if (0 !== $recordMetaData['deleted'] && (1 === $actionId || 0 === $actionId || 17 === $actionId)) {
			switch ($recordMetaData['deleted']) {
				case 1:
					static::$isPermittedLevel = 'SEC_RECORD_DELETED';
					\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_DELETED');
					break;
				case 2:
					static::$isPermittedLevel = 'SEC_RECORD_ARCHIVED';
					\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_ARCHIVED');
					break;
				default:
					break;
			}
			return false;
		}
		if (\App\Config::security('PERMITTED_BY_PRIVATE_FIELD') && $recordMetaData['private']
			&& ($fieldInfo = \App\Field::getFieldInfo('private', $recordMetaData['setype'])) && \in_array($fieldInfo['presence'], [0, 2])) {
			$level = 'SEC_PRIVATE_RECORD_NO';
			$isPermittedPrivateRecord = false;
			$recOwnId = $recordMetaData['smownerid'];
			$recOwnType = \App\Fields\Owner::getType($recOwnId);
			if ('Users' === $recOwnType) {
				if ($userId === $recOwnId) {
					$level = 'SEC_PRIVATE_RECORD_OWNER_CURRENT_USER';
					$isPermittedPrivateRecord = true;
				}
			} elseif ('Groups' === $recOwnType) {
				if (\in_array($recOwnId, $userPrivileges['groups'])) {
					$level = 'SEC_PRIVATE_RECORD_OWNER_CURRENT_GROUP';
					$isPermittedPrivateRecord = true;
				}
			}
			if (!$isPermittedPrivateRecord && \App\Config::security('PERMITTED_BY_SHARED_OWNERS')) {
				$shownerIds = Fields\SharedOwner::getById($record);
				if (\in_array($userId, $shownerIds) || \count(array_intersect($shownerIds, $userPrivileges['groups'])) > 0) {
					$level = 'SEC_PRIVATE_RECORD_SHARED_OWNER';
					$isPermittedPrivateRecord = true;
				}
			}
			static::$isPermittedLevel = $level;
			\App\Log::trace('Exiting isPermitted method ... - ' . static::$isPermittedLevel);
			return $isPermittedPrivateRecord;
		}
		// Check advanced permissions
		if (\App\Config::security('PERMITTED_BY_ADVANCED_PERMISSION')) {
			$prvAdv = PrivilegeAdvanced::checkPermissions($record, $moduleName, $userId);
			if (false !== $prvAdv) {
				if (0 === $prvAdv) {
					static::$isPermittedLevel = 'SEC_ADVANCED_PERMISSION_NO';
					\App\Log::trace('Exiting isPermitted method ... - SEC_ADVANCED_PERMISSION_NO');
					return false;
				}
				static::$isPermittedLevel = 'SEC_ADVANCED_PERMISSION_YES';
				\App\Log::trace('Exiting isPermitted method ... - SEC_ADVANCED_PERMISSION_YES');
				return true;
			}
		}
		if (($modules = \App\Config::security('permittedModulesByCreatorField')) && \in_array($moduleName, $modules) && $userId === $recordMetaData['smcreatorid']) {
			if (3 == $actionId || 4 == $actionId) {
				static::$isPermittedLevel = 'SEC_RECORD_CREATOR_CURRENT_USER_READ_ACCESS';
				\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_CREATOR_CURRENT_USER_READ_ACCESS');
				return true;
			}
			if (\App\Config::security('permittedWriteAccessByCreatorField') && (0 == $actionId || 1 == $actionId)) {
				static::$isPermittedLevel = 'SEC_RECORD_CREATOR_CURRENT_USER_WRITE_ACCESS';
				\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_CREATOR_CURRENT_USER_WRITE_ACCESS');
				return true;
			}
		}
		if (\App\Config::security('PERMITTED_BY_SHARED_OWNERS')) {
			$shownerids = Fields\SharedOwner::getById($record);
			if (\in_array($userId, $shownerids) || \count(array_intersect($shownerids, $userPrivileges['groups'])) > 0) {
				static::$isPermittedLevel = 'SEC_RECORD_SHARED_OWNER';
				\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_SHARED_OWNER');
				return true;
			}
		}
		//Retreiving the RecordOwnerId
		$recOwnId = $recordMetaData['smownerid'];
		$recOwnType = Fields\Owner::getType($recOwnId);
		if ('Users' === $recOwnType) {
			//Checking if the Record Owner is the current User
			if ($userId === $recOwnId) {
				static::$isPermittedLevel = 'SEC_RECORD_OWNER_CURRENT_USER';
				\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_OWNER_CURRENT_USER');
				return true;
			}
			if (\App\Config::security('PERMITTED_BY_ROLES')) {
				//Checking if the Record Owner is the Subordinate User
				foreach ($userPrivileges['subordinate_roles_users'] as $usersByRole) {
					if (isset($usersByRole[$recOwnId])) {
						static::$isPermittedLevel = 'SEC_RECORD_OWNER_SUBORDINATE_USER';
						\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_OWNER_SUBORDINATE_USER');
						return true;
					}
				}
			}
		} elseif ('Groups' === $recOwnType) {
			//Checking if the record owner is the current user's group
			if (\in_array($recOwnId, $userPrivileges['groups'])) {
				static::$isPermittedLevel = 'SEC_RECORD_OWNER_CURRENT_GROUP';
				\App\Log::trace('Exiting isPermitted method ... - SEC_RECORD_OWNER_CURRENT_GROUP');
				return true;
			}
		}
		if (\App\Config::security('PERMITTED_BY_RECORD_HIERARCHY')) {
			$userPrivilegesModel = \Users_Privileges_Model::getInstanceById($userId);
			$role = $userPrivilegesModel->getRoleDetail();
			if (((3 == $actionId || 4 == $actionId) && 0 != $role->get('previewrelatedrecord')) || ((0 == $actionId || 1 == $actionId) && 0 != $role->get('editrelatedrecord'))) {
				$parentRecord = \Users_Privileges_Model::getParentRecord($record, $moduleName, $role->get('previewrelatedrecord'), $actionId);
				if ($parentRecord) {
					$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($parentRecord);
					$permissionsRoleForRelatedField = $role->get('permissionsrelatedfield');
					$permissionsRelatedField = '' === $permissionsRoleForRelatedField ? [] : explode(',', $role->get('permissionsrelatedfield'));
					$relatedPermission = false;
					foreach ($permissionsRelatedField as $row) {
						switch ($row) {
							case 0:
								$relatedPermission = $recordMetaData['smownerid'] === $userId || \in_array($recordMetaData['smownerid'], $userPrivileges['groups']);
								break;
							case 1:
								$relatedPermission = \in_array($userId, Fields\SharedOwner::getById($parentRecord));
								break;
							case 2:
								if (\App\Config::security('PERMITTED_BY_SHARING')) {
									$relatedPermission = static::isPermittedBySharing($recordMetaData['setype'], Module::getModuleId($recordMetaData['setype']), $actionId, $parentRecord, $userId);
								}
								break;
							case 3:
								$relatedPermission = static::isPermitted($recordMetaData['setype'], 'DetailView', $parentRecord);
								break;
							default:
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
		if (\App\Config::security('PERMITTED_BY_SHARING')) {
			$permission = static::isPermittedBySharing($moduleName, $tabId, $actionId, $record, $userId);
		}
		static::$isPermittedLevel = 'SEC_RECORD_BY_SHARING_' . ($permission ? 'YES' : 'NO');
		\App\Log::trace('Exiting isPermitted method ... - ' . static::$isPermittedLevel);

		return $permission;
	}

	public static function isPermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId)
	{
		$sharingPrivileges = \App\User::getSharingFile($userId);
		//Retreiving the default Organisation sharing Access
		$othersPermissionId = $sharingPrivileges['defOrgShare'][$tabId];
		//Checking for Default Org Sharing permission
		if (0 == $othersPermissionId) {
			if (1 === $actionId || 0 === $actionId) {
				return static::isReadWritePermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
			}
			return 2 !== $actionId;
		}
		if (1 == $othersPermissionId) {
			return 2 !== $actionId;
		}
		if (2 == $othersPermissionId) {
			return true;
		}
		if (3 == $othersPermissionId) {
			if (3 === $actionId || 4 === $actionId) {
				return static::isReadPermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
			}
			if (0 === $actionId || 1 === $actionId) {
				return static::isReadWritePermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
			}
			return 2 !== $actionId;
		}
		return true;
	}

	/** Function to check if the currently logged in user has Read Access due to Sharing for the specified record.
	 * @param $moduleName -- Module Name:: Type varchar
	 * @param $actionId   -- Action Id:: Type integer
	 * @param $recordId   -- Record Id:: Type integer
	 * @param $tabId      -- Tab Id:: Type integer
	 * @param mixed $userId
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
		if ('Users' == $ownerType) {
			//Checking the Read Sharing Permission Array in Role Users
			foreach ($read['ROLE'] as $userids) {
				if (\in_array($ownerId, $userids)) {
					\App\Log::trace('Exiting isReadPermittedBySharing method ...');

					return true;
				}
			}
			//Checking the Read Sharing Permission Array in Groups Users
			foreach ($read['GROUP'] as $userids) {
				if (\in_array($ownerId, $userids)) {
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
		$relatedModuleArray = null;
		if (isset($sharingPrivileges['relatedModuleShare'][$tabId])) {
			$relatedModuleArray = $sharingPrivileges['relatedModuleShare'][$tabId];
		}
		if (\is_array($relatedModuleArray)) {
			foreach ($relatedModuleArray as $parModId) {
				$parRecordOwner = PrivilegeUtil::getParentRecordOwner($tabId, $parModId, $recordId);
				if (\count($parRecordOwner) > 0) {
					$parModName = Module::getModuleName($parModId);
					if (isset($sharingPrivileges['permission'][$parModName . '_' . $moduleName])) {
						$readRelated = $sharingPrivileges['permission'][$parModName . '_' . $moduleName]['read'];

						$relOwnerType = '';
						$relOwnerId = '';
						foreach ($parRecordOwner as $rel_type => $rel_id) {
							$relOwnerType = $rel_type;
							$relOwnerId = $rel_id;
						}
						if ('Users' == $relOwnerType) {
							//Checking in Role Users
							foreach ($readRelated['ROLE'] as $userids) {
								if (\in_array($relOwnerId, $userids)) {
									\App\Log::trace('Exiting isReadPermittedBySharing method ...');

									return true;
								}
							}
							//Checking in Group Users
							foreach ($readRelated['GROUP'] as $userids) {
								if (\in_array($relOwnerId, $userids)) {
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

	/** Function to check if the currently logged in user has Write Access due to Sharing for the specified record.
	 * @param $moduleName -- Module Name:: Type varchar
	 * @param $actionId   -- Action Id:: Type integer
	 * @param $recordid   -- Record Id:: Type integer
	 * @param $tabId      -- Tab Id:: Type integer
	 * @param mixed $recordId
	 * @param mixed $userId
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
		if ('Users' == $ownerType) {
			//Checking the Write Sharing Permission Array in Role Users
			foreach ($write['ROLE'] as $userids) {
				if (\in_array($ownerId, $userids)) {
					\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');

					return true;
				}
			}
			//Checking the Write Sharing Permission Array in Groups Users
			foreach ($write['GROUP'] as $userids) {
				if (\in_array($ownerId, $userids)) {
					\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');

					return true;
				}
			}
		} elseif ('Groups' == $ownerType) {
			if (isset($write['GROUP'][$ownerId])) {
				\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');

				return true;
			}
		}
		//Checking for the Related Sharing Permission
		if (isset($sharingPrivileges['relatedModuleShare'][$tabId]) && \is_array($sharingPrivileges['relatedModuleShare'][$tabId])) {
			foreach ($sharingPrivileges['relatedModuleShare'][$tabId] as $parModId) {
				$parRecordOwner = PrivilegeUtil::getParentRecordOwner($tabId, $parModId, $recordId);
				if (!empty($parRecordOwner)) {
					$parModName = Module::getModuleName($parModId);
					if (isset($sharingPrivileges['permission'][$parModName . '_' . $moduleName])) {
						$writeRelated = $sharingPrivileges['permission'][$parModName . '_' . $moduleName]['write'];
						$relOwnerType = '';
						$relOwnerId = '';
						foreach ($parRecordOwner as $rel_type => $rel_id) {
							$relOwnerType = $rel_type;
							$relOwnerId = $rel_id;
						}
						if ('Users' == $relOwnerType) {
							//Checking in Role Users
							foreach ($writeRelated['ROLE'] as $userids) {
								if (\in_array($relOwnerId, $userids)) {
									\App\Log::trace('Exiting isReadWritePermittedBySharing method ...');

									return true;
								}
							}
							//Checking in Group Users
							foreach ($writeRelated['GROUP'] as $userids) {
								if (\in_array($relOwnerId, $userids)) {
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
	 *
	 * @param string $moduleName Module name
	 * @param int    $record     If type = 1 starting number if type = 0 record ID
	 * @param int    $priority
	 * @param int    $type
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
