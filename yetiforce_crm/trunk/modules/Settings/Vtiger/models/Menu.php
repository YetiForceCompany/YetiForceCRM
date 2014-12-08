<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Settings Menu Model Class
 */
class Settings_Vtiger_Menu_Model extends Vtiger_Base_Model {

	protected static $menusTable = 'vtiger_settings_blocks';
	protected static $menuId = 'blockid';

	/**
	 * Function to get the Id of the Menu Model
	 * @return <Number> - Menu Id
	 */
	public function getId() {
		return $this->get(self::$menuId);
	}

	/**
	 * Function to get the menu label
	 * @return <String> - Menu Label
	 */
	public function getLabel() {
		return $this->get('label');
	}

	/**
	 * Function to get the url to list the items of the Menu
	 * @return <String> - List url
	 */
	public function getListUrl() {
		return 'index.php?module=Vtiger&parent=Settings&view=ListMenu&block='.$this->getId();
	}

	/**
	 * Function to get all the menu items of the current menu
	 * @return <Array> - List of Settings_Vtiger_MenuItem_Model instances
	 */
	public function getItems() {
		return Settings_Vtiger_MenuItem_Model::getAll($this);
	}

	/**
	 * Static function to get the list of all the Settings Menus
	 * @return <Array> - List of Settings_Vtiger_Menu_Model instances
	 */
	public static function getAll() {
		$db = PearDatabase::getInstance();
		$restrictBlock = array('LBL_MODULE_MANAGER');

		$sql = 'SELECT * FROM '.self::$menusTable. ' WHERE label NOT IN ('.generateQuestionMarks($restrictBlock).')
				ORDER BY sequence';
		$params = array($restrictBlock);

		$result = $db->pquery($sql, $params);
		$noOfMenus = $db->num_rows($result);

		$menuModels = array();
		for($i=0; $i<$noOfMenus; ++$i) {
			$blockId = $db->query_result($result, $i, self::$menuId);
			$rowData = $db->query_result_rowdata($result, $i);
			$menuModels[$blockId] = Settings_Vtiger_Menu_Model::getInstanceFromArray($rowData);
		}
		return $menuModels;
	}

	/**
	 * Static Function to get the instance of Settings Menu model with the given value map array
	 * @param <Array> $valueMap
	 * @return Settings_Vtiger_Menu_Model instance
	 */
	public static function getInstanceFromArray($valueMap) {
		return new self($valueMap);
	}

	/**
	 * Static Function to get the instance of Settings Menu model for given menu id
	 * @param <Number> $id - Menu Id
	 * @return Settings_Vtiger_Menu_Model instance
	 */
	public static function getInstanceById($id) {
		$db = PearDatabase::getInstance();

		$sql = 'SELECT * FROM '.self::$menusTable. ' WHERE ' .self::$menuId. ' = ?';
		$params = array($id);

		$result = $db->pquery($sql, $params);

		if($db->num_rows($result) > 0) {
			$rowData = $db->query_result_rowdata($result, 0);
			return Settings_Vtiger_Menu_Model::getInstanceFromArray($rowData);
		}
		return false;
	}

	/**
	 * Static Function to get the instance of Settings Menu model for the given menu name
	 * @param <String> $name - Menu Name
	 * @return Settings_Vtiger_Menu_Model instance
	 */
	public static function getInstance($name) {
		$db = PearDatabase::getInstance();

		$sql = 'SELECT * FROM '.self::$menusTable. ' WHERE label = ?';
		$params = array($name);

		$result = $db->pquery($sql, $params);

		if($db->num_rows($result) > 0) {
			$rowData = $db->query_result_rowdata($result, 0);
			return Settings_Vtiger_Menu_Model::getInstanceFromArray($rowData);
		}
		return false;
	}

	/**
	 * Function returns menu items for the current menu
	 * @return <Settings_Vtiger_MenuItem_Model>
	 */
	public function getMenuItems() {
		return Settings_Vtiger_MenuItem_Model::getAll($this);
	}

}