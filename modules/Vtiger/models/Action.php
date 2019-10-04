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
 * Vtiger Action Model Class.
 */
class Vtiger_Action_Model extends \App\Base
{
	/**
	 * Standard actions.
	 *
	 * @var array
	 */
	public static $standardActions = [0 => 'Save', 1 => 'EditView', 2 => 'Delete', 3 => 'index', 4 => 'DetailView', 7 => 'CreateView'];

	/**
	 * Non configurable actions.
	 *
	 * @var array
	 */
	public static $nonConfigurableActions = ['Save', 'index', 'SavePriceBook', 'SaveVendor',
		'DetailViewAjax', 'PriceBookEditView', 'QuickCreate', 'VendorEditView',
		'DeletePriceBook', 'DeleteVendor', 'Popup', 'PriceBookDetailView',
		'VendorDetailView'];

	/**
	 * Utility actions.
	 *
	 * @var array
	 */
	public static $utilityActions = [5 => 'Import', 6 => 'Export', 9 => 'ConvertLead'];

	/**
	 * Return action id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('actionid');
	}

	/**
	 * Return action name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('actionname');
	}

	/**
	 * Check if is a utility tool.
	 *
	 * @return bool
	 */
	public function isUtilityTool()
	{
		return false;
	}

	/**
	 * Check if module is enabled.
	 *
	 * @param Vtiger_Module_Model $module
	 *
	 * @return bool
	 */
	public function isModuleEnabled($module)
	{
		if (!$module->isEntityModule()) {
			return false;
		}
		if (\in_array($this->getName(), self::$standardActions)) {
			return true;
		}
		$tabId = $module->getId();
		$query = (new App\Db\Query())->select(['profileid'])->from('vtiger_profile2standardpermissions')->where(['tabid' => $tabId, 'operation' => $this->getId()]);
		if ($query->count()) {
			return true;
		}
		return false;
	}

	/**
	 * Create instance from record row.
	 *
	 * @param array $row
	 *
	 * @return Vtiger_Action_Model
	 */
	public static function getInstanceFromRow($row)
	{
		$className = 'Vtiger_Action_Model';
		$actionName = $row['actionname'];
		if (!\in_array($actionName, self::$standardActions)) {
			$className = 'Vtiger_Utility_Model';
		}
		$actionModel = new $className();
		return $actionModel->setData($row);
	}

	/**
	 * Cached instances.
	 *
	 * @var array
	 */
	protected static $cachedInstances = null;

	/**
	 * Return instance.
	 *
	 * @param int  $value
	 * @param bool $force
	 *
	 * @return Vtiger_Action_Model|null
	 */
	public static function getInstance($value, $force = false)
	{
		if (!self::$cachedInstances || $force) {
			self::$cachedInstances = self::getAll();
		}
		if (self::$cachedInstances) {
			$actionid = vtlib\Utils::isNumber($value) ? $value : false;
			if (false === $actionid && isset(self::$cachedInstances[$value])) {
				return self::$cachedInstances[$value];
			}
			foreach (self::$cachedInstances as $instance) {
				if ($instance->get('actionid') == $actionid) {
					return $instance;
				}
			}
		}
		return null;
	}

	/**
	 * Return instance by id or name.
	 *
	 * @param int|string $value
	 *
	 * @return Vtiger_Action_Model|null
	 */
	public static function getInstanceWithIdOrName($value)
	{
		$query = (new App\Db\Query())->from('vtiger_actionmapping');
		if (vtlib\Utils::isNumber($value)) {
			$query->where(['actionid' => $value])->limit(1);
		} else {
			$query->where(['actionname' => $value]);
		}
		$row = $query->one();
		if ($row) {
			return self::getInstanceFromRow($row);
		}
		return null;
	}

	/**
	 * Return all instances.
	 *
	 * @param bool $configurable
	 *
	 * @return Vtiger_Action_Model[]
	 */
	public static function getAll($configurable = false)
	{
		if (\App\Cache::has('Actions', 'all')) {
			$rows = \App\Cache::get('Actions', 'all');
		} else {
			$rows = (new \App\Db\Query())->from('vtiger_actionmapping')->all();
			\App\Cache::save('Actions', 'all', $rows);
		}
		if ($configurable) {
			foreach ($rows as $key => &$row) {
				if (\in_array($row['actionname'], self::$nonConfigurableActions)) {
					unset($rows[$key]);
				}
			}
		}
		$actionModels = [];
		foreach ($rows as &$row) {
			$actionModels[$row['actionname']] = self::getInstanceFromRow($row);
		}
		return $actionModels;
	}

	/**
	 * Function to get all instances models of basic action.
	 *
	 * @param bool $configurable
	 *
	 * @return self[]
	 */
	public static function getAllBasic($configurable = false)
	{
		$query = (new App\Db\Query())->from('vtiger_actionmapping')
			->where(['actionid' => array_keys(self::$standardActions)]);
		if ($configurable) {
			$query->andWhere(['NOT IN', 'actionname', self::$nonConfigurableActions]);
		}
		$actionModels = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$actionModels[] = self::getInstanceFromRow($row);
		}
		$dataReader->close();

		return $actionModels;
	}

	/**
	 * Function to get all instances models of utility action.
	 *
	 * @param bool $configurable
	 *
	 * @return self[]
	 */
	public static function getAllUtility($configurable = false)
	{
		$query = (new App\Db\Query())->from('vtiger_actionmapping')
			->where(['NOT IN', 'actionid', array_keys(self::$standardActions)]);
		if ($configurable) {
			$query->andWhere(['NOT IN', 'actionname', self::$nonConfigurableActions]);
		}
		$actionModels = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$actionModels[] = self::getInstanceFromRow($row);
		}
		$dataReader->close();

		return $actionModels;
	}
}
