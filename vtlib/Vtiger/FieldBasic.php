<?php

 /* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

namespace vtlib;

/**
 * Provides basic API to work with vtiger CRM Fields.
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
	public $tabindex = 0;
	public $masseditable = 1; // Default: Enable massedit for field
	public $uitype = 1;
	public $typeofdata = 'V~O';
	public $displaytype = 1;
	public $generatedtype = 1;
	public $readonly = 0;
	public $visible = 0;
	public $presence = 2;
	public $defaultvalue = '';
	public $maximumlength;
	public $sequence = false;
	public $quickcreate = 1;
	public $quicksequence = false;
	public $info_type = 'BAS';
	public $block;
	public $fieldparams = '';
	public $color = '';
	public $icon = '';
	/**
	 * @var string[] Anonymization targets form field ex. logs.
	 */
	public $anonymizationTarget = [];

	/**
	 * Initialize this instance.
	 *
	 * @param array        $valuemap
	 * @param mixed        $module        Mixed id or name of the module
	 * @param \vtlib\Block $blockInstance Instance of block to which this field belongs
	 */
	public function initialize($valuemap, $module = false, $blockInstance = false)
	{
		$this->id = (int) $valuemap['fieldid'];
		$this->tabid = (int) $valuemap['tabid'];
		$this->name = $valuemap['fieldname'];
		$this->label = $valuemap['fieldlabel'];
		$this->column = $valuemap['columnname'];
		$this->table = $valuemap['tablename'];
		$this->uitype = (int) $valuemap['uitype'];
		$this->typeofdata = $valuemap['typeofdata'];
		$this->helpinfo = $valuemap['helpinfo'];
		$this->masseditable = (int) $valuemap['masseditable'];
		$this->header_field = $valuemap['header_field'];
		$this->maximumlength = $valuemap['maximumlength'];
		$this->maxlengthtext = (int) $valuemap['maxlengthtext'];
		$this->maxwidthcolumn = (int) $valuemap['maxwidthcolumn'];
		$this->tabindex = (int) $valuemap['tabindex'];
		$this->displaytype = (int) $valuemap['displaytype'];
		$this->generatedtype = (int) $valuemap['generatedtype'];
		$this->readonly = (int) $valuemap['readonly'];
		$this->presence = (int) $valuemap['presence'];
		$this->defaultvalue = $valuemap['defaultvalue'];
		$this->quickcreate = (int) $valuemap['quickcreate'];
		$this->sequence = (int) $valuemap['sequence'];
		$this->quicksequence = (int) $valuemap['quickcreatesequence'];
		$this->summaryfield = (int) $valuemap['summaryfield'];
		$this->fieldparams = $valuemap['fieldparams'];
		$this->visible = (int) $valuemap['visible'];
		$this->color = $valuemap['color'];
		$this->icon = $valuemap['icon'];
		$this->block = $blockInstance ?: Block::getInstance($valuemap['block'], $module);
		if (!empty($valuemap['anonymization_target'])) {
			$this->anonymizationTarget = \App\Json::decode($valuemap['anonymization_target']);
		}
	}

	/** Cache (Record) the schema changes to improve performance */
	public static $__cacheSchemaChanges = [];

	/**
	 * Get next sequence id to use within a block for this instance.
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
	 * Get next quick create sequence id for this instance.
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
	 * Create this field instance.
	 *
	 * @param vtlib\Block Instance of the block to use
	 * @param mixed $blockInstance
	 */
	public function __create($blockInstance)
	{
		$db = \App\Db::getInstance();
		$this->block = $blockInstance;
		$moduleInstance = $this->getModuleInstance();
		if (!$this->sequence) {
			$this->sequence = $this->__getNextSequence();
		}
		if (1 != $this->quickcreate) { // If enabled for display
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
		if (!empty($this->columntype)) {
			Utils::addColumn($this->table, $this->column, $this->columntype);
			if (10 === $this->uitype) {
				$nameIndex = "{$this->table}_{$this->column}_idx";
				$indexes = $db->getSchema()->getTableIndexes($this->table, true);
				$isCreateIndex = true;
				foreach ($indexes as $indexObject) {
					if ($indexObject->name === $nameIndex) {
						$isCreateIndex = false;
						break;
					}
				}
				if ($isCreateIndex) {
					$db->createCommand()->createIndex("{$this->table}_{$this->column}_idx", $this->table, $this->column)->execute();
				}
			}
		}
		if (!$this->maximumlength && method_exists($this, 'getRangeValues')) {
			$this->maximumlength = $this->getRangeValues();
		}
		$db->createCommand()->insert('vtiger_field', [
			'tabid' => $this->getModuleId(),
			'columnname' => $this->column,
			'tablename' => $this->table,
			'generatedtype' => (int) ($this->generatedtype),
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
			'quickcreate' => (int) ($this->quickcreate),
			'quickcreatesequence' => (int) ($this->quicksequence),
			'info_type' => $this->info_type,
			'helpinfo' => $this->helpinfo,
			'summaryfield' => (int) ($this->summaryfield),
			'fieldparams' => $this->fieldparams,
			'masseditable' => $this->masseditable,
			'visible' => $this->visible,
			'icon' => $this->icon,
		])->execute();
		$this->id = (int) $db->getLastInsertID('vtiger_field_fieldid_seq');
		Profile::initForField($this);
		$this->clearCache();
		\App\Log::trace("Creating field $this->name ... DONE", __METHOD__);
	}

	public function __update()
	{
		$this->clearCache();
		\App\Log::trace("Updating Field $this->name ... DONE", __METHOD__);
	}

	/**
	 * Delete this field instance.
	 */
	public function __delete()
	{
		Profile::deleteForField($this);
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('vtiger_field', ['fieldid' => $this->id])->execute();
		if (10 === $this->uitype) {
			$db->createCommand()->delete('vtiger_fieldmodulerel', ['fieldid' => $this->id])->execute();
			$nameIndex = "{$this->table}_{$this->column}_idx";
			$indexes = $db->getSchema()->getTableIndexes($this->table, true);
			foreach ($indexes as $indexObject) {
				if ($indexObject->name === $nameIndex) {
					$db->createCommand()->dropIndex($nameIndex, $this->table)->execute();
					break;
				}
			}
		}
		$this->clearCache();
		\App\Log::trace("Deleteing Field $this->name ... DONE", __METHOD__);
	}

	/**
	 * Get block id to which this field instance is associated.
	 */
	public function getBlockId()
	{
		return $this->block->id;
	}

	/**
	 * Get module id to which this field instance is associated.
	 */
	public function getModuleId()
	{
		if ($this->tabid) {
			return $this->tabid;
		}
		if (!empty($this->block)) {
			return $this->block->tabid;
		}
		return false;
	}

	/**
	 * Get module name to which this field instance is associated.
	 *
	 * @return bool|string
	 */
	public function getModuleName()
	{
		$moduleName = '';
		if ($this->tabid) {
			$moduleName = \App\Module::getModuleName($this->tabid);
		} elseif (!empty($this->block) && \is_object($this->block)) {
			$moduleName = $this->block->module->name;
		} elseif (!empty($this->module)) {
			$moduleName = $this->module->getName();
		}
		return $moduleName;
	}

	/**
	 * Get module instance to which this field instance is associated.
	 *
	 * @return mixed
	 */
	public function getModuleInstance()
	{
		return $this->block->module;
	}

	/**
	 * Save this field instance.
	 *
	 * @param vtlib\Block Instance of block to which this field should be added
	 * @param mixed $blockInstance
	 */
	public function save($blockInstance = false)
	{
		if ($this->id) {
			$this->__update();
		} else {
			$this->__create($blockInstance);
		}
		return $this->id;
	}

	/**
	 * Delete this field instance.
	 */
	public function delete()
	{
		$this->__delete();
	}

	/**
	 * Set Help Information for this instance.
	 *
	 * @param string Help text (content)
	 * @param mixed $helptext
	 */
	public function setHelpInfo($helptext)
	{
		// Make sure to initialize the core tables first
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['helpinfo' => $helptext], ['fieldid' => $this->id])
			->execute();
		\App\Log::trace("Updated help information of $this->name ... DONE", __METHOD__);
	}

	/**
	 * Set Masseditable information for this instance.
	 *
	 * @param int Masseditable value
	 * @param mixed $value
	 */
	public function setMassEditable($value)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['masseditable' => $value], ['fieldid' => $this->id])
			->execute();
		\App\Log::trace("Updated masseditable information of $this->name ... DONE", __METHOD__);
	}

	/**
	 * Set Summaryfield information for this instance.
	 *
	 * @param int Summaryfield value
	 * @param mixed $value
	 */
	public function setSummaryField($value)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['summaryfield' => $value], ['fieldid' => $this->id])
			->execute();
		\App\Log::trace("Updated summaryfield information of $this->name ... DONE", __METHOD__);
	}

	/**
	 * Get block name.
	 *
	 * @return string
	 */
	public function getBlockName()
	{
		if ($this->block) {
			return $this->block->label;
		}
		return '';
	}

	/**
	 * Clear cache.
	 *
	 * @return void
	 */
	protected function clearCache(): void
	{
		\App\Cache::staticDelete('ModuleFields', $this->getModuleId());
		\App\Cache::delete('AllFieldForModule', $this->getModuleId());
		\App\Cache::staticDelete('module', $this->getModuleName());
		\App\Cache::delete('BlocksForModule', $this->getModuleId());
		\App\Cache::delete('ModuleFieldInfosByName', $this->getModuleName());
		\App\Cache::delete('ModuleFieldInfosByColumn', $this->getModuleName());
		\App\Cache::delete('App\Field::getFieldsPermissions' . \App\User::getCurrentUserId(), $this->getModuleName());
		\Vtiger_Module_Model::getInstance($this->getModuleName())->clearCache();
	}
}
