<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * User Privileges Model Class.
 */
class Users_Privileges_Model extends Users_Record_Model
{
	/**
	 * Function to get the Display Name for the record.
	 *
	 * @return string - Entity Display Name for the record
	 */
	public function getName(): string
	{
		if (!isset($this->label)) {
			$entityData = \App\Module::getEntityInfo('Users');
			$separator = $entityData['separator'] ?? ' ';
			$labelName = [];
			foreach ($entityData['fieldnameArr'] as $columnName) {
				$fieldModel = $this->getModule()->getFieldByColumn($columnName);
				$labelName[] = $fieldModel->getDisplayValue($this->get($fieldModel->getName()), $this->getId(), $this, true);
			}
			$this->label = \App\Purifier::encodeHtml(implode($separator, $labelName));
		}
		return $this->label;
	}

	/**
	 * Function to get the Global Read Permission for the user.
	 *
	 * @return <Number> 0/1
	 */
	protected function getGlobalReadPermission()
	{
		$profileGlobalPermissions = $this->get('profile_global_permission');

		return $profileGlobalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW];
	}

	/**
	 * Function to get the Global Write Permission for the user.
	 *
	 * @return <Number> 0/1
	 */
	protected function getGlobalWritePermission()
	{
		$profileGlobalPermissions = $this->get('profile_global_permission');

		return $profileGlobalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT];
	}

	/**
	 * Function to check if the user has Global Read Permission.
	 *
	 * @return bool true/false
	 */
	public function hasGlobalReadPermission()
	{
		return $this->isAdminUser()
			|| Settings_Profiles_Module_Model::IS_PERMITTED_VALUE === $this->getGlobalReadPermission()
			|| Settings_Profiles_Module_Model::IS_PERMITTED_VALUE === $this->getGlobalWritePermission();
	}

	/**
	 * Function to check if the user has Global Write Permission.
	 *
	 * @return bool true/false
	 */
	public function hasGlobalWritePermission()
	{
		return $this->isAdminUser() || Settings_Profiles_Module_Model::IS_PERMITTED_VALUE === $this->getGlobalWritePermission();
	}

	public function hasGlobalPermission($actionId)
	{
		if (Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW == $actionId) {
			return $this->hasGlobalReadPermission();
		}
		if (Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT == $actionId) {
			return $this->hasGlobalWritePermission();
		}
		return false;
	}

	/**
	 * Function to check whether the user has access to a given module by tabid.
	 *
	 * @param int $mixed
	 *
	 * @return bool true/false
	 */
	public function hasModulePermission($mixed)
	{
		$profileTabsPermissions = $this->get('profile_tabs_permission');
		$moduleModel = Vtiger_Module_Model::getInstance($mixed);
		return !empty($moduleModel) && $moduleModel->isActive() && ($this->isAdminUser() || (isset($profileTabsPermissions[$moduleModel->getId()]) && 0 === $profileTabsPermissions[$moduleModel->getId()]));
	}

	/**
	 * Function to check whether the user has access to the specified action/operation on a given module by tabid.
	 *
	 * @param <Number>        $tabId
	 * @param <String/Number> $action
	 * @param mixed           $mixed
	 *
	 * @return bool true/false
	 */
	public function hasModuleActionPermission($mixed, $action)
	{
		if (!is_a($action, 'Vtiger_Action_Model')) {
			$action = Vtiger_Action_Model::getInstance($action);
		}
		$actionId = $action->getId();
		$profileTabsPermissions = $this->get('profile_action_permission');
		if ((is_numeric($mixed) && 3 === $mixed) || 'Home' === $mixed) {
			$mixed = 1;
		}
		$moduleModel = Vtiger_Module_Model::getInstance($mixed);
		return $moduleModel->isActive() && $this->hasModulePermission($mixed) && (($this->isAdminUser() || (isset($profileTabsPermissions[$moduleModel->getId()][$actionId]) && Settings_Profiles_Module_Model::IS_PERMITTED_VALUE === $profileTabsPermissions[$moduleModel->getId()][$actionId])));
	}

	/**
	 * Static Function to get the instance of the User Privileges model from the given list of key-value array.
	 *
	 * @param <Array> $valueMap
	 *
	 * @return \Users_Privileges_Model object
	 */
	public static function getInstance($valueMap)
	{
		$instance = new self();
		foreach ($valueMap as $key => $value) {
			$instance->{$key} = $value;
		}
		$instance->setData($valueMap);
		$instance->setModule('Users');
		return $instance;
	}

	protected static $userPrivilegesModelCache = [];

	/**
	 * Static Function to get the instance of the User Privileges model, given the User id.
	 *
	 * @param <Number>   $userId
	 * @param mixed|null $module
	 *
	 * @return \Users_Privileges_Model object
	 */
	public static function getInstanceById($userId, $module = null)
	{
		if (empty($userId)) {
			return null;
		}
		if (isset(self::$userPrivilegesModelCache[$userId])) {
			return self::$userPrivilegesModelCache[$userId];
		}
		$valueMap = App\User::getPrivilegesFile($userId);
		if (\is_array($valueMap['user_info'])) {
			$valueMap = array_merge($valueMap, $valueMap['user_info']);
		}
		$instance = self::getInstance($valueMap);
		$instance->setId($userId);
		self::$userPrivilegesModelCache[$userId] = $instance;
		return $instance;
	}

	/**
	 * Static function to get the User Privileges Model for the current user.
	 *
	 * @return \Users_Privileges_Model object
	 */
	public static function getCurrentUserPrivilegesModel()
	{
		return self::getInstanceById(App\User::getCurrentUserId());
	}

	protected static $lockEditCache = [];

	public static function checkLockEdit($moduleName, Vtiger_Record_Model $recordModel)
	{
		$recordId = $recordModel->getId();
		if (isset(self::$lockEditCache[$moduleName . $recordId])) {
			return self::$lockEditCache[$moduleName . $recordId];
		}
		$return = false;
		if (empty($recordId)) {
			self::$lockEditCache[$moduleName . $recordId] = $return;
			return $return;
		}
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTEntityMethodManager.php');
		$workflows = (new VTWorkflowManager())->getWorkflowsForModule($moduleName, VTWorkflowManager::$BLOCK_EDIT);
		if (\count($workflows)) {
			foreach ($workflows as &$workflow) {
				if ($workflow->evaluate($recordModel)) {
					$return = true;
				}
			}
		}
		self::$lockEditCache[$moduleName . $recordId] = $return;
		return $return;
	}

	/**
	 * Clear LockEdit Cache.
	 *
	 * @param string $cacheName
	 */
	public static function clearLockEditCache(string $cacheName = '')
	{
		if ($cacheName && isset(self::$lockEditCache[$cacheName])) {
			unset(self::$lockEditCache[$cacheName]);
		} elseif (!$cacheName) {
			self::$lockEditCache = [];
		}
	}

	/**
	 * Clear user cache.
	 *
	 * @param int|bool $userId
	 */
	public static function clearCache($userId = false)
	{
		self::$lockEditCache = [];
		if ($userId) {
			unset(self::$userPrivilegesModelCache[$userId]);
		} else {
			self::$userPrivilegesModelCache = [];
		}
	}

	/**
	 * Function to set Shared Owner.
	 *
	 * @param int|array|string $userIds
	 * @param int              $record
	 */
	public static function setSharedOwner($userIds, $record)
	{
		$saveFull = true;
		$db = \App\Db::getInstance();
		if ('SaveAjax' == \App\Request::_get('action') && \App\Request::_has('field') && 'shownerid' != \App\Request::_get('field')) {
			$saveFull = false;
		}
		if ($saveFull) {
			$db->createCommand()->delete('u_#__crmentity_showners', ['crmid' => $record])->execute();
			if (empty($userIds)) {
				return false;
			}
			if (!\is_array($userIds) && $userIds) {
				$userIds = explode(',', $userIds);
			}
			foreach (array_unique($userIds) as $userId) {
				$db->createCommand()->insert('u_#__crmentity_showners', [
					'crmid' => $record,
					'userid' => $userId,
				])->execute();
			}
			\App\Cache::delete('SharedOwnerFieldValue', $record);
		}
	}

	public static function isPermittedByUserId($userId, $moduleName, $actionName = '', $record = false)
	{
		return \App\Privilege::isPermitted($moduleName, $actionName, $record, $userId);
	}

	/**
	 * Get parent record id.
	 *
	 * @param int         $record
	 * @param string|bool $moduleName
	 * @param int         $type
	 * @param type        $actionid
	 *
	 * @return int|bool
	 */
	public static function getParentRecord($record, $moduleName = false, $type = 1, $actionid = false)
	{
		$cacheKey = "$record,$moduleName,$type,$actionid";
		if (\App\Cache::staticHas('PrivilegesParentRecord', $cacheKey)) {
			return \App\Cache::staticGet('PrivilegesParentRecord', $cacheKey);
		}
		$userModel = App\User::getCurrentUserModel();
		$currentUserId = $userModel->getId();
		$currentUserGroups = (array) $userModel->get('groups');
		if (!$moduleName) {
			$recordMetaData = vtlib\Functions::getCRMRecordMetadata($record);
			$moduleName = $recordMetaData['setype'];
		}
		$parentRecord = false;
		if ($parentModule = \App\ModuleHierarchy::getModulesMap1M($moduleName)) {
			$parentModuleModel = Vtiger_Module_Model::getInstance($moduleName);
			$parentModelFields = $parentModuleModel->getFields();

			foreach ($parentModelFields as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField() && \count(array_intersect($parentModule, $fieldModel->getReferenceList())) > 0) {
					$recordModel = Vtiger_Record_Model::getInstanceById($record);
					$value = $recordModel->get($fieldName);
					if (!empty($value) && \App\Record::isExists($value)) {
						$parentRecord = $value;
					}
				}
			}
			if ($parentRecord && 2 == $type) {
				$rparentRecord = self::getParentRecord($parentRecord, false, $type, $actionid);
				if ($rparentRecord) {
					$parentRecord = $rparentRecord;
				}
			}
			$parentRecord = $record != $parentRecord ? $parentRecord : false;
		} elseif (\in_array($moduleName, \App\ModuleHierarchy::getModulesMapMMBase())) {
			$role = $userModel->getRoleInstance();
			$dataReader = (new \App\Db\Query())->select(['relcrmid', 'crmid'])
				->from('vtiger_crmentityrel')
				->where(['or', ['crmid' => $record], ['relcrmid' => $record]])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$id = $row['crmid'] == $record ? $row['relcrmid'] : $row['crmid'];
				$recordMetaData = vtlib\Functions::getCRMRecordMetadata($id);
				$permissionsRoleForRelatedField = $role->get('permissionsrelatedfield');
				$permissionsRelatedField = '' == $permissionsRoleForRelatedField ? [] : explode(',', $role->get('permissionsrelatedfield'));
				$relatedPermission = false;
				foreach ($permissionsRelatedField as $row) {
					if (!$relatedPermission) {
						switch ($row) {
							case 0:
								$relatedPermission = $recordMetaData['smownerid'] == $currentUserId || \in_array($recordMetaData['smownerid'], $currentUserGroups);
								break;
							case 1:
								$relatedPermission = \in_array($currentUserId, \App\Fields\SharedOwner::getById($id));
								break;
							case 2:
								$relatedPermission = \App\Privilege::isPermittedBySharing($recordMetaData['setype'], \App\Module::getModuleId($recordMetaData['setype']), $actionid, $id, $currentUserId);
								break;
							case 3:
								$relatedPermission = \App\Privilege::isPermitted($recordMetaData['setype'], 'DetailView', $id);
								break;
							default:
								break;
						}
					}
				}
				if ($relatedPermission) {
					$parentRecord = $id;
					break;
				}
				if (2 == $type) {
					$rparentRecord = self::getParentRecord($id, $recordMetaData['setype'], $type, $actionid);
					if ($rparentRecord) {
						$parentRecord = $rparentRecord;
					}
				}
			}
			$dataReader->close();
		} elseif ($relationInfo = \App\ModuleHierarchy::getModulesMapMMCustom($moduleName)) {
			$role = $userModel->getRoleInstance();
			$dataReader = (new \App\Db\Query())->select(['crmid' => $relationInfo['rel']])->from($relationInfo['table'])
				->where([$relationInfo['base'] => $record])
				->createCommand()->query();
			while ($id = $dataReader->readColumn(0)) {
				$recordMetaData = vtlib\Functions::getCRMRecordMetadata($id);
				$permissionsRelatedField = '' == $role->get('permissionsrelatedfield') ? [] : explode(',', $role->get('permissionsrelatedfield'));
				$relatedPermission = false;
				foreach ($permissionsRelatedField as $row) {
					if (!$relatedPermission) {
						switch ($row) {
							case 0:
								$relatedPermission = $recordMetaData['smownerid'] == $currentUserId || \in_array($recordMetaData['smownerid'], $currentUserGroups);
								break;
							case 1:
								$relatedPermission = \in_array($currentUserId, \App\Fields\SharedOwner::getById($id));
								break;
							case 2:
								$relatedPermission = \App\Privilege::isPermittedBySharing($recordMetaData['setype'], \App\Module::getModuleId($recordMetaData['setype']), $actionid, $id, $currentUserId);
								break;
							case 3:
								$relatedPermission = \App\Privilege::isPermitted($recordMetaData['setype'], 'DetailView', $id);
								break;
							default:
								break;
						}
					}
				}
				if ($relatedPermission) {
					$parentRecord = $id;
					break;
				}
				if (2 == $type) {
					$rparentRecord = self::getParentRecord($id, $recordMetaData['setype'], $type, $actionid);
					if ($rparentRecord) {
						$parentRecord = $rparentRecord;
					}
				}
			}
			$dataReader->close();
		}
		\App\Cache::staticSave('PrivilegesParentRecord', $cacheKey, $parentRecord);

		return $parentRecord;
	}

	/**
	 * Get profiles ids.
	 *
	 * @return array
	 */
	public function getProfiles()
	{
		\App\Log::trace('Get profile list');

		return $this->get('profiles');
	}
}
