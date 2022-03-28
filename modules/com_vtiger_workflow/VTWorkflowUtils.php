<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

//A collection of util functions for the workflow module

/**
 * Class vTWorkflowUtils.
 */
class VTWorkflowUtils
{
	/**
	 * User stack.
	 *
	 * @var array
	 */
	public static $userStack;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (empty(self::$userStack)) {
			self::$userStack = [];
		}
	}

	/**
	 * Check whether the given identifier is valid.
	 *
	 * @param string $identifier Description
	 */
	public function validIdentifier($identifier)
	{
		if (\is_string($identifier)) {
			return preg_match('/^[a-zA-Z][a-zA-Z_0-9]+$/', $identifier);
		}
		return false;
	}

	/** function to check if the module has workflow.
	 * @param string $modulename - name of the module
	 */
	public static function checkModuleWorkflow($modulename)
	{
		return (new \App\Db\Query())->from('vtiger_tab')->where(['NOT IN', 'name', ['Calendar', 'Faq', 'Users']])->andWhere(['isentitytype' => 1, 'presence' => 0, 'tabid' => \App\Module::getModuleId($modulename)])->exists();
	}

	/**
	 * Get modules.
	 *
	 * @return array
	 */
	public function vtGetModules()
	{
		$query = (new \App\Db\Query())->select(['vtiger_field.tabid', 'name'])->from('vtiger_field')->innerJoin('vtiger_tab', 'vtiger_field.tabid=vtiger_tab.tabid')->where(['vtiger_tab.isentitytype' => 1, 'vtiger_tab.presence' => [0, 2]])->distinct();
		$modules = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modules[] = $row['name'];
		}
		return $modules;
	}
}
