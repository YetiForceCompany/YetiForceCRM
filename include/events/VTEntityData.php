<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */

/**
 * A wrapper around CRMEntity instances
 */
class VTEntityData
{

	public static $entityData = [];
	private $isNew = false;

	/**
	 * Get an entity data object.
	 *
	 * @param $adb Pear database instance.
	 * @param $entityId The id of the entity to load.
	 * @return The new entity data object.
	 */
	static function fromEntityId($adb, $entityId, $moduleName = '', $reload = true)
	{

		if (empty($moduleName)) {
			$result = $adb->pquery('select setype from vtiger_crmentity where crmid=?', array($entityId));
			$setype = $adb->getSingleValue($result);
			if ($setype === 'Calendar') {
				$setype = vtws_getCalendarEntityType($entityId);
			}
			$moduleName = $setype;
		}
		if ($reload && isset(self::$entityData[$moduleName][$entityId])) {
			return self::$entityData[$moduleName][$entityId];
		}
		$obj = new VTEntityData();
		$obj->entityId = $entityId;
		$obj->moduleName = $moduleName;

		$focus = CRMEntity::getInstance($moduleName);
		$focus->retrieve_entity_info($entityId, $moduleName);
		$focus->id = $entityId;
		$obj->isNew = false;
		$obj->focus = $focus;
		if ($reload) {
			self::$entityData[$moduleName][$entityId] = $obj;
		}
		return $obj;
	}

	/**
	 * Get an entity data object.
	 * @param $adb Pear database instance.
	 * @param $userId The id of the entity to load.
	 * @return The new entity data object.
	 */
	static function fromUserId($adb, $userId)
	{
		$obj = new VTEntityData();
		$obj->entityId = $userId;

		$obj->moduleName = 'Users';

		require_once('include/CRMEntity.php');
		$focus = CRMEntity::getInstance($obj->moduleName);

		$focus->retrieve_entity_info($userId, $obj->moduleName);
		$focus->id = $userId;
		$obj->isNew = false;
		$obj->focus = $focus;
		return $obj;
	}

	/**
	 * Get an entity data object from a crmentity object
	 *
	 * @param $crmEntity The CRMEntity instance.
	 * @return The new entity data object.
	 */
	static function fromCRMEntity($crmEntity)
	{
		$obj = new VTEntityData();
		$obj->entityId = $crmEntity->id;
		$obj->moduleName = $crmEntity->moduleName;
		$obj->focus = $crmEntity;
		$obj->isNew = isset($crmEntity->mode) ? $crmEntity->mode != 'edit' : true;
		return $obj;
	}

	/**
	 * Get the data from the entity object as an array.
	 *
	 * @return An array representation of the module data.
	 */
	function getData()
	{
		return $this->focus->column_fields;
	}

	/**
	 * Get the entity id.
	 *
	 * @return The entity id.
	 */
	function getId()
	{
		return $this->focus->id;
	}

	/**
	 * Get the name of the module represented by the entity data object.
	 *
	 * @return The module name.
	 */
	function getModuleName()
	{
		$className = get_class($this->focus);
		$importModuleMapping = Array(
			"ImportLead" => "Leads",
			"ImportAccount" => "Accounts",
			"ImportContact" => "Contacts",
			"ImportProduct" => "Products",
			"ImportTicket" => "HelpDesk",
			"ImportVendors" => "Vendors"
		);
		$moduleName = $className;
		if (array_key_exists($className, $importModuleMapping)) {
			$moduleName = $importModuleMapping[$className];
		}

		if ($className == 'Activity') {
			$id = $this->getId();
			if ($id != null || $id != '') {
				$moduleName = vtws_getCalendarEntityType($id);
			}
		}
		return $moduleName;
	}

	function get($fieldName)
	{
		return $this->focus->column_fields[$fieldName];
	}

	function set($fieldName, $value)
	{
		$this->focus->column_fields[$fieldName] = $value;
	}

	/**
	 * Check whether the object is stored on the database.
	 * 
	 * @return True if the object is saved false otherwiser.
	 */
	function isSaved()
	{
		return isset($this->focus->id);
	}

	/**
	 * Check wether the obkect is new.
	 * 
	 * @return True if the object is new, false otherwise.
	 */
	function isNew()
	{
		return $this->isNew;
	}
}
