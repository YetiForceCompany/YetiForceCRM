<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Import_Map_Model extends Vtiger_Base_Model {

	static $tableName = 'vtiger_import_maps';
	var $map;
	var $user;

	public function  __construct($map, $user) {
		$this->map = $map;
		$this->user = $user;
	}

	public static function getInstanceFromDb($row, $user) {
		$map = array();
		foreach($row as $key=>$value) {
			if($key == 'content') {
				$content = array();
				$pairs = explode("&", $value);
				foreach($pairs as $pair) {
					list($mappedName, $sequence) = explode("=", $pair);
					$mappedName = str_replace('/eq/', '=', $mappedName);
					$mappedName = str_replace('/amp/', '&', $mappedName);
					$content["$mappedName"] = $sequence;
				}
				$map[$key] = $content;

			} else {
				$map[$key] = $value;
			}
		}
		return new Import_Map_Model($map, $user);
	}

	public static function markAsDeleted($mapId) {
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE vtiger_import_maps SET deleted=1 WHERE id=?', array($mapId));
	}

	public function getId() {
		$map = $this->map;
		return $map['id'];
	}

	public function getAllValues() {
		return $this->map;
	}

	public function getValue($key) {
		$map = $this->map;
		return $map[$key];
	}

	public function getStringifiedContent() {
		if(empty($this->map['content'])) return;
		$content = $this->map['content'];
		$keyValueStrings = array();
		foreach($content as $key => $value) {
			$key = str_replace('=', '/eq/', $key);
			$key = str_replace('&', '/amp/', $key);
			$keyValueStrings[] = $key.'='.$value;
		}
		$stringifiedContent = implode('&', $keyValueStrings);
		return $stringifiedContent;
	}

	public function save() {
		$db = PearDatabase::getInstance();

		$map = $this->getAllValues();
		$map['content'] = "".$db->getEmptyBlob()."";
		$columnNames = array_keys($map);
		$columnValues = array_values($map);
		if(count($map) > 0) {
			$db->pquery('INSERT INTO '.self::$tableName.' ('. implode(',',$columnNames).') VALUES ('. generateQuestionMarks($columnValues).')', array($columnValues));
			$db->updateBlob(self::$tableName,"content","name='". $db->sql_escape_string($this->getValue('name')).
						"' AND module='".$db->sql_escape_string($this->getValue('module'))."'",$this->getStringifiedContent());
		}
	}

	public static function getAllByModule($moduleName) {
		global $current_user;
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT * FROM '.self::$tableName.' WHERE deleted=0 AND module=?', array($moduleName));
		$noOfMaps = $db->num_rows($result);

		$savedMaps = array();
		for($i=0; $i<$noOfMaps; ++$i) {
			$importMap = Import_Map_Model::getInstanceFromDb($db->query_result_rowdata($result, $i), $current_user);
			$savedMaps[$importMap->getId()] = $importMap;
		}

		return $savedMaps;
	}

}