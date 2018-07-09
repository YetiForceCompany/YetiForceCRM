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
 * Provides API to work with vtiger CRM Module Blocks.
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
	 * Basic table name.
	 *
	 * @var string
	 */
	public static $baseTable = 'vtiger_blocks';

	/**
	 * Get next sequence value to use for this block instance.
	 *
	 * @return int
	 */
	public function __getNextSequence()
	{
		return (new \App\Db\Query())->from(self::$baseTable)->where(['tabid' => $this->module->id])->max('sequence') + 1;
	}

	/**
	 * Initialize this block instance.
	 *
	 * @param array Map of column name and value
	 * @param mixed $module Mixed id or name of the module
	 */
	public function initialize($valuemap, $module = false)
	{
		$this->id = $valuemap['blockid'] ?? null;
		$this->label = $valuemap['blocklabel'] ?? null;
		$this->display_status = $valuemap['display_status'] ?? null;
		$this->sequence = $valuemap['sequence'] ?? null;
		$this->iscustom = $valuemap['iscustom'] ?? null;
		$this->module = Module::getInstance($module ? $module : $valuemap['tabid']);
	}

	/**
	 * Create vtiger CRM block.
	 *
	 * @param \Module $moduleInstance
	 */
	public function __create($moduleInstance)
	{
		$db = \App\Db::getInstance();
		$this->module = $moduleInstance;

		if (!$this->sequence) {
			$this->sequence = $this->__getNextSequence();
		}
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
			'iscustom' => $this->iscustom,
		])->execute();
		$this->id = $db->getLastInsertID(self::$baseTable . '_blockid_seq');
		\App\Log::trace("Creating Block $this->label ... DONE", __METHOD__);
		\App\Log::trace("Module language entry for $this->label ... CHECK", __METHOD__);
	}

	public function __update()
	{
		\App\Log::trace("Updating Block $this->label ... DONE", __METHOD__);
	}

	/**
	 * Delete this instance.
	 */
	public function __delete()
	{
		\App\Log::trace("Deleting Block $this->label ... ", __METHOD__);
		\App\Db::getInstance()->createCommand()->delete(self::$baseTable, ['blockid' => $this->id])->execute();
		\App\Log::trace('DONE', __METHOD__);
	}

	/**
	 * Save this block instance.
	 *
	 * @param Module Instance of the module to which this block is associated
	 */
	public function save($moduleInstance = false)
	{
		if ($this->id) {
			$this->__update();
		} else {
			$this->__create($moduleInstance);
		}
		return $this->id;
	}

	/**
	 * Delete block instance.
	 *
	 * @param bool True to delete associated fields, False to avoid it
	 */
	public function delete($recursive = true)
	{
		if ($recursive) {
			$fields = Field::getAllForBlock($this);
			foreach ($fields as $fieldInstance) {
				$fieldInstance->delete($recursive);
			}
		}
		$this->__delete();
	}

	/**
	 * Add field to this block.
	 *
	 * @param FieldBasic $fieldInstance
	 *
	 * @return Reference to this block instance
	 */
	public function addField(FieldBasic $fieldInstance)
	{
		$fieldInstance->save($this);

		return $this;
	}

	/**
	 * Get instance of block.
	 *
	 * @param int|string block id or block label
	 * @param mixed $module Mixed id or name of the module
	 *
	 * @return \self
	 */
	public static function getInstance($value, $module = false)
	{
		$tabId = is_numeric($module) ? $module : \App\Module::getModuleId($module);
		$cacheName = $value . '|' . $tabId;
		if (\App\Cache::has('BlockInstance', $cacheName)) {
			$data = \App\Cache::get('BlockInstance', $cacheName);
		} else {
			$query = (new \App\Db\Query())->from(self::$baseTable);
			if (Utils::isNumber($value)) {
				$query->where(['blockid' => $value]);
			} else {
				$query->where(['blocklabel' => $value, 'tabid' => $tabId]);
			}
			$data = $query->one();
			\App\Cache::save('BlockInstance', $cacheName, $data);
		}
		$instance = false;
		if ($data) {
			$instance = new self();
			$instance->initialize($data, $tabId);
		}
		return $instance;
	}

	/**
	 * Get all block instances associated with the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 *
	 * @return self
	 */
	public static function getAllForModule(ModuleBasic $moduleInstance)
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
			$instance->initialize($row, $moduleInstance->id);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Delete all blocks associated with module.
	 *
	 * @param ModuleBasic $moduleInstance
	 * @param bool true to delete associated fields, false otherwise
	 */
	public static function deleteForModule(ModuleBasic $moduleInstance, $recursive = true)
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
		\App\Log::trace('Deleting blocks for module ... DONE', __METHOD__);
	}
}
