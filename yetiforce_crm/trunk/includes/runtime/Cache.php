<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

include_once dirname(__FILE__) . '/cache/Connector.php';

class Vtiger_Cache  {
    private static  $selfInstance = false;
    public static $cacheEnable = true;

	protected $connector;

	private function __construct() {
		$this->connector = Vtiger_Cache_Connector::getInstance();
	}

    public static function getInstance(){
		if(self::$selfInstance){
			return self::$selfInstance;
		} else{
			self::$selfInstance = new self();
			return self::$selfInstance;
		}
    }

	public static function get($ns, $key) {
		$self = self::getInstance();
		if($key && self::$cacheEnable) {
			return $self->connector->get($ns, $key);
		}
		return false;
	}

	public static function set($ns, $key, $value) {
		$self = self::getInstance();
		if(self::$cacheEnable) {
			$self->connector->set($ns, $key, $value);
		}
	}
    
    public static function flush(){
        $self = self::getInstance();
        $self->connector->flush();
    }

    
	private static $_user_list;

	public function getUserList($module,$currentUser){
		if(isset(self::$_user_list[$currentUser][$module])){
			return self::$_user_list[$currentUser][$module];
		}
		return false;
	}

	public function setUserList($module,$userList,$currentUser){
		if(self::$cacheEnable){
			self::$_user_list[$currentUser][$module]=$userList;
		}
	}

	private static $_group_list;

	public function getGroupList($module,$currentUser){
		if(isset(self::$_group_list[$currentUser][$module])){
			return self::$_group_list[$currentUser][$module];
		}
		return false;
	}

	public function setGroupList($module,$GroupList,$currentUser){
		if(self::$cacheEnable){
			self::$_group_list[$currentUser][$module]=$GroupList;
		}
	}

	private static $_picklist_values;

	public function getPicklistValues($fieldName){
		if(isset(self::$_picklist_values[$fieldName])){
			return self::$_picklist_values[$fieldName];
		}
		return false;
	}

	public function setPicklistValues($fieldName,$values){
		if(self::$cacheEnable){
			self::$_picklist_values[$fieldName]=$values;
		}
	}

	private static $_picklist_details;

	public function getPicklistDetails($module,$field){
		if(isset(self::$_picklist_details[$module][$field])){
			return self::$_picklist_details[$module][$field];
		}
		return false;
	}

	public function setPicklistDetails($module,$field,$picklistDetails){
		if(self::$cacheEnable){
			self::$_picklist_details[$module][$field] = $picklistDetails;
		}
	}

	private static $_module_ownedby;

	public function getModuleOwned($module){
		if(isset(self::$_module_ownedby[$module])){
 			return self::$_module_ownedby[$module];
		}
		return false;
	}

	public function setModuleOwned($module,$ownedby){
		if(self::$cacheEnable){
			self::$_module_ownedby[$module] = $ownedby;
		}
	}

	private static $_block_instance;

	public function getBlockInstance($block, $moduleName){
		if(isset(self::$_block_instance[$moduleName][$block])){
 			return self::$_block_instance[$moduleName][$block];
		}
		return false;
	}

	public function setBlockInstance($block, $moduleName, $instance){
		if(self::$cacheEnable){
			self::$_block_instance[$moduleName][$block] = $instance;
		}
	}


	private static $_field_instance;

	public function getFieldInstance($field,$moduleId){
		if(isset(self::$_field_instance[$moduleId][$field])){
 			return self::$_field_instance[$moduleId][$field];
		}
		return false;
	}

	public function setFieldInstance($field,$moduleId,$instance){
		if(self::$cacheEnable){
			self::$_field_instance[$moduleId][$field] = $instance;
		}
	}

	private static $_admin_user_id = false;

	public function getAdminUserId(){
 			return self::$_admin_user_id;
	}

	public function setAdminUserId($userId){
		if(self::$cacheEnable){
			self::$_admin_user_id = $userId;
		}
	}

	//cache for the module Instance
    private static  $_module_name = array();

    public function getModuleName($moduleId){
	if(isset(self::$_module_name[$moduleId])){
	    return self::$_module_name[$moduleId];
	}
	return false;
    }

    public function setModuleName($moduleId,$moduleName){
		if(self::$cacheEnable){
			self::$_module_name[$moduleId] = $moduleName;
		}
    }

	//cache for the module Instance
    private static  $_workflow_for_module = array();

    public function getWorkflowForModule($module){
		if(isset(self::$_workflow_for_module[$module])){
			return self::$_workflow_for_module[$module];
		}
		return false;
    }

    public function setWorkflowForModule($module,$workflows){
		if(self::$cacheEnable){
			self::$_workflow_for_module[$module] = $workflows;
		}
    }

	//cache for the module Instance
    private static  $_workflow_for_module_supporting_comments = array();

    public function getWorkflowForModuleSupportingComments($module){
		if(isset(self::$_workflow_for_module_supporting_comments[$module])){
			return self::$_workflow_for_module_supporting_comments[$module];
		}
		return false;
    }

    public function setWorkflowForModuleSupportingComments($module,$workflows){
		if(self::$cacheEnable){
			self::$_workflow_for_module_supporting_comments[$module] = $workflows;
		}
    }


	private static $_user_id ;

	public function getUserId($userName){
		if(isset(self::$_user_id[$userName])){
			return self::$_user_id[$userName];
		}
		return false;
	}

	public function setUserId($userName,$userId){
		if(self::$cacheEnable){
			self::$_user_id[$userName] = $userId;
		}
	}

	private static $_table_exists ;

	public function getTableExists($tableName){
		if(isset(self::$_table_exists[$tableName])){
			return self::$_table_exists[$tableName];
		}
		return false;
	}

	public function setTableExists($tableName,$exists){
		if(self::$cacheEnable){
			self::$_table_exists[$tableName] = $exists;
		}
	}

	private static $_picklist_id;

	public function getPicklistId($fieldName,$moduleName){
		if(isset(self::$_picklist_id[$moduleName][$fieldName])){
			return self::$_picklist_id[$moduleName][$fieldName];
		}
		return false;
	}
	public function setPicklistId($fieldName,$moduleName,$picklistId){
		if(self::$cacheEnable){
			self::$_picklist_id[$moduleName][$fieldName] = $picklistId;
		}
	}

	private static $_group_id;

	public function getGroupId($groupName){
		if(isset(self::$_group_id[$groupName])){
			return self::$_group_id[$groupName];
		}
		return false;
	}

	public function setGroupId($groupName,$groupId){
		if(self::$cacheEnable){
			self::$_group_id[$groupName]=$groupId;
		}
	}

	private static $_assigned_picklist_values;

	public function getAssignedPicklistValues($tableName,$roleId){
		if(isset(self::$_assigned_picklist_values[$tableName][$roleId])){
			return self::$_assigned_picklist_values[$tableName][$roleId];
		}
		return false;
	}

	public function setAssignedPicklistValues($tableName,$roleId,$values){
		if(self::$cacheEnable){
			self::$_assigned_picklist_values[$tableName][$roleId]=$values;
		}
	}

	public function hasAssignedPicklistValues($tableName, $roleId) {
		$values = $this->getAssignedPicklistValues($tableName, $roleId);
		return $values !== false;
	}

	private static $_block_fields;

	public function getBlockFields($block,$module){
		if(isset(self::$_block_fields[$module][$block])){
			return self::$_block_fields[$module][$block];
		}
		return false;
	}

	public function setBlockFields($block,$module,$fields){
		if(self::$cacheEnable){
			self::$_block_fields[$module][$block] = $fields;
		}
	}

	private static $_name_fields;	

	public function getNameFields($module){
		if(isset(self::$_name_fields[$module])){
			return self::$_name_fields[$module];
		}
		return false;
	}
	
	public function setNameFields($module,$nameFields){
		if(self::$cacheEnable){
			self::$_name_fields[$module] = $nameFields; 
		}
	}

	public function purifyGet($key) {
		if (self::$cacheEnable) {
			return $this->connector->get('purify', $key);
		}
		return false;
	}

	public function purifySet($key, $value) {
		if (self::$cacheEnable) {
			$this->connector->set('purify', $key, $value);
		}
	}

	private static $_owners_names_list;

	public function getOwnerName($id){
		if(isset(self::$_owners_names_list[$id])) {
			return self::$_owners_names_list[$id];
		}
		return false;
	}

	public function setOwnerName($id, $value){
		if(self::$cacheEnable){
			self::$_owners_names_list[$id] = $value;
		}
	}

	public function hasOwnerName($id) {
		$value = $this->getOwnerName($id);
		return $value !== false;
	}

	private static $_owners_db_names_list;

	public function getOwnerDbName($id){
		if(isset(self::$_owners_db_names_list[$id])) {
			return self::$_owners_db_names_list[$id];
		}
		return false;
	}

	public function setOwnerDbName($id, $value){
		if(self::$cacheEnable){
			self::$_owners_db_names_list[$id] = $value;
		}
	}

	public function hasOwnerDbName($id) {
		$value = $this->getOwnerDbName($id);
		return $value !== false;
	}

	private static $_creator_ids_list;

	public function getCreator($id){
		if(isset(self::$_creator_ids_list[$id])) {
			return self::$_creator_ids_list[$id];
		}
		return false;
	}

	public function setCreator($id, $value){
		if(self::$cacheEnable){
			self::$_creator_ids_list[$id] = $value;
		}
	}

	public function hasCreator($id) {
		$value = $this->getCreator($id);
		return $value !== false;
	}
}
