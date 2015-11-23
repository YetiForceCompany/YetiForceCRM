<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
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
		$entityData = Vtiger_Functions::getEntityModuleInfo('Users');
		$colums = [];
		foreach (explode(',', $entityData['fieldname']) as &$fieldname) {
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
	public function hasModulePermission($tabId)
	{
		$profileTabsPermissions = $this->get('profile_tabs_permission');
		$moduleModel = Vtiger_Module_Model::getInstance($tabId);
		return (($this->isAdminUser() || $profileTabsPermissions[$tabId] === 0) && $moduleModel->isActive());
	}

	/**
	 * Function to check whether the user has access to the specified action/operation on a given module by tabid
	 * @param <Number> $tabId
	 * @param <String/Number> $action
	 * @return <Boolean> true/false
	 */
	public function hasModuleActionPermission($tabId, $action)
	{
		if (!is_a($action, 'Vtiger_Action_Model')) {
			$action = Vtiger_Action_Model::getInstance($action);
		}
		$actionId = $action->getId();
		$profileTabsPermissions = $this->get('profile_action_permission');
		$moduleModel = Vtiger_Module_Model::getInstance($tabId);
		return (($this->isAdminUser() || $profileTabsPermissions[$tabId][$actionId] === Settings_Profiles_Module_Model::IS_PERMITTED_VALUE) && $moduleModel->isActive());
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

	/**
	 * Static Function to get the instance of the User Privileges model, given the User id
	 * @param <Number> $userId
	 * @return Users_Privilege_Model object
	 */
	public static function getInstanceById($userId, $module = null)
	{
		if (empty($userId))
			return null;

		require("user_privileges/user_privileges_$userId.php");
		require("user_privileges/sharing_privileges_$userId.php");

		$valueMap = array();
		$valueMap['id'] = $userId;
		$valueMap['is_admin'] = (bool) $is_admin;
		$valueMap['roleid'] = $current_user_roles;
		$valueMap['parent_role_seq'] = $current_user_parent_role_seq;
		$valueMap['profiles'] = $current_user_profiles;
		$valueMap['profile_global_permission'] = $profileGlobalPermission;
		$valueMap['profile_tabs_permission'] = $profileTabsPermission;
		$valueMap['profile_action_permission'] = $profileActionPermission;
		$valueMap['groups'] = $current_user_groups;
		$valueMap['subordinate_roles'] = $subordinate_roles;
		$valueMap['parent_roles'] = $parent_roles;
		$valueMap['subordinate_roles_users'] = $subordinate_roles_users;
		$valueMap['defaultOrgSharingPermission'] = $defaultOrgSharingPermission;
		$valueMap['related_module_share'] = $related_module_share;

		if (is_array($user_info)) {
			$valueMap = array_merge($valueMap, $user_info);
		}

		return self::getInstance($valueMap);
	}

	/**
	 * Static function to get the User Privileges Model for the current user
	 * @return Users_Privilege_Model object
	 */
	public static function getCurrentUserPrivilegesModel()
	{
		//TODO : Remove the global dependency
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
	public static function isPermitted($moduleName, $actionName, $record = false)
	{
		$permission = isPermitted($moduleName, $actionName, $record);
		if ($permission == 'yes') {
			return true;
		}
		return false;
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
		$currentUserId = $currentUserModel->getId();

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

	/**
	 * Function to set Shared Owner
	 */
	public static function setSharedOwner($userid, $record)
	{
		$db = PearDatabase::getInstance();
		$shownerid = '';
		if (!is_array($userid) && $userid) {
			$userid = [$userid];
		}
		if ($userid) {
			$shownerid = implode(',', $userid);
		}
		$db->pquery('UPDATE vtiger_crmentity SET shownerid=? WHERE crmid=?', [$shownerid, $record]);
	}

	/**
	 * Function to get Shared Owner from record
	 */
	public function getSharedOwner($record)
	{
		$log = vglobal('log');
		$log->info("Entering Into fn getSharedOwner($record)");
		$db = PearDatabase::getInstance();
		$shownerid = Vtiger_Cache::get('SharedOwner', $record);
		if (!$shownerid) {
			$result = $db->pquery('SELECT shownerid FROM vtiger_crmentity WHERE crmid = ?', [$record]);
			$shownerid = $db->getSingleValue($result);
			Vtiger_Cache::set('SharedOwner', $record, $shownerid);
		}
		$log->info("Exiting fn getSharedOwner()");
		return Vtiger_Functions::getArrayFromValue($shownerid);
	}

	/**
	 * Function to get set Shared Owner Recursively
	 */
	public static function setSharedOwnerRecursively($recordId, $addUser, $removeUser, $moduleName)
	{
		$log = vglobal('log');
		$db = PearDatabase::getInstance();
		$log->info("Entering Into fn setSharedOwnerRecursively( $recordId , $addUser, $removeUser, $moduleName )");
		$records = self::getSharedRecordsRecursively($recordId, $moduleName);
		$sqlRecords = implode("','", $records);
		$result = $db->pquery("SELECT crmid, shownerid FROM vtiger_crmentity WHERE crmid in ('$sqlRecords');");
		$sqlUpdate = '';
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$crmid = $db->query_result_raw($result, $i, 'crmid');
			$shownerid = $db->query_result_raw($result, $i, 'shownerid');
			$shownerIdArray = Vtiger_Functions::getArrayFromValue($shownerid);
			foreach ($shownerIdArray as $key => $user) {
				if (in_array($user, $removeUser)) {
					unset($shownerIdArray[$key]);
				}
			}
			$newArray = array_merge($shownerIdArray, $addUser);
			$db->query("UPDATE vtiger_crmentity SET shownerid='" . implode(",", $newArray) . "' WHERE crmid='$crmid';");
		}
		$log->info("Exiting fn setSharedOwnerRecursively()");
	}

	/**
	 * Function to get set Shared Owner Recursively
	 */
	public static function getSharedRecordsRecursively($recordId, $moduleName)
	{
		$log = vglobal('log');
		$log->info("Entering Into fn getSharedRecordsRecursively( $recordId, $moduleName )");
		$db = PearDatabase::getInstance();
		$modulesSchema = [];
		$modulesSchema[$moduleName] = [];
		$modulesSchema['Accounts'] = array(
			'Contacts' => array('key' => 'contactid', 'table' => 'vtiger_contactdetails', 'relfield' => 'parentid'),
			'Potentials' => array('key' => 'potentialid', 'table' => 'vtiger_potential', 'relfield' => 'related_to'),
			'Campaigns' => array('key' => 'campaignid', 'table' => 'vtiger_campaignaccountrel', 'relfield' => 'accountid'),
			'Project' => array('key' => 'projectid', 'table' => 'vtiger_project', 'relfield' => 'linktoaccountscontacts'),
			'HelpDesk' => array('key' => 'ticketid', 'table' => 'vtiger_troubletickets', 'relfield' => 'parent_id'),
		);
		$modulesSchema['Project'] = array(
			'ProjectMilestone' => array('key' => 'projectmilestoneid', 'table' => 'vtiger_projectmilestone', 'relfield' => 'projectid'),
			'ProjectTask' => array('key' => 'projecttaskid', 'table' => 'vtiger_projecttask', 'relfield' => 'projectid'),
		);
		$modulesSchema['Potentials'] = array(
			'Quotes' => array('key' => 'quoteid', 'table' => 'vtiger_quotes', 'relfield' => 'potentialid'),
			'SalesOrder' => array('key' => 'salesorderid', 'table' => 'vtiger_salesorder', 'relfield' => 'potentialid'),
			'Invoice' => array('key' => 'invoiceid', 'table' => 'vtiger_invoice', 'relfield' => 'potentialid'),
			'Calculations' => array('key' => 'calculationsid', 'table' => 'vtiger_calculations', 'relfield' => 'potentialid'),
		);
		$modulesSchema['HelpDesk'] = array(
			'OSSTimeControl' => array('key' => 'osstimecontrolid', 'table' => 'vtiger_osstimecontrol', 'relfield' => 'ticketid'),
		);
		$sql = '';
		$params = array();
		$array = array();
		foreach ($modulesSchema[$moduleName] as $key => $module) {
			$sql .= " UNION SELECT " . $module['key'] . " AS id , '" . $key . "' AS module FROM " . $module['table'] . " WHERE " . $module['relfield'] . " = ?";
			$params[] = $recordId;
		}
		if ($sql != '' && $params) {
			$result = $db->pquery(substr($sql, 6), $params);
			for ($i = 0; $i < $db->num_rows($result); $i++) {
				$array = array_merge($array, self::getSharedRecordsRecursively($db->query_result_raw($result, $i, 'id'), $db->query_result_raw($result, $i, 'module')));
				$array[] = $db->query_result_raw($result, $i, 'id');
			}
		}
		return $array;
		$log->info("Exiting fn getSharedRecordsRecursively()");
	}

	protected static $parentRecordCache = [];

	public function getParentRecord($record, $moduleName = false, $type = 1)
	{
		if (isset(self::$parentRecordCache[$record])) {
			return self::$parentRecordCache[$record];
		}
		if (!$moduleName) {
			$recordMetaData = Vtiger_Functions::getCRMRecordMetadata($record);
			$moduleName = $recordMetaData['setype'];
		}
		if ($moduleName == 'Events') {
			$moduleName = 'Calendar';
		}

		$parentRecord = false;
		include('user_privileges/moduleHierarchy.php');
		if (key_exists($moduleName, $modulesMap1M)) {
			$parentModule = $modulesMap1M[$moduleName];
			$parentModuleModel = Vtiger_Module_Model::getInstance($moduleName);
			$parentModelFields = $parentModuleModel->getFields();

			foreach ($parentModelFields as $fieldName => $fieldModel) {
				if ($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE && count(array_intersect($parentModule, $fieldModel->getReferenceList())) > 0) {
					$recordModel = Vtiger_Record_Model::getInstanceById($record);
					$value = $recordModel->get($fieldName);
					if ($value != '' && $value != 0) {
						$parentRecord = $value;
						continue;
					}
				}
			}
			if ($parentRecord && $type == 2) {
				$rparentRecord = self::getParentRecord($parentRecord, false, $type);
				if ($rparentRecord) {
					$parentRecord = $rparentRecord;
				}
			}
			return $record != $parentRecord ? $parentRecord : false;
		} else if (in_array($moduleName, $modulesMapMMBase)) {
			$currentUser = vglobal('current_user');
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT * FROM vtiger_crmentityrel WHERE crmid=? OR relcrmid =?', [$record, $record]);
			while ($row = $db->fetch_array($result)) {
				$id = $row['crmid'] == $record ? $row['relcrmid'] : $row['crmid'];
				$recordMetaData = Vtiger_Functions::getCRMRecordMetadata($id);
				if ($currentUser->id == $recordMetaData['smownerid']) {
					$parentRecord = $id;
					break;
				} else if ($type == 2) {
					$rparentRecord = self::getParentRecord($id, $recordMetaData['setype'], $type);
					if ($rparentRecord) {
						$parentRecord = $rparentRecord;
					}
				}
			}
		} else if (key_exists($moduleName, $modulesMapMMCustom)) {
			$currentUser = vglobal('current_user');
			$relationInfo = $modulesMapMMCustom[$moduleName];
			$db = PearDatabase::getInstance();
			$query = 'SELECT ' . $relationInfo['rel'] . ' AS crmid FROM `' . $relationInfo['table'] . '` WHERE ' . $relationInfo['base'] . ' = ?';
			$result = $db->pquery($query, [$record]);
			while ($row = $db->fetch_array($result)) {
				$recordMetaData = Vtiger_Functions::getCRMRecordMetadata($row['crmid']);
				if ($currentUser->id == $recordMetaData['smownerid']) {
					$parentRecord = $row['crmid'];
					break;
				} else if ($type == 2) {
					$rparentRecord = self::getParentRecord($row['crmid'], $recordMetaData['setype'], $type);
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
