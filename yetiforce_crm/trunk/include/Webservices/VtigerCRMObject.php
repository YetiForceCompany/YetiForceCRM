<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class VtigerCRMObject{
	
	private $moduleName ;
	private $moduleId ;
	private $instance ;
	
	function VtigerCRMObject($moduleCredential, $isId=false){
		
		if($isId){
			$this->moduleId = $moduleCredential;
			$this->moduleName = $this->getObjectTypeName($this->moduleId);
		}else{
			$this->moduleName = $moduleCredential;
			$this->moduleId = $this->getObjectTypeId($this->moduleName);
		}
		$this->instance = null;
		$this->getInstance();
	}
	
	public function getModuleName(){
		return $this->moduleName;
	}
	
	public function getModuleId(){
		return $this->moduleId;
	}
	
	public function getInstance(){
		if($this->instance == null){
			$this->instance = $this->getModuleClassInstance($this->moduleName);
		}
		return $this->instance;
	}
	
	public function getObjectId(){
		if($this->instance==null){
			$this->getInstance();
		}
		return $this->instance->id;
	}
	
	public function setObjectId($id){
		if($this->instance==null){
			$this->getInstance();
		}
		$this->instance->id = $id;
	}
	
	private function titleCase($str){
		$first = substr($str, 0, 1);
		return strtoupper($first).substr($str,1);
	}
	
	private function getObjectTypeId($objectName){
		
		// Use getTabid API
		$tid = getTabid($objectName);

		if($tid === false) {
			global $adb;
		
			$sql = "select * from vtiger_tab where name=?;";
			$params = array($objectName);
			$result = $adb->pquery($sql, $params);
			$data1 = $adb->fetchByAssoc($result,1,false);
		
			$tid = $data1["tabid"];
		}
		// END
		
		return $tid;
		
	}
	
	private function getModuleClassInstance($moduleName){
		return CRMEntity::getInstance($moduleName);
	}
	
	private function getObjectTypeName($moduleId){
		
		return getTabModuleName($moduleId);
		
	}
	
	private function getTabName(){
		if($this->moduleName == 'Events'){
			return 'Calendar';
		}
		return $this->moduleName;
	}
	
	public function read($id){
		global $adb;
		
		$error = false;
		$adb->startTransaction();
		$this->instance->retrieve_entity_info($id,$this->getTabName());
		$error = $adb->hasFailedTransaction();
		$adb->completeTransaction();
		return !$error;
	}
	
	public function create($element){
		global $adb;
		
		$error = false;
		foreach($element as $k=>$v){
			$this->instance->column_fields[$k] = $v;
		}
		
		$adb->startTransaction();
		$this->instance->Save($this->getTabName());
		$error = $adb->hasFailedTransaction();
		$adb->completeTransaction();
		return !$error;
	}
	
	public function update($element){
		
		global $adb;
		$error = false;
		
		foreach($element as $k=>$v){
			$this->instance->column_fields[$k] = $v;
		}
		
		$adb->startTransaction();
		$this->instance->mode = "edit";
		$this->instance->Save($this->getTabName());
		$error = $adb->hasFailedTransaction();
		$adb->completeTransaction();
		return !$error;
	}
	
	public function revise($element){
		global $adb;
		$error = false;

		$error = $this->read($this->getObjectId());
		if($error == false){
			return $error;
		}

		foreach($element as $k=>$v){
			$this->instance->column_fields[$k] = $v;
		}

		//added to fix the issue of utf8 characters
		foreach($this->instance->column_fields as $key=>$value){
			$this->instance->column_fields[$key] = decode_html($value);
		}

                $adb->startTransaction();
		$this->instance->mode = "edit";
		$this->instance->Save($this->getTabName());
		$error = $adb->hasFailedTransaction();
		$adb->completeTransaction();
		return !$error;
	}

	public function delete($id){
		global $adb;
		$error = false;
		$adb->startTransaction();
		DeleteEntity($this->getTabName(), $this->getTabName(), $this->instance, $id,$returnid);
		$error = $adb->hasFailedTransaction();
		$adb->completeTransaction();
		return !$error;
	}
	
	public function getFields(){
		return $this->instance->column_fields;
	}
	
	function exists($id){
		global $adb;
		
		$exists = false;
		$sql = "select * from vtiger_crmentity where crmid=? and deleted=0";
		$result = $adb->pquery($sql , array($id));
		if($result != null && isset($result)){
			if($adb->num_rows($result)>0){
				$exists = true;
			}
		}
		return $exists;
	}
	
	function getSEType($id){
		global $adb;
		
		$seType = null;
		$sql = "select * from vtiger_crmentity where crmid=? and deleted=0";
		$result = $adb->pquery($sql , array($id));
		if($result != null && isset($result)){
			if($adb->num_rows($result)>0){
				$seType = $adb->query_result($result,0,"setype");
			}
		}
		return $seType;
	}
	
}

?>
