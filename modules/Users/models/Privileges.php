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
	 * @return <String> - Entity Display Name for the record
	 */
	public function getName()
	{
		$entityData = \includes\Modules::getEntityInfo('Users');
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
	 * @return <Boolean> true/false
	 */
	public function hasGlobalReadPermission()
	{
		return ($this->isAdminUser() ||
			$this->getGlobalReadPermission() === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE ||
			$this->getGlobalWritePermission() === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE);
	}

	/**
	 * Function to check if the user has Global Write Permission
	 * @return <Boolean> true/false
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
	 * @return <Boolean> true/false
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
	 * @return <Boolean> true/false
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
		$valueMap = Vtiger_Util_Helper::getUserPrivilegesFile($userId);
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
		$currentUser = vglobal('current_user');
		$currentUserId = $currentUser->id;
		return self::getInstanceById($currentUserId);
	}

	/**
	 * Function to check permission for a Module/Action/Record
	 * @param <String> $moduleName
	 * @param <String> $actionName
	 * @param <Number> $record
	 * @return Boolean
	 */
	public static function isPermitted($moduleName, $actionName = null, $record = false)
	{
		return \includes\Privileges::isPermitted($moduleName, $actionName, $record);
	}

	public static function getLastPermittedAccessLog()
	{
		return vglobal('isPermittedLog');
	}

	/**
	 * Function returns non admin access control check query
	 * @param <String> $module
	 * @return <String>
	 */
	public static function getNonAdminAccessControlQuery($module)
	{
		$currentUser = vglobal('current_user');
		return getNonAdminAccessControlQuery($module, $currentUser);
	}

	protected static $lockEditCache = [];

	public static function checkLockEdit($moduleName, $record)
	{
		if (isset(self::$lockEditCache[$moduleName . $record])) {
			return self::$lockEditCache[$moduleName . $record];
		}
		$return = false;
		if (empty($record)) {
			self::$lockEditCache[$moduleName . $record] = $return;
			return $return;
		}
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		vimport('~~modules/com_vtiger_workflow/include.inc');
		vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.inc');
		vimport('~~modules/com_vtiger_workflow/VTEntityCache.inc');
		vimport('~~include/Webservices/Retrieve.php');
		$wfs = new VTWorkflowManager(PearDatabase::getInstance());
		$workflows = $wfs->getWorkflowsForModule($moduleName, VTWorkflowManager::$BLOCK_EDIT);
		if (count($workflows)) {
			$wsId = vtws_getWebserviceEntityId($moduleName, $record);
			$entityCache = new VTEntityCache($currentUserModel);
			$entityData = $entityCache->forId($wsId);
			foreach ($workflows as $id => $workflow) {
				if ($workflow->evaluate($entityCache, $entityData->getId())) {
					$return = true;
				}
			}
		}
		self::$lockEditCache[$moduleName . $record] = $return;
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
	 */
	public static function setSharedOwner(Vtiger_Record_Model $recordModel)
	{
		$saveFull = true;

		$db = PearDatabase::getInstance();
		$userIds = $recordModel->get('shownerid');
		$record = $recordModel->getId();
		if (AppRequest::get('action') == 'SaveAjax' && AppRequest::has('field') && AppRequest::get('field') != 'shownerid') {
			$saveFull = false;
		}
		if ($saveFull) {
			$db->delete('u_yf_crmentity_showners', 'crmid = ?', [$record]);
			if (empty($userIds)) {
				return false;
			}
			if (!is_array($userIds) && $userIds) {
				$userIds = [$userIds];
			}
			foreach ($userIds as $userId) {
				$db->insert('u_yf_crmentity_showners', [
					'crmid' => $record,
					'userid' => $userId,
				]);
			}
		}
	}

	/**
	 * Function to get set Shared Owner Recursively
	 */
	public static function setSharedOwnerRecursively($recordId, $addUser, $removeUser, $moduleName)
	{
		$log = vglobal('log');
		$db = PearDatabase::getInstance();
		$log->info('Entering Into setSharedOwnerRecursively( ' . $recordId . ', ' . $moduleName . ')');

		$recordsByModule = self::getSharedRecordsRecursively($recordId, $moduleName);
		if (count($recordsByModule) === 0) {
			$log->info('Exiting setSharedOwnerRecursively() - No shared records');
			return false;
		}
		$removeUserString = $addUserString = false;
		if (count($removeUser) > 0) {
			$removeUserString = implode(',', $removeUser);
		}
		if (count($addUser) > 0) {
			$addUserString = implode(',', $addUser);
		}
		foreach ($recordsByModule as $parentModuleName => &$records) {
			$sqlRecords = implode(',', $records);

			if ($removeUserString !== false) {
				$db->delete('u_yf_crmentity_showners', 'userid IN(' . $removeUserString . ') && crmid IN (' . $sqlRecords . ')');
			}

			if ($addUserString !== false) {
				$usersExist = [];
				$query = 'SELECT crmid, userid FROM u_yf_crmentity_showners WHERE userid IN(%s) && crmid IN (%s)';
				$query = sprintf($query, $addUserString, $sqlRecords);
				$result = $db->query($query);
				while ($row = $db->getRow($result)) {
					$usersExist[$row['crmid']][$row['userid']] = true;
				}
				foreach ($records as &$record) {
					foreach ($addUser as $userId) {
						if (!isset($usersExist[$record][$userId])) {
							$db->insert('u_yf_crmentity_showners', [
								'crmid' => $record,
								'userid' => $userId,
							]);
						}
					}
				}
			}
		}
		$log->info('Exiting setSharedOwnerRecursively()');
	}

	public static function isPermittedByUserId($userId, $moduleName, $actionName = '', $record = false)
	{
		return \includes\Privileges::isPermitted($moduleName, $actionName, $record, $userId);
	}

	/**
	 * Function to get set Shared Owner Recursively
	 */
	public static function getSharedRecordsRecursively($recordId, $moduleName)
	{
		$log = vglobal('log');
		$log->info('Entering Into getSharedRecordsRecursively( ' . $recordId . ', ' . $moduleName . ')');

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
		$log->info('Exiting getSharedRecordsRecursively()');
	}

	protected static $parentRecordCache = [];

	public function getParentRecord($record, $moduleName = false, $type = 1, $actionid = false)
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
		if ($parentModule = Vtiger_ModulesHierarchy_Model::getModulesMap1M($moduleName)) {
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
		} else if (in_array($moduleName, Vtiger_ModulesHierarchy_Model::getModulesMapMMBase())) {
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
								$permission = isPermittedBySharing($recordMetaData['setype'], \includes\Modules::getModuleId($recordMetaData['setype']), $actionid, $id);
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
		} else if ($relationInfo = Vtiger_ModulesHierarchy_Model::getModulesMapMMCustom($moduleName)) {
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
								$permission = isPermittedBySharing($recordMetaData['setype'], \includes\Modules::getModuleId($recordMetaData['setype']), $actionid, $id);
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
}
