<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */
require_once("include/events/SqlResultIterator.php");

class VTEntityMethodManager
{

	public function addEntityMethod($moduleName, $methodName, $functionPath, $functionName)
	{
		$adb = PearDatabase::getInstance();
		$id = $adb->getUniqueId("com_vtiger_workflowtasks_entitymethod");
		$adb->pquery("insert into com_vtiger_workflowtasks_entitymethod (workflowtasks_entitymethod_id, module_name, function_path, function_name, method_name) values (?,?,?,?,?)", array($id, $moduleName, $functionPath, $functionName, $methodName));
	}

	public function executeMethod(Vtiger_Record_Model $recordModel, $methodName)
	{
		$adb = PearDatabase::getInstance();
		$moduleName = $recordModel->getModuleName();
		$result = $adb->pquery("select function_path, function_name from com_vtiger_workflowtasks_entitymethod where module_name=? and method_name=?", array($moduleName, $methodName));
		if ($adb->num_rows($result) != 0) {
			$data = $adb->raw_query_result_rowdata($result, 0);
			$functionPath = $data['function_path'];
			$functionName = $data['function_name'];
			require_once($functionPath);
			$functionName($recordModel);
		}
	}

	public function methodsForModule($moduleName)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("select method_name from com_vtiger_workflowtasks_entitymethod where module_name=?", array($moduleName));
		$it = new SqlResultIterator($adb, $result);
		$methodNames = [];
		foreach ($it as $row) {
			$methodNames[] = $row->method_name;
		}
		return $methodNames;
	}
	/*
	  private function methodExists($object, $methodName){
	  $className = get_class($object);
	  $class = new ReflectionClass($className);
	  $methods = $class->getMethods();
	  foreach($methods as $method){
	  if($method->getName()==$methodName){
	  return true;
	  }
	  }
	  return false;
	  } */

	/**
	 * Function to remove workflowtasks entity method 
	 * @param string Module Name
	 * @param string Entity Method Name.
	 */
	public function removeEntityMethod($moduleName, $methodName)
	{
		PearDatabase::getInstance()->pquery("DELETE FROM com_vtiger_workflowtasks_entitymethod WHERE module_name = ? and method_name= ?", array($moduleName, $methodName));
	}
}
