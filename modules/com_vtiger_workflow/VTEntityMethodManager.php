<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */

/**
 * Class VTEntityMethodManager.
 */
class VTEntityMethodManager
{
	/**
	 * Add entity method.
	 *
	 * @param string $moduleName
	 * @param string $methodName
	 * @param string $functionPath
	 * @param string $functionName
	 */
	public function addEntityMethod($moduleName, $methodName, $functionPath, $functionName)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()
			->insert('com_vtiger_workflowtasks_entitymethod', [
				'module_name' => $moduleName,
				'function_path' => $functionPath,
				'function_name' => $functionName,
				'method_name' => $methodName,
			])->execute();
	}

	/**
	 * Execute method.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param string              $methodName
	 */
	public function executeMethod(Vtiger_Record_Model $recordModel, $methodName)
	{
		$data = (new \App\Db\Query())->select(['function_path', 'function_name'])->from('com_vtiger_workflowtasks_entitymethod')->where(['module_name' => $recordModel->getModuleName(), 'method_name' => $methodName])->one();
		if ($data) {
			require_once $data['function_path'];
			\call_user_func("{$data['function_name']}::$methodName", $recordModel);
		}
	}

	/**
	 * Get methods for module.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function methodsForModule($moduleName)
	{
		return (new \App\Db\Query())->select(['method_name'])->from('com_vtiger_workflowtasks_entitymethod')->where(['module_name' => $moduleName])->column();
	}

	/**
	 * Function to remove workflowtasks entity method.
	 *
	 * @param string $moduleName Module Name
	 * @param string $methodName Entity Method Name
	 */
	public function removeEntityMethod($moduleName, $methodName)
	{
		\App\Db::getInstance()->createCommand()->delete('com_vtiger_workflowtasks_entitymethod', ['module_name' => $moduleName, 'method_name' => $methodName])->execute();
	}
}
