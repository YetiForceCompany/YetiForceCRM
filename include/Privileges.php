<?php namespace includes;

/**
 * Privileges basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Privileges
{

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
		$log->debug("Entering isPermitted($moduleName,$actionName,$record,$userId) method ...");
		if (!$userId) {
			$currentUser = vglobal('current_user');
			$userId = $currentUser->id;
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
		$tabid = Modules::getModuleId($moduleName);
		$actionid = getActionid($actionName);
		$checkModule = $moduleName;

		if ($checkModule == 'Events') {
			$checkModule = 'Calendar';
		}

		if (\includes\Modules::isModuleActive($checkModule)) {
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
				if (\vtlib\Functions::getModuleOwner($moduleName) == 1) {
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
			$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($record);
			$recOwnId = $recordMetaData['smownerid'];
			$recOwnType = \includes\fields\Owner::getType($recOwnId);

			if (\AppConfig::security('PERMITTED_BY_SHARED_OWNERS')) {
				$shownerids = \Vtiger_SharedOwner_UIType::getSharedOwners($record, $moduleName);
				if (in_array($userId, $shownerids) || count(array_intersect($shownerids, $userPrivileges['groups'])) > 0) {
					self::$isPermittedLevel = 'SEC_RECORD_SHARED_OWNER';
					$log->debug('Exiting isPermitted method ... - Shared Owner');
					return true;
				}
			}
			if ($recOwnType == 'Users') {
				//Checking if the Record Owner is the current User
				if ($userId == $recOwnId) {
					self::$isPermittedLevel = 'SEC_RECORD_OWNER_CURRENT_USER';
					$log->debug('Exiting isPermitted method ...');
					return true;
				}
				if (\AppConfig::security('PERMITTED_BY_ROLES')) {
					//Checking if the Record Owner is the Subordinate User
					foreach ($userPrivileges['subordinate_roles_users'] as $roleid => $userids) {
						if (in_array($recOwnId, $userids)) {
							self::$isPermittedLevel = 'SEC_RECORD_OWNER_SUBORDINATE_USER';
							$log->debug('Exiting isPermitted method ...');
							return true;
						}
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
										$relatedPermission = self::isPermittedBySharing($recordMetaData['setype'], \includes\Modules::getModuleId($recordMetaData['setype']), $actionid, $parentRecord, $userId);
									}
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
			}
			if (\AppConfig::security('PERMITTED_BY_SHARING')) {
				$permission = self::isPermittedBySharing($moduleName, $tabid, $actionid, $record, $userId);
			}
			self::$isPermittedLevel = 'SEC_RECORD_BY_SHARING_' . $permission;
			$log->debug('Exiting isPermitted method ... - isPermittedBySharing');
		} else {
			$permission = false;
			self::$isPermittedLevel = 'SEC_MODULE_IS_INACTIVE';
		}

		$log->debug('Exiting isPermitted method ...');
		return $permission;
	}

	public static function isPermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId)
	{
		$sharingPrivileges = \Vtiger_Util_Helper::getUserSharingFile($userId);
		//Retreiving the default Organisation sharing Access
		$othersPermissionId = $sharingPrivileges['defOrgShare'][$tabId];
		//Checking for Default Org Sharing permission
		if ($othersPermissionId == 0) {
			if ($actionId == 1 || $actionId == 0) {
				return self::isReadWritePermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
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
				return self::isReadPermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
			} elseif ($actionId == 0 || $actionId == 1) {
				return self::isReadWritePermittedBySharing($moduleName, $tabId, $actionId, $recordId, $userId);
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
		$log = \LoggerManager::getInstance();
		$log->debug("Entering isReadPermittedBySharing($moduleName,$tabId,$actionId,$recordId,$userId) method ...");
		$sharingPrivileges = \Vtiger_Util_Helper::getUserSharingFile($userId);

		if (!isset($sharingPrivileges['permission'][$moduleName])) {
			return false;
		}
		$sharingPrivilegesModule = $sharingPrivileges['permission'][$moduleName];

		$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($recordId);
		$ownerId = $recordMetaData['smownerid'];
		$ownerType = \includes\fields\Owner::getType($ownerId);

		$read = $sharingPrivilegesModule['read'];
		if ($ownerType == 'Users') {
			//Checking the Read Sharing Permission Array in Role Users
			foreach ($read['ROLE'] as $userids) {
				if (in_array($ownerId, $userids)) {
					$log->debug('Exiting isReadPermittedBySharing method ...');
					return true;
				}
			}
			//Checking the Read Sharing Permission Array in Groups Users
			foreach ($read['GROUP'] as $userids) {
				if (in_array($ownerId, $userids)) {
					$log->debug('Exiting isReadPermittedBySharing method ...');
					return true;
				}
			}
		} else {
			if (isset($read['GROUP'][$ownerId])) {
				$log->debug('Exiting isReadPermittedBySharing method ...');
				return true;
			}
		}

		//Checking for the Related Sharing Permission
		$relatedModuleArray = $sharingPrivileges['relatedModuleShare'][$tabId];
		if (is_array($relatedModuleArray)) {
			foreach ($relatedModuleArray as $parModId) {
				$parRecordOwner = PrivilegesUtils::getParentRecordOwner($tabId, $parModId, $recordId);
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
									$log->debug('Exiting isReadPermittedBySharing method ...');
									return true;
								}
							}
							//Checking in Group Users
							foreach ($readRelated['GROUP'] as $userids) {
								if (in_array($relOwnerId, $userids)) {
									$log->debug('Exiting isReadPermittedBySharing method ...');
									return true;
								}
							}
						} else {
							if (isset($readRelated['GROUP'][$relOwnerId])) {
								$log->debug('Exiting isReadPermittedBySharing method ...');
								return true;
							}
						}
					}
				}
			}
		}
		$log->debug('Exiting isReadPermittedBySharing method ...');
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
		$log = \LoggerManager::getInstance();
		$log->debug("Entering isReadWritePermittedBySharing($moduleName,$tabId,$actionId,$recordId,$userId) method ...");
		$sharingPrivileges = \Vtiger_Util_Helper::getUserSharingFile($userId);
		if (!isset($sharingPrivileges['permission'][$moduleName])) {
			return false;
		}
		$sharingPrivilegesModule = $sharingPrivileges['permission'][$moduleName];

		$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($recordId);
		$ownerId = $recordMetaData['smownerid'];
		$ownerType = \includes\fields\Owner::getType($ownerId);

		$write = $sharingPrivilegesModule['write'];
		if ($ownerType == 'Users') {
			//Checking the Write Sharing Permission Array in Role Users
			foreach ($write['ROLE'] as $userids) {
				if (in_array($ownerId, $userids)) {
					$log->debug('Exiting isReadWritePermittedBySharing method ...');
					return true;
				}
			}
			//Checking the Write Sharing Permission Array in Groups Users
			foreach ($write['GROUP'] as $userids) {
				if (in_array($ownerId, $userids)) {
					$log->debug('Exiting isReadWritePermittedBySharing method ...');
					return true;
				}
			}
		} elseif ($ownerType == 'Groups') {
			if (isset($write['GROUP'][$ownerId])) {
				$log->debug('Exiting isReadWritePermittedBySharing method ...');
				return true;
			}
		}
		//Checking for the Related Sharing Permission
		$relatedModuleArray = $sharingPrivileges['relatedModuleShare'][$tabId];
		if (is_array($relatedModuleArray)) {
			foreach ($relatedModuleArray as $parModId) {
				$parRecordOwner = PrivilegesUtils::getParentRecordOwner($tabId, $parModId, $recordId);
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
									$log->debug('Exiting isReadWritePermittedBySharing method ...');
									return true;
								}
							}
							//Checking in Group Users
							foreach ($writeRelated['GROUP'] as $userids) {
								if (in_array($relOwnerId, $userids)) {
									$log->debug('Exiting isReadWritePermittedBySharing method ...');
									return true;
								}
							}
						} else {
							if (isset($writeRelated['GROUP'][$relOwnerId])) {
								$log->debug('Exiting isReadWritePermittedBySharing method ...');
								return true;
							}
						}
					}
				}
			}
		}
		$log->debug('Exiting isReadWritePermittedBySharing method ...');
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
		GlobalPrivileges::setUpdater($moduleName, $record, $priority, $type);
	}

	public static function setAllUpdater()
	{
		GlobalPrivileges::setAllUpdater();
	}
}
