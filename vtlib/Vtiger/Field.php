<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ********************************************************************************** */
namespace vtlib;

/**
 * Provides APIs to control vtiger CRM Field
 * @package vtlib
 */
class Field extends FieldBasic
{

	/**
	 * Get picklist values from table
	 */
	public function getPicklistValues()
	{
		return \App\Fields\Picklist::getPickListValues($this->name);
	}

	/**
	 * Set values for picklist field (for all the roles)
	 * @param Array List of values to add.
	 *
	 * @internal Creates picklist base if it does not exists
	 */
	public function setPicklistValues($values)
	{
		// Non-Role based picklist values
		if ($this->uitype === 16) {
			$this->setNoRolePicklistValues($values);
			return;
		}
		$db = \App\Db::getInstance();
		$picklistTable = 'vtiger_' . $this->name;
		$picklistIdCol = $this->name . 'id';
		if (!$db->isTableExists($picklistTable)) {
			$importer = new \App\Db\Importers\Base();
			$db->createTable($picklistTable, [
				$picklistIdCol => 'pk',
				$this->name => 'string',
				'presence' => $importer->boolean()->defaultValue(true),
				'picklist_valueid' => $importer->smallInteger()->defaultValue(0),
				'sortorderid' => $importer->smallInteger()->defaultValue(0)
			]);
			$db->createCommand()->insert('vtiger_picklist', ['name' => $this->name])->execute();
			$newPicklistId = $db->getLastInsertID('vtiger_picklist_picklistid_seq');
			self::log("Creating table $picklistTable ... DONE");
		} else {
			$newPicklistId = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')->where(['name' => $this->name])->scalar();
		}
		$specialNameSpacedPicklists = [
			'opportunity_type' => 'opptypeid',
			'duration_minutes' => 'minutesid',
		];
		// Fix Table ID column names
		$fieldName = (string) $this->name;
		if ($db->getTableSchema($picklistTable, true)->getColumn($fieldName . '_id')) {
			$picklistIdCol = $fieldName . '_id';
		} elseif (array_key_exists($fieldName, $specialNameSpacedPicklists)) {
			$picklistIdCol = $specialNameSpacedPicklists[$fieldName];
		}
		// END
		// Add value to picklist now
		$picklistValues = self::getPicklistValues();
		$sortid = 0;
		foreach ($values as &$value) {
			if (in_array($value, $picklistValues)) {
				continue;
			}
			$newPicklistValueId = $db->getUniqueID('vtiger_picklistvalues');
			$presence = 1; // 0 - readonly, Refer function in include/ComboUtil.php
			++$sortid;
			$db->createCommand()->insert($picklistTable, [$this->name => $value, 'presence' => $presence,
				'picklist_valueid' => $newPicklistValueId, 'sortorderid' => $sortid
			])->execute();

			// Associate picklist values to all the role
			$query = (new \App\Db\Query)->select('roleid')->from('vtiger_role');
			$roleIds = $query->column();
			$insertedData = [];
			foreach ($roleIds as &$value) {
				$insertedData [] = [$value, $newPicklistValueId, $newPicklistId, $sortid];
			}
			$db->createCommand()
				->batchInsert('vtiger_role2picklist', ['roleid', 'picklistvalueid', 'picklistid', 'sortid'], $insertedData)
				->execute();
		}
	}

	/**
	 * Set values for picklist field (non-role based)
	 * @param Array List of values to add
	 *
	 * @internal Creates picklist base if it does not exists
	 * @access private
	 */
	public function setNoRolePicklistValues($values)
	{

		$db = \App\Db::getInstance();
		$pickListNameIDs = array('recurring_frequency', 'payment_duration');
		$picklistTable = 'vtiger_' . $this->name;
		$picklistIdCol = $this->name . 'id';
		if (in_array($this->name, $pickListNameIDs)) {
			$picklistIdCol = $this->name . '_id';
		}

		if (!$db->isTableExists($picklistTable)) {
			$importer = new \App\Db\Importers\Base();
			$db->createTable($picklistTable, [
				$picklistIdCol => 'pk',
				$this->name => 'string',
				'presence' => $importer->boolean()->defaultValue(true),
				'sortorderid' => $importer->smallInteger()->defaultValue(0)
			]);
			self::log("Creating table $picklistTable ... DONE");
		}
		// Add value to picklist now
		$picklistValues = $this->getPicklistValues();

		$sortid = 1;
		foreach ($values as &$value) {
			if (in_array($value, $picklistValues)) {
				continue;
			}
			$presence = 1; // 0 - readonly, Refer function in include/ComboUtil.php

			$data = [
				$this->name => $value,
				'sortorderid' => $sortid,
				'presence' => $presence,
			];
			$db->createCommand()->insert($picklistTable, $data)->execute();
			$sortid = $sortid + 1;
		}
	}

	/**
	 * Set relation between field and modules (UIType 10)
	 * @param Array List of module names
	 *
	 * @internal Creates table vtiger_fieldmodulerel if it does not exists
	 */
	public function setRelatedModules($moduleNames)
	{
		if (count($moduleNames) == 0) {
			self::log("Setting $this->name relation with $relmodule ... ERROR: No module names");
			return false;
		}
		$db = \App\Db::getInstance();
		// END
		foreach ($moduleNames as &$relmodule) {
			$checkRes = (new \App\Db\Query())->from('vtiger_fieldmodulerel')->where(['fieldid' => $this->id, 'module' => $this->getModuleName(), 'relmodule' => $relmodule])->one();

			// If relation already exist continue
			if ($checkRes) {
				continue;
			}

			$db->createCommand()->insert('vtiger_fieldmodulerel', ['fieldid' => $this->id, 'module' => $this->getModuleName(), 'relmodule' => $relmodule])->execute();
			self::log("Setting $this->name relation with $checkRes ... DONE");
		}
		return true;
	}

	/**
	 * Remove relation between the field and modules (UIType 10)
	 * @param Array List of module names
	 */
	public function unsetRelatedModules($moduleNames)
	{
		$db = \App\Db::getInstance();
		foreach ($moduleNames as &$relmodule) {
			$db->createCommand()->delete('vtiger_fieldmodulerel', ['fieldid' => $this->id, 'module' => $this->getModuleName(), 'relmodule' => $relmodule])->execute();
			Utils::Log("Unsetting $this->name relation with $relmodule ... DONE");
		}
		return true;
	}

	/**
	 * Get Field instance by fieldid or fieldname
	 * @param mixed fieldid or fieldname
	 * @param Module Instance of the module if fieldname is used
	 */
	public static function getInstance($value, $moduleInstance = false)
	{
		$instance = false;
		$moduleid = null;
		if ($moduleInstance) {
			$moduleid = $moduleInstance->id;
		}
		$data = Functions::getModuleFieldInfo($moduleid, $value);
		if ($data) {
			$instance = new self();
			$instance->initialize($data, $moduleInstance);
		}
		return $instance;
	}

	/**
	 * Get Field instances related to block
	 * @param vtlib\Block Instnace of block to use
	 * @param Module Instance of module to which block is associated
	 */
	public static function getAllForBlock($blockInstance, $moduleInstance = false)
	{
		$cache = \Vtiger_Cache::getInstance();
		if ($cache->getBlockFields($blockInstance->id, $moduleInstance->id)) {
			return $cache->getBlockFields($blockInstance->id, $moduleInstance->id);
		} else {
			$instances = false;
			$query = false;
			$queryParams = false;
			if ($moduleInstance) {
				$query = (new \App\Db\Query())->from('vtiger_field')->where(['block' => $blockInstance->id, 'tabid' => $moduleInstance->id])->orderBy('sequence');
			} else {
				$query = (new \App\Db\Query())->from('vtiger_field')->where(['block' => $blockInstance->id])->orderBy('sequence');
			}
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$instance = new self();
				$instance->initialize($row, $moduleInstance, $blockInstance);
				$instances[] = $instance;
			}
			$cache->setBlockFields($blockInstance->id, $moduleInstance->id, $instances);
			return $instances;
		}
	}

	/**
	 * Get Field instances related to module
	 * @param Module Instance of module to use
	 */
	public static function getAllForModule($moduleInstance)
	{
		$moduleId = $moduleInstance->id;
		if (\App\Cache::has('AllFieldForModule', $moduleId)) {
			$rows = \App\Cache::get('AllFieldForModule', $moduleId);
		} else {
			$rows = (new \App\Db\Query())->select(['vtiger_field.*'])->from('vtiger_field')
				->leftJoin('vtiger_blocks', 'vtiger_field.block = vtiger_blocks.blockid')
				->where(['vtiger_field.tabid' => $moduleId])->orderBy(['vtiger_blocks.sequence' => SORT_ASC, 'vtiger_field.sequence' => SORT_ASC])
				->all();
			\App\Cache::save('AllFieldForModule', $moduleId, $rows);
		}
		$instances = false;
		foreach ($rows as $row) {
			$instance = new self();
			$instance->initialize($row, $moduleInstance);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Delete fields associated with the module
	 * @param Module Instance of module
	 * @access private
	 */
	public static function deleteForModule($moduleInstance)
	{
		self::deletePickLists($moduleInstance);
		self::deleteUiType10Fields($moduleInstance);
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('vtiger_field', ['tabid' => $moduleInstance->id])->execute();
		$db->createCommand()->delete('vtiger_fieldmodulerel', ['or', "module = '$moduleInstance->name'", "relmodule = '$moduleInstance->name'"])->execute();
		self::log("Deleting fields of the module ... DONE");
	}

	public function setTreeTemplate($tree, $moduleInstance)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_trees_templates', ['name' => $tree->name, 'module' => $moduleInstance->id, 'access' => $tree->access])->execute();
		$templateId = $db->getLastInsertID('vtiger_trees_templates_templateid_seq');

		foreach ($tree->tree_values->tree_value as $treeValue) {
			$db->createCommand()->insert('vtiger_trees_templates_data', ['templateid' => $templateId, 'name' => $treeValue->name, 'tree' => $treeValue->tree,
				'parenttrre' => $treeValue->parenttrre, 'depth' => $treeValue->depth, 'label' => $treeValue->label, 'state' => $treeValue->state
			])->execute();
		}
		self::log("Add tree template $tree->name ... DONE");
		return $templateId;
	}

	/**
	 * Function to remove uitype10 fields
	 * @param Module Instance of module
	 */
	public static function deleteUiType10Fields($moduleInstance)
	{
		self::log(__METHOD__ . ' | Start');
		$query = (new \App\Db\Query)->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['relmodule' => $moduleInstance->name]);
		$dataReader = $query->createCommand()->query();
		while ($fieldId = $dataReader->readColumn(0)) {
			$count = (new \App\Db\Query)->from('vtiger_fieldmodulerel')->where(['fieldid' => $fieldId])->count();
			if ((int) $count === 1) {
				$field = Field::getInstance($fieldId);
				$field->delete();
			}
		}
		self::log(__METHOD__ . ' | END');
	}

	/**
	 * Function to remove picklist-type or multiple choice picklist-type table
	 * @param Module Instance of module
	 */
	public static function deletePickLists($moduleInstance)
	{
		self::log(__METHOD__ . ' | Start');
		$db = \App\Db::getInstance();
		$query = (new \App\Db\Query)->select(['fieldname'])->from('vtiger_field')->where(['tabid' => $moduleInstance->getId(), 'uitype' => [15, 16, 33]]);
		$modulePicklists = $query->column();
		if (!empty($modulePicklists)) {
			$query = (new \App\Db\Query)->select('fieldname')->from('vtiger_field')->where(['fieldname' => $modulePicklists, 'uitype' => [15, 16, 33]])
				->andWhere(['<>', 'tabid', $moduleInstance->getId()]);
			$picklists = $query->column();
			$modulePicklists = array_diff($modulePicklists, $picklists);
		}
		foreach ($modulePicklists as &$picklistName) {
			if ($db->isTableExists("vtiger_$picklistName")) {
				$db->createCommand()->dropTable("vtiger_$picklistName")->execute();
			}
			if ($db->isTableExists("vtiger_{$picklistName}_seq")) {
				$db->createCommand()->dropTable("vtiger_{$picklistName}_seq")->execute();
			}
			$picklistId = (new \App\Db\Query)->select('picklistid')->from('vtiger_picklist')->where(['name' => $picklistName])->scalar();
			$db->createCommand()->delete('vtiger_role2picklist', ['picklistid' => $picklistId])->execute();
			$db->createCommand()->delete('vtiger_picklist', ['name' => $picklistName])->execute();
		}
		self::log(__METHOD__ . ' | END');
	}
}
