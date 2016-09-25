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
 * Provides API to work with vtiger CRM Custom View (Filter)
 * @package vtlib
 */
class Filter
{

	/** ID of this filter instance */
	var $id;
	var $name;
	var $isdefault;
	var $status = false; // 5.1.0 onwards
	var $inmetrics = false;
	var $entitytype = false;
	var $presence = 1;
	var $featured = 0;
	var $description;
	var $privileges = 1;
	var $sort;
	var $module;

	/**
	 * Get unique id for this instance
	 * @access private
	 */
	public function __getUniqueId()
	{
		$adb = \PearDatabase::getInstance();
		return $adb->getUniqueID('vtiger_customview');
	}

	/**
	 * Initialize this filter instance
	 * @param Module Instance of the module to which this filter is associated.
	 * @access private
	 */
	public function initialize($valuemap, $moduleInstance = false)
	{
		$this->id = $valuemap[cvid];
		$this->name = $valuemap[viewname];
		$this->module = $moduleInstance ? $moduleInstance : Module::getInstance($valuemap[tabid]);
	}

	/**
	 * Create this instance
	 * @param Module Instance of the module to which this filter should be associated with
	 * @access private
	 */
	public function __create($moduleInstance)
	{
		$db = \PearDatabase::getInstance();
		$this->module = $moduleInstance;

		$this->id = $this->__getUniqueId();
		$this->isdefault = ($this->isdefault === true || $this->isdefault == 'true') ? 1 : 0;
		$this->inmetrics = ($this->inmetrics === true || $this->inmetrics == 'true') ? 1 : 0;
		if (!isset($this->sequence)) {
			$result = $db->pquery('SELECT MAX(sequence) AS max  FROM vtiger_customview WHERE entitytype = ?;', [$this->module->name]);
			$this->sequence = $result->rowCount() ? (int) $db->getSingleValue($result) + 1 : 0;
		}
		if (!isset($this->status)) {
			if ($this->presence == 0)
				$this->status = '0'; // Default
			else
				$this->status = '3'; // Public
		}

		$db->insert('vtiger_customview', [
			'cvid' => $this->id,
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
		]);
		self::log("Creating Filter $this->name ... DONE");
	}

	public function __update()
	{
		self::log("Updating Filter $this->name ... DONE");
	}

	/**
	 * Delete this instance
	 * @access private
	 */
	public function __delete()
	{
		$adb = \PearDatabase::getInstance();
		$adb->pquery("DELETE FROM vtiger_cvadvfilter WHERE cvid=?", Array($this->id));
		$adb->pquery("DELETE FROM vtiger_cvcolumnlist WHERE cvid=?", Array($this->id));
		$adb->pquery("DELETE FROM vtiger_customview WHERE cvid=?", Array($this->id));
	}

	/**
	 * Save this instance
	 * @param Module Instance of the module to use
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
	 * Delete this instance
	 * @access private
	 */
	public function delete()
	{
		$this->__delete();
	}

	/**
	 * Get the column value to use in custom view tables.
	 * @param Field Instance of the field
	 * @access private
	 */
	public function __getColumnValue($fieldInstance)
	{
		$tod = explode('~', $fieldInstance->typeofdata);
		$displayinfo = $fieldInstance->getModuleName() . '_' . str_replace(' ', '_', $fieldInstance->label) . ':' . $tod[0];
		$cvcolvalue = "$fieldInstance->table:$fieldInstance->column:$fieldInstance->name:$displayinfo";
		return $cvcolvalue;
	}

	/**
	 * Add the field to this filer instance
	 * @param Field Instance of the field
	 * @param Integer Index count to use
	 */
	public function addField($fieldInstance, $index = 0)
	{
		$adb = \PearDatabase::getInstance();

		$cvcolvalue = $this->__getColumnValue($fieldInstance);

		$adb->pquery("UPDATE vtiger_cvcolumnlist SET columnindex=columnindex+1 WHERE cvid=? && columnindex>=? ORDER BY columnindex DESC", Array($this->id, $index));
		$adb->pquery("INSERT INTO vtiger_cvcolumnlist(cvid,columnindex,columnname) VALUES(?,?,?)", Array($this->id, $index, $cvcolvalue));

		$this->log("Adding $fieldInstance->name to $this->name filter ... DONE");
		return $this;
	}

	/**
	 * Add rule to this filter instance
	 * @param Field Instance of the field
	 * @param String One of [EQUALS, NOT_EQUALS, STARTS_WITH, ENDS_WITH, CONTAINS, DOES_NOT_CONTAINS, LESS_THAN, 
	 *                       GREATER_THAN, LESS_OR_EQUAL, GREATER_OR_EQUAL]
	 * @param String Value to use for comparision
	 * @param Integer Index count to use
	 */
	public function addRule($fieldInstance, $comparator, $comparevalue, $index = 0, $group = 1, $condition = 'and')
	{
		$adb = \PearDatabase::getInstance();

		if (empty($comparator))
			return $this;

		$comparator = self::translateComparator($comparator);
		$cvcolvalue = $this->__getColumnValue($fieldInstance);

		$adb->pquery("UPDATE vtiger_cvadvfilter set columnindex=columnindex+1 WHERE cvid=? && columnindex>=? ORDER BY columnindex DESC", Array($this->id, $index));
		$adb->pquery("INSERT INTO vtiger_cvadvfilter(cvid, columnindex, columnname, comparator, value, groupid, column_condition) VALUES(?,?,?,?,?,?,?)", Array($this->id, $index, $cvcolvalue, $comparator, $comparevalue, $group, $condition));

		Utils::Log("Adding Condition " . self::translateComparator($comparator, true) . " on $fieldInstance->name of $this->name filter ... DONE");

		return $this;
	}

	/**
	 * Translate comparator (condition) to long or short form.
	 * @access private
	 */
	static function translateComparator($value, $tolongform = false)
	{
		$comparator = false;
		if ($tolongform) {
			$comparator = strtolower($value);
			if ($comparator == 'e')
				$comparator = 'EQUALS';
			else if ($comparator == 'n')
				$comparator = 'NOT_EQUALS';
			else if ($comparator == 's')
				$comparator = 'STARTS_WITH';
			else if ($comparator == 'ew')
				$comparator = 'ENDS_WITH';
			else if ($comparator == 'c')
				$comparator = 'CONTAINS';
			else if ($comparator == 'k')
				$comparator = 'DOES_NOT_CONTAINS';
			else if ($comparator == 'l')
				$comparator = 'LESS_THAN';
			else if ($comparator == 'g')
				$comparator = 'GREATER_THAN';
			else if ($comparator == 'm')
				$comparator = 'LESS_OR_EQUAL';
			else if ($comparator == 'h')
				$comparator = 'GREATER_OR_EQUAL';
		} else {
			$comparator = strtoupper($value);
			if ($comparator == 'EQUALS')
				$comparator = 'e';
			else if ($comparator == 'NOT_EQUALS')
				$comparator = 'n';
			else if ($comparator == 'STARTS_WITH')
				$comparator = 's';
			else if ($comparator == 'ENDS_WITH')
				$comparator = 'ew';
			else if ($comparator == 'CONTAINS')
				$comparator = 'c';
			else if ($comparator == 'DOES_NOT_CONTAINS')
				$comparator = 'k';
			else if ($comparator == 'LESS_THAN')
				$comparator = 'l';
			else if ($comparator == 'GREATER_THAN')
				$comparator = 'g';
			else if ($comparator == 'LESS_OR_EQUAL')
				$comparator = 'm';
			else if ($comparator == 'GREATER_OR_EQUAL')
				$comparator = 'h';
		}
		return $comparator;
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
	 * Get instance by filterid or filtername
	 * @param mixed filterid or filtername
	 * @param Module Instance of the module to use when filtername is used
	 */
	static function getInstance($value, $moduleInstance = false)
	{
		$adb = \PearDatabase::getInstance();
		$instance = false;

		$query = false;
		$queryParams = false;
		if (Utils::isNumber($value)) {
			$query = "SELECT * FROM vtiger_customview WHERE cvid=?";
			$queryParams = Array($value);
		} else {
			$query = "SELECT * FROM vtiger_customview WHERE viewname=? && entitytype=?";
			$queryParams = Array($value, $moduleInstance->name);
		}
		$result = $adb->pquery($query, $queryParams);
		if ($adb->num_rows($result)) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance);
		}
		return $instance;
	}

	/**
	 * Get all instances of filter for the module
	 * @param Module Instance of module
	 */
	static function getAllForModule($moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		$instances = false;

		$query = "SELECT * FROM vtiger_customview WHERE entitytype=?";
		$queryParams = Array($moduleInstance->name);

		$result = $adb->pquery($query, $queryParams);
		for ($index = 0; $index < $adb->num_rows($result); ++$index) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Delete filter associated for module
	 * @param Module Instance of module
	 */
	static function deleteForModule($moduleInstance)
	{
		$db = \PearDatabase::getInstance();

		$cvidres = $db->pquery('SELECT cvid FROM vtiger_customview WHERE entitytype=?', [$moduleInstance->name]);
		$cvids = [];
		while (($cvid = $db->getSingleValue($cvidres)) !== false) {
			$cvids[] = $cvid;
		}
		if (!empty($cvids)) {
			$db->delete('vtiger_cvadvfilter', 'cvid IN (' . implode(',', $cvids) . ')');
			$db->delete('vtiger_cvcolumnlist', 'cvid IN (' . implode(',', $cvids) . ')');
			$db->delete('vtiger_customview', 'cvid IN (' . implode(',', $cvids) . ')');
		}
	}
}
