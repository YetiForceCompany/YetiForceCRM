<?php namespace includes;

/**
 * Record basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Modules
{

	protected static $moduleEntityCacheByName = [];
	protected static $moduleEntityCacheById = [];

	static public function getEntityModuleInfo($mixed = false)
	{
		$entity = false;
		if ($mixed) {
			if (is_numeric($mixed))
				$entity = isset(self::$moduleEntityCacheById[$mixed]) ? self::$moduleEntityCacheById[$mixed] : false;
			else
				$entity = isset(self::$moduleEntityCacheByName[$mixed]) ? self::$moduleEntityCacheByName[$mixed] : false;
		}
		if (!$entity) {
			$adb = \PearDatabase::getInstance();
			$result = $adb->query('SELECT * from vtiger_entityname');
			while ($row = $adb->getRow($result)) { {
					self::$moduleEntityCacheByName[$row['modulename']] = $row;
					self::$moduleEntityCacheById[$row['tabid']] = $row;
				}
			}
			if ($mixed) {
				if (is_numeric($mixed))
					return self::$moduleEntityCacheById[$mixed];
				else
					return self::$moduleEntityCacheByName[$mixed];
			}
		}
		return $entity;
	}

	static public function getAllEntityModuleInfo($sort = false)
	{
		if (empty(self::$moduleEntityCacheById)) {
			self::getEntityModuleInfo();
		}
		$entity = [];
		if ($sort) {
			foreach (self::$moduleEntityCacheById as $tabid => $row) {
				$entity[$row['sequence']] = $row;
			}
			ksort($entity);
		} else {
			$entity = self::$moduleEntityCacheById;
		}
		return $entity;
	}
}
