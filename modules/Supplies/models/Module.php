<?php

/**
 * Supplies Module Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Module_Model extends Vtiger_Module_Model
{

	protected static $modulesNameForTpl = [];
	protected $moduleType = 'Supplies';

	public static function getModuleNameForTpl($tpl, $moduleName)
	{
		if (isset(self::$modulesNameForTpl[$moduleName . '_' . $tpl])) {
			return self::$modulesNameForTpl[$moduleName . '_' . $tpl];
		}
		$filename = 'layouts' . DIRECTORY_SEPARATOR . 'vlayout' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)){
			self::$modulesNameForTpl[$moduleName . '_' . $tpl] = $moduleName;
			return $moduleName;
		}

		$basicModuleName = 'Supplies';
		$filename = 'layouts' . DIRECTORY_SEPARATOR . 'vlayout' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $basicModuleName . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)){
			self::$modulesNameForTpl[$moduleName . '_' . $tpl] = $basicModuleName;
			return $basicModuleName;
		}
	}
}
