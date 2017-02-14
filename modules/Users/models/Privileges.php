<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * User Privileges Model Class
 */
class Users_Privileges_Model extends Users_Record_Model
{

	/**
	 * Function to get the Display Name for the record
	 * @return string - Entity Display Name for the record
	 */
	public function getName()
	{
		$entityData = \App\Module::getEntityInfo('Users');
		$colums = [];
		foreach ($entityData['fieldnameArr'] as $fieldname) {
			$colums[] = $this->get($fieldname);
		}
		return implode(' ', $colums);
	}

	/**
	 * Function to get the Global Read Permission for the user
	 * @return <Number> 0/1
	 */
	protected function getGlobalReadPermission()
	{
		$profileGlobalPermissions = $this->get('profile_global_permission');
		return $profileGlobalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW];
	}

	/**
	 * Function to get the Global Write Permission for the user
	 * @return <Number> 0/1
	 */
	protected function getGlobalWritePermission()
	{
		$profileGlobalPermissions = $this->get('profile_global_permission');
		return $profileGlobalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT];
	}

	/**
	 * Function to check if the user has Global Read Permission
	 * @return boolean true/false
	 */
	public function hasGlobalReadPermission()
	{
		return ($this->isAdminUser() ||
			$this->getGlobalReadPermission() === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE ||
			$this->getGlobalWritePermission() === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE);
	}

	/**
	 * Function to check if the user has Global Write Permission
	 * @return boolean true/false
	 */
	public function hasGlobalWritePermission()
	{
		return ($this->isAdminUser() || $this->getGlobalWritePermission() === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE);
	}

	public function hasGlobalPermission($actionId)
	{
		if ($actionId == Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW) {
			return $this->hasGlobalReadPermission();
		}
		if ($actionId == Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT) {
			return $this->hasGlobalWritePermission();
		}
		return false;
	}

	/**
	 * Function to check whether the user has access to a given module by tabid
	 * @param <Number> $tabId
	 * @return boolean true/false
	 */
	public function hasModulePermission($mixed)
	{
		$profileTabsPermissions = $this->get('profile_tabs_permission');
		$moduleModel = Vtiger_Module_Model::getInstance($mixed);
		return !empty($moduleModel) && $moduleModel->isActive() && (($this->isAdminUser() || $profileTabsPermissions[$moduleModel->getId()] === 0));
	}

	/**
	 * Function to check whether the user has access to the specified action/operation on a given module by tabid
	 * @param <Number> $tabId
	 * @param <String/Number> $action
	 * @return boolean true/false
	 */
	public function hasModuleActionPermission($mixed, $action)
	{
		if (!is_a($action, 'Vtiger_Action_Model')) {
			$action = Vtiger_Action_Model::getInstance($action);
		}
		$actionId = $action->getId();
		$profileTabsPermissions = $this->get('profile_action_permission');
		$moduleModel = Vtiger_Module_Model::getInstance($mixed);
		return $moduleModel->isActive() && (($this->isAdminUser() || $profileTabsPermissions[$moduleModel->getId()][$actionId] === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE));
	}

	/**
	 * Static Function to get the instance of the User Privileges model from the given list of key-value array
	 * @param <Array> $valueMap
	 * @return Users_Privilege_Model object
	 */
	public static function getInstance($valueMap)
	{
		$instance = new self();
		foreach ($valueMap as $key => $value) {
			$instance->$key = $value;
		}
		$instance->setData($valueMap);
		return $instance;
	}

	protected static $userPrivilegesModelCache = [];

	/**
	 * Static Function to get the instance of the User Privileges model, given the User id
	 * @param <Number> $userId
	 * @return Users_Privilege_Model object
	 */
	public static function getInstanceById($userId, $module = null)
	{
		if (empty($userId))
			return null;

		if (isset(self::$userPrivilegesModelCache[$userId])) {
			return self::$userPrivilegesModelCache[$userId];
		}
		$valueMap = App\User::getPrivilegesFile($userId);
		if (is_array($valueMap['user_info'])) {
			$valueMap = array_merge($valueMap, $valueMap['user_info']);
		}
		$instance = self::getInstance($valueMap);
		self::$userPrivilegesModelCache[$userId] = $instance;
		return $instance;
	}

	/**
	 * Static function to get the User Privileges Model for the current user
	 * @return Users_Privilege_Model object
	 */
	public static function getCurrentUserPrivilegesModel()
	{
		return self::getInstanceById(App\User::getCurrentUserId());
	}

	/**
	 * Function to check permission for a Module/Action/Record
	 * @param string $moduleName
	 * @param string $actionName
	 * @param <Number> $record
	 * @return Boolean
	 */
	public static function isPermitted($moduleName, $actionName = null, $record = false)
	{
		return \App\Privilege::isPermitted($moduleName, $actionName, $record);
	}

	public static function getLastPermittedAccessLog()
	{
		return vglobal('isPermittedLog');
	}

	/**
	 * Function returns non admin access control check query
	 * @param string $module
	 * @return string
	 */
	public static function getNonAdminAccessControlQuery($module)
	{
		$currentUser = vglobal('current_user');
		return getNonAdminAccessControlQuery($module, $currentUser);
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
		vimport('~~modules/com_vtiger_workflow/include.php');
		vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.php');
		vimport('~~include/Webservices/Retrieve.php');
		$workflows = (new VTWorkflowManager(PearDatabase::getInstance()))->getWorkflowsForModule($moduleName, VTWorkflowManager::$BLOCK_EDIT);
		if (count($workflows)) {
			foreach ($workflows as &$workflow) {
				if ($workflow->evaluate($recordModel)) {
					$return = true;
				}
			}
		}
		self::$lockEditCache[$moduleName . $recordId] = $return;
		return $return;
	}

	public static function clearLockEditCache($cacheName = false)
	{
		if ($cacheName) {
			unset(self::$lockEditCache[$cacheName]);
		} else {
			self::$lockEditCache = [];
		}
	}

	/**
	 * Function to set Shared Owner
	 * @param int|array|string $userIds
	 * @param int $record
	 */
	public static function setSharedOwner($userIds, $record)
	{
		$saveFull = true;

		$db = \App\Db::getInstance();
		if (AppRequest::get('action') == 'SaveAjax' && AppRequest::has('field') && AppRequest::get('field') != 'shownerid') {
			$saveFull = false;
		}
		if ($saveFull) {
			$db->createCommand()->delete('u_#__crmentity_showners', ['crmid' => $record])->execute();
			if (empty($userIds)) {
				return false;
			}
			if (!is_array($userIds) && $userIds) {
				$userIds = explode(',', $userIds);
			}
			foreach ($userIds as $userId) {
				$db->createCommand()->insert('u_#__crmentity_showners', [
					'crmid' => $record,
					'userid' => $userId,
				])->execute();
			}
		}
	}

	public static function isPermittedByUserId($userId, $moduleName, $actionName = '', $record = false)
	{
		return \App\Privilege::isPermitted($moduleName, $actionName, $record, $userId);
	}

	/**
	 * Function to get set Shared Owner Recursively
	 */
	public static function getSharedRecordsRecursively($recordId, $moduleName)
	{

		\App\Log::trace('Entering Into getSharedRecordsRecursively( ' . $recordId . ', ' . $moduleName . ')');

		$db = PearDatabase::getInstance();
		$modulesSchema = [];
		$modulesSchema[$moduleName] = [];
		$modulesSchema['Accounts'] = [
			'Contacts' => ['key' => 'contactid', 'table' => 'vtiger_contactdetails', 'relfield' => 'parentid'],
			'Campaigns' => ['key' => 'campaignid', 'table' => 'vtiger_campaign_records', 'relfield' => 'crmid'],
			'Project' => ['key' => 'projectid', 'table' => 'vtiger_project', 'relfield' => 'linktoaccountscontacts'],
			'HelpDesk' => ['key' => 'ticketid', 'table' => 'vtiger_troubletickets', 'relfield' => 'parent_id']
		];
		$modulesSchema['Project'] = [
			'ProjectMilestone' => ['key' => 'projectmilestoneid', 'table' => 'vtiger_projectmilestone', 'relfield' => 'projectid'],
			'ProjectTask' => ['key' => 'projecttaskid', 'table' => 'vtiger_projecttask', 'relfield' => 'projectid']
		];
		$modulesSchema['HelpDesk'] = [
			'OSSTimeControl' => ['key' => 'osstimecontrolid', 'table' => 'vtiger_osstimecontrol', 'relfield' => 'link']
		];
		$sql = '';
		$params = [];
		$array = [];
		foreach ($modulesSchema[$moduleName] as $key => $module) {
			$sql .= " UNION SELECT " . $module['key'] . " AS id , '" . $key . "' AS module FROM " . $module['table'] . " WHERE " . $module['relfield'] . " = ?";
			$params[] = $recordId;
		}
		if ($sql != '' && $params) {
			$result = $db->pquery(substr($sql, 6), $params);
			while ($row = $db->getRow($result)) {
				$array = array_merge($array, self::getSharedRecordsRecursively($row['id'], $row['module']));
				$array[$row['module']][] = $row['id'];
			}
		}
		return $array;
		\App\Log::trace('Exiting getSharedRecordsRecursively()');
	}

	protected static $parentRecordCache = [];

	public static function getParentRecord($record, $moduleName = false, $type = 1, $actionid = false)
	{
		if (isset(self::$parentRecordCache[$record])) {
			return self::$parentRecordCache[$record];
		}
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$currentUserId = $userPrivilegesModel->getId();
		$currentUserGroups = $userPrivilegesModel->get('groups');
		if (!$moduleName) {
			$recordMetaData = vtlib\Functions::getCRMRecordMetadata($record);
			$moduleName = $recordMetaData['setype'];
		}
		if ($moduleName == 'Events') {
			$moduleName = 'Calendar';
		}

		$parentRecord = false;
		if ($parentModule = \App\ModuleHierarchy::getModulesMap1M($moduleName)) {
			$parentModuleModel = Vtiger_Module_Model::getInstance($moduleName);
			$parentModelFields = $parentModuleModel->getFields();

			foreach ($parentModelFields as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField() && count(array_intersect($parentModule, $fieldModel->getReferenceList())) > 0) {
					$recordModel = Vtiger_Record_Model::getInstanceById($record);
					$value = $recordModel->get($fieldName);
					if ($value != '' && $value != 0) {
						$parentRecord = $value;
						continue;
					}
				}
			}
			if ($parentRecord && $type == 2) {
				$rparentRecord = self::getParentRecord($parentRecord, false, $type, $actionid);
				if ($rparentRecord) {
					$parentRecord = $rparentRecord;
				}
			}
			$parentRecord = $record != $parentRecord ? $parentRecord : false;
		} else if (in_array($moduleName, \App\ModuleHierarchy::getModulesMapMMBase())) {
			$db = PearDatabase::getInstance();
			$role = $userPrivilegesModel->getRoleDetail();
			$result = $db->pquery('SELECT * FROM vtiger_crmentityrel WHERE crmid=? || relcrmid =?', [$record, $record]);
			while ($row = $db->getRow($result)) {
				$id = $row['crmid'] == $record ? $row['relcrmid'] : $row['crmid'];
				$recordMetaData = vtlib\Functions::getCRMRecordMetadata($id);
				$permissionsRoleForRelatedField = $role->get('permissionsrelatedfield');
				$permissionsRelatedField = $permissionsRoleForRelatedField == '' ? [] : explode(',', $role->get('permissionsrelatedfield'));
				$relatedPermission = false;
				foreach ($permissionsRelatedField as &$row) {
					if (!$relatedPermission) {
						switch ($row) {
							case 0:
								$relatedPermission = $recordMetaData['smownerid'] == $currentUserId || in_array($recordMetaData['smownerid'], $currentUserGroups);
								break;
							case 1:
								$relatedPermission = in_array($currentUserId, Vtiger_SharedOwner_UIType::getSharedOwners($id, $recordMetaData['setype']));
								break;
							case 2:
								$permission = isPermittedBySharing($recordMetaData['setype'], \App\Module::getModuleId($recordMetaData['setype']), $actionid, $id);
								$relatedPermission = $permission == 'yes' ? true : false;
								break;
						}
					}
				}
				if ($relatedPermission) {
					$parentRecord = $id;
					break;
				} else if ($type == 2) {
					$rparentRecord = self::getParentRecord($id, $recordMetaData['setype'], $type, $actionid);
					if ($rparentRecord) {
						$parentRecord = $rparentRecord;
					}
				}
			}
		} else if ($relationInfo = \App\ModuleHierarchy::getModulesMapMMCustom($moduleName)) {
			$db = PearDatabase::getInstance();
			$role = $userPrivilegesModel->getRoleDetail();
			$query = 'SELECT %s AS crmid FROM `%s` WHERE %s = ?';
			$query = sprintf($query, $relationInfo['rel'], $relationInfo['table'], $relationInfo['base']);
			$result = $db->pquery($query, [$record]);
			while ($row = $db->getRow($result)) {
				$id = $row['crmid'];
				$recordMetaData = vtlib\Functions::getCRMRecordMetadata($id);
				$permissionsRelatedField = $role->get('permissionsrelatedfield') == '' ? [] : explode(',', $role->get('permissionsrelatedfield'));
				$relatedPermission = false;
				foreach ($permissionsRelatedField as &$row) {
					if (!$relatedPermission) {
						switch ($row) {
							case 0:
								$relatedPermission = $recordMetaData['smownerid'] == $currentUserId || in_array($recordMetaData['smownerid'], $currentUserGroups);
								break;
							case 1:
								$relatedPermission = in_array($currentUserId, Vtiger_SharedOwner_UIType::getSharedOwners($id, $recordMetaData['setype']));
								break;
							case 2:
								$permission = isPermittedBySharing($recordMetaData['setype'], \App\Module::getModuleId($recordMetaData['setype']), $actionid, $id);
								$relatedPermission = $permission == 'yes' ? true : false;
								break;
						}
					}
				}
				if ($relatedPermission) {
					$parentRecord = $id;
					break;
				} else if ($type == 2) {
					$rparentRecord = self::getParentRecord($id, $recordMetaData['setype'], $type, $actionid);
					if ($rparentRecord) {
						$parentRecord = $rparentRecord;
					}
				}
			}
		}
		self::$parentRecordCache[$record] = $parentRecord;
		return $parentRecord;
	}

	/**
	 * Get profiles ids
	 * @return array
	 */
	public function getProfiles()
	{
		\App\Log::trace('Get profile list');
		return $this->get('profiles');
	}
}
