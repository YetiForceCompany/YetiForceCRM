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

/**
 * Class VTEntityMethodManager
 */
class VTEntityMethodManager
{

	/**
	 * Add entity method
	 * @param string $moduleName
	 * @param string $methodName
	 * @param string $functionPath
	 * @param string $functionName
	 */
	public function addEntityMethod($moduleName, $methodName, $functionPath, $functionName)
	{
		$db = \App\Db::getInstance();
		$id = $db->getUniqueId("com_vtiger_workflowtasks_entitymethod");
		$db->createCommand()
			->insert('com_vtiger_workflowtasks_entitymethod', [
				'workflowtasks_entitymethod_id' => $id,
				'module_name' => $moduleName,
				'function_path' => $functionPath,
				'function_name' => $functionName,
				'method_name' => $methodName
			])->execute();
	}

	/**
	 * Execute method
	 * @param Vtiger_Record_Model $recordModel
	 * @param string $methodName
	 */
	public function executeMethod(Vtiger_Record_Model $recordModel, $methodName)
	{
		$moduleName = $recordModel->getModuleName();
		$data = (new \App\Db\Query())->select(['function_path', 'function_name'])->from('com_vtiger_workflowtasks_entitymethod')->where(['module_name' => $moduleName, 'method_name' => $methodName])->one();
		if ($data) {
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
