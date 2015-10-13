<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
include_once('vtlib/Vtiger/Access.php');
include_once('vtlib/Vtiger/Block.php');
include_once('vtlib/Vtiger/Field.php');
include_once('vtlib/Vtiger/Filter.php');
include_once('vtlib/Vtiger/Profile.php');
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Link.php');
include_once('vtlib/Vtiger/Event.php');
include_once('vtlib/Vtiger/Webservice.php');
include_once('vtlib/Vtiger/Version.php');
require_once 'include/runtime/Cache.php';

/**
 * Provides API to work with vtiger CRM Module
 * @package vtlib
 */
class Vtiger_ModuleBasic
{

	/** ID of this instance */
	var $id = false;
	var $name = false;
	var $label = false;
	var $version = 0;
	var $minversion = false;
	var $maxversion = false;
	var $presence = 0;
	var $ownedby = 0; // 0 - Sharing Access Enabled, 1 - Sharing Access Disabled
	var $tabsequence = false;
	var $parent = false;
	var $customized = 0;
	var $isentitytype = true; // Real module or an extension?
	var $entityidcolumn = false;
	var $entityidfield = false;
	var $basetable = false;
	var $basetableid = false;
	var $customtable = false;
	var $grouptable = false;
	var $type = 0;
	var $tableName;

	const EVENT_MODULE_ENABLED = 'module.enabled';
	const EVENT_MODULE_DISABLED = 'module.disabled';
	const EVENT_MODULE_POSTINSTALL = 'module.postinstall';
	const EVENT_MODULE_PREUNINSTALL = 'module.preuninstall';
	const EVENT_MODULE_PREUPDATE = 'module.preupdate';
	const EVENT_MODULE_POSTUPDATE = 'module.postupdate';

	/**
	 * Constructor
	 */
	function __construct()
	{
		
	}

	/**
	 * Initialize this instance
	 * @access private
	 */
	function initialize($valuemap)
	{
		$this->id = $valuemap['tabid'];
		$this->name = $valuemap['name'];
		$this->label = $valuemap['tablabel'];
		$this->version = $valuemap['version'];

		$this->presence = $valuemap['presence'];
		$this->ownedby = $valuemap['ownedby'];
		$this->tabsequence = $valuemap['tabsequence'];
		$this->parent = $valuemap['parent'];
		$this->customized = $valuemap['customized'];
		$this->type = $valuemap['type'];

		$this->isentitytype = $valuemap['isentitytype'];

		if ($this->isentitytype || $this->name == 'Users') {
			// Initialize other details too
			$this->initialize2();
		}
	}

	/**
	 * Initialize more information of this instance
	 * @access private
	 */
	function initialize2()
	{
		$entitydata = Vtiger_Functions::getEntityModuleInfo($this->name);
		if ($entitydata) {
			$this->basetable = $entitydata['tablename'];
			$this->basetableid = $entitydata['entityidfield'];
		}
	}

	/**
	 * Get unique id for this instance
	 * @access private
	 */
	function __getUniqueId()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->query("SELECT MAX(tabid) AS max_seq FROM vtiger_tab");
		$maxseq = $adb->query_result($result, 0, 'max_seq');
		return ++$maxseq;
	}

	/**
	 * Get next sequence to use for this instance
	 * @access private
	 */
	function __getNextSequence()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT MAX(tabsequence) AS max_tabseq FROM vtiger_tab", array());
		$maxtabseq = $adb->query_result($result, 0, 'max_tabseq');
		return ++$maxtabseq;
	}

	/**
	 * Initialize vtiger schema changes.
	 * @access private
	 */
	function __handleVtigerCoreSchemaChanges()
	{
		// Add version column to the table first
		Vtiger_Utils::AddColumn('vtiger_tab', 'version', ' VARCHAR(10)');
		Vtiger_Utils::AddColumn('vtiger_tab', 'parent', ' VARCHAR(30)');
	}

	/**
	 * Create this module instance
	 * @access private
	 */
	function __create()
	{
		$adb = PearDatabase::getInstance();

		self::log("Creating Module $this->name ... STARTED");

		$this->id = $this->__getUniqueId();
		if (!$this->tabsequence)
			$this->tabsequence = $this->__getNextSequence();
		if (!$this->label)
			$this->label = $this->name;

		$customized = 1; // To indicate this is a Custom Module

		$this->__handleVtigerCoreSchemaChanges();

		$adb->insert('vtiger_tab', [
			'tabid' => $this->id,
			'name' => $this->name,
			'presence' => $this->presence,
			'tabsequence' => -1,
			'tablabel' => $this->label,
			'modifiedby' => NULL,
			'modifiedtime' => NULL,
			'customized' => $customized,
			'ownedby' => $this->ownedby,
			'version' => $this->version,
			'parent' => $this->parent,
			'isentitytype' => $this->isentitytype ? 1 : 0,
			'type' => $this->type,
		]);

		if (!Vtiger_Utils::CheckTable('vtiger_tab_info')) {
			Vtiger_Utils::CreateTable(
				'vtiger_tab_info', '(tabid INT, prefname VARCHAR(256), prefvalue VARCHAR(256), FOREIGN KEY fk_1_vtiger_tab_info(tabid) REFERENCES vtiger_tab(tabid) ON DELETE CASCADE ON UPDATE CASCADE)', true);
		}
		if ($this->minversion) {
			$tabResult = $adb->pquery("SELECT 1 FROM vtiger_tab_info WHERE tabid=? AND prefname='vtiger_min_version'", array($this->id));
			if ($adb->num_rows($tabResult) > 0) {
				$adb->pquery("UPDATE vtiger_tab_info SET prefvalue=? WHERE tabid=? AND prefname='vtiger_min_version'", array($this->minversion, $this->id));
			} else {
				$adb->pquery('INSERT INTO vtiger_tab_info(tabid, prefname, prefvalue) VALUES (?,?,?)', array($this->id, 'vtiger_min_version', $this->minversion));
			}
		}
		if ($this->maxversion) {
			$tabResult = $adb->pquery("SELECT 1 FROM vtiger_tab_info WHERE tabid=? AND prefname='vtiger_max_version'", array($this->id));
			if ($adb->num_rows($tabResult) > 0) {
				$adb->pquery("UPDATE vtiger_tab_info SET prefvalue=? WHERE tabid=? AND prefname='vtiger_max_version'", array($this->maxversion, $this->id));
			} else {
				$adb->pquery('INSERT INTO vtiger_tab_info(tabid, prefname, prefvalue) VALUES (?,?,?)', array($this->id, 'vtiger_max_version', $this->maxversion));
			}
		}

		Vtiger_Profile::initForModule($this);

		self::syncfile();

		if ($this->isentitytype) {
			Vtiger_Access::initSharing($this);
		}

		$moduleInstance = Vtiger_Module::getInstance($this->name);
		$parentTab = $this->parent;
		if (!empty($parentTab)) {
			
		}
		self::log("Creating Module $this->name ... DONE");
	}

	/**
	 * Update this instance
	 * @access private
	 */
	function __update()
	{
		self::log("Updating Module $this->name ... DONE");
	}

	/**
	 * Delete this instance
	 * @access private
	 */
	function __delete()
	{
		Vtiger_Module::fireEvent($this->name, Vtiger_Module::EVENT_MODULE_PREUNINSTALL);

		$adb = PearDatabase::getInstance();
		if ($this->isentitytype) {
			$this->unsetEntityIdentifier();
		}

		$adb->pquery("DELETE FROM vtiger_tab WHERE tabid=?", Array($this->id));
		self::log("Deleting Module $this->name ... DONE");
	}

	/**
	 * Update module version information
	 * @access private
	 */
	function __updateVersion($newversion)
	{
		$this->__handleVtigerCoreSchemaChanges();
		$adb = PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_tab SET version=? WHERE tabid=?', Array($newversion, $this->id));
		$this->version = $newversion;
		self::log("Updating version to $newversion ... DONE");
	}

	/**
	 * Save this instance
	 */
	function save()
	{
		if ($this->id)
			$this->__update();
		else
			$this->__create();
		return $this->id;
	}

	/**
	 * Delete this instance
	 */
	function delete()
	{
		$moduleInstance = Vtiger_Module_Model::getInstance($this->name);
		require_once "modules/$this->name/$this->name.php";
		$focus = new $this->name();
		$this->tableName = $focus->table_name;
		
		if ($this->isentitytype) {
			$this->deleteFromCRMEntity();
			Vtiger_Access::deleteSharing($this);
			Vtiger_Access::deleteTools($this);
			Vtiger_Filter::deleteForModule($this);
			Vtiger_Block::deleteForModule($this);
			if (method_exists($this, 'deinitWebservice')) {
				$this->deinitWebservice();
			}
			
		}
		
		$this->deleteIcons();
		$this->unsetRelatedList($moduleInstance);
		ModComments_Module_Model::deleteForModule($moduleInstance);
		Vtiger_Language::deleteForModule($moduleInstance);
		Vtiger_Access::deleteSharing($moduleInstance);
		$this->deleteFromModentityNum();
		Vtiger_Cron::deleteForModule($moduleInstance);
		Vtiger_Profile::deleteForModule($moduleInstance);
		Settings_Workflows_Module_Model::deleteForModule($moduleInstance);
		Vtiger_Menu::deleteForModule($moduleInstance);
		$this->deleteGroup2Modules();
		$this->deleteModuleTables();
		$this->deleteCRMEntityRel();
		Vtiger_Profile::deleteForModule($this);
		Vtiger_Link::deleteAll($this->id);
		$this->deleteDir($moduleInstance);
		$this->__delete();
		self::syncfile();
	}

	/**
	 * Initialize table required for the module
	 * @param String Base table name (default modulename in lowercase)
	 * @param String Base table column (default modulenameid in lowercase)
	 *
	 * Creates basetable, customtable, grouptable <br>
	 * customtable name is basetable + 'cf'<br>
	 * grouptable name is basetable + 'grouprel'<br>
	 */
	function initTables($basetable = false, $basetableid = false)
	{
		$this->basetable = $basetable;
		$this->basetableid = $basetableid;

		// Initialize tablename and index column names
		$lcasemodname = strtolower($this->name);
		if (!$this->basetable)
			$this->basetable = "vtiger_$lcasemodname";
		if (!$this->basetableid)
			$this->basetableid = $lcasemodname . 'id';

		if (!$this->customtable)
			$this->customtable = $this->basetable . 'cf';

		Vtiger_Utils::CreateTable($this->basetable, "($this->basetableid int(19) PRIMARY KEY, CONSTRAINT `fk_1_$this->basetable` FOREIGN KEY (`$this->basetableid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE)", true);
		Vtiger_Utils::CreateTable($this->customtable, "($this->basetableid int(19) PRIMARY KEY, CONSTRAINT `fk_1_$this->customtable` FOREIGN KEY (`$this->basetableid`) REFERENCES `$this->basetable` (`$this->basetableid`) ON DELETE CASCADE)", true);
		if ($this->type == 1) {
			Vtiger_Utils::CreateTable($this->basetable . '_invfield', "(id int(19) AUTO_INCREMENT PRIMARY KEY, columnname varchar(30) NOT NULL, label varchar(50) NOT NULL, invtype varchar(30) NOT NULL,presence tinyint(1) unsigned NOT NULL DEFAULT '0',
				defaultvalue varchar(255),sequence int(10) unsigned NOT NULL, block tinyint(1) unsigned NOT NULL,displaytype tinyint(1) unsigned NOT NULL DEFAULT '1', params text, colspan tinyint(1) unsigned NOT NULL DEFAULT '1')", true);
			Vtiger_Utils::CreateTable($this->basetable . '_inventory', '(id int(19),seq int(10),KEY id (id),CONSTRAINT `fk_1_' . $this->basetable . '_inventory` FOREIGN KEY (`id`) REFERENCES `' . $this->basetable . '` (`' . $this->basetableid . '`) ON DELETE CASCADE)', true);
			Vtiger_Utils::CreateTable($this->basetable . '_invmap', '(module varchar(50) PRIMARY KEY,field varchar(50),tofield varchar(50))', true);
		}
	}

	/**
	 * Set entity identifier field for this module
	 * @param Vtiger_Field Instance of field to use
	 */
	function setEntityIdentifier($fieldInstance)
	{
		$adb = PearDatabase::getInstance();

		if ($this->basetableid) {
			if (!$this->entityidfield)
				$this->entityidfield = $this->basetableid;
			if (!$this->entityidcolumn)
				$this->entityidcolumn = $this->basetableid;
		}
		if ($this->entityidfield && $this->entityidcolumn) {
			$result = $adb->pquery('SELECT tabid FROM vtiger_entityname WHERE tablename=? AND tabid=?', array($fieldInstance->table, $this->id));
			if ($adb->num_rows($result) == 0) {
				$adb->pquery('INSERT INTO vtiger_entityname(tabid, modulename, tablename, fieldname, entityidfield, entityidcolumn, searchcolumn) VALUES(?,?,?,?,?,?,?)', Array($this->id, $this->name, $fieldInstance->table, $fieldInstance->name, $this->entityidfield, $this->entityidcolumn, $fieldInstance->name));
				self::log('Setting entity identifier ... DONE');
			} else {
				$adb->pquery('UPDATE vtiger_entityname SET fieldname=?,entityidfield=?,entityidcolumn=? WHERE tablename=? AND tabid=?', array($fieldInstance->name, $this->entityidfield, $this->name, $fieldInstance->table, $this->id));
				self::log('Updating entity identifier ... DONE');
			}
		}
	}

	/**
	 * Unset entity identifier information
	 */
	function unsetEntityIdentifier()
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery('DELETE FROM vtiger_entityname WHERE tabid=?', Array($this->id));
		self::log('Unsetting entity identifier ... DONE');
	}




	/**
	 * Configure default sharing access for the module
	 * @param String Permission text should be one of ['Public_ReadWriteDelete', 'Public_ReadOnly', 'Public_ReadWrite', 'Private']
	 */
	function setDefaultSharing($permission_text = 'Public_ReadWriteDelete')
	{
		Vtiger_Access::setDefaultSharing($this, $permission_text);
	}

	/**
	 * Allow module sharing control
	 */
	function allowSharing()
	{
		Vtiger_Access::allowSharing($this, true);
	}

	/**
	 * Disallow module sharing control
	 */
	function disallowSharing()
	{
		Vtiger_Access::allowSharing($this, false);
	}

	/**
	 * Enable tools for this module
	 * @param mixed String or Array with value ['Import', 'Export', 'Merge']
	 */
	function enableTools($tools)
	{
		if (is_string($tools)) {
			$tools = Array(0 => $tools);
		}

		foreach ($tools as $tool) {
			Vtiger_Access::updateTool($this, $tool, true);
		}
	}

	/**
	 * Disable tools for this module
	 * @param mixed String or Array with value ['Import', 'Export', 'Merge']
	 */
	function disableTools($tools)
	{
		if (is_string($tools)) {
			$tools = Array(0 => $tools);
		}
		foreach ($tools as $tool) {
			Vtiger_Access::updateTool($this, $tool, false);
		}
	}

	/**
	 * Add block to this module
	 * @param Vtiger_Block Instance of block to add
	 */
	function addBlock($blockInstance)
	{
		$blockInstance->save($this);
		return $this;
	}

	/**
	 * Add filter to this module
	 * @param Vtiger_Filter Instance of filter to add
	 */
	function addFilter($filterInstance)
	{
		$filterInstance->save($this);
		return $this;
	}

	/**
	 * Get all the fields of the module or block
	 * @param Vtiger_Block Instance of block to use to get fields, false to get all the block fields
	 */
	function getFields($blockInstance = false)
	{
		$fields = false;
		if ($blockInstance)
			$fields = Vtiger_Field::getAllForBlock($blockInstance, $this);
		else
			$fields = Vtiger_Field::getAllForModule($this);
		return $fields;
	}

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delimit = true)
	{
		Vtiger_Utils::Log($message, $delimit);
	}

	/**
	 * Synchronize the menu information to flat file
	 * @access private
	 */
	static function syncfile()
	{
		self::log("Updating tabdata file ... ", false);
		create_tab_data_file();
		self::log("DONE");
	}
	
	/**
	 * Unset related list information that exists with other module
	 */
	public  function unsetRelatedList()
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_relatedlists', 'tabid=? OR related_tabid=?', [$this->id, $this->id]);
		self::log(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove rows in vtiger_group2modules table
	 */
	public function deleteGroup2Modules()
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_group2modules', 'tabid = ?', [$this->id]);
		self::log(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove rows in vtiger_crmentityrel
	 */
	public function deleteCRMEntityRel()
	{
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_crmentityrel', '`module` = ? OR `relmodule` = ?', [$this->name, $this->name]);
	}

	/**
	 * Function to remove rows in vtiger_crmentity, vtiger_crmentityrel
	 */
	public function deleteFromCRMEntity()
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT crmid FROM vtiger_crmentity where setype = ?', [$this->name]);
		while ($id = $db->getSingleValue($result)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->name);
			$recordModel->delete();
		}
		$db->delete('vtiger_crmentity', 'setype = ?', [$this->name]);
		self::log(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove row in vtiger_modentity_num table
	 */
	public function deleteFromModentityNum()
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_modentity_num', 'semodule = ?', [$this->name]);
		self::log(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove tables created by a module
	 */
	public function deleteModuleTables()
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$db->query('SET foreign_key_checks = 0');
		$moduleInstance = Vtiger_Module_Model::getInstance($this->name);
		if ($moduleInstance->isInventory()) {
			$db->query('DROP TABLE IF EXISTS ' . $this->tableName . '_inventory');
			$db->query('DROP TABLE IF EXISTS ' . $this->tableName . '_invfield');
			$db->query('DROP TABLE IF EXISTS ' . $this->tableName . '_invmap');
		}
		$db->query('DROP TABLE IF EXISTS ' . $this->tableName . 'cf');
		$db->query('DROP TABLE IF EXISTS ' . $this->tableName);
		$db->query('SET foreign_key_checks = 1');
		self::log(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove files related to a module
	 * @param  string $path - dir path
	 */
	public function deleteDir($moduleInstance)
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$modulePath = 'modules/' . $moduleInstance->name;
		Vtiger_Functions::recurseDelete($modulePath);
		$layoutPath = 'layouts/vlayout/modules/' . $moduleInstance->name;
		Vtiger_Functions::recurseDelete($layoutPath);
		self::log(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove icons related to a module
	 */
	public function deleteIcons()
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$iconSize = ['', 48, 64, 128];
		foreach ($iconSize as $value) {
			$fileName = "layouts/vlayout/skins/images/" . $this->name . $value . ".png";
			if (file_exists($fileName)) {
				@unlink($fileName);
			}
		}
		self::log(__CLASS__ . '::' . __METHOD__ . ' | End');
	}
}

?>
