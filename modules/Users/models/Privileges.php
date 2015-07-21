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
	public static function getInstanceById($userId)
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

	function CheckPermissionsToEditView($moduleName, $record)
	{
		$log = vglobal('log');
		$log->info("Entering Into fn CheckPermissionsToEditView($moduleName, $record)");
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currentUserId = $currentUserModel->getId();
		$recordPermission = true;
		if ($record == '' || $currentUserModel->isAdminUser()) {
			return true;
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$PermissionsHandlers = Settings_DataAccess_Module_Model::executePermissionsHandlers($moduleName, $record, $recordModel);
		$log->info("Exiting fn CheckPermissionsToEditView()");
		return $PermissionsHandlers['success'];
	}

	/**
	 * Function to set Shared Owner
	 */
	public function setSharedOwner($userid, $record)
	{
		$db = PearDatabase::getInstance();
		$shownerid = '';
		if (!is_array($userid) && $userid) {
			$userid = [$userid];
		}
		if ($userid) {
			foreach ($userid as $key => $user) {
				$shownerid .= $user . ',';
			}
			$shownerid = rtrim($shownerid, ',');
		} else {
			$shownerid = NULL;
		}
		$db->pquery('UPDATE vtiger_crmentity SET shownerid=? WHERE crmid=?', array($shownerid, $record));
	}

	/**
	 * Function to set Shared Owner
	 */
	public function setAllSharedOwner($userid, $delete = false)
	{
		$log = vglobal('log');
		$log->info("Entering Into fn setAllSharedOwner($userid, $delete)");
		$db = PearDatabase::getInstance();
		$allSharedOwner = self::getAllSharedOwner();
		if ($delete) {
			foreach ($allSharedOwner as $key => $user) {
				if ($user == $userid) {
					unset($allSharedOwner[$key]);
				}
			}
		} else {
			$allSharedOwner[] = $userid;
		}
		$allSharedOwner = implode("','", array_unique($allSharedOwner));
		$db->query("ALTER TABLE vtiger_crmentity CHANGE `shownerid` `shownerid` SET('$allSharedOwner');");
		$log->info("Exiting fn setAllSharedOwner()");
	}

	/**
	 * Function to get Shared Owner from record
	 */
	public function getSharedOwner($record)
	{
		$log = vglobal('log');
		$log->info("Entering Into fn getSharedOwner($record)");
		$db = PearDatabase::getInstance();
		$sharedOwner = Vtiger_Cache::get('SharedOwner', $record);
		if (!$sharedOwner) {
			$Result = $db->pquery('SELECT shownerid FROM vtiger_crmentity WHERE crmid = ?', array($record));
			$shownerid = $db->query_result($Result, 0, 'shownerid');
			Vtiger_Cache::set('SharedOwner', $record, $shownerid);
		} else {
			$shownerid = $sharedOwner;
		}
		$log->info("Exiting fn getSharedOwner()");
		return Vtiger_Functions::getArrayFromValue($shownerid);
	}

	/**
	 * Function to get All Shared Owner from record
	 */
	public function getAllSharedOwner()
	{
		$log = vglobal('log');
		$log->info("Entering Into fn getAllSharedOwner()");
		$db = PearDatabase::getInstance();
		$result = $db->query("SHOW COLUMNS FROM `vtiger_crmentity` WHERE `Field` = 'shownerid'");
		$field = $db->raw_query_result_rowdata($result, 0);
		$set = $field['Type'];
		$set = substr($set, 5, strlen($set) - 7);
		$log->info("Exiting fn getAllSharedOwner()");
		return preg_split("/','/", $set);
	}

	/**
	 * Function to get set Shared Owner Recursively
	 */
	public function setSharedOwnerRecursively($recordId, $addUser, $removeUser, $moduleName)
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
	public function getSharedRecordsRecursively($recordId, $moduleName)
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
}
