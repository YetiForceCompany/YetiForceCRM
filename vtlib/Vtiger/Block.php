<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */
namespace vtlib;

/**
 * Provides API to work with vtiger CRM Module Blocks
 * @package vtlib
 */
class Block
{

	/** ID of this block instance */
	public $id;

	/** Label for this block instance */
	public $label;
	public $sequence;
	public $showtitle = 0;
	public $visible = 0;
	public $increateview = 0;
	public $ineditview = 0;
	public $indetailview = 0;
	public $display_status = 1;
	public $iscustom = 0;
	public $module;

	/**
	 * Get unquie id for this instance
	 * @access private
	 */
	public function __getUniqueId()
	{
		$adb = \PearDatabase::getInstance();

		/** Sequence table was added from 5.1.0 */
		$maxblockid = $adb->getUniqueID('vtiger_blocks');
		return $maxblockid;
	}

	/**
	 * Get next sequence value to use for this block instance
	 * @access private
	 */
	public function __getNextSequence()
	{
		$db = \PearDatabase::getInstance();
		$result = $db->pquery('SELECT MAX(sequence) as max_sequence from vtiger_blocks where tabid = ?', [$this->module->id]);
		$maxseq = 0;
		if ($db->num_rows($result)) {
			$maxseq = $db->getSingleValue($result);
		}
		return ++$maxseq;
	}

	/**
	 * Initialize this block instance
	 * @param Array Map of column name and value
	 * @param Module Instance of module to which this block is associated
	 * @access private
	 */
	public function initialize($valuemap, $moduleInstance = false)
	{
		$this->id = isset($valuemap['blockid']) ? $valuemap['blockid'] : null;
		$this->label = isset($valuemap['blocklabel']) ? $valuemap['blocklabel'] : null;
		$this->display_status = isset($valuemap['display_status']) ? $valuemap['display_status'] : null;
		$this->sequence = isset($valuemap['sequence']) ? $valuemap['sequence'] : null;
		$this->iscustom = isset($valuemap['iscustom']) ? $valuemap['iscustom'] : null;
		$tabid = isset($valuemap['tabid']) ? $valuemap['tabid'] : null;
		$this->module = $moduleInstance ? $moduleInstance : Vtiger_Module::getInstance($tabid);
	}

	/**
	 * Create vtiger CRM block
	 * @access private
	 */
	public function __create($moduleInstance)
	{
		$adb = \PearDatabase::getInstance();

		$this->module = $moduleInstance;

		$this->id = $this->__getUniqueId();
		if (!$this->sequence)
			$this->sequence = $this->__getNextSequence();
		if ($this->display_status != 0) {
			$this->display_status = 1;
		}
		$adb->pquery("INSERT INTO vtiger_blocks(blockid,tabid,blocklabel,sequence,show_title,visible,create_view,edit_view,detail_view,display_status,iscustom)
			VALUES(?,?,?,?,?,?,?,?,?,?,?)", Array($this->id, $this->module->id, $this->label, $this->sequence,
			$this->showtitle, $this->visible, $this->increateview, $this->ineditview, $this->indetailview, $this->display_status, $this->iscustom));
		self::log("Creating Block $this->label ... DONE");
		self::log("Module language entry for $this->label ... CHECK");
	}

	public function __update()
	{
		self::log("Updating Block $this->label ... DONE");
	}

	/**
	 * Delete this instance
	 * @access private
	 */
	public function __delete()
	{
		$adb = \PearDatabase::getInstance();
		self::log("Deleting Block $this->label ... ", false);
		$adb->pquery("DELETE FROM vtiger_blocks WHERE blockid=?", Array($this->id));
		self::log("DONE");
	}

	/**
	 * Save this block instance
	 * @param Module Instance of the module to which this block is associated
	 */
	public function save($moduleInstance = false)
	{
		if ($this->id)
			$this->__update();
		else
			$this->__create($moduleInstance);
		return $this->id;
	}

	/**
	 * Delete block instance
	 * @param Boolean True to delete associated fields, False to avoid it
	 */
	public function delete($recursive = true)
	{
		if ($recursive) {
			$fields = Field::getAllForBlock($this);
			foreach ($fields as $fieldInstance)
				$fieldInstance->delete($recursive);
		}
		$this->__delete();
	}

	/**
	 * Add field to this block
	 * @param Field Instance of field to add to this block.
	 * @return Reference to this block instance
	 */
	public function addField($fieldInstance)
	{
		$fieldInstance->save($this);
		return $this;
	}

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim = true)
	{
		Utils::Log($message, $delim);
	}

	/**
	 * Get instance of block
	 * @param mixed block id or block label
	 * @param Module Instance of the module if block label is passed
	 */
	static function getInstance($value, $moduleInstance = false)
	{
		$adb = \PearDatabase::getInstance();
		$cache = \Vtiger_Cache::getInstance();
		if ($moduleInstance && $cache->getBlockInstance($value, $moduleInstance->id)) {
			return $cache->getBlockInstance($value, $moduleInstance->id);
		} else {
			$instance = false;
			$query = false;
			$queryParams = false;
			if (Utils::isNumber($value)) {
				$query = "SELECT * FROM vtiger_blocks WHERE blockid=?";
				$queryParams = Array($value);
			} else {
				$query = "SELECT * FROM vtiger_blocks WHERE blocklabel=? && tabid=?";
				$queryParams = Array($value, $moduleInstance->id);
			}
			$result = $adb->pquery($query, $queryParams);
			if ($adb->num_rows($result)) {
				$instance = new self();
				$instance->initialize($adb->fetch_array($result), $moduleInstance);
				$cache->setBlockInstance($value, $instance->module->id, $instance);
			}
			return $instance;
		}
	}

	/**
	 * Get all block instances associated with the module
	 * @param Module Instance of the module
	 */
	static function getAllForModule($moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		$instances = false;

		$query = "SELECT * FROM vtiger_blocks WHERE tabid=? ORDER BY sequence";
		$queryParams = Array($moduleInstance->id);

		$result = $adb->pquery($query, $queryParams);
		$countResult = $adb->num_rows($result);
		for ($index = 0; $index < $countResult; ++$index) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Delete all blocks associated with module
	 * @param Module Instnace of module to use
	 * @param Boolean true to delete associated fields, false otherwise
	 * @access private
	 */
	static function deleteForModule($moduleInstance, $recursive = true)
	{
		$adb = \PearDatabase::getInstance();
		if ($recursive)
			Field::deleteForModule($moduleInstance);
		$adb->delete('vtiger_module_dashboard_blocks', 'tabid = ?', [$moduleInstance->getId()]);
		$adb->pquery('DELETE vtiger_blocks, vtiger_blocks_hide'
			. ' FROM vtiger_blocks'
			. ' INNER JOIN `vtiger_blocks_hide`'
			. ' ON vtiger_blocks.`blockid` = vtiger_blocks_hide.`blockid`'
			. '  WHERE vtiger_blocks.`tabid` =?', [$moduleInstance->getId()]);
		$adb->delete('vtiger_blocks', 'tabid=?', [$moduleInstance->getId()]);
		self::log("Deleting blocks for module ... DONE");
	}
}
