<?php

namespace App;

/**
 * Modules basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Module
{
	/**
	 * Cache for tabdata.php.
	 *
	 * @var array
	 */
	protected static $tabdataCache;

	/**
	 * Init tabdata from file.
	 */
	public static function init()
	{
		static::$tabdataCache = require ROOT_DIRECTORY . '/user_privileges/tabdata.php';
		static::$tabdataCache['tabName'] = array_flip(static::$tabdataCache['tabId']);
	}

	/**
	 * Init tabdata form db.
	 */
	public static function initFromDb()
	{
		static::$tabdataCache = static::getModuleMeta();
		static::$tabdataCache['tabName'] = array_flip(static::$tabdataCache['tabId']);
	}

	/**
	 * Gets entity info.
	 *
	 * @param string $moduleName
	 *
	 * @return array|null
	 */
	public static function getEntityInfo(string $moduleName = null): ?array
	{
		return self::getEntitiesInfo()[$moduleName] ?? null;
	}

	/**
	 * Gets all entities data.
	 *
	 * @param array
	 */
	public static function getEntitiesInfo(): array
	{
		$cacheName = 'ModuleEntityInfo';
		if (!Cache::has($cacheName, '')) {
			$entityInfos = [];
			$dataReader = (new \App\Db\Query())->from('vtiger_entityname')->createCommand()->query();
			while ($row = $dataReader->read()) {
				$row['fieldnameArr'] = $row['fieldname'] ? explode(',', $row['fieldname']) : [];
				$row['searchcolumnArr'] = $row['searchcolumn'] ? explode(',', $row['searchcolumn']) : [];
				$entityInfos[$row['modulename']] = $row;
			}
			return Cache::save($cacheName, '', $entityInfos);
		}
		return Cache::get($cacheName, '');
	}

	public static function getAllEntityModuleInfo($sort = false)
	{
		$entity = static::getEntitiesInfo();
		if ($sort) {
			usort($entity, function ($a, $b) {
				return $a['sequence'] < $b['sequence'] ? -1 : 1;
			});
		}
		return $entity;
	}

	protected static $isModuleActiveCache = [];

	/**
	 * Function to check whether the module is active.
	 *
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public static function isModuleActive(string $moduleName): bool
	{
		if (isset(static::$isModuleActiveCache[$moduleName])) {
			return static::$isModuleActiveCache[$moduleName];
		}
		if (\in_array($moduleName, ['CustomView', 'Users', 'Import', 'com_vtiger_workflow', 'PickList'])) {
			static::$isModuleActiveCache[$moduleName] = true;
			return true;
		}
		$moduleId = static::getModuleId($moduleName);
		$isActive = (isset(static::$tabdataCache['tabPresence'][$moduleId]) && 0 == static::$tabdataCache['tabPresence'][$moduleId]);
		static::$isModuleActiveCache[$moduleName] = $isActive;
		return $isActive;
	}

	/**
	 * Get module id by module name.
	 *
	 * @param string $moduleName
	 *
	 * @return bool|int
	 */
	public static function getModuleId($moduleName)
	{
		return static::$tabdataCache['tabId'][$moduleName] ?? false;
	}

	/**
	 * Get module nane by module id.
	 *
	 * @param int $tabId
	 *
	 * @return bool|string
	 */
	public static function getModuleName($tabId)
	{
		return static::$tabdataCache['tabName'][$tabId] ?? false;
	}

	/**
	 * Get module owner by module id.
	 *
	 * @param int $tabId
	 *
	 * @return int
	 */
	public static function getModuleOwner($tabId)
	{
		return static::$tabdataCache['tabOwnedby'][$tabId] ?? false;
	}

	/**
	 * Get all module names.
	 *
	 * @return string[]
	 */
	public static function getAllModuleNames()
	{
		return static::$tabdataCache['tabName'];
	}

	/**
	 * Function to get the list of module for which the user defined sharing rules can be defined.
	 *
	 * @param array $eliminateModules
	 *
	 * @return array
	 */
	public static function getSharingModuleList($eliminateModules = false)
	{
		$modules = \vtlib\Functions::getAllModules(true, true, 0, false, 0);
		$sharingModules = [];
		foreach ($modules as $row) {
			if (!$eliminateModules || !\in_array($row['name'], $eliminateModules)) {
				$sharingModules[] = $row['name'];
			}
		}
		return $sharingModules;
	}

	/**
	 * Get sql for name in display format.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public static function getSqlForNameInDisplayFormat($moduleName)
	{
		$db = \App\Db::getInstance();
		$entityFieldInfo = static::getEntityInfo($moduleName);
		$fieldsName = $entityFieldInfo['fieldnameArr'];
		if (\count($fieldsName) > 1) {
			$sqlString = 'CONCAT(';
			foreach ($fieldsName as &$column) {
				$sqlString .= "{$db->quoteTableName($entityFieldInfo['tablename'])}.{$db->quoteColumnName($column)},' ',";
			}
			$formattedName = new \yii\db\Expression(rtrim($sqlString, ',\' \',') . ')');
		} else {
			$fieldsName = array_pop($fieldsName);
			$formattedName = "{$db->quoteTableName($entityFieldInfo['tablename'])}.{$db->quoteColumnName($fieldsName)}";
		}
		return $formattedName;
	}

	/**
	 * Function to get a action id for a given action name.
	 *
	 * @param string $action
	 *
	 * @return int|null
	 */
	public static function getActionId($action)
	{
		if (empty($action)) {
			return null;
		}
		if (Cache::has('getActionId', $action)) {
			return Cache::get('getActionId', $action);
		}
		$actionIds = static::$tabdataCache['actionId'];
		if (isset($actionIds[$action])) {
			$actionId = $actionIds[$action];
		}
		if (empty($actionId)) {
			$actionId = (new Db\Query())->select(['actionid'])->from('vtiger_actionmapping')->where(['actionname' => $action])->scalar();
		}
		if (is_numeric($actionId)) {
			$actionId = (int) $actionId;
		}
		Cache::save('getActionId', $action, $actionId, Cache::LONG);
		return $actionId;
	}

	/**
	 * Get module meta data.
	 *
	 * @return array
	 */
	public static function getModuleMeta()
	{
		$tabNames = $tabPresence = $tabOwned = [];
		$allModules = \vtlib\Functions::getAllModules(false, true);
		foreach ($allModules as $moduleInfo) {
			$tabNames[$moduleInfo['name']] = $tabId = (int) $moduleInfo['tabid'];
			$tabPresence[$tabId] = $moduleInfo['presence'];
			$tabOwned[$tabId] = $moduleInfo['ownedby'];
		}
		//Constructing the actionname=>actionid array
		$actionAll = [];
		$dataReader = (new Db\Query())->from(['vtiger_actionmapping'])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$actionname = $row['actionname'];
			$actionAll[$actionname] = $actionid = (int) $row['actionid'];
			if (0 === (int) $row['securitycheck']) {
				$actionSecure[$actionid] = $actionname;
			}
		}
		return [
			'tabId' => $tabNames,
			'tabPresence' => $tabPresence,
			'tabOwnedby' => $tabOwned,
			'actionId' => $actionAll,
			'actionName' => $actionSecure,
		];
	}

	/**
	 * Function to create file about modules.
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public static function createModuleMetaFile()
	{
		Cache::delete('moduleTabs', 'all');
		Cache::delete('getTrackingModules', 'all');
		$filename = ROOT_DIRECTORY . '/user_privileges/tabdata.php';
		if (file_exists($filename)) {
			if (is_writable($filename)) {
				$moduleMeta = static::getModuleMeta();
				$content = '$tab_seq_array=' . Utils::varExport($moduleMeta['tabPresence']) . ";\n";
				$content .= 'return ' . Utils::varExport($moduleMeta) . ";\n";
				if (!Utils::saveToFile($filename, $content)) {
					throw new Exceptions\NoPermitted("Cannot write file ($filename)");
				}
			} else {
				Log::error("The file $filename is not writable");
			}
		} else {
			Log::error("The file $filename does not exist");
		}
		static::initFromDb();
		register_shutdown_function(function () {
			try {
				YetiForce\Shop::generateCache();
			} catch (\Throwable $e) {
				\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
				throw $e;
			}
		});
	}

	/**
	 * Function changes the module type.
	 *
	 * @param string $moduleName
	 * @param int    $type
	 */
	public static function changeType(string $moduleName, int $type)
	{
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleModel && $moduleModel->changeType($type) && PrivilegeUtil::modifyPermissions($moduleName, ['RecordPdfInventory'], \Vtiger_Module_Model::ADVANCED_TYPE === $type)) {
			UserPrivilegesFile::recalculateAll();
		}
	}

	/**
	 * Get all module names by filter.
	 *
	 * @param bool     $isEntityType
	 * @param bool     $showRestricted
	 * @param bool|int $presence
	 *
	 * @return string[]
	 */
	public static function getAllModuleNamesFilter($isEntityType = true, $showRestricted = false, $presence = false): array
	{
		$modules = [];
		foreach (\vtlib\Functions::getAllModules($isEntityType, $showRestricted, $presence) as $value) {
			$modules[$value['name']] = Language::translate($value['name'], $value['name']);
		}
		return $modules;
	}

	/**
	 * Function to get the list of all accessible modules for Quick Create.
	 *
	 * @param bool $restrictList
	 * @param bool $tree
	 *
	 * @return array List of Vtiger_Module_Model instances
	 */
	public static function getQuickCreateModules($restrictList = false, $tree = false): array
	{
		$restrictListString = $restrictList ? 1 : 0;
		if ($tree) {
			$userModel = \App\User::getCurrentUserModel();
			$quickCreateModulesTreeCache = \App\Cache::get('getQuickCreateModules', 'tree' . $restrictListString . $userModel->getDetail('roleid'));
			if (false !== $quickCreateModulesTreeCache) {
				return $quickCreateModulesTreeCache;
			}
		} else {
			$quickCreateModules = \App\Cache::get('getQuickCreateModules', $restrictListString);
			if (false !== $quickCreateModules) {
				return $quickCreateModules;
			}
		}

		$userPrivModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$query = new \App\Db\Query();
		$query->select(['vtiger_tab.*'])->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where(['<>', 'vtiger_tab.presence', 1]);
		if ($tree) {
			$query->andWhere(['<>', 'vtiger_tab.name', 'Users']);
		} else {
			$query->andWhere(['quickcreate' => [0, 2]])
				->andWhere(['<>', 'vtiger_tab.type', 1]);
		}
		if ($restrictList) {
			$query->andWhere(['not in', 'vtiger_tab.name', ['ModComments', 'PriceBooks', 'CallHistory', 'OSSMailView']]);
		}
		$quickCreateModules = [];
		$dataReader = $query->distinct()->createCommand()->query();
		while ($row = $dataReader->read()) {
			if ($userPrivModel->hasModuleActionPermission($row['tabid'], 'CreateView')) {
				$moduleModel = \Vtiger_Module_Model::getInstanceFromArray($row);
				$quickCreateModules[$row['name']] = $moduleModel;
			}
		}
		if ($tree) {
			$menu = \Vtiger_Menu_Model::getAll();
			$quickCreateModulesTree = [];
			foreach ($menu as $parent) {
				if (!empty($parent['childs'])) {
					$items = [];
					foreach ($parent['childs'] as $child) {
						if (isset($quickCreateModules[$child['mod']])) {
							$items[$quickCreateModules[$child['mod']]->name] = $quickCreateModules[$child['mod']];
							unset($quickCreateModules[$child['mod']]);
						}
					}
					if (!empty($items)) {
						$quickCreateModulesTree[] = ['name' => $parent['name'], 'icon' => $parent['icon'], 'modules' => $items];
					}
				}
			}
			if (!empty($quickCreateModules)) {
				$quickCreateModulesTree[] = ['name' => 'LBL_OTHER', 'icon' => 'yfm-Other', 'modules' => $quickCreateModules];
			}
			\App\Cache::save('getQuickCreateModules', 'tree' . $restrictListString . $userPrivModel->get('roleid'), $quickCreateModulesTree);
			return $quickCreateModulesTree;
		}
		\App\Cache::save('getQuickCreateModules', $restrictListString, $quickCreateModules);
		return $quickCreateModules;
	}
}

Module::init();
