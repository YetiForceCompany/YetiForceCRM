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
 * Provides API to work with vtiger CRM Custom View (Filter).
 */
class Filter
{
	/** ID of this filter instance */
	public $id;
	public $name;
	public $isdefault;
	public $status = false; // 5.1.0 onwards
	public $inmetrics = false;
	public $entitytype = false;
	public $presence = 1;
	public $featured = 0;
	public $description;
	public $privileges = 1;
	public $sort;
	public $module;

	/**
	 * Initialize this filter instance.
	 *
	 * @param mixed $module   Mixed id or name of the module
	 * @param mixed $valuemap
	 */
	public function initialize($valuemap, $module = false)
	{
		$this->id = $valuemap['cvid'];
		$this->name = $valuemap['viewname'];
		$this->module = Module::getInstance($module ?: $valuemap['tabid']);
	}

	/**
	 * Create this instance.
	 *
	 * @param Module Instance of the module to which this filter should be associated with
	 * @param mixed $moduleInstance
	 */
	public function __create($moduleInstance)
	{
		$this->module = $moduleInstance;
		$this->isdefault = (true === $this->isdefault || 'true' == $this->isdefault) ? 1 : 0;
		$this->inmetrics = (true === $this->inmetrics || 'true' == $this->inmetrics) ? 1 : 0;
		if (!isset($this->sequence)) {
			$sequence = (new \App\Db\Query())->from('vtiger_customview')
				->where(['entitytype' => $this->module->name])
				->max('sequence');
			$this->sequence = $sequence ? (int) $sequence + 1 : 0;
		}
		if (!isset($this->status)) {
			if (0 == $this->presence) {
				$this->status = '0';
			} // Default
			else {
				$this->status = '3';
			} // Public
		}
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_customview', [
			'viewname' => $this->name,
			'setdefault' => $this->isdefault,
			'setmetrics' => $this->inmetrics,
			'entitytype' => $this->module->name,
			'status' => $this->status,
			'privileges' => $this->privileges,
			'featured' => $this->featured,
			'sequence' => $this->sequence,
			'presence' => $this->presence,
			'description' => $this->description,
			'sort' => $this->sort,
		])->execute();
		$this->id = $db->getLastInsertID('vtiger_customview_cvid_seq');
		\App\Log::trace("Creating Filter $this->name ... DONE", __METHOD__);
	}

	public function __update()
	{
		\App\Log::trace("Updating Filter $this->name ... DONE", __METHOD__);
	}

	/**
	 * Delete this instance.
	 */
	public function __delete()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_customview', ['cvid' => $this->id])->execute();
		\App\CustomView::clearCacheById($this->id, $this->module->name);
	}

	/**
	 * Save this instance.
	 *
	 * @param Module Instance of the module to use
	 * @param mixed $moduleInstance
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
	 * Delete this instance.
	 */
	public function delete()
	{
		$this->__delete();
	}

	/**
	 * Get the column value to use in custom view tables.
	 *
	 * @param FieldBasic $fieldInstance
	 *
	 * @return string
	 */
	public function __getColumnValue(FieldBasic $fieldInstance)
	{
		$tod = explode('~', $fieldInstance->typeofdata);
		$displayinfo = $fieldInstance->getModuleName() . '_' . str_replace(' ', '_', $fieldInstance->label) . ':' . $tod[0];
		return "$fieldInstance->table:$fieldInstance->column:$fieldInstance->name:$displayinfo";
	}

	/**
	 * Add the field to this filer instance.
	 *
	 * @param FieldBasic $fieldInstance
	 * @param int        $index
	 *
	 * @return $this
	 */
	public function addField(FieldBasic $fieldInstance, $index = 0)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->update('vtiger_cvcolumnlist', ['columnindex' => new \yii\db\Expression('columnindex + 1')], ['and', ['cvid' => $this->id], ['>=', 'columnindex', $index]])->execute();
		$db->createCommand()->insert('vtiger_cvcolumnlist', [
			'cvid' => $this->id,
			'columnindex' => $index,
			'field_name' => $fieldInstance->name,
			'module_name' => $fieldInstance->getModuleName(),
			'source_field_name' => $fieldInstance->sourcefieldname ?? null
		])->execute();
		\App\Log::trace("Adding $fieldInstance->name to $this->name filter ... DONE", __METHOD__);

		return $this;
	}

	/**
	 * Add rule to this filter instance.
	 *
	 * @param array $conditions
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return $this
	 */
	public function addRule(array $conditions)
	{
		if (empty($conditions)) {
			return $this;
		}
		$cvRecordModel = \CustomView_Record_Model::getCleanInstance();
		$cvRecordModel->set('cvid', $this->id);
		$cvRecordModel->set('advfilterlist', $conditions);
		$cvRecordModel->set('advfilterlistDbFormat', true);
		$cvRecordModel->setConditionsForFilter();
		\App\Log::trace('Adding Condition', __METHOD__);
		return $this;
	}

	/**
	 * Get instance by filterid or filtername.
	 *
	 * @param mixed filterid or filtername
	 * @param mixed $module Mixed id or name of the module
	 * @param mixed $value
	 */
	public static function getInstance($value, $module = false)
	{
		$instance = false;
		$moduleName = is_numeric($module) ? \App\Module::getModuleName($module) : $module;
		if (Utils::isNumber($value)) {
			$result = \App\CustomView::getCVDetails((int) $value, $moduleName ?: null);
		} else {
			$result = (new \App\Db\Query())->from('vtiger_customview')->where(['viewname' => $value, 'entitytype' => $moduleName])->one();
		}
		if ($result) {
			$instance = new self();
			$instance->initialize($result, $module);
		}
		return $instance;
	}

	/**
	 * Get all instances of filter for the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 *
	 * @return self
	 */
	public static function getAllForModule(ModuleBasic $moduleInstance)
	{
		$instances = [];
		$dataReader = (new \App\Db\Query())->from('vtiger_customview')
			->where(['entitytype' => $moduleInstance->name])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$instance = new self();
			$instance->initialize($row, $moduleInstance->id);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Delete filter associated for module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public static function deleteForModule(ModuleBasic $moduleInstance)
	{
		$cvIds = (new \App\Db\Query())->from('vtiger_customview')->where(['entitytype' => $moduleInstance->name])->column();
		\App\Db::getInstance()->createCommand()->delete('vtiger_customview', ['entitytype' => $moduleInstance->name])->execute();
		foreach ($cvIds as $cvId) {
			\App\CustomView::clearCacheById($cvId, $moduleInstance->name);
		}
	}
}
