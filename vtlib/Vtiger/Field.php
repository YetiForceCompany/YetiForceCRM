<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
namespace vtlib;

/**
 * Provides APIs to control vtiger CRM Field
 * @package vtlib
 */
class Field extends FieldBasic
{

	/**
	 * Get unique picklist id to use
	 * @access private
	 */
	public function __getPicklistUniqueId()
	{
		$adb = \PearDatabase::getInstance();
		return $adb->getUniqueID('vtiger_picklist');
	}

	/**
	 * Get picklist values from table
	 */
	public function getPicklistValues()
	{
		$adb = \PearDatabase::getInstance();
		$picklist_table = 'vtiger_' . $this->name;
		$picklistValues = [];
		$picklistResult = $adb->query(sprintf("SELECT %s FROM %s", $this->name, $picklist_table));
		while ($row = $adb->getRow($picklistResult)) {
			$picklistValues[] = $row[$this->name];
		}
		return $picklistValues;
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
		if ($this->uitype == '16') {
			$this->setNoRolePicklistValues($values);
			return;
		}
		$adb = \PearDatabase::getInstance();
		$picklist_table = 'vtiger_' . $this->name;
		$picklist_idcol = $this->name . 'id';
		if (!Utils::CheckTable($picklist_table)) {
			Utils::CreateTable(
				$picklist_table, "($picklist_idcol INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				$this->name VARCHAR(200) NOT NULL,
				presence INT (1) NOT NULL DEFAULT 1,
				picklist_valueid INT NOT NULL DEFAULT 0,
                sortorderid INT DEFAULT 0)", true);
			$new_picklistid = $this->__getPicklistUniqueId();
			$adb->pquery("INSERT INTO vtiger_picklist (picklistid,name) VALUES(?,?)", Array($new_picklistid, $this->name));
			self::log("Creating table $picklist_table ... DONE");
		} else {
			$picklistResult = $adb->pquery("SELECT picklistid FROM vtiger_picklist WHERE name=?", Array($this->name));
			$new_picklistid = $adb->query_result($picklistResult, 0, 'picklistid');
		}

		$specialNameSpacedPicklists = array(
			'opportunity_type' => 'opptypeid',
			'duration_minutes' => 'minutesid',
			'recurringtype' => 'recurringeventid'
		);

		// Fix Table ID column names
		$fieldName = (string) $this->name;
		if (in_array($fieldName . '_id', $adb->getColumnNames($picklist_table))) {
			$picklist_idcol = $fieldName . '_id';
		} elseif (array_key_exists($fieldName, $specialNameSpacedPicklists)) {
			$picklist_idcol = $specialNameSpacedPicklists[$fieldName];
		}
		// END
		// Add value to picklist now
		$picklistValues = self::getPicklistValues();
		$sortid = 0;
		foreach ($values as $value) {
			if (in_array($value, $picklistValues)) {
				continue;
			}
			$new_picklistvalueid = $adb->getUniqueID('vtiger_picklistvalues');
			$presence = 1; // 0 - readonly, Refer function in include/ComboUtil.php
			$new_id = $adb->getUniqueID($picklist_table);
			++$sortid;

			$adb->pquery("INSERT INTO $picklist_table($picklist_idcol, $this->name, presence, picklist_valueid,sortorderid) VALUES(?,?,?,?,?)", Array($new_id, $value, $presence, $new_picklistvalueid, $sortid));


			// Associate picklist values to all the role
			$adb->pquery("INSERT INTO vtiger_role2picklist(roleid, picklistvalueid, picklistid, sortid) SELECT roleid,
				$new_picklistvalueid, $new_picklistid, $sortid FROM vtiger_role", []);
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
		$adb = \PearDatabase::getInstance();
		$pickListNameIDs = array('recurring_frequency', 'payment_duration');
		$picklistTable = 'vtiger_' . $this->name;
		$picklistIDcol = $this->name . 'id';
		if (in_array($this->name, $pickListNameIDs)) {
			$picklistIDcol = $this->name . '_id';
		}

		if (!Utils::CheckTable($picklistTable)) {
			Utils::CreateTable(
				$picklistTable, "($picklistIDcol INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				$this->name VARCHAR(200) NOT NULL,
				sortorderid INT(11),
				presence INT (11) NOT NULL DEFAULT 1)", true);
			self::log("Creating table $picklistTable ... DONE");
		}
		// Add value to picklist now
		$picklistValues = $this->getPicklistValues();

		$sortid = 1;
		foreach ($values as $value) {
			if (in_array($value, $picklistValues)) {
				continue;
			}
			$presence = 1; // 0 - readonly, Refer function in include/ComboUtil.php
			$newId = $adb->getUniqueId($picklistTable);

			$data = [
				$picklistIDcol => $newId,
				$this->name => $value,
				'sortorderid' => $sortid,
				'presence' => $presence,
			];
			$adb->insert($picklistTable, $data);
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
		// We need to create core table to capture the relation between the field and modules.
		if (!Utils::CheckTable('vtiger_fieldmodulerel')) {
			Utils::CreateTable(
				'vtiger_fieldmodulerel', '(fieldid INT NOT NULL, module VARCHAR(100) NOT NULL, relmodule VARCHAR(100) NOT NULL, status VARCHAR(10), sequence INT)', true
			);
		}
		// END

		$adb = \PearDatabase::getInstance();
		foreach ($moduleNames as $relmodule) {
			$checkres = $adb->pquery('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? && module=? && relmodule=?', Array($this->id, $this->getModuleName(), $relmodule));

			// If relation already exist continue
			if ($adb->num_rows($checkres))
				continue;

			$adb->pquery('INSERT INTO vtiger_fieldmodulerel(fieldid, module, relmodule) VALUES(?,?,?)', Array($this->id, $this->getModuleName(), $relmodule));

			self::log("Setting $this->name relation with $relmodule ... DONE");
		}
		return true;
	}

	/**
	 * Remove relation between the field and modules (UIType 10)
	 * @param Array List of module names
	 */
	public function unsetRelatedModules($moduleNames)
	{
		$adb = \PearDatabase::getInstance();
		foreach ($moduleNames as $relmodule) {
			$adb->pquery('DELETE FROM vtiger_fieldmodulerel WHERE fieldid=? && module=? && relmodule = ?', Array($this->id, $this->getModuleName(), $relmodule));

			Utils::Log("Unsetting $this->name relation with $relmodule ... DONE");
		}
		return true;
	}

	/**
	 * Get Field instance by fieldid or fieldname
	 * @param mixed fieldid or fieldname
	 * @param Module Instance of the module if fieldname is used
	 */
	static function getInstance($value, $moduleInstance = false)
	{
		$adb = \PearDatabase::getInstance();
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
	static function getAllForBlock($blockInstance, $moduleInstance = false)
	{
		$cache = \Vtiger_Cache::getInstance();
		if ($cache->getBlockFields($blockInstance->id, $moduleInstance->id)) {
			return $cache->getBlockFields($blockInstance->id, $moduleInstance->id);
		} else {
			$adb = \PearDatabase::getInstance();
			$instances = false;
			$query = false;
			$queryParams = false;
			if ($moduleInstance) {
				$query = "SELECT * FROM vtiger_field WHERE block=? && tabid=? ORDER BY sequence";
				$queryParams = Array($blockInstance->id, $moduleInstance->id);
			} else {
				$query = "SELECT * FROM vtiger_field WHERE block=? ORDER BY sequence";
				$queryParams = Array($blockInstance->id);
			}
			$result = $adb->pquery($query, $queryParams);
			while ($row = $adb->getRow($result)) {
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
	static function getAllForModule($moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		$instances = false;

		$query = 'SELECT * FROM vtiger_field LEFT JOIN vtiger_blocks ON vtiger_field.block=vtiger_blocks.blockid WHERE vtiger_field.tabid=? ORDER BY vtiger_blocks.sequence,vtiger_field.sequence';
		$queryParams = Array($moduleInstance->id);

		$result = $adb->pquery($query, $queryParams);
		while ($row = $adb->getRow($result)) {
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
	static function deleteForModule($moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		self::deletePickLists($moduleInstance);
		self::deleteUiType10Fields($moduleInstance);
		$adb->delete('vtiger_field', 'tabid=?', [$moduleInstance->id]);
		$adb->delete('vtiger_fieldmodulerel', 'module = ? || relmodule = ?', [$moduleInstance->name, $moduleInstance->name]);
		self::log("Deleting fields of the module ... DONE");
	}

	public function setTreeTemplate($tree, $moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		$adb->pquery('INSERT INTO vtiger_trees_templates(name, module, access) VALUES (?,?,?)', Array($tree->name, $moduleInstance->id, $tree->access));
		$templateid = $adb->getLastInsertID();

		foreach ($tree->tree_values->tree_value as $treeValue) {
			$sql = 'INSERT INTO vtiger_trees_templates_data(templateid, name, tree, parenttrre, depth, label, state) VALUES (?,?,?,?,?,?,?)';
			$params = array($templateid, $treeValue->name, $treeValue->tree, $treeValue->parenttrre, $treeValue->depth, $treeValue->label, $treeValue->state);
			$adb->pquery($sql, $params);
		}
		self::log("Add tree template $tree->name ... DONE");
		return $templateid;
	}

	/**
	 * Function to remove uitype10 fields
	 * @param Module Instance of module
	 */
	static function deleteUiType10Fields($moduleInstance)
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = \PearDatabase::getInstance();
		$query = 'SELECT fieldid FROM `vtiger_fieldmodulerel` WHERE relmodule = ?';
		$result = $db->pquery($query, [$moduleInstance->name]);
		while ($fieldId = $db->getSingleValue($result)) {
			$query = 'SELECT COUNT(1) FROM `vtiger_fieldmodulerel` WHERE fieldid = ?';
			$resultQuery = $db->pquery($query, [$fieldId]);
			if ((int) $db->getSingleValue($resultQuery) == 1) {
				$field = Field::getInstance($fieldId);
				$field->delete();
			}
		}
		self::log(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove picklist-type or multiple choice picklist-type table
	 * @param Module Instance of module
	 */
	static function deletePickLists($moduleInstance)
	{
		self::log(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = \PearDatabase::getInstance();
		$query = "SELECT `fieldname` FROM `vtiger_field` WHERE `tabid` = ? &&  uitype IN (15, 16, 33)";
		$result = $db->pquery($query, [$moduleInstance->getId()]);
		$modulePicklists = $db->getArrayColumn($result, 'fieldname');
		if (!empty($modulePicklists)) {
			$params = $modulePicklists;
			$query = sprintf("SELECT `fieldname` FROM `vtiger_field` WHERE `fieldname` IN (%s) && `tabid` <> ? && uitype IN (?, ?, ?)", $db->generateQuestionMarks($modulePicklists));
			array_push($params, $moduleInstance->getId(), 15, 16, 33);
			$result = $db->pquery($query, $params);
			$picklists = $db->getArrayColumn($result, 'fieldname');
			$modulePicklists = array_diff($modulePicklists, $picklists);
		}
		foreach ($modulePicklists as $picklistName) {
			$db->query('DROP TABLE IF EXISTS vtiger_' . $picklistName . '');
			$db->query('DROP TABLE IF EXISTS vtiger_' . $picklistName . '_seq');
			$query = $db->query("SELECT picklistid from vtiger_picklist WHERE name = '$picklistName'");
			$picklistId = $db->getSingleValue($query);
			$db->query("DELETE FROM vtiger_role2picklist WHERE picklistid = '$picklistId'");
			$db->query("DELETE FROM vtiger_picklist WHERE name = '$picklistName'");
		}
		self::log(__CLASS__ . '::' . __METHOD__ . ' | END');
	}
}
