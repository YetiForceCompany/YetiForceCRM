<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Import_Map_Model extends \App\Base
{
	public static $tableName = 'vtiger_import_maps';
	public $map;

	public function __construct($map)
	{
		$this->map = $map;
	}

	public static function getInstanceFromDb($row)
	{
		$map = [];
		foreach ($row as $key => $value) {
			if ('content' == $key) {
				$content = [];
				$pairs = explode('&', $value);
				foreach ($pairs as $pair) {
					[$mappedName, $sequence] = explode('=', $pair);
					$mappedName = str_replace('/eq/', '=', $mappedName);
					$mappedName = str_replace('/amp/', '&', $mappedName);
					$content["$mappedName"] = $sequence;
				}
				$map[$key] = $content;
			} else {
				$map[$key] = $value;
			}
		}
		return new self($map);
	}

	public static function markAsDeleted($mapId)
	{
		\App\Db::getInstance()
			->createCommand()
			->update('vtiger_import_maps', ['date_modified' => date('Y-m-d H:i:s'), 'deleted' => 1], ['id' => $mapId])
			->execute();
	}

	public function getId()
	{
		return $this->map['id'];
	}

	public function getAllValues()
	{
		return $this->map;
	}

	public function getValue($key)
	{
		return $this->map[$key];
	}

	public function getStringifiedContent()
	{
		if (empty($this->map['content'])) {
			return;
		}
		$content = $this->map['content'];
		$keyValueStrings = [];
		foreach ($content as $key => $value) {
			$key = str_replace('=', '/eq/', $key);
			$key = str_replace('&', '/amp/', $key);
			$keyValueStrings[] = $key . '=' . $value;
		}
		return implode('&', $keyValueStrings);
	}

	public function save()
	{
		$values = $this->getAllValues();
		$values['content'] = null;
		$values['date_entered'] = date('Y-m-d H:i:s');
		if (\count($values) > 0) {
			$dbCommand = App\Db::getInstance()->createCommand();
			$dbCommand->insert(self::$tableName, $values)->execute();
			$dbCommand->update(self::$tableName, ['content' => $this->getStringifiedContent()], ['name' => $this->getValue('name'), 'module' => $this->getValue('module')])->execute();
		}
	}

	public static function getAllByModule($moduleName)
	{
		$dataReader = (new App\Db\Query())->from(self::$tableName)
			->where(['deleted' => 0, 'module' => $moduleName])
			->createCommand()->query();
		$savedMaps = [];
		while ($row = $dataReader->read()) {
			$importMap = self::getInstanceFromDb($row);
			$savedMaps[$importMap->getId()] = $importMap;
		}
		$dataReader->close();

		return $savedMaps;
	}
}
