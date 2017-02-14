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
	public $id;
	public $name;
	public $tabid = false;
	public $label = false;
	public $table = false;
	public $column = false;
	public $columntype = false;
	public $helpinfo = '';
	public $summaryfield = 0;
	public $header_field = false;
	public $maxlengthtext = 0;
	public $maxwidthcolumn = 0;
	public $masseditable = 1; // Default: Enable massedit for field
	public $uitype = 1;
	public $typeofdata = 'V~O';
	public $displaytype = 1;
	public $generatedtype = 1;
	public $readonly = 1;
	public $presence = 2;
	public $defaultvalue = '';
	public $maximumlength = 100;
	public $sequence = false;
	public $quickcreate = 1;
	public $quicksequence = false;
	public $info_type = 'BAS';
	public $block;
	public $fieldparams = '';

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
	 * Get unique id for this instance
	 * @access private
	 */
	public function __getUniqueId()
	{
		return \App\Db::getInstance()->getUniqueID('vtiger_field');
	}

	/**
	 * Get next sequence id to use within a block for this instance
	 * @access private
	 */
	public function __getNextSequence()
	{
		$maxSeq = (new \App\Db\Query())->from('vtiger_field')
			->where(['tabid' => $this->getModuleId(), 'block' => $this->getBlockId()])
			->max('sequence');
		if ($maxSeq) {
			return $maxSeq + 1;
		}
		return 0;
	}

	/**
	 * Get next quick create sequence id for this instance
	 * @access private
	 */
	public function __getNextQuickCreateSequence()
	{
		$maxSeq = (new \App\Db\Query())->from('vtiger_field')
			->where(['tabid' => $this->getModuleId()])
			->max('quickcreatesequence');
		if ($maxSeq) {
			return $maxSeq + 1;
		}
		return 0;
	}

	/**
	 * Create this field instance
	 * @param vtlib\Block Instance of the block to use
	 * @access private
	 */
	public function __create($blockInstance)
	{
		$db = \App\Db::getInstance();
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
		if (!$this->table) {
			$this->table = $moduleInstance->basetable;
		}
		if (!$this->column) {
			$this->column = strtolower($this->name);
		}
		if (!$this->columntype) {
			$this->columntype = 'string(100)';
		}
		if (!$this->label) {
			$this->label = $this->name;
		}
		$db->createCommand()->insert('vtiger_field', [
			'tabid' => $this->getModuleId(),
			'fieldid' => $this->id,
			'columnname' => $this->column,
			'tablename' => $this->table,
			'generatedtype' => intval($this->generatedtype),
			'uitype' => $this->uitype,
			'fieldname' => $this->name,
			'fieldlabel' => $this->label,
			'readonly' => $this->readonly,
			'presence' => $this->presence,
			'defaultvalue' => $this->defaultvalue,
			'maximumlength' => $this->maximumlength,
			'sequence' => $this->sequence,
			'block' => $this->getBlockId(),
			'displaytype' => $this->displaytype,
			'typeofdata' => $this->typeofdata,
			'quickcreate' => intval($this->quickcreate),
			'quickcreatesequence' => intval($this->quicksequence),
			'info_type' => $this->info_type,
			'helpinfo' => $this->helpinfo,
			'summaryfield' => intval($this->summaryfield),
			'fieldparams' => $this->fieldparams,
			'masseditable' => $this->masseditable,
		])->execute();
		Profile::initForField($this);
		if (!empty($this->columntype)) {
			Utils::AddColumn($this->table, $this->column, $this->columntype);
			if ($this->uitype === 10) {
				$db->createCommand()->createIndex("{$this->table}_{$this->column}_idx", $this->table, $this->column)->execute();
			}
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
		Profile::deleteForField($this);
		\App\Db::getInstance()->createCommand()->delete('vtiger_field', ['fieldid' => $this->id])->execute();
		if ($this->uitype === 10) {
			\App\Db::getInstance()->createCommand()->delete('vtiger_fieldmodulerel', ['fieldid' => $this->id])->execute();
		}
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
			return \App\Module::getModuleName($this->tabid);
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
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['helpinfo' => $helptext], ['fieldid' => $this->id])
			->execute();
		self::log("Updated help information of $this->name ... DONE");
	}

	/**
	 * Set Masseditable information for this instance.
	 * @param Integer Masseditable value
	 */
	public function setMassEditable($value)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['masseditable' => $value], ['fieldid' => $this->id])
			->execute();
		self::log("Updated masseditable information of $this->name ... DONE");
	}

	/**
	 * Set Summaryfield information for this instance. 
	 * @param Integer Summaryfield value 
	 */
	public function setSummaryField($value)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['summaryfield' => $value], ['fieldid' => $this->id])
			->execute();
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

	/**
	 * Get block name
	 * @return string
	 */
	public function getBlockName()
	{
		if ($this->block) {
			return $this->block->label;
		}
		return '';
	}
}
