<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
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
	 * Basic table name
	 * @var string 
	 */
	public static $baseTable = 'vtiger_blocks';

	/**
	 * Get next sequence value to use for this block instance
	 * @return int
	 */
	public function __getNextSequence()
	{
		return (new \App\Db\Query())->from(self::$baseTable)->where(['tabid' => $this->module->id])->max('sequence') + 1;
	}

	/**
	 * Initialize this block instance
	 * @param array Map of column name and value
	 * @param \Module Module Instance of module to which this block is associated
	 */
	public function initialize($valuemap, $moduleInstance = false)
	{
		$this->id = isset($valuemap['blockid']) ? $valuemap['blockid'] : null;
		$this->label = isset($valuemap['blocklabel']) ? $valuemap['blocklabel'] : null;
		$this->display_status = isset($valuemap['display_status']) ? $valuemap['display_status'] : null;
		$this->sequence = isset($valuemap['sequence']) ? $valuemap['sequence'] : null;
		$this->iscustom = isset($valuemap['iscustom']) ? $valuemap['iscustom'] : null;
		$tabid = isset($valuemap['tabid']) ? $valuemap['tabid'] : null;
		$this->module = $moduleInstance ? $moduleInstance : Module::getInstance($tabid);
	}

	/**
	 * Create vtiger CRM block
	 * @param \Module $moduleInstance
	 */
	public function __create($moduleInstance)
	{
		$db = \App\Db::getInstance();
		$this->module = $moduleInstance;

		if (!$this->sequence)
			$this->sequence = $this->__getNextSequence();
		if ($this->display_status != 0) {
			$this->display_status = 1;
		}
		$db->createCommand()->insert(self::$baseTable, [
			'tabid' => $this->module->id,
			'blocklabel' => $this->label,
			'sequence' => $this->sequence,
			'show_title' => $this->showtitle,
			'visible' => $this->visible,
			'create_view' => $this->increateview,
			'edit_view' => $this->ineditview,
			'detail_view' => $this->indetailview,
			'display_status' => $this->display_status,
			'iscustom' => $this->iscustom
		])->execute();
		$this->id = $db->getLastInsertID(self::$baseTable . '_blockid_seq');
		self::log("Creating Block $this->label ... DONE");
		self::log("Module language entry for $this->label ... CHECK");
	}

	public function __update()
	{
		self::log("Updating Block $this->label ... DONE");
	}

	/**
	 * Delete this instance
	 */
	public function __delete()
	{
		self::log("Deleting Block $this->label ... ", false);
		\App\Db::getInstance()->createCommand()->delete(self::$baseTable, ['blockid' => $this->id])->execute();
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
	public static function log($message, $delim = true)
	{
		Utils::Log($message, $delim);
	}

	/**
	 * Get instance of block
	 * @param int|string block id or block label
	 * @param \Module Module Instance of the module if block label is passed
	 * @return \self
	 */
	public static function getInstance($value, $moduleInstance = false)
	{
		if (\App\Cache::has('BlockInstance', $value)) {
			$data = \App\Cache::get('BlockInstance', $value);
		} else {
			$query = (new \App\Db\Query())->from(self::$baseTable);
			if (Utils::isNumber($value)) {
				$query->where(['blockid' => $value]);
			} else {
				$query->where(['blocklabel' => $value, 'tabid' => $moduleInstance->id]);
			}
			$data = $query->one();
			\App\Cache::save('BlockInstance', $value, $data);
		}
		$instance = false;
		if ($data) {
			$instance = new self();
			$instance->initialize($data, $moduleInstance);
		}
		return $instance;
	}

	/**
	 * Get all block instances associated with the module
	 * @param \Module Module Instance of the module
	 */
	public static function getAllForModule($moduleInstance)
	{
		if (\App\Cache::has('BlocksForModule', $moduleInstance->id)) {
			$blocks = \App\Cache::get('BlocksForModule', $moduleInstance->id);
		} else {
			$blocks = (new \App\Db\Query())->from(self::$baseTable)
				->where(['tabid' => $moduleInstance->id])
				->orderBy(['sequence' => SORT_ASC])
				->all();
			\App\Cache::save('BlocksForModule', $moduleInstance->id, $blocks);
		}
		$instances = false;
		foreach ($blocks as $row) {
			$instance = new self();
			$instance->initialize($row, $moduleInstance);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Delete all blocks associated with module
	 * @param \Module Module Instnace of module to use
	 * @param boolean true to delete associated fields, false otherwise
	 */
	public static function deleteForModule($moduleInstance, $recursive = true)
	{
		$db = \App\Db::getInstance();
		if ($recursive) {
			Field::deleteForModule($moduleInstance);
		}
		$tabId = $moduleInstance->getId();
		$db->createCommand()->delete('vtiger_module_dashboard_blocks', ['tabid' => $tabId])->execute();
		$query = (new \App\Db\Query())->select(['blockid'])->from(self::$baseTable)->where(['tabid' => $tabId]);
		$db->createCommand()->delete('vtiger_blocks_hide', ['blockid' => $query])->execute();
		$db->createCommand()->delete(self::$baseTable, ['tabid' => $tabId])->execute();
		self::log("Deleting blocks for module ... DONE");
	}
}
