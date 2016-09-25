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
namespace vtlib;

/**
 * Provides basic API to work with vtiger CRM Fields
 * @package vtlib
 */
class FieldBasic
{

	/** ID of this field instance */
	var $id;
	var $name;
	var $tabid = false;
	var $label = false;
	var $table = false;
	var $column = false;
	var $columntype = false;
	var $helpinfo = '';
	var $summaryfield = 0;
	var $header_field = false;
	var $maxlengthtext = 0;
	var $maxwidthcolumn = 0;
	var $masseditable = 1; // Default: Enable massedit for field
	var $uitype = 1;
	var $typeofdata = 'V~O';
	var $displaytype = 1;
	var $generatedtype = 1;
	var $readonly = 1;
	var $presence = 2;
	var $defaultvalue = '';
	var $maximumlength = 100;
	var $sequence = false;
	var $quickcreate = 1;
	var $quicksequence = false;
	var $info_type = 'BAS';
	var $block;
	var $fieldparams = '';

	/**
	 * Initialize this instance
	 * @param Array 
	 * @param Module Instance of module to which this field belongs
	 * @param vtlib\Block Instance of block to which this field belongs
	 * @access private
	 */
	public function initialize($valuemap, $moduleInstance = false, $blockInstance = false)
	{
		$this->id = $valuemap['fieldid'];
		$this->tabid = $valuemap['tabid'];
		$this->name = $valuemap['fieldname'];
		$this->label = $valuemap['fieldlabel'];
		$this->column = $valuemap['columnname'];
		$this->table = $valuemap['tablename'];
		$this->uitype = $valuemap['uitype'];
		$this->typeofdata = $valuemap['typeofdata'];
		$this->helpinfo = $valuemap['helpinfo'];
		$this->masseditable = $valuemap['masseditable'];
		$this->header_field = $valuemap['header_field'];
		$this->maxlengthtext = $valuemap['maxlengthtext'];
		$this->maxwidthcolumn = $valuemap['maxwidthcolumn'];
		$this->displaytype = $valuemap['displaytype'];
		$this->generatedtype = $valuemap['generatedtype'];
		$this->readonly = $valuemap['readonly'];
		$this->presence = $valuemap['presence'];
		$this->defaultvalue = $valuemap['defaultvalue'];
		$this->quickcreate = $valuemap['quickcreate'];
		$this->sequence = $valuemap['sequence'];
		$this->quicksequence = $valuemap['quickcreatesequence'];
		$this->summaryfield = $valuemap['summaryfield'];
		$this->fieldparams = $valuemap['fieldparams'];
		$this->block = $blockInstance ? $blockInstance : Block::getInstance($valuemap['block'], $moduleInstance);
	}

	/** Cache (Record) the schema changes to improve performance */
	static $__cacheSchemaChanges = [];

	/**
	 * Initialize vtiger schema changes.
	 * @access private
	 */
	public function __handleVtigerCoreSchemaChanges()
	{
// Add helpinfo column to the vtiger_field table
		if (empty(self::$__cacheSchemaChanges['vtiger_field.helpinfo'])) {
			Utils::AddColumn('vtiger_field', 'helpinfo', ' TEXT');
			self::$__cacheSchemaChanges['vtiger_field.helpinfo'] = true;
		}
		if (empty(self::$__cacheSchemaChanges['vtiger_field.summaryfield'])) {
			Utils::AddColumn('vtiger_field', 'summaryfield', ' INT(10) NOT NULL DEFAULT 0');
			self::$__cacheSchemaChanges['vtiger_field.summaryfield'] = 0;
		}
	}

	/**
	 * Get unique id for this instance
	 * @access private
	 */
	public function __getUniqueId()
	{
		$adb = \PearDatabase::getInstance();
		return $adb->getUniqueID('vtiger_field');
	}

	/**
	 * Get next sequence id to use within a block for this instance
	 * @access private
	 */
	public function __getNextSequence()
	{
		$db = \PearDatabase::getInstance();
		$result = $db->pquery("SELECT MAX(sequence) AS max_seq FROM vtiger_field WHERE tabid=? && block=?", Array($this->getModuleId(), $this->getBlockId()));
		$maxseq = 0;
		if ($result && $db->num_rows($result)) {
			$maxseq = $db->getSingleValue($result);
			$maxseq += 1;
		}
		return $maxseq;
	}

	/**
	 * Get next quick create sequence id for this instance
	 * @access private
	 */
	public function __getNextQuickCreateSequence()
	{
		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery("SELECT MAX(quickcreatesequence) AS max_quickcreateseq FROM vtiger_field WHERE tabid=?", Array($this->getModuleId()));
		$max_quickcreateseq = 0;
		if ($result && $adb->num_rows($result)) {
			$max_quickcreateseq = $adb->query_result($result, 0, 'max_quickcreateseq');
			$max_quickcreateseq += 1;
		}
		return $max_quickcreateseq;
	}

	/**
	 * Create this field instance
	 * @param vtlib\Block Instance of the block to use
	 * @access private
	 */
	public function __create($blockInstance)
	{
		$this->__handleVtigerCoreSchemaChanges();

		$adb = \PearDatabase::getInstance();

		$this->block = $blockInstance;

		$moduleInstance = $this->getModuleInstance();

		$this->id = $this->__getUniqueId();

		if (!$this->sequence) {
			$this->sequence = $this->__getNextSequence();
		}

		if ($this->quickcreate != 1) { // If enabled for display
			if (!$this->quicksequence) {
				$this->quicksequence = $this->__getNextQuickCreateSequence();
			}
		} else {
			$this->quicksequence = null;
		}

// Initialize other variables which are not done
		if (!$this->table)
			$this->table = $moduleInstance->basetable;
		if (!$this->column) {
			$this->column = strtolower($this->name);
			if (!$this->columntype)
				$this->columntype = 'VARCHAR(100)';
		}

		if (!$this->label)
			$this->label = $this->name;

		$adb->pquery('INSERT INTO vtiger_field (tabid, fieldid, columnname, tablename, generatedtype,
uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence,
block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type, helpinfo, summaryfield, fieldparams) 
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', Array($this->getModuleId(), $this->id, $this->column, $this->table, $this->generatedtype,
			$this->uitype, $this->name, $this->label, $this->readonly, $this->presence, $this->defaultvalue,
			$this->maximumlength, $this->sequence, $this->getBlockId(), $this->displaytype, $this->typeofdata,
			$this->quickcreate, $this->quicksequence, $this->info_type, $this->helpinfo, $this->summaryfield, $this->fieldparams));

// Set the field status for mass-edit (if set)
		$adb->pquery('UPDATE vtiger_field SET masseditable=? WHERE fieldid=?', Array($this->masseditable, $this->id));

		Profile::initForField($this);

		if (!empty($this->columntype)) {
			$columntype = $this->columntype;
			if ($this->uitype == 10)
				$columntype .= ', ADD INDEX (`' . $this->column . '`)';
			Utils::AddColumn($this->table, $this->column, $columntype);
		}

		self::log("Creating field $this->name ... DONE");
	}

	public function __update()
	{
		self::log("Updating Field $this->name ... DONE");
	}

	/**
	 * Delete this field instance
	 * @access private
	 */
	public function __delete()
	{
		$adb = \PearDatabase::getInstance();

		Profile::deleteForField($this);

		$adb->pquery("DELETE FROM vtiger_field WHERE fieldid=?", Array($this->id));
		self::log("Deleteing Field $this->name ... DONE");
	}

	/**
	 * Get block id to which this field instance is associated
	 */
	public function getBlockId()
	{
		return $this->block->id;
	}

	/**
	 * Get module id to which this field instance is associated
	 */
	public function getModuleId()
	{
		if ($this->tabid) {
			return $this->tabid;
		}
		return $this->block->module->id;
	}

	/**
	 * Get module name to which this field instance is associated
	 */
	public function getModuleName()
	{
		if ($this->tabid) {
			return Functions::getModuleName($this->tabid);
		}
		return $this->block->module->name;
	}

	/**
	 * Get module instance to which this field instance is associated
	 */
	public function getModuleInstance()
	{
		return $this->block->module;
	}

	/**
	 * Save this field instance
	 * @param vtlib\Block Instance of block to which this field should be added.
	 */
	public function save($blockInstance = false)
	{
		if ($this->id)
			$this->__update();
		else
			$this->__create($blockInstance);
		return $this->id;
	}

	/**
	 * Delete this field instance
	 */
	public function delete()
	{
		$this->__delete();
	}

	/**
	 * Set Help Information for this instance.
	 * @param String Help text (content)
	 */
	public function setHelpInfo($helptext)
	{
// Make sure to initialize the core tables first
		$this->__handleVtigerCoreSchemaChanges();

		$adb = \PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_field SET helpinfo=? WHERE fieldid=?', Array($helptext, $this->id));
		self::log("Updated help information of $this->name ... DONE");
	}

	/**
	 * Set Masseditable information for this instance.
	 * @param Integer Masseditable value
	 */
	public function setMassEditable($value)
	{
		$adb = \PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_field SET masseditable=? WHERE fieldid=?', Array($value, $this->id));
		self::log("Updated masseditable information of $this->name ... DONE");
	}

	/**
	 * Set Summaryfield information for this instance. 
	 * @param Integer Summaryfield value 
	 */
	public function setSummaryField($value)
	{
		$adb = \PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_field SET summaryfield=? WHERE fieldid=?', Array($value, $this->id));
		self::log("Updated summaryfield information of $this->name ... DONE");
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
}
