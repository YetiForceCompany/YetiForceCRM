<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class VtigerCRMObject
{

	private $moduleName;
	private $moduleId;
	private $instance;

	public function __construct($moduleCredential, $isId = false)
	{

		if ($isId) {
			$this->moduleId = $moduleCredential;
			$this->moduleName = \App\Module::getModuleName($this->moduleId);
		} else {
			$this->moduleName = $moduleCredential;
			$this->moduleId = \App\Module::getModuleId($this->moduleName);
		}
		$this->instance = null;
		$this->getInstance();
	}

	public function getModuleName()
	{
		return $this->moduleName;
	}

	public function getModuleId()
	{
		return $this->moduleId;
	}

	public function getInstance()
	{
		if ($this->instance === null) {
			$this->instance = $this->getModuleClassInstance($this->moduleName);
		}
		return $this->instance;
	}

	public function getObjectId()
	{
		if ($this->instance === null) {
			$this->getInstance();
		}
		return $this->instance->id;
	}

	public function setObjectId($id)
	{
		if ($this->instance === null) {
			$this->getInstance();
		}
		$this->instance->id = $id;
	}

	private function titleCase($str)
	{
		$first = substr($str, 0, 1);
		return strtoupper($first) . substr($str, 1);
	}

	private function getObjectTypeId($objectName)
	{

		// Use getTabid API
		$tid = \App\Module::getModuleId($objectName);

		if ($tid === false) {
			$adb = PearDatabase::getInstance();

			$sql = "select * from vtiger_tab where name=?;";
			$params = array($objectName);
			$result = $adb->pquery($sql, $params);
			$data1 = $adb->fetchByAssoc($result, 1, false);

			$tid = $data1["tabid"];
		}
		// END

		return $tid;
	}

	private function getModuleClassInstance($moduleName)
	{
		return CRMEntity::getInstance($moduleName);
	}


	private function getTabName()
	{
		if ($this->moduleName == 'Events') {
			return 'Calendar';
		}
		return $this->moduleName;
	}

	public function read($id)
	{
		$adb = PearDatabase::getInstance();
		$this->instance->retrieve_entity_info($id, $this->getTabName());
		return true;
	}

	public function create($element)
	{
		$adb = PearDatabase::getInstance();

		$error = false;
		foreach ($element as $k => $v) {
			$this->instance->column_fields[$k] = $v;
		}

		$this->instance->Save($this->getTabName());
		$error = $this->instance->db->hasFailedTransaction();
		return !$error;
	}

	public function update($element)
	{
		$adb = PearDatabase::getInstance();
		$error = false;

		foreach ($element as $k => $v) {
			$this->instance->column_fields[$k] = $v;
		}

		$this->instance->mode = "edit";
		$this->instance->Save($this->getTabName());
		$error = $this->instance->db->hasFailedTransaction();
		return !$error;
	}

	public function revise($element)
	{
		$adb = PearDatabase::getInstance();
		$error = false;

		$error = $this->read($this->getObjectId());
		if ($error === false) {
			return $error;
		}

		foreach ($element as $k => $v) {
			$this->instance->column_fields[$k] = $v;
		}

		//added to fix the issue of utf8 characters
		foreach ($this->instance->column_fields as $key => $value) {
			$this->instance->column_fields[$key] = decode_html($value);
		}

		$this->instance->mode = "edit";
		$this->instance->Save($this->getTabName());
		$error = $this->instance->db->hasFailedTransaction();
		return !$error;
	}

	public function delete($id)
	{
		$adb = PearDatabase::getInstance();
		$error = false;
		$adb->startTransaction();
		DeleteEntity($this->getTabName(), $this->getTabName(), $this->instance, $id, $returnid);
		$error = $adb->hasFailedTransaction();
		$adb->completeTransaction();
		return !$error;
	}

	public function getFields()
	{
		return $this->instance->column_fields;
	}

	public function exists($id)
	{
		$adb = PearDatabase::getInstance();

		$exists = false;
		$sql = "select * from vtiger_crmentity where crmid=? and deleted=0";
		$result = $adb->pquery($sql, array($id));
		if ($result != null && isset($result)) {
			if ($adb->num_rows($result) > 0) {
				$exists = true;
			}
		}
		return $exists;
	}

	public function getSEType($id)
	{
		$adb = PearDatabase::getInstance();

		$seType = null;
		$sql = "select * from vtiger_crmentity where crmid=? and deleted=0";
		$result = $adb->pquery($sql, array($id));
		if ($result != null && isset($result)) {
			if ($adb->num_rows($result) > 0) {
				$seType = $adb->query_result($result, 0, "setype");
			}
		}
		return $seType;
	}
}
