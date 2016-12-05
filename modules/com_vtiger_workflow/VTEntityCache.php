<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ********************************************************************************** */
require_once 'include/runtime/Cache.php';

class VTWorkflowEntity
{

	function __construct($user, $id)
	{
		$this->moduleName = null;
		$this->id = $id;
		$this->user = $user;
		$data = vtws_retrieve($id, $user);
		foreach ($data as $key => $value) {
			if (is_string($value)) {
				$data[$key] = html_entity_decode($value, ENT_QUOTES, 'utf-8');
			}
		}
		$this->data = $data;
		VTEntityCache::setCachedEntity($id, $this);
	}

	/**
	 * Get the data from the entity object as an array.
	 *
	 * @return An array representation of the module data.
	 */
	function getData()
	{
		return $this->data;
	}

	/**
	 * Get the entity id.
	 *
	 * @return The entity id.
	 */
	function getId()
	{
		return $this->data['id'];
	}

	/**
	 * Get the name of the module represented by the entity data object.
	 *
	 * @return The module name.
	 */
	function getModuleName()
	{
		$cache = Vtiger_Cache::getInstance();

		if ($this->moduleName == null) {
			$adb = PearDatabase::getInstance();
			$wsId = $this->data['id'];
			$parts = explode('x', $wsId);
			if ($cache->getModuleName($parts[0])) {
				$this->moduleName = $cache->getModuleName($parts[0]);
			} else {
				$result = $adb->pquery('select name from vtiger_ws_entity where id=?', array($parts[0]));
				$rowData = $adb->raw_query_result_rowdata($result, 0);
				$this->moduleName = $rowData['name'];
				$cache->setModuleName($parts[0], $this->moduleName);
			}
		}
		return $this->moduleName;
	}

	function get($fieldName)
	{
		return $this->data[$fieldName];
	}

	function set($fieldName, $value)
	{

		$this->data[$fieldName] = $value;
	}

	function save()
	{
		vtws_update($this->data, $this->user);
	}

	function isNew()
	{
		$wsId = $this->data['id'];
		$parts = explode('x', $wsId);
		$recordId = $parts[1];
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->moduleName);
		if ($recordModel->getPreviousValue()) {
			return true;
		} else {
			return false;
		}
	}
}

class VTEntityCache
{

	function __construct($user)
	{
		$this->user = $user;
		$this->cache = [];
	}

	static $_vtWorflow_entity_cache = [];

	function forId($id)
	{
		if (!isset($this->cache[$id])) {
			$entity = VTEntityCache::getCachedEntity($id);
			if (!$entity) {
				$data = new VTWorkflowEntity($this->user, $id);
				$this->cache[$id] = $data;
			} else {
				return $entity;
			}
		}
		return $this->cache[$id];
	}

	public static function getCachedEntity($id)
	{
		if (isset(self::$_vtWorflow_entity_cache[$id])) {
			return self::$_vtWorflow_entity_cache[$id];
		}
		return false;
	}

	public static function setCachedEntity($id, $entity)
	{
		self::$_vtWorflow_entity_cache[$id] = $entity;
	}
}
