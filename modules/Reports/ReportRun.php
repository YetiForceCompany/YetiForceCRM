<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
global $calpath;
global $theme;


$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";
require_once('modules/Reports/Reports.php');
require_once 'modules/Reports/ReportUtils.php';
require_once('modules/Vtiger/helpers/Util.php');

/*
 * Helper class to determine the associative dependency between tables.
 */

class ReportRunQueryDependencyMatrix
{

	protected $matrix = array();
	protected $computedMatrix = null;

	public function setDependency($table, array $dependents)
	{
		$this->matrix[$table] = $dependents;
	}

	public function addDependency($table, $dependent)
	{
		if (isset($this->matrix[$table]) && !in_array($dependent, $this->matrix[$table])) {
			$this->matrix[$table][] = $dependent;
		} else {
			$this->setDependency($table, array($dependent));
		}
	}

	public function getDependents($table)
	{
		$this->computeDependencies();
		return isset($this->computedMatrix[$table]) ? $this->computedMatrix[$table] : array();
	}

	protected function computeDependencies()
	{
		if ($this->computedMatrix !== null)
			return;

		$this->computedMatrix = array();
		foreach ($this->matrix as $key => $values) {
			$this->computedMatrix[$key] = $this->computeDependencyForKey($key, $values);
		}
	}

	protected function computeDependencyForKey($key, $values)
	{
		$merged = array();
		foreach ($values as $value) {
			$merged[] = $value;
			if (isset($this->matrix[$value])) {
				$merged = array_merge($merged, $this->matrix[$value]);
			}
		}
		return $merged;
	}
}

class ReportRunQueryPlanner
{

	// Turn-off the query planning to revert back - backward compatiblity
	protected $disablePlanner = false;
	protected $tables = array();
	protected $customTables = array();
	protected $tempTables = array();
	protected $tempTablesInitialized = false;
	// Turn-off in case the query result turns-out to be wrong.
	protected $allowTempTables = true;
	protected $tempTablePrefix = 'vtiger_reptmptbl_';
	protected static $tempTableCounter = 0;
	protected $registeredCleanup = false;
	public static $existTables = [];

	public function addTable($table)
	{
		if (!empty($table))
			$this->tables[$table] = $table;
	}

	public function addCustomTable($table)
	{
		if (!in_array($table, $this->customTables)) {
			$this->customTables[] = $table;
		}
	}

	public function requireTable($table, $dependencies = null)
	{

		if ($this->disablePlanner) {
			return true;
		}

		if (isset($this->tables[$table])) {
			return true;
		}
		if (is_array($dependencies)) {
			foreach ($dependencies as $dependentTable) {
				if (isset($this->tables[$dependentTable])) {
					return true;
				}
			}
		} else if ($dependencies instanceof ReportRunQueryDependencyMatrix) {
			$dependents = $dependencies->getDependents($table);
			if ($dependents) {
				return count(array_intersect($this->tables, $dependents)) > 0;
			}
		}
		return false;
	}

	public function getTables()
	{
		return $this->tables;
	}

	public function getCustomTables()
	{
		return $this->customTables;
	}

	public function newDependencyMatrix()
	{
		return new ReportRunQueryDependencyMatrix();
	}

	public function registerTempTable($query, $keyColumns)
	{
		if ($this->allowTempTables && !$this->disablePlanner) {
			$current_user = vglobal('current_user');

			$keyColumns = is_array($keyColumns) ? array_unique($keyColumns) : array($keyColumns);

			// Minor optimization to avoid re-creating similar temporary table.
			$uniqueName = NULL;
			foreach ($this->tempTables as $tmpUniqueName => $tmpTableInfo) {
				if (strcasecmp($query, $tmpTableInfo['query']) === 0) {
					// Capture any additional key columns
					$tmpTableInfo['keycolumns'] = array_unique(array_merge($tmpTableInfo['keycolumns'], $keyColumns));
					$uniqueName = $tmpUniqueName;
					break;
				}
			}

			if ($uniqueName === NULL) {
				$uniqueName = $this->tempTablePrefix .
					str_replace('.', '', uniqid($current_user->id, true)) . (self::$tempTableCounter++);

				$this->tempTables[$uniqueName] = array(
					'query' => $query,
					'keycolumns' => is_array($keyColumns) ? array_unique($keyColumns) : array($keyColumns),
				);
			}

			return $uniqueName;
		}
		return "($query)";
	}

	public function initializeTempTables()
	{
		$adb = PearDatabase::getInstance();
		foreach ($this->tempTables as $uniqueName => $tempTableInfo) {
			if (!in_array($uniqueName, self::$existTables)) {
				$query1 = sprintf('CREATE TEMPORARY TABLE %s AS %s', $uniqueName, $tempTableInfo['query']);
				$adb->query($query1);
			}

			$keyColumns = $tempTableInfo['keycolumns'];
			foreach ($keyColumns as $keyColumn) {
				if (!empty($keyColumn)) {
					$result = $adb->query("SHOW COLUMNS FROM `$uniqueName` LIKE '$keyColumn';");
					if ($result->rowCount() > 0) {
						$query2 = sprintf('ALTER TABLE %s ADD INDEX (%s)', $uniqueName, $keyColumn);
						$adb->query($query2);
					}
				}
			}
			self::$existTables[] = $uniqueName;
		}

		// Trigger cleanup of temporary tables when the execution of the request ends.
		// NOTE: This works better than having in __destruct
		// (as the reference to this object might end pre-maturely even before query is executed)
		if (!$this->registeredCleanup) {
			register_shutdown_function(array($this, 'cleanup'));
			// To avoid duplicate registration on this instance.
			$this->registeredCleanup = true;
		}
	}

	public function cleanup()
	{
		$adb = PearDatabase::getInstance();

		$oldDieOnError = $adb->dieOnError;
		$adb->dieOnError = false; // To avoid abnormal termination during shutdown...
		foreach ($this->tempTables as $uniqueName => $tempTableInfo) {
			$adb->pquery('DROP TABLE ' . $uniqueName, array());
		}
		$adb->dieOnError = $oldDieOnError;

		$this->tempTables = array();
	}
}

class ReportRun extends CRMEntity
{

	// Maximum rows that should be emitted in HTML view.
	static $HTMLVIEW_MAX_ROWS = 1000;
	public $reportid;
	public $primarymodule;
	public $secondarymodule;
	public $orderbylistsql;
	public $orderbylistcolumns;
	public $selectcolumns;
	public $groupbylist;
	public $reporttype;
	public $reportname;
	public $totallist;
	public $_groupinglist = false;
	public $_columnslist = false;
	public $_stdfilterlist = false;
	public $_columnstotallist = false;
	public $_advfiltersql = false;
	// All UItype 72 fields are added here so that in reports the values are append currencyId::value
	public $append_currency_symbol_to_value = array('Products_Unit_Price', 'Services_Price'
	);
	public $ui10_fields = array();
	public $ui101_fields = array();
	public $groupByTimeParent = array('Quarter' => array('Year'),
		'Month' => array('Year')
	);
	public $queryPlanner = null;
	protected static $instances = false;
	// Added to support line item fields calculation, if line item fields
	// are selected then module fields cannot be selected and vice versa
	public $lineItemFieldsInCalculation = false;

	/** Function to set reportid,primarymodule,secondarymodule,reporttype,reportname, for given reportid
	 *  This function accepts the $reportid as argument
	 *  It sets reportid,primarymodule,secondarymodule,reporttype,reportname for the given reportid
	 *  To ensure single-instance is present for $reportid
	 *  as we optimize using ReportRunPlanner and setup temporary tables.
	 */
	public function __construct($reportid)
	{
		$oReport = new Reports($reportid);
		$this->reportid = $reportid;
		$this->primarymodule = $oReport->primodule;
		$this->secondarymodule = $oReport->secmodule;
		$this->reporttype = $oReport->reporttype;
		$this->reportname = $oReport->reportname;
		$this->queryPlanner = new ReportRunQueryPlanner();
	}

	public static function getInstance($reportid)
	{
		if (!isset(self::$instances[$reportid])) {
			self::$instances[$reportid] = new ReportRun($reportid);
		}
		return self::$instances[$reportid];
	}

	/** Function to get the columns for the reportid
	 *  This function accepts the $reportid and $outputformat (optional)
	 *  This function returns  $columnslist Array($tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname As Header value,
	 * 					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 As Header value,
	 * 					      					|
	 * 					      $tablenamen:$columnnamen:$fieldlabeln:$fieldnamen:$typeofdatan=>$tablenamen.$columnnamen As Header value
	 * 				      	     )
	 *
	 */
	public function getQueryColumnsList($reportid, $outputformat = '')
	{
		// Have we initialized information already?
		if ($this->_columnslist !== false) {
			return $this->_columnslist;
		}

		$adb = PearDatabase::getInstance();
		global $modules;

		$current_user = vglobal('current_user');
		$ssql = 'select vtiger_selectcolumn.* from vtiger_report inner join vtiger_selectquery on vtiger_selectquery.queryid = vtiger_report.queryid';
		$ssql .= ' left join vtiger_selectcolumn on vtiger_selectcolumn.queryid = vtiger_selectquery.queryid';
		$ssql .= ' where vtiger_report.reportid = ?';
		$ssql .= ' order by vtiger_selectcolumn.columnindex';
		$result = $adb->pquery($ssql, array($reportid));
		$permitted_fields = Array();

		while ($columnslistrow = $adb->fetch_array($result)) {
			$fieldname = '';
			$fieldcolname = $columnslistrow['columnname'];
			list($tablename, $colname, $module_field, $fieldname, $single) = explode(':', $fieldcolname);
			list($module, $field) = explode('__', $module_field, 2);
			$inventory_fields = array('serviceid');
			$inventory_modules = getInventoryModules();
			require('user_privileges/user_privileges_' . $current_user->id . '.php');
			if (sizeof($permitted_fields[$module]) == 0 && $is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
				$permitted_fields[$module] = $this->getaccesfield($module);
			}
			if (in_array($module, $inventory_modules)) {
				if (!empty($permitted_fields)) {
					foreach ($inventory_fields as $value) {
						array_push($permitted_fields[$module], $value);
					}
				}
			}
			$selectedfields = explode(':', $fieldcolname);
			if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && !in_array($selectedfields[3], $permitted_fields[$module])) {
				//user has no access to this field, skip it.
				continue;
			}
			$querycolumns = $this->getEscapedColumns($selectedfields);

			$targetTableName = $tablename;

			$fieldlabel = trim(preg_replace("/$module/", ' ', $selectedfields[2], 1));
			$mod_arr = explode('__', $fieldlabel);
			$fieldlabel = trim(str_replace('__', ' ', $fieldlabel));
			//modified code to support i18n issue
			$fld_arr = explode(' ', $fieldlabel);
			if (($mod_arr[0] == '')) {
				$mod = $module;
				$mod_lbl = \App\Language::translate($module, $module); //module
			} else {
				$mod = $mod_arr[0];
				array_shift($fld_arr);
				$mod_lbl = \App\Language::translate($fld_arr[0], $mod); //module
			}
			$fld_lbl_str = implode(' ', $fld_arr);
			$fld_lbl = \App\Language::translate($fld_lbl_str, $module); //fieldlabel
			$fieldlabel = $mod_lbl . ' ' . $fld_lbl;
			if (($selectedfields[0] == 'vtiger_usersRel1') && ($selectedfields[1] == 'user_name') && ($selectedfields[2] == 'Quotes_Inventory_Manager')) {
				$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . '.first_name', 'last_name' => $selectedfields[0] . '.last_name'), 'Users');
				$columnslist[$fieldcolname] = "trim( $concatSql ) as " . $module . '_Inventory_Manager';
				$this->queryPlanner->addTable($selectedfields[0]);
				continue;
			}
			if ((!\App\Field::getFieldPermission($mod, $fieldname) && $colname !== 'crmid' && (!in_array($fieldname, $inventory_fields) && in_array($module, $inventory_modules))) || empty($fieldname)) {
				continue;
			} else {
				$this->labelMapping[$selectedfields[2]] = str_replace(' ', '__', $fieldlabel);

				// To check if the field in the report is a custom field
				// and if yes, get the label of this custom field freshly from the vtiger_field as it would have been changed.
				// Asha - Reference ticket : #4906

				if ($querycolumns == '') {
					$columnslist[$fieldcolname] = $this->getColumnSQL($selectedfields);
				} else {
					$columnslist[$fieldcolname] = $querycolumns;
				}

				$this->queryPlanner->addTable($targetTableName);
			}
		}

		if ($outputformat == 'HTML' || $outputformat == 'PDF' || $outputformat == 'PRINT') {
			$columnslist['vtiger_crmentity:crmid:LBL_ACTION:crmid:I'] = 'vtiger_crmentity.crmid AS "' . $this->primarymodule . '__LBL_ACTION"';
		}

		// Save the information
		$this->_columnslist = $columnslist;

		\App\Log::trace('ReportRun :: Successfully returned getQueryColumnsList' . $reportid);
		return $columnslist;
	}

	public function getColumnSQL($selectedfields)
	{
		$adb = PearDatabase::getInstance();
		$header_label = $selectedfields[2]; // Header label to be displayed in the reports table

		list($module, $field) = explode('__', $selectedfields[2]);
		$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . '.first_name', 'last_name' => $selectedfields[0] . '.last_name'), 'Users');
		$moduleInstance = CRMEntity::getInstance($module);
		$this->queryPlanner->addTable($moduleInstance->table_name);
		if ($selectedfields[4] == 'C') {
			$field_label_data = explode('__', $selectedfields[2]);
			$module = $field_label_data[0];
			if ($module != $this->primarymodule) {
				$columnSQL = 'case when (' . $selectedfields[0] . '.' . $selectedfields[1] . "='1')then 'yes' else case when (vtiger_crmentity$module.crmid !='') then 'no' else '-' end end AS '" . decode_html($selectedfields[2]) . "'";
				$this->queryPlanner->addTable("vtiger_crmentity$module");
			} else {
				if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule) {
					$columnSQL = 'case when ( vtiger_crmentity.' . $selectedfields[1] . "='1')then 'yes' else case when (vtiger_crmentity.crmid !='') then 'no' else '-' end end AS '" . decode_html($selectedfields[2]) . "'";
				} else {
					$columnSQL = 'case when (' . $selectedfields[0] . '.' . $selectedfields[1] . "='1')then 'yes' else case when (vtiger_crmentity.crmid !='') then 'no' else '-' end end AS '" . decode_html($selectedfields[2]) . "'";
					$this->queryPlanner->addTable($selectedfields[0]);
				}
			}
		} elseif ($selectedfields[4] == 'D' || $selectedfields[4] == 'DT') {
			if ($selectedfields[5] == 'Y') {
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					$columnSQL = "YEAR(cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME)) AS Calendar__Start__Date__and__Time__Year";
				} else if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule) {
					$columnSQL = 'YEAR(vtiger_crmentity.' . $selectedfields[1] . ") AS '" . decode_html($header_label) . "__Year'";
				} else {
					$columnSQL = 'YEAR(' . $selectedfields[0] . '.' . $selectedfields[1] . ") AS '" . decode_html($header_label) . "__Year'";
				}
				$this->queryPlanner->addTable($selectedfields[0]);
			} elseif ($selectedfields[5] == 'M') {
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					$columnSQL = "MONTHNAME(cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME)) AS Calendar__Start__Date__and__Time__Month";
				} else if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule) {
					$columnSQL = "MONTHNAME(vtiger_crmentity." . $selectedfields[1] . ") AS '" . decode_html($header_label) . "__Month'";
				} else {
					$columnSQL = 'MONTHNAME(' . $selectedfields[0] . "." . $selectedfields[1] . ") AS '" . decode_html($header_label) . "__Month'";
				}
				$this->queryPlanner->addTable($selectedfields[0]);
			} elseif ($selectedfields[5] == 'MY') { // used in charts to get the year also, which will be used for click throughs
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					$columnSQL = "date_format(cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME), '%M %Y') AS Calendar__Start__Date__and__Time__Month";
				} else if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule) {
					$columnSQL = 'date_format(vtiger_crmentity.' . $selectedfields[1] . ", '%M %Y') AS '" . decode_html($header_label) . "__Month'";
				} else {
					$columnSQL = 'date_format(' . $selectedfields[0] . "." . $selectedfields[1] . ", '%M %Y') AS '" . decode_html($header_label) . "__Month'";
				}
				$this->queryPlanner->addTable($selectedfields[0]);
			} else {
				$userModel = Users_Record_Model::getCurrentUserModel();
				$userformat = $userModel->get('date_format');
				$userformat = str_replace('dd', '%d', $userformat);
				$userformat = str_replace('yyyy', '%Y', $userformat);
				$userformat = str_replace('rrrr', '%Y', $userformat);
				$userformat = str_replace('mm', '%m', $userformat);
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					$columnSQL = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME) AS Calendar__Start__Date__and__Time";
				} else if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule) {
					$columnSQL = "date_format(vtiger_crmentity." . $selectedfields[1] . ",'$userformat') AS '" . decode_html($header_label) . "'";
				} else {
					$columnSQL = "date_format (" . $selectedfields[0] . "." . $selectedfields[1] . ",'$userformat') AS '" . decode_html($header_label) . "'";
				}

				$this->queryPlanner->addTable($selectedfields[0]);
			}
		} elseif ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'status') {
			$columnSQL = 'vtiger_activity.status AS Calendar__Status';
		} elseif ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
			$columnSQL = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME) AS Calendar__Start__Date__and__Time";
		} elseif (stristr($selectedfields[0], 'vtiger_users') && ($selectedfields[1] == 'user_name')) {
			$temp_module_from_tablename = str_replace('vtiger_users', '', $selectedfields[0]);
			if ($module != $this->primarymodule) {
				$condition = 'and vtiger_crmentity' . $module . ".crmid!=''";
				$this->queryPlanner->addTable("vtiger_crmentity$module");
			} else {
				$condition = "and vtiger_crmentity.crmid!=''";
			}
			if ($temp_module_from_tablename == $module) {
				$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
				$columnSQL = " case when(" . $selectedfields[0] . ".last_name NOT LIKE '' $condition ) THEN " . $concatSql . " else vtiger_groups" . $module . ".groupname end AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable('vtiger_groups' . $module); // Auto-include the dependent module table.
			} else {//Some Fields can't assigned to groups so case avoided (fields like inventory manager)
				$columnSQL = $selectedfields[0] . ".user_name AS '" . decode_html($header_label) . "'";
			}
			$this->queryPlanner->addTable($selectedfields[0]);
		} elseif (stristr($selectedfields[0], "vtiger_crmentity") && ($selectedfields[1] == 'modifiedby')) {
			$targetTableName = 'vtiger_lastModifiedBy' . $module;
			$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('last_name' => $targetTableName . '.last_name', 'first_name' => $targetTableName . '.first_name'), 'Users');
			$columnSQL = "trim($concatSql) AS $header_label";
			$this->queryPlanner->addTable("vtiger_crmentity$module");
			$this->queryPlanner->addTable($targetTableName);

			// Added when no fields from the secondary module is selected but lastmodifiedby field is selected
			$moduleInstance = CRMEntity::getInstance($module);
			$this->queryPlanner->addTable($moduleInstance->table_name);
		} else if (stristr($selectedfields[0], "vtiger_crmentity") && ($selectedfields[1] == 'smcreatorid')) {
			$targetTableName = 'vtiger_createdby' . $module;
			$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('last_name' => $targetTableName . '.last_name', 'first_name' => $targetTableName . '.first_name'), 'Users');
			$columnSQL = "trim($concatSql) AS " . decode_html($header_label) . "";
			$this->queryPlanner->addTable("vtiger_crmentity$module");
			$this->queryPlanner->addTable($targetTableName);

			// Added when no fields from the secondary module is selected but creator field is selected
			$moduleInstance = CRMEntity::getInstance($module);
			$this->queryPlanner->addTable($moduleInstance->table_name);
		} else if (stristr($selectedfields[0], "vtiger_crmentity") && ($selectedfields[1] == 'shownerid')) {
			$targetTableName = 'vtiger_shOwners' . $module;
			$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('last_name' => $targetTableName . '.last_name', 'first_name' => $targetTableName . '.first_name'), 'Users');
			$columnSQL = "trim($concatSql) AS $header_label";
			$this->queryPlanner->addTable("u_yf_crmentity_showners");
			$this->queryPlanner->addTable("vtiger_crmentity$module");
			$this->queryPlanner->addTable($targetTableName);

			// Added when no fields from the secondary module is selected but lastmodifiedby field is selected
			$moduleInstance = CRMEntity::getInstance($module);

			$this->queryPlanner->addTable($moduleInstance->table_name);
		} elseif ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
			$columnSQL = "vtiger_crmentity." . $selectedfields[1] . " AS '" . decode_html($header_label) . "'";
		} elseif ($selectedfields[0] == 'vtiger_products' && $selectedfields[1] == 'unit_price') {
			$columnSQL = "concat(" . $selectedfields[0] . ".currency_id,'::',innerProduct.actual_unit_price) AS '" . decode_html($header_label) . "'";
			$this->queryPlanner->addTable("innerProduct");
		} elseif (in_array($selectedfields[2], $this->append_currency_symbol_to_value)) {
			if ($selectedfields[1] == 'discount_amount') {
				$columnSQL = "CONCAT(" . $selectedfields[0] . ".currency_id,'::', IF(" . $selectedfields[0] . ".discount_amount != ''," . $selectedfields[0] . ".discount_amount, (" . $selectedfields[0] . ".discount_percent/100) * " . $selectedfields[0] . ".subtotal)) AS " . decode_html($header_label);
			} else {
				$columnSQL = "concat(" . $selectedfields[0] . ".currency_id,'::'," . $selectedfields[0] . "." . $selectedfields[1] . ") AS '" . decode_html($header_label) . "'";
			}
		} elseif ($selectedfields[0] == 'vtiger_notes' && ($selectedfields[1] == 'filelocationtype' || $selectedfields[1] == 'filesize' || $selectedfields[1] == 'folderid' || $selectedfields[1] == 'filestatus')) {
			if ($selectedfields[1] == 'filelocationtype') {
				$columnSQL = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when 'I' then 'Internal' when 'E' then 'External' else '-' end AS '" . decode_html($selectedfields[2]) . "'";
			} else if ($selectedfields[1] == 'folderid') {
				$columnSQL = "`vtiger_trees_templates_data`.name AS '$selectedfields[2]'";
				$this->queryPlanner->addTable("`vtiger_trees_templates_data`");
			} elseif ($selectedfields[1] == 'filestatus') {
				$columnSQL = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when '1' then 'yes' when '0' then 'no' else '-' end AS '" . decode_html($selectedfields[2]) . "'";
			} elseif ($selectedfields[1] == 'filesize') {
				$columnSQL = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when '' then '-' else concat(" . $selectedfields[0] . "." . $selectedfields[1] . "/1024,'  ','KB') end AS '" . decode_html($selectedfields[2]) . "'";
			}
		} elseif ($selectedfields[0] == 'vtiger_inventoryproductrel') {
			if ($selectedfields[1] == 'discount_amount') {
				$columnSQL = " case when (vtiger_inventoryproductrel{$module}.discount_amount != '') then vtiger_inventoryproductrel{$module}.discount_amount else ROUND((vtiger_inventoryproductrel{$module}.listprice * vtiger_inventoryproductrel{$module}.quantity * (vtiger_inventoryproductrel{$module}.discount_percent/100)),3) end AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable($selectedfields[0] . $module);
			} else if ($selectedfields[1] == 'productid') {
				$columnSQL = "vtiger_products{$module}.productname AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable("vtiger_products{$module}");
			} else if ($selectedfields[1] == 'serviceid') {
				$columnSQL = "vtiger_service{$module}.servicename AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable("vtiger_service{$module}");
			} else if ($selectedfields[1] == 'listprice') {
				$moduleInstance = CRMEntity::getInstance($module);
				$columnSQL = $selectedfields[0] . $module . "." . $selectedfields[1] . "/" . $moduleInstance->table_name . ".conversion_rate AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable($selectedfields[0] . $module);
			} else {
				$columnSQL = $selectedfields[0] . $module . "." . $selectedfields[1] . " AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable($selectedfields[0] . $module);
			}
		} else {
			$columnSQL = $selectedfields[0] . "." . $selectedfields[1] . " AS '" . decode_html($header_label) . "'";
			$this->queryPlanner->addTable($selectedfields[0]);
		}
		return $columnSQL;
	}

	/** Function to get field columns based on profile
	 *  @ param $module : Type string
	 *  returns permitted fields in array format
	 */
	public function getaccesfield($module)
	{
		$currentUser = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$adb = PearDatabase::getInstance();
		$access_fields = Array();

		$profileList = $currentUser->getProfiles();
		$query = "select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where";
		$params = array();
		if ($module == "Calendar") {
			if (count($profileList) > 0) {
				$query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype <> 4 and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0
								and vtiger_field.presence IN (0,2) and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
				array_push($params, $profileList);
			} else {
				$query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype <> 4 and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0
								and vtiger_field.presence IN (0,2) group by vtiger_field.fieldid order by block,sequence";
			}
		} else {
			array_push($params, $module);
			if (count($profileList) > 0) {
				$query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?)) and vtiger_field.displaytype <> 4 and vtiger_profile2field.visible=0
								and vtiger_field.presence IN (0,2) and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
				array_push($params, $profileList);
			} else {
				$query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?)) and vtiger_field.displaytype <> 4 and vtiger_profile2field.visible=0
								and vtiger_field.presence IN (0,2) and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence";
			}
		}
		$result = $adb->pquery($query, $params);

		while ($collistrow = $adb->fetch_array($result)) {
			$access_fields[] = $collistrow["fieldname"];
		}
		//added to include ticketid for Reports module in select columnlist for all users
		if ($module == "HelpDesk")
			$access_fields[] = "ticketid";
		return $access_fields;
	}

	/** Function to get Escapedcolumns for the field in case of multiple parents
	 *  @ param $selectedfields : Type Array
	 *  returns the case query for the escaped columns
	 */
	public function getEscapedColumns($selectedfields)
	{

		$tableName = $selectedfields[0];
		$columnName = $selectedfields[1];
		$moduleFieldLabel = $selectedfields[2];
		$fieldName = $selectedfields[3];
		list($moduleName, $fieldLabel) = explode('__', $moduleFieldLabel, 2);
		$fieldLabel = str_replace('__', '_', $fieldLabel);
		$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);

		if ($moduleName == 'ModComments' && $fieldName == 'creator') {
			$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_usersModComments.first_name',
					'last_name' => 'vtiger_usersModComments.last_name'), 'Users');
			$queryColumn = "trim(case when (vtiger_usersModComments.user_name not like '' and vtiger_crmentity.crmid!='') then $concatSql end) AS ModComments_Creator";
			$this->queryPlanner->addTable('vtiger_usersModComments');
			$this->queryPlanner->addTable("vtiger_usersModComments");
		} elseif (($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype'])) && $fieldInfo['uitype'] != '52' && $fieldInfo['uitype'] != '53') {
			$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
			if (count($fieldSqlColumns) > 0) {
				$queryColumn = "(CASE WHEN $tableName.$columnName NOT LIKE '' THEN (CASE";
				foreach ($fieldSqlColumns as $columnSql) {
					$queryColumn .= " WHEN $columnSql NOT LIKE '' THEN $columnSql";
				}
				$moduleFieldLabel = App\Purifier::purify(decode_html($moduleFieldLabel));
				$queryColumn .= " ELSE '' END) ELSE '' END) AS '$moduleFieldLabel'";
				$this->queryPlanner->addTable($tableName);
			}
		}
		return $queryColumn;
	}

	/** Function to get selectedcolumns for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the query of columnlist for the selected columns
	 */
	public function getSelectedColumnsList($reportid)
	{

		$adb = PearDatabase::getInstance();
		global $modules;


		$ssql = "select vtiger_selectcolumn.* from vtiger_report inner join vtiger_selectquery on vtiger_selectquery.queryid = vtiger_report.queryid";
		$ssql .= " left join vtiger_selectcolumn on vtiger_selectcolumn.queryid = vtiger_selectquery.queryid where vtiger_report.reportid = ? ";
		$ssql .= " order by vtiger_selectcolumn.columnindex";

		$result = $adb->pquery($ssql, array($reportid));
		$noofrows = $adb->num_rows($result);

		if ($this->orderbylistsql != "") {
			$sSQL .= $this->orderbylistsql . ", ";
		}

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, "columnname");
			$ordercolumnsequal = true;
			if ($fieldcolname != "") {
				$countOrderByListColumns = count($this->orderbylistcolumns);
				for ($j = 0; $j < $countOrderByListColumns; $j++) {
					if ($this->orderbylistcolumns[$j] == $fieldcolname) {
						$ordercolumnsequal = false;
						break;
					} else {
						$ordercolumnsequal = true;
					}
				}
				if ($ordercolumnsequal) {
					$selectedfields = explode(":", $fieldcolname);
					if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
						$selectedfields[0] = "vtiger_crmentity";
					$sSQLList[] = $selectedfields[0] . "." . $selectedfields[1] . " '" . $selectedfields[2] . "'";
				}
			}
		}
		$sSQL .= implode(",", $sSQLList);

		\App\Log::trace("ReportRun :: Successfully returned getSelectedColumnsList" . $reportid);
		return $sSQL;
	}

	/** Function to get advanced comparator in query form for the given Comparator and value
	 *  @ param $comparator : Type String
	 *  @ param $value : Type String
	 *  returns the check query for the comparator
	 */
	public function getAdvComparator($comparator, $value, $datatype = "", $columnName = '')
	{

		global $ogReport;
		$adb = PearDatabase::getInstance();

		$default_charset = AppConfig::main('default_charset');
		$value = html_entity_decode(trim($value), ENT_QUOTES, $default_charset);
		$value_len = strlen($value);
		$is_field = false;
		if ($value_len > 1 && $value[0] == '$' && $value[$value_len - 1] == '$') {
			$temp = str_replace('$', '', $value);
			$is_field = true;
		}
		if ($datatype == 'C') {
			$value = str_replace("yes", "1", str_replace("no", "0", $value));
		}

		if ($is_field === true) {
			$value = $this->getFilterComparedField($temp);
		}
		if ($comparator == 'e') {
			if (trim($value) == 'NULL') {
				$rtvalue = ' is NULL';
			} elseif (trim($value) != '') {
				$rtvalue = " = " . $adb->quote($value);
			} elseif (trim($value) == '' && $datatype == 'V') {
				$rtvalue = ' = ' . $adb->quote($value);
			} else {
				$rtvalue = ' is NULL';
			}
		}
		if ($comparator == 'n') {
			if (trim($value) == 'NULL') {
				$rtvalue = ' is NOT NULL';
			} elseif (trim($value) != '') {
				if ($columnName)
					$rtvalue = ' <> ' . $adb->quote($value) . ' OR ' . $columnName . " IS NULL ";
				else
					$rtvalue = ' <> ' . $adb->quote($value);
			}elseif (trim($value) == '' && $datatype == 'V') {
				$rtvalue = ' <> ' . $adb->quote($value);
			} else {
				$rtvalue = ' is NOT NULL';
			}
		}
		if ($comparator == 's') {
			$rtvalue = " like " . formatForSqlLike($value, 2, $is_field);
		}
		if ($comparator == 'ew') {
			$rtvalue = ' like ' . formatForSqlLike($value, 1, $is_field);
		}
		if ($comparator == 'c') {
			$rtvalue = ' like ' . formatForSqlLike($value, 0, $is_field);
		}
		if ($comparator == 'k') {
			$rtvalue = ' not like ' . formatForSqlLike($value, 0, $is_field);
		}
		if ($comparator == 'l') {
			$rtvalue = ' < ' . $adb->quote($value);
		}
		if ($comparator == 'g') {
			$rtvalue = ' > ' . $adb->quote($value);
		}
		if ($comparator == 'm') {
			$rtvalue = ' <= ' . $adb->quote($value);
		}
		if ($comparator == 'h') {
			$rtvalue = ' >= ' . $adb->quote($value);
		}
		if ($comparator == 'b') {
			$rtvalue = ' < ' . $adb->quote($value);
		}
		if ($comparator == 'a') {
			$rtvalue = ' > ' . $adb->quote($value);
		}
		if ($comparator == 'om') {
			$currentUser = Users_Privileges_Model::getCurrentUserModel();
			$rtvalue = ' = ' . $adb->quote($currentUser->getId());
		}
		if ($is_field === true) {
			$rtvalue = str_replace("'", "", $rtvalue);
			$rtvalue = str_replace("\\", "", $rtvalue);
		}
		\App\Log::trace("ReportRun :: Successfully returned getAdvComparator");
		return $rtvalue;
	}

	/** Function to get field that is to be compared in query form for the given Comparator and field
	 *  @ param $field : field
	 *  returns the value for the comparator
	 */
	public function getFilterComparedField($field)
	{
		global $ogReport;
		$adb = PearDatabase::getInstance();
		if (!empty($this->secondarymodule)) {
			$secModules = explode(':', $this->secondarymodule);
			foreach ($secModules as $secModule) {
				$secondary = CRMEntity::getInstance($secModule);
				$this->queryPlanner->addTable($secondary->table_name);
			}
		}
		$field = explode('#', $field);
		$module = $field[0];
		$fieldname = trim($field[1]);
		$tabid = \App\Module::getModuleId($module);
		$field_query = $adb->pquery("SELECT tablename,columnname,typeofdata,fieldname,uitype FROM vtiger_field WHERE tabid = ? && fieldname= ?", array($tabid, $fieldname));
		$fieldtablename = $adb->query_result($field_query, 0, 'tablename');
		$fieldcolname = $adb->query_result($field_query, 0, 'columnname');
		$typeofdata = $adb->query_result($field_query, 0, 'typeofdata');
		$fieldtypeofdata = \vtlib\Functions::transformFieldTypeOfData($fieldtablename, $fieldcolname, $typeofdata[0]);
		$uitype = $adb->query_result($field_query, 0, 'uitype');
		if ($uitype == 68 || $uitype == 59) {
			$fieldtypeofdata = 'V';
		}
		if ($fieldtablename == "vtiger_crmentity" && $module != $this->primarymodule) {
			$fieldtablename = $fieldtablename . $module;
		}
		if ($fieldname == "assigned_user_id") {
			$fieldtablename = "vtiger_users" . $module;
			$fieldcolname = "user_name";
		}
		if ($fieldtablename == "vtiger_crmentity" && $fieldname == "modifiedby") {
			$fieldtablename = "vtiger_lastModifiedBy" . $module;
			$fieldcolname = "user_name";
		}
		if ($fieldname == "assigned_user_id1") {
			$fieldtablename = "vtiger_usersRel1";
			$fieldcolname = "user_name";
		}

		$value = $fieldtablename . "." . $fieldcolname;

		$this->queryPlanner->addTable($fieldtablename);
		return $value;
	}

	/** Function to get the advanced filter columns for the reportid
	 *  This function accepts the $reportid
	 *  This function returns  $columnslist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
	 * 					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
	 * 					      					|
	 * 					      $tablenamen:$columnnamen:$fieldlabeln:$fieldnamen:$typeofdatan=>$tablenamen.$columnnamen filtercriteria
	 * 				      	     )
	 *
	 */
	public function getAdvFilterList($reportid)
	{
		$adb = PearDatabase::getInstance();


		$advft_criteria = array();

		$sql = 'SELECT * FROM vtiger_relcriteria_grouping WHERE queryid = ? ORDER BY groupid';
		$groupsresult = $adb->pquery($sql, array($reportid));

		$i = 1;
		$j = 0;
		while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
			$groupId = $relcriteriagroup["groupid"];
			$groupCondition = $relcriteriagroup["group_condition"];

			$ssql = 'select vtiger_relcriteria.* from vtiger_report
						inner join vtiger_relcriteria on vtiger_relcriteria.queryid = vtiger_report.queryid
						left join vtiger_relcriteria_grouping on vtiger_relcriteria.queryid = vtiger_relcriteria_grouping.queryid
								and vtiger_relcriteria.groupid = vtiger_relcriteria_grouping.groupid';
			$ssql .= " where vtiger_report.reportid = ? && vtiger_relcriteria.groupid = ? order by vtiger_relcriteria.columnindex";

			$result = $adb->pquery($ssql, array($reportid, $groupId));
			$noOfColumns = $adb->num_rows($result);
			if ($noOfColumns <= 0)
				continue;

			while ($relcriteriarow = $adb->fetch_array($result)) {
				$columnIndex = $relcriteriarow["columnindex"];
				$criteria = array();
				$criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"]);
				$criteria['comparator'] = $relcriteriarow["comparator"];
				$advfilterval = $relcriteriarow["value"];
				$col = explode(":", $relcriteriarow["columnname"]);
				$criteria['value'] = $advfilterval;
				$criteria['column_condition'] = $relcriteriarow["column_condition"];

				$advft_criteria[$i]['columns'][$j] = $criteria;
				$advft_criteria[$i]['condition'] = $groupCondition;
				$j++;

				$this->queryPlanner->addTable($col[0]);
			}
			if (!empty($advft_criteria[$i]['columns'][$j - 1]['column_condition'])) {
				$advft_criteria[$i]['columns'][$j - 1]['column_condition'] = '';
			}
			$i++;
		}
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advft_criteria[$i - 1]['condition']))
			$advft_criteria[$i - 1]['condition'] = '';
		return $advft_criteria;
	}

	public function generateAdvFilterSql($advfilterlist)
	{

		$adb = PearDatabase::getInstance();

		$advfiltersql = '';
		foreach ($advfilterlist as $groupindex => $groupinfo) {
			$groupcondition = $groupinfo['condition'];
			$groupcolumns = $groupinfo['columns'];

			if (count($groupcolumns) > 0) {

				$advfiltergroupsql = "";
				foreach ($groupcolumns as $columnindex => $columninfo) {
					$fieldcolname = $columninfo["columnname"];
					$comparator = $columninfo["comparator"];
					$value = $columninfo["value"];
					$columncondition = $columninfo["column_condition"];
					$advcolsql = array();

					if ($fieldcolname != "" && $comparator != "") {
						if (in_array($comparator, \App\CustomView::STD_FILTER_CONDITIONS)) {
							if ($fieldcolname != 'none') {
								$selectedFields = explode(':', $fieldcolname);
								if ($selectedFields[0] == 'vtiger_crmentity' . $this->primarymodule) {
									$selectedFields[0] = 'vtiger_crmentity';
								}

								if ($comparator != 'custom') {
									list($startDate, $endDate) = $this->getStandarFiltersStartAndEndDate($comparator);
								} else {
									list($startDateTime, $endDateTime) = explode(',', $value);
									list($startDate, $startTime) = explode(' ', $startDateTime);
									list($endDate, $endTime) = explode(' ', $endDateTime);
								}

								$type = $selectedFields[4];
								if ($startDate != '0000-00-00' && $endDate != '0000-00-00' && $startDate != '' && $endDate != '') {
									$startDateTime = new DateTimeField($startDate . ' ' . date('H:i:s'));
									$userStartDate = $startDateTime->getDisplayDate();
									if ($type == 'DT') {
										$userStartDate = $userStartDate . ' 00:00:00';
									}
									$startDateTime = getValidDBInsertDateTimeValue($userStartDate);

									$endDateTime = new DateTimeField($endDate . ' ' . date('H:i:s'));
									$userEndDate = $endDateTime->getDisplayDate();
									if ($type == 'DT') {
										$userEndDate = $userEndDate . ' 23:59:59';
									}
									$endDateTime = getValidDBInsertDateTimeValue($userEndDate);

									if ($selectedFields[1] == 'birthday') {
										$tableColumnSql = 'DATE_FORMAT(' . $selectedFields[0] . '.' . $selectedFields[1] . ', "%m%d")';
										$startDateTime = "DATE_FORMAT('$startDateTime', '%m%d')";
										$endDateTime = "DATE_FORMAT('$endDateTime', '%m%d')";
									} else {
										if ($selectedFields[0] == 'vtiger_activity' && ($selectedFields[1] == 'date_start')) {
											$tableColumnSql = 'CAST((CONCAT(date_start, " ", time_start)) AS DATETIME)';
										} else {
											$tableColumnSql = $selectedFields[0] . '.' . $selectedFields[1];
										}
										$startDateTime = "'$startDateTime'";
										$endDateTime = "'$endDateTime'";
									}

									$advfiltergroupsql .= "$tableColumnSql BETWEEN $startDateTime AND $endDateTime";
									if (!empty($columncondition)) {
										$advfiltergroupsql .= ' ' . $columncondition . ' ';
									}

									$this->queryPlanner->addTable($selectedFields[0]);
								}
							}
							continue;
						}
						$selectedFields = explode(":", $fieldcolname);
						$tempComparators = array('e', 'n', 'bw', 'a', 'b');
						if ($selectedFields[4] == 'DT' && in_array($comparator, $tempComparators)) {
							if ($selectedFields[0] == 'vtiger_crmentity' . $this->primarymodule) {
								$selectedFields[0] = 'vtiger_crmentity';
							}

							if ($selectedFields[0] == 'vtiger_activity' && ($selectedFields[1] == 'date_start')) {
								$tableColumnSql = 'CAST((CONCAT(date_start, " ", time_start)) AS DATETIME)';
							} else {
								$tableColumnSql = $selectedFields[0] . '.' . $selectedFields[1];
							}

							if ($value != null && $value != '') {
								if ($comparator == 'e' || $comparator == 'n') {
									$dateTimeComponents = explode(' ', $value);
									$dateTime = new DateTime($dateTimeComponents[0] . ' ' . '00:00:00');
									$date1 = $dateTime->format('Y-m-d H:i:s');
									$dateTime->modify("+1 days");
									$date2 = $dateTime->format('Y-m-d H:i:s');
									$tempDate = strtotime($date2) - 1;
									$date2 = date('Y-m-d H:i:s', $tempDate);

									$start = getValidDBInsertDateTimeValue($date1);
									$end = getValidDBInsertDateTimeValue($date2);
									$start = "'$start'";
									$end = "'$end'";
									if ($comparator == 'e')
										$advfiltergroupsql .= "$tableColumnSql BETWEEN $start AND $end";
									else
										$advfiltergroupsql .= "$tableColumnSql NOT BETWEEN $start AND $end";
								}else if ($comparator == 'bw') {
									$values = explode(',', $value);
									$startDateTime = explode(' ', $values[0]);
									$endDateTime = explode(' ', $values[1]);

									$startDateTime = new DateTimeField($startDateTime[0] . ' ' . date('H:i:s'));
									$userStartDate = $startDateTime->getDisplayDate();
									$userStartDate = $userStartDate . ' 00:00:00';
									$start = getValidDBInsertDateTimeValue($userStartDate);

									$endDateTime = new DateTimeField($endDateTime[0] . ' ' . date('H:i:s'));
									$userEndDate = $endDateTime->getDisplayDate();
									$userEndDate = $userEndDate . ' 23:59:59';
									$end = getValidDBInsertDateTimeValue($userEndDate);

									$advfiltergroupsql .= "$tableColumnSql BETWEEN '$start' AND '$end'";
								} else if ($comparator == 'a' || $comparator == 'b') {
									$value = explode(' ', $value);
									$dateTime = new DateTime($value[0]);
									if ($comparator == 'a') {
										$modifiedDate = $dateTime->modify('+1 days');
										$nextday = $modifiedDate->format('Y-m-d H:i:s');
										$temp = strtotime($nextday) - 1;
										$date = date('Y-m-d H:i:s', $temp);
										$value = getValidDBInsertDateTimeValue($date);
										$advfiltergroupsql .= "$tableColumnSql > '$value'";
									} else {
										$prevday = $dateTime->format('Y-m-d H:i:s');
										$temp = strtotime($prevday) - 1;
										$date = date('Y-m-d H:i:s', $temp);
										$value = getValidDBInsertDateTimeValue($date);
										$advfiltergroupsql .= "$tableColumnSql < '$value'";
									}
								}
								if (!empty($columncondition)) {
									$advfiltergroupsql .= ' ' . $columncondition . ' ';
								}
								$this->queryPlanner->addTable($selectedFields[0]);
							}
							continue;
						}
						$selectedfields = explode(":", $fieldcolname);
						$moduleFieldLabel = $selectedfields[2];
						list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
						$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);
						$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
						// Added to handle the crmentity table name for Primary module
						if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
							$selectedfields[0] = "vtiger_crmentity";
						}
						//Added to handle yes or no for checkbox  field in reports advance filters. -shahul
						if ($selectedfields[4] == 'C') {
							if (strcasecmp(trim($value), "yes") == 0)
								$value = "1";
							if (strcasecmp(trim($value), "no") == 0)
								$value = "0";
						}
						if (in_array($comparator, \App\CustomView::STD_FILTER_CONDITIONS)) {
							$columninfo['stdfilter'] = $columninfo['comparator'];
							$valueComponents = explode(',', $columninfo['value']);
							if ($comparator == 'custom') {
								if ($selectedfields[4] == 'DT') {
									$startDateTimeComponents = explode(' ', $valueComponents[0]);
									$endDateTimeComponents = explode(' ', $valueComponents[1]);
									$columninfo['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
									$columninfo['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
								} else {
									$columninfo['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
									$columninfo['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
								}
							}
							$dateFilterResolvedList = \App\CustomView::resolveDateFilterValue($columninfo);
							$startDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['startdate']);
							$endDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['enddate']);
							$columninfo['value'] = $value = implode(',', array($startDate, $endDate));
							$comparator = 'bw';
						}
						$valuearray = explode(",", trim($value));
						$datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";
						if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {

							$advcolumnsql = "";
							$countValueArray = count($valuearray);
							for ($n = 0; $n < $countValueArray; $n++) {

								if (($selectedfields[0] == "vtiger_users" . $this->primarymodule || $selectedfields[0] == "vtiger_users" . $this->secondarymodule) && $selectedfields[1] == 'user_name') {
									$module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
									if (is_numeric($valuearray[$n])) {
										$advcolsql[] = '(' . $selectedfields[0] . '.id ' . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . " OR vtiger_groups$module_from_tablename.groupid " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . ')';
									} else {
										$advcolsql[] = " (trim($concatSql)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . " or vtiger_groups$module_from_tablename.groupname " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . ")";
									}
									$this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
								} elseif ($selectedfields[1] == 'status') {//when you use comma seperated values.
									if ($selectedfields[2] == 'Calendar_Status') {
										$advcolsql[] = "vtiger_activity.status" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									} else if ($selectedfields[2] == 'HelpDesk_Status') {
										$advcolsql[] = "vtiger_troubletickets.status" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									} else if ($selectedfields[2] == 'Faq_Status') {
										$advcolsql[] = "vtiger_faq.status" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									} else
										$advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
								} elseif ($selectedfields[1] == 'description') {//when you use comma seperated values.
									if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule)
										$advcolsql[] = "vtiger_crmentity.description" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									else
										$advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
								} elseif ($selectedfields[2] == 'Quotes_Inventory_Manager') {
									$advcolsql[] = ("trim($concatSql)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype));
								} elseif ($selectedfields[1] == 'modifiedby') {
									$module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
									if ($module_from_tablename != '') {
										$tableName = 'vtiger_lastModifiedBy' . $module_from_tablename;
									} else {
										$tableName = 'vtiger_lastModifiedBy' . $this->primarymodule;
									}
									if (is_numeric($valuearray[$n]) || empty($valuearray[$n])) {
										$advcolsql [] = '(' . $tableName . '.id' . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . ')';
									} else {
										$advcolsql[] = 'trim(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') . ')' .
											$this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									}
									$this->queryPlanner->addTable($tableName);
								} elseif ($selectedfields[1] == 'shownerid') {
									$advcolsql[] = ' u_yf_crmentity_showners.userid' . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									$this->queryPlanner->addTable('u_yf_crmentity_showners');
								} else {
									$advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
								}
							}
							//If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
							if ($comparator == 'n' || $comparator == 'k')
								$advcolumnsql = implode(" and ", $advcolsql);
							else
								$advcolumnsql = implode(" or ", $advcolsql);
							$fieldvalue = " (" . $advcolumnsql . ") ";
						} elseif ($selectedfields[1] == 'user_name') {
							if ($selectedfields[0] == "vtiger_users" . $this->primarymodule) {
								$module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
								if (is_numeric($value) || empty($value)) {
									$fieldvalue = '(' . $selectedfields[0] . '.id' . $this->getAdvComparator($comparator, trim($value), $datatype) . " OR vtiger_groups$module_from_tablename.groupid" . $this->getAdvComparator($comparator, trim($value), $datatype) . ')';
								} else {
									$fieldvalue = " trim(case when (" . $selectedfields[0] . ".last_name NOT LIKE '') then " . $concatSql . " else vtiger_groups" . $module_from_tablename . ".groupname end) " . $this->getAdvComparator($comparator, trim($value), $datatype);
								}
								$this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
							} else {
								$secondaryModules = explode(':', $this->secondarymodule);
								$firstSecondaryModule = "vtiger_users" . $secondaryModules[0];
								$secondSecondaryModule = "vtiger_users" . $secondaryModules[1];
								if (($firstSecondaryModule && $firstSecondaryModule == $selectedfields[0]) || ($secondSecondaryModule && $secondSecondaryModule == $selectedfields[0])) {
									$module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
									$moduleInstance = CRMEntity::getInstance($module_from_tablename);
									if (is_numeric($value)) {
										$fieldvalue = '(' . $selectedfields[0] . '.id' . $this->getAdvComparator($comparator, trim($value), $datatype) . " OR vtiger_groups$module_from_tablename.groupid" . $this->getAdvComparator($comparator, trim($value), $datatype) . ')';
									} else {
										$fieldvalue = " trim(case when (" . $selectedfields[0] . ".last_name NOT LIKE '') then " . $concatSql . " else vtiger_groups" . $module_from_tablename . ".groupname end) " . $this->getAdvComparator($comparator, trim($value), $datatype);
									}
									$this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
									$this->queryPlanner->addTable($moduleInstance->table_name);
								}
							}
						} elseif ($comparator == 'bw' && count($valuearray) == 2) {
							if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
								$fieldvalue = "(" . "vtiger_crmentity." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
							} else {
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
							}
						} elseif ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
							$fieldvalue = "vtiger_crmentity." . $selectedfields[1] . " " . $this->getAdvComparator($comparator, trim($value), $datatype);
						} elseif ($selectedfields[2] == 'Quotes_Inventory_Manager') {
							$fieldvalue = ("trim($concatSql)" . $this->getAdvComparator($comparator, trim($value), $datatype));
						} elseif ($selectedfields[1] == 'modifiedby') {
							$module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
							if ($module_from_tablename != '') {
								$tableName = 'vtiger_lastModifiedBy' . $module_from_tablename;
							} else {
								$tableName = 'vtiger_lastModifiedBy' . $this->primarymodule;
							}
							if (is_numeric($value) || empty($value)) {
								$fieldvalue = '(' . $tableName . '.id' . $this->getAdvComparator($comparator, trim($value), $datatype) . ')';
							} else {
								$fieldvalue = 'trim(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') . ')' .
									$this->getAdvComparator($comparator, trim($value), $datatype);
							}
							$this->queryPlanner->addTable($tableName);
						} elseif ($selectedfields[1] == 'smcreatorid') {
							$module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
							if ($module_from_tablename != '') {
								$tableName = 'vtiger_createdby' . $module_from_tablename;
							} else {
								$tableName = 'vtiger_createdby' . $this->primarymodule;
							}
							if ($moduleName == 'ModComments') {
								$tableName = 'vtiger_users' . $moduleName;
							}
							if (is_numeric($value) || empty($value)) {
								$fieldvalue = '(' . $tableName . '.id' . $this->getAdvComparator($comparator, trim($value), $datatype) . ')';
							} else {
								$fieldvalue = 'trim(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') . ')' .
									$this->getAdvComparator($comparator, trim($value), $datatype);
							}

							$this->queryPlanner->addTable($tableName);
						} elseif ($selectedfields[1] == 'shownerid') {
							$fieldvalue = ' u_yf_crmentity_showners.userid' . $this->getAdvComparator($comparator, trim($value), $datatype);
							$this->queryPlanner->addTable('u_yf_crmentity_showners');
						} elseif ($selectedfields[0] == "vtiger_activity" && ($selectedfields[1] == 'status' || $selectedfields[1] == 'activitystatus')) {
							// for "Is Empty" condition we need to check with "value NOT NULL" || "value = ''" conditions
							if ($comparator == 'y') {
								$fieldvalue = "(case when vtiger_activity.status IS NULL || vtiger_activity.status = ''";
							} else {
								$fieldvalue = "vtiger_activity.status" . $this->getAdvComparator($comparator, trim($value), $datatype);
							}
						} else if ($comparator == 'ny') {
							if ($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype']))
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NOT NULL && " . $selectedfields[0] . "." . $selectedfields[1] . " != '' && " . $selectedfields[0] . "." . $selectedfields[1] . "  != '0')";
							else
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NOT NULL && " . $selectedfields[0] . "." . $selectedfields[1] . " != '')";
						}elseif ($comparator == 'y' || ($comparator == 'e' && (trim($value) == "NULL" || trim($value) == ''))) {
							if ($selectedfields[0] == 'vtiger_inventoryproductrel') {
								$selectedfields[0] = 'vtiger_inventoryproductrel' . $moduleName;
							}
							if ($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype']))
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NULL || " . $selectedfields[0] . "." . $selectedfields[1] . " = '' || " . $selectedfields[0] . "." . $selectedfields[1] . " = '0')";
							else
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NULL || " . $selectedfields[0] . "." . $selectedfields[1] . " = '')";
						} elseif ($selectedfields[0] == 'vtiger_inventoryproductrel') {
							if ($selectedfields[1] == 'productid') {
								$fieldvalue = "vtiger_products$moduleName.productname " . $this->getAdvComparator($comparator, trim($value), $datatype);
								$this->queryPlanner->addTable("vtiger_products$moduleName");
							} else if ($selectedfields[1] == 'serviceid') {
								$fieldvalue = "vtiger_service$moduleName.servicename " . $this->getAdvComparator($comparator, trim($value), $datatype);
								$this->queryPlanner->addTable("vtiger_service$moduleName");
							} else {
								//for inventory module table should be follwed by the module name
								$selectedfields[0] = 'vtiger_inventoryproductrel' . $moduleName;
								$fieldvalue = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, $value, $datatype);
							}
						} elseif ($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype'])) {

							$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
							$comparatorValue = $this->getAdvComparator($comparator, trim($value), $datatype, $fieldSqlColumns[0]);
							$fieldSqls = array();

							foreach ($fieldSqlColumns as $columnSql) {
								$fieldSqls[] = $columnSql . $comparatorValue;
							}
							$fieldvalue = ' (' . implode(' OR ', $fieldSqls) . ') ';
						} else {
							$fieldvalue = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($value), $datatype);
						}

						$advfiltergroupsql .= $fieldvalue;
						if (!empty($columncondition)) {
							$advfiltergroupsql .= ' ' . $columncondition . ' ';
						}

						$this->queryPlanner->addTable($selectedfields[0]);
					}
				}

				if (trim($advfiltergroupsql) != "") {
					$advfiltergroupsql = "( $advfiltergroupsql ) ";
					if (!empty($groupcondition)) {
						$advfiltergroupsql .= ' ' . $groupcondition . ' ';
					}

					$advfiltersql .= $advfiltergroupsql;
				}
			}
		}
		if (trim($advfiltersql) != "")
			$advfiltersql = '(' . $advfiltersql . ')';

		return $advfiltersql;
	}

	public function getAdvFilterSql($reportid)
	{
		// Have we initialized information already?
		if ($this->_advfiltersql !== false) {
			return $this->_advfiltersql;
		}


		$advfilterlist = $this->getAdvFilterList($reportid);
		$advfiltersql = $this->generateAdvFilterSql($advfilterlist);

		// Save the information
		$this->_advfiltersql = $advfiltersql;

		\App\Log::trace("ReportRun :: Successfully returned getAdvFilterSql" . $reportid);
		return $advfiltersql;
	}

	/** Function to get the Standard filter columns for the reportid
	 *  This function accepts the $reportid datatype Integer
	 *  This function returns  $stdfilterlist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
	 * 					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
	 * 				      	     )
	 *
	 */
	public function getStdFilterList($reportid)
	{
		// Have we initialized information already?
		if ($this->_stdfilterlist !== false) {
			return $this->_stdfilterlist;
		}

		$adb = PearDatabase::getInstance();

		$stdfilterlist = array();

		$stdfiltersql = "select vtiger_reportdatefilter.* from vtiger_report";
		$stdfiltersql .= " inner join vtiger_reportdatefilter on vtiger_report.reportid = vtiger_reportdatefilter.datefilterid";
		$stdfiltersql .= " where vtiger_report.reportid = ?";

		$result = $adb->pquery($stdfiltersql, array($reportid));
		$stdfilterrow = $adb->fetch_array($result);
		if (isset($stdfilterrow)) {
			$fieldcolname = $stdfilterrow["datecolumnname"];
			$datefilter = $stdfilterrow["datefilter"];
			$startdate = $stdfilterrow["startdate"];
			$enddate = $stdfilterrow["enddate"];

			if ($fieldcolname != "none") {
				$selectedfields = explode(":", $fieldcolname);
				if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
					$selectedfields[0] = "vtiger_crmentity";

				$moduleFieldLabel = $selectedfields[3];
				list($moduleName, $fieldLabel) = explode('__', $moduleFieldLabel, 2);
				$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);
				$typeOfData = $fieldInfo['typeofdata'];
				list($type, $typeOtherInfo) = explode('~', $typeOfData, 2);

				if ($datefilter != "custom") {
					$startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
					$startdate = $startenddate[0];
					$enddate = $startenddate[1];
				}

				if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $startdate != "" && $enddate != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {

					$startDateTime = new DateTimeField($startdate . ' ' . date('H:i:s'));
					$userStartDate = $startDateTime->getDisplayDate();
					if ($type == 'DT') {
						$userStartDate = $userStartDate . ' 00:00:00';
					}
					$startDateTime = getValidDBInsertDateTimeValue($userStartDate);

					$endDateTime = new DateTimeField($enddate . ' ' . date('H:i:s'));
					$userEndDate = $endDateTime->getDisplayDate();
					if ($type == 'DT') {
						$userEndDate = $userEndDate . ' 23:59:00';
					}
					$endDateTime = getValidDBInsertDateTimeValue($userEndDate);

					if ($selectedfields[1] == 'birthday') {
						$tableColumnSql = "DATE_FORMAT(" . $selectedfields[0] . "." . $selectedfields[1] . ", '%m%d')";
						$startDateTime = "DATE_FORMAT('$startDateTime', '%m%d')";
						$endDateTime = "DATE_FORMAT('$endDateTime', '%m%d')";
					} else {
						if ($selectedfields[0] == 'vtiger_activity' && ($selectedfields[1] == 'date_start')) {
							$tableColumnSql = '';
							$tableColumnSql = "CAST((CONCAT(date_start,' ',time_start)) AS DATETIME)";
						} else {
							$tableColumnSql = $selectedfields[0] . "." . $selectedfields[1];
						}
						$startDateTime = "'$startDateTime'";
						$endDateTime = "'$endDateTime'";
					}

					$stdfilterlist[$fieldcolname] = $tableColumnSql . " between " . $startDateTime . " and " . $endDateTime;
					$this->queryPlanner->addTable($selectedfields[0]);
				}
			}
		}
		// Save the information
		$this->_stdfilterlist = $stdfilterlist;

		\App\Log::trace("ReportRun :: Successfully returned getStdFilterList" . $reportid);
		return $stdfilterlist;
	}

	/** Function to get the RunTime filter columns for the given $filtercolumn,$filter,$startdate,$enddate
	 *  @ param $filtercolumn : Type String
	 *  @ param $filter : Type String
	 *  @ param $startdate: Type String
	 *  @ param $enddate : Type String
	 *  This function returns  $stdfilterlist Array($columnname => $tablename:$columnname:$fieldlabel=>$tablename.$columnname 'between' $startdate 'and' $enddate)
	 *
	 */
	public function RunTimeFilter($filtercolumn, $filter, $startdate, $enddate)
	{
		if ($filtercolumn != "none") {
			$selectedfields = explode(":", $filtercolumn);
			if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
				$selectedfields[0] = "vtiger_crmentity";
			if ($filter == "custom") {
				if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $startdate != "" &&
					$enddate != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
					$stdfilterlist[$filtercolumn] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . " 00:00:00' and '" . $enddate . " 23:59:00'";
				}
			} else {
				if ($startdate != "" && $enddate != "") {
					$startenddate = $this->getStandarFiltersStartAndEndDate($filter);
					if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
						$stdfilterlist[$filtercolumn] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startenddate[0] . " 00:00:00' and '" . $startenddate[1] . " 23:59:00'";
					}
				}
			}
		}
		return $stdfilterlist;
	}

	/** Function to get the RunTime Advanced filter conditions
	 *  @ param $advft_criteria : Type Array
	 *  @ param $advft_criteria_groups : Type Array
	 *  This function returns  $advfiltersql
	 *
	 */
	public function RunTimeAdvFilter($advft_criteria, $advft_criteria_groups)
	{
		$adb = PearDatabase::getInstance();

		$advfilterlist = array();
		$advfiltersql = '';
		if (!empty($advft_criteria)) {
			foreach ($advft_criteria as $column_index => $column_condition) {

				if (empty($column_condition))
					continue;

				$adv_filter_column = $column_condition["columnname"];
				$adv_filter_comparator = $column_condition["comparator"];
				$adv_filter_value = $column_condition["value"];
				$adv_filter_column_condition = $column_condition["columncondition"];
				$adv_filter_groupid = $column_condition["groupid"];

				$column_info = explode(":", $adv_filter_column);

				$moduleFieldLabel = $column_info[2];
				$fieldName = $column_info[3];
				list($module, $fieldLabel) = explode('__', $moduleFieldLabel, 2);
				$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
				$fieldType = null;
				if (!empty($fieldInfo)) {
					$field = WebserviceField::fromArray($adb, $fieldInfo);
					$fieldType = $field->getFieldDataType();
				}

				if ($fieldType == 'currency') {
					// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
					if ($field->getUIType() == '72') {
						$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value, null, true);
					} else {
						$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value);
					}
				}

				$temp_val = explode(",", $adv_filter_value);
				if (($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' )) {
					$val = Array();
					$countTempVal = count($temp_val);
					for ($x = 0; $x < $countTempVal; $x++) {
						if ($column_info[4] == 'D') {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertDateValue();
						} elseif ($column_info[4] == 'DT') {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertDateTimeValue();
						} else {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertTimeValue();
						}
					}
					$adv_filter_value = implode(",", $val);
				}
				$criteria = array();
				$criteria['columnname'] = $adv_filter_column;
				$criteria['comparator'] = $adv_filter_comparator;
				$criteria['value'] = $adv_filter_value;
				$criteria['column_condition'] = $adv_filter_column_condition;

				$advfilterlist[$adv_filter_groupid]['columns'][] = $criteria;
			}

			foreach ($advft_criteria_groups as $group_index => $group_condition_info) {
				if (empty($group_condition_info))
					continue;
				if (empty($advfilterlist[$group_index]))
					continue;
				$advfilterlist[$group_index]['condition'] = $group_condition_info["groupcondition"];
				$noOfGroupColumns = count($advfilterlist[$group_index]['columns']);
				if (!empty($advfilterlist[$group_index]['columns'][$noOfGroupColumns - 1]['column_condition'])) {
					$advfilterlist[$group_index]['columns'][$noOfGroupColumns - 1]['column_condition'] = '';
				}
			}
			$noOfGroups = count($advfilterlist);
			if (!empty($advfilterlist[$noOfGroups]['condition'])) {
				$advfilterlist[$noOfGroups]['condition'] = '';
			}

			$advfiltersql = $this->generateAdvFilterSql($advfilterlist);
		}
		return $advfiltersql;
	}

	/** Function to get standardfilter for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the query of columnlist for the selected columns
	 */
	public function getStandardCriterialSql($reportid)
	{
		$adb = PearDatabase::getInstance();
		global $modules;


		$sreportstdfiltersql = "select vtiger_reportdatefilter.* from vtiger_report";
		$sreportstdfiltersql .= " inner join vtiger_reportdatefilter on vtiger_report.reportid = vtiger_reportdatefilter.datefilterid";
		$sreportstdfiltersql .= " where vtiger_report.reportid = ?";

		$result = $adb->pquery($sreportstdfiltersql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, "datecolumnname");
			$datefilter = $adb->query_result($result, $i, "datefilter");
			$startdate = $adb->query_result($result, $i, "startdate");
			$enddate = $adb->query_result($result, $i, "enddate");

			if ($fieldcolname != "none") {
				$selectedfields = explode(":", $fieldcolname);
				if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
					$selectedfields[0] = "vtiger_crmentity";
				if ($datefilter == "custom") {

					if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "" && $startdate != '' && $enddate != '') {

						$startDateTime = new DateTimeField($startdate . ' ' . date('H:i:s'));
						$startdate = $startDateTime->getDisplayDate();
						$endDateTime = new DateTimeField($enddate . ' ' . date('H:i:s'));
						$enddate = $endDateTime->getDisplayDate();

						$sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . "' and '" . $enddate . "'";
					}
				} else {

					$startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);

					$startDateTime = new DateTimeField($startenddate[0] . ' ' . date('H:i:s'));
					$startdate = $startDateTime->getDisplayDate();
					$endDateTime = new DateTimeField($startenddate[1] . ' ' . date('H:i:s'));
					$enddate = $endDateTime->getDisplayDate();

					if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
						$sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . "' and '" . $enddate . "'";
					}
				}
			}
		}
		\App\Log::trace("ReportRun :: Successfully returned getStandardCriterialSql" . $reportid);
		return $sSQL;
	}

	/** Function to get standardfilter startdate and enddate for the given type
	 *  @ param $type : Type String
	 *  returns the $datevalue Array in the given format
	 * 		$datevalue = Array(0=>$startdate,1=>$enddate)
	 */
	public function getStandarFiltersStartAndEndDate($type)
	{
		return DateTimeRange::getDateRangeByType($type);
	}

	public function hasGroupingList()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT 1 FROM vtiger_reportsortcol WHERE reportid=? and columnname <> "none"', array($this->reportid));
		return ($result && $adb->num_rows($result)) ? true : false;
	}

	/** Function to get getGroupingList for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the $grouplist Array in the following format
	 *  		$grouplist = Array($tablename:$columnname:$fieldlabel:fieldname:typeofdata=>$tablename:$columnname $sorder,
	 * 				   $tablename1:$columnname1:$fieldlabel1:fieldname1:typeofdata1=>$tablename1:$columnname1 $sorder,
	 * 				   $tablename2:$columnname2:$fieldlabel2:fieldname2:typeofdata2=>$tablename2:$columnname2 $sorder)
	 * This function also sets the return value in the class variable $this->groupbylist
	 */
	public function getGroupingList($reportid)
	{
		$adb = PearDatabase::getInstance();
		global $modules;


		// Have we initialized information already?
		if ($this->_groupinglist !== false) {
			return $this->_groupinglist;
		}

		$sreportsortsql = " SELECT vtiger_reportsortcol.*, vtiger_reportgroupbycolumn.* FROM vtiger_report";
		$sreportsortsql .= " inner join vtiger_reportsortcol on vtiger_report.reportid = vtiger_reportsortcol.reportid";
		$sreportsortsql .= " LEFT JOIN vtiger_reportgroupbycolumn ON (vtiger_report.reportid = vtiger_reportgroupbycolumn.reportid && vtiger_reportsortcol.sortcolid = vtiger_reportgroupbycolumn.sortid)";
		$sreportsortsql .= " where vtiger_report.reportid =? && vtiger_reportsortcol.columnname IN (SELECT columnname from vtiger_selectcolumn WHERE queryid=?) order by vtiger_reportsortcol.sortcolid";

		$result = $adb->pquery($sreportsortsql, array($reportid, $reportid));
		$grouplist = array();

		$inventoryModules = getInventoryModules();
		while ($reportsortrow = $adb->fetch_array($result)) {
			$fieldcolname = $reportsortrow["columnname"];
			list($tablename, $colname, $module_field, $fieldname, $single) = explode(':', $fieldcolname);
			$sortorder = $reportsortrow["sortorder"];

			if ($sortorder == "Ascending") {
				$sortorder = "ASC";
			} elseif ($sortorder == "Descending") {
				$sortorder = "DESC";
			}

			if ($fieldcolname != "none") {
				$selectedfields = explode(":", $fieldcolname);
				if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
					$selectedfields[0] = "vtiger_crmentity";
				if (stripos($selectedfields[1], 'cf_') == 0 && stristr($selectedfields[1], 'cf_') === true) {
					//In sql queries forward slash(/) is treated as query terminator,so to avoid this problem
					//the column names are enclosed within ('[]'),which will treat this as part of column name
					$sqlvalue = "`" . $adb->sql_escape_string(decode_html($selectedfields[2])) . "` " . $sortorder;
				} else {
					$sqlvalue = "`" . self::replaceSpecialChar($selectedfields[2]) . "` " . $sortorder;
				}
				if ($selectedfields[4] == "D" && strtolower($reportsortrow["dategroupbycriteria"]) != "none") {
					$groupField = $module_field;
					$groupCriteria = $reportsortrow["dategroupbycriteria"];
					if (in_array($groupCriteria, array_keys($this->groupByTimeParent))) {
						$parentCriteria = $this->groupByTimeParent[$groupCriteria];
						foreach ($parentCriteria as $criteria) {
							$groupByCondition[] = $this->GetTimeCriteriaCondition($criteria, $groupField) . " " . $sortorder;
						}
					}
					$groupByCondition[] = $this->GetTimeCriteriaCondition($groupCriteria, $groupField) . " " . $sortorder;
					$sqlvalue = implode(", ", $groupByCondition);
				}
				$grouplist[$fieldcolname] = $sqlvalue;
				$temp = explode('__', $selectedfields[2], 2);
				$module = $temp[0];
				if (in_array($module, $inventoryModules) && $fieldname == 'serviceid') {
					$grouplist[$fieldcolname] = $sqlvalue;
				} else if (\App\Field::getFieldPermission($module, $fieldname)) {
					$grouplist[$fieldcolname] = $sqlvalue;
				} else {
					$grouplist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1];
				}

				$this->queryPlanner->addTable($tablename);
			}
		}

		// Save the information
		$this->_groupinglist = $grouplist;

		\App\Log::trace("ReportRun :: Successfully returned getGroupingList" . $reportid);
		return $grouplist;
	}

	/** function to replace special characters
	 *  @ param $selectedfield : type string
	 *  this returns the string for grouplist
	 */
	public function replaceSpecialChar($selectedfield)
	{
		$selectedfield = decode_html(decode_html($selectedfield));
		preg_match('/&/', $selectedfield, $matches);
		if (!empty($matches)) {
			$selectedfield = str_replace('&', 'and', ($selectedfield));
		}
		return $selectedfield;
	}

	/** function to get the selectedorderbylist for the given reportid
	 *  @ param $reportid : type integer
	 *  this returns the columns query for the sortorder columns
	 *  this function also sets the return value in the class variable $this->orderbylistsql
	 */
	public function getSelectedOrderbyList($reportid)
	{

		$adb = PearDatabase::getInstance();
		global $modules;


		$sreportsortsql = "select vtiger_reportsortcol.* from vtiger_report";
		$sreportsortsql .= " inner join vtiger_reportsortcol on vtiger_report.reportid = vtiger_reportsortcol.reportid";
		$sreportsortsql .= " where vtiger_report.reportid =? order by vtiger_reportsortcol.sortcolid";

		$result = $adb->pquery($sreportsortsql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, 'columnname');
			$sortorder = $adb->query_result($result, $i, 'sortorder');

			if ($sortorder == 'Ascending') {
				$sortorder = 'ASC';
			} elseif ($sortorder == 'Descending') {
				$sortorder = 'DESC';
			}

			if ($fieldcolname != 'none') {
				$this->orderbylistcolumns[] = $fieldcolname;
				$n = $n + 1;
				$selectedfields = explode(':', $fieldcolname);
				if ($n > 1) {
					$sSQL .= ', ';
					$this->orderbylistsql .= ', ';
				}
				if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule)
					$selectedfields[0] = 'vtiger_crmentity';
				$sSQL .= $selectedfields[0] . '.' . $selectedfields[1] . ' ' . $sortorder;
				$this->orderbylistsql .= $selectedfields[0] . '.' . $selectedfields[1] . ' ' . $selectedfields[2];
			}
		}
		\App\Log::trace('ReportRun :: Successfully returned getSelectedOrderbyList' . $reportid);
		return $sSQL;
	}

	/** function to get secondary Module for the given Primary module and secondary module
	 *  @ param $module : type String
	 *  @ param $secmodule : type String
	 *  this returns join query for the given secondary module
	 */
	public function getRelatedModulesQuery($module, $secmodule)
	{

		$current_user = vglobal('current_user');
		$query = '';
		if ($secmodule != '') {
			$secondarymodule = explode(':', $secmodule);
			foreach ($secondarymodule as $key => $value) {
				$foc = CRMEntity::getInstance($value);

				// Case handling: Force table requirement ahead of time.
				$this->queryPlanner->addTable('vtiger_crmentity' . $value);

				$focQuery = $foc->generateReportsSecQuery($module, $value, $this->queryPlanner);

				if ($focQuery) {
					if (count($secondarymodule) > 1) {
						$query .= $focQuery . $this->getReportsNonAdminAccessControlQuery($value, $current_user, $value);
					} else {
						$query .= $focQuery . getNonAdminAccessControlQuery($value, $current_user, $value);
						;
					}
				}
			}
		}
		\App\Log::trace('ReportRun :: Successfully returned getRelatedModulesQuery' . $secmodule);

		return $query;
	}

	/**
	 * Non admin user not able to see the records of report even he has permission
	 * Fix for Case :- Report with One Primary Module, and Two Secondary modules, let's say for one of the
	 * secondary module, non-admin user don't have permission, then reports is not showing the record even
	 * the user has permission for another seconday module.
	 * @param type $module
	 * @param type $user
	 * @param type $scope
	 * @return $query
	 */
	public function getReportsNonAdminAccessControlQuery($module, $user, $scope = '')
	{
		require('user_privileges/user_privileges_' . $user->id . '.php');
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$query = ' ';
		$tabId = \App\Module::getModuleId($module);
		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$sharingRuleInfoVariable = $module . '_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;

			if ($module == 'Calendar') {
				$sharedTabId = $tabId;
				$tableName = 'vt_tmp_u' . $user->id . '_t' . $tabId;
			} else if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
				count($sharingRuleInfo['GROUP']) > 0)) {
				$sharedTabId = $tabId;
			}

			if (!empty($sharedTabId)) {
				$module = \App\Module::getModuleName($sharedTabId);
				if ($module == 'Calendar') {
					// For calendar we have some special case to check like, calendar shared type
					$moduleInstance = CRMEntity::getInstance($module);
					$query = $moduleInstance->getReportsNonAdminAccessControlQuery($tableName, $tabId, $user, $current_user_parent_role_seq, $current_user_groups);
				} else {
					$query = $this->getNonAdminAccessQuery($module, $user, $current_user_parent_role_seq, $current_user_groups);
				}

				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array());
				$rows = $db->num_rows($result);
				for ($i = 0; $i < $rows; $i++) {
					$ids[] = $db->query_result($result, $i, 'id');
				}
				if (!empty($ids)) {
					$query = " && vtiger_crmentity$scope.smownerid IN (" . implode(',', $ids) . ') ';
				}
			}
		}
		return $query;
	}

	/** function to get report query for the given module
	 *  @ param $module : type String
	 *  this returns join query for the given module
	 */
	public function getReportsQuery($module, $type = '')
	{

		$current_user = vglobal('current_user');
		$secondary_module = "'";
		$secondary_module .= str_replace(":", "','", $this->secondarymodule);
		$secondary_module .= "'";

		if ($module == 'Leads') {
			$query = 'from vtiger_leaddetails
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_leaddetails.leadid';

			if ($this->queryPlanner->requireTable('vtiger_leadsubdetails')) {
				$query .= '	inner join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid=vtiger_leaddetails.leadid';
			}
			if ($this->queryPlanner->requireTable('vtiger_entity_stats')) {
				$query .= ' inner join vtiger_entity_stats on vtiger_leaddetails.leadid= vtiger_entity_stats.crmid';
			}
			if ($this->queryPlanner->requireTable('vtiger_leadaddress')) {
				$query .= '	inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid=vtiger_leaddetails.leadid';
			}
			if ($this->queryPlanner->requireTable('vtiger_leadscf')) {
				$query .= ' inner join vtiger_leadscf on vtiger_leaddetails.leadid = vtiger_leadscf.leadid';
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsLeads')) {
				$query .= '	left join vtiger_groups as vtiger_groupsLeads on vtiger_groupsLeads.groupid = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_usersLeads')) {
				$query .= ' left join vtiger_users as vtiger_usersLeads on vtiger_usersLeads.id = vtiger_crmentity.smownerid';
			}

			$query .= ' left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid';

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByLeads')) {
				$query .= ' left join vtiger_users as vtiger_lastModifiedByLeads on vtiger_lastModifiedByLeads.id = vtiger_crmentity.modifiedby';
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyLeads')) {
				$query .= ' left join vtiger_users as vtiger_createdbyLeads on vtiger_createdbyLeads.id = vtiger_crmentity.smcreatorid';
			}
			if ($this->queryPlanner->requireTable('u_yf_crmentity_showners')) {
				$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable("vtiger_shOwners$module")) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
			}
			foreach ($this->queryPlanner->getCustomTables() as $customTable) {
				$query .= ' left join ' . $customTable['refTable'] . ' as ' . $customTable['reference'] . ' on ' . $customTable['reference'] . '.' . $customTable['refIndex'] . ' = ' . $customTable['table'] . '.' . $customTable['field'];
			}
			$query .= ' ' . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
				getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
				' where vtiger_crmentity.deleted=0 and vtiger_leaddetails.converted=0';
		} else if ($module == 'Accounts') {
			$query = 'from vtiger_account
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_account.accountid';

			if ($this->queryPlanner->requireTable('vtiger_entity_stats')) {
				$query .= ' inner join vtiger_entity_stats on vtiger_account.accountid=vtiger_entity_stats.crmid';
			}
			if ($this->queryPlanner->requireTable('vtiger_accountaddress')) {
				$query .= ' inner join vtiger_accountaddress on vtiger_account.accountid=vtiger_accountaddress.accountaddressid';
			}
			if ($this->queryPlanner->requireTable('vtiger_accountscf')) {
				$query .= ' inner join vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid';
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsAccounts')) {
				$query .= ' left join vtiger_groups as vtiger_groupsAccounts on vtiger_groupsAccounts.groupid = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_accountAccounts')) {
				$query .= '	left join vtiger_account as vtiger_accountAccounts on vtiger_accountAccounts.accountid = vtiger_account.parentid';
			}
			if ($this->queryPlanner->requireTable('vtiger_usersAccounts')) {
				$query .= ' left join vtiger_users as vtiger_usersAccounts on vtiger_usersAccounts.id = vtiger_crmentity.smownerid';
			}

			$query .= ' left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid';

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByAccounts')) {
				$query .= ' left join vtiger_users as vtiger_lastModifiedByAccounts on vtiger_lastModifiedByAccounts.id = vtiger_crmentity.modifiedby';
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyAccounts')) {
				$query .= ' left join vtiger_users as vtiger_createdbyAccounts on vtiger_createdbyAccounts.id = vtiger_crmentity.smcreatorid';
			}
			foreach ($this->queryPlanner->getCustomTables() as $customTable) {
				$query .= ' left join ' . $customTable['refTable'] . ' as ' . $customTable['reference'] . ' on ' . $customTable['reference'] . '.' . $customTable['refIndex'] . ' = ' . $customTable['table'] . '.' . $customTable['field'];
			}
			if ($this->queryPlanner->requireTable('u_yf_crmentity_showners')) {
				$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable("vtiger_shOwners$module")) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
			}
			$query .= ' ' . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
				getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
				' where vtiger_crmentity.deleted=0 ';
		} else if ($module == 'Contacts') {
			$query = 'from vtiger_contactdetails
				inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid';

			if ($this->queryPlanner->requireTable('vtiger_contactaddress')) {
				$query .= '	inner join vtiger_contactaddress on vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid';
			}
			if ($this->queryPlanner->requireTable('vtiger_entity_stats')) {
				$query .= ' inner join vtiger_entity_stats on vtiger_contactdetails.contactid = vtiger_entity_stats.crmid';
			}
			if ($this->queryPlanner->requireTable('vtiger_customerdetails')) {
				$query .= '	inner join vtiger_customerdetails on vtiger_customerdetails.customerid = vtiger_contactdetails.contactid';
			}
			if ($this->queryPlanner->requireTable('vtiger_contactsubdetails')) {
				$query .= '	inner join vtiger_contactsubdetails on vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid';
			}
			if ($this->queryPlanner->requireTable('vtiger_contactscf')) {
				$query .= '	inner join vtiger_contactscf on vtiger_contactdetails.contactid = vtiger_contactscf.contactid';
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsContacts')) {
				$query .= ' left join vtiger_groups vtiger_groupsContacts on vtiger_groupsContacts.groupid = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_contactdetailsContacts')) {
				$query .= '	left join vtiger_contactdetails as vtiger_contactdetailsContacts on vtiger_contactdetailsContacts.contactid = vtiger_contactdetails.reportsto';
			}
			if ($this->queryPlanner->requireTable('vtiger_accountContacts')) {
				$query .= '	left join vtiger_account as vtiger_accountContacts on vtiger_accountContacts.accountid = vtiger_contactdetails.parentid';
			}
			if ($this->queryPlanner->requireTable('vtiger_usersContacts')) {
				$query .= ' left join vtiger_users as vtiger_usersContacts on vtiger_usersContacts.id = vtiger_crmentity.smownerid';
			}

			$query .= ' left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid';
			foreach ($this->queryPlanner->getCustomTables() as $customTable) {
				$query .= ' left join ' . $customTable['refTable'] . ' as ' . $customTable['reference'] . ' on ' . $customTable['reference'] . '.' . $customTable['refIndex'] . ' = ' . $customTable['table'] . '.' . $customTable['field'];
			}
			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByContacts')) {
				$query .= ' left join vtiger_users as vtiger_lastModifiedByContacts on vtiger_lastModifiedByContacts.id = vtiger_crmentity.modifiedby';
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyContacts')) {
				$query .= ' left join vtiger_users as vtiger_createdbyContacts on vtiger_createdbyContacts.id = vtiger_crmentity.smcreatorid';
			}
			if ($this->queryPlanner->requireTable('u_yf_crmentity_showners')) {
				$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable("vtiger_shOwners$module")) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
			}
			$query .= ' ' . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
				getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
				' where vtiger_crmentity.deleted=0';
		}

		//For this Product - we can related Accounts, Contacts (Also Leads)
		else if ($module == 'Products') {
			$query .= ' from vtiger_products';
			$query .= ' inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_products.productid';
			if ($this->queryPlanner->requireTable('vtiger_productcf')) {
				$query .= ' left join vtiger_productcf on vtiger_products.productid = vtiger_productcf.productid';
			}
			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByProducts')) {
				$query .= ' left join vtiger_users as vtiger_lastModifiedByProducts on vtiger_lastModifiedByProducts.id = vtiger_crmentity.modifiedby';
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyProducts')) {
				$query .= ' left join vtiger_users as vtiger_createdbyProducts on vtiger_createdbyProducts.id = vtiger_crmentity.smcreatorid';
			}
			if ($this->queryPlanner->requireTable('vtiger_usersProducts')) {
				$query .= ' left join vtiger_users as vtiger_usersProducts on vtiger_usersProducts.id = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsProducts')) {
				$query .= ' left join vtiger_groups as vtiger_groupsProducts on vtiger_groupsProducts.groupid = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_vendorRelProducts')) {
				$query .= ' left join vtiger_vendor as vtiger_vendorRelProducts on vtiger_vendorRelProducts.vendorid = vtiger_products.vendor_id';
			}
			if ($this->queryPlanner->requireTable('u_yf_crmentity_showners')) {
				$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable("vtiger_shOwners$module")) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
			}
			if ($this->queryPlanner->requireTable('innerProduct')) {
				$query .= ' LEFT JOIN (
						SELECT vtiger_products.productid,
								(CASE WHEN (vtiger_products.currency_id = 1 ) THEN vtiger_products.unit_price
									ELSE (vtiger_products.unit_price / vtiger_currency_info.conversion_rate) END
								) AS actual_unit_price
						FROM vtiger_products
						LEFT JOIN vtiger_currency_info ON vtiger_products.currency_id = vtiger_currency_info.id
						LEFT JOIN vtiger_productcurrencyrel ON vtiger_products.productid = vtiger_productcurrencyrel.productid
						AND vtiger_productcurrencyrel.currencyid = ' . $current_user->currency_id . '
				) AS innerProduct ON innerProduct.productid = vtiger_products.productid';
			}
			foreach ($this->queryPlanner->getCustomTables() as $customTable) {
				$query .= ' left join ' . $customTable['refTable'] . ' as ' . $customTable['reference'] . ' on ' . $customTable['reference'] . '.' . $customTable['refIndex'] . ' = ' . $customTable['table'] . '.' . $customTable['field'];
			}
			$query .= ' ' . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
				getNonAdminAccessControlQuery($this->primarymodule, $current_user) . '
				where vtiger_crmentity.deleted=0';
		} else if ($module == 'HelpDesk') {
			$matrix = $this->queryPlanner->newDependencyMatrix();

			$matrix->setDependency('vtiger_crmentityRelHelpDesk', array('vtiger_accountRelHelpDesk', 'vtiger_contactdetailsRelHelpDesk'));

			$query = 'from vtiger_troubletickets inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_troubletickets.ticketid';

			if ($this->queryPlanner->requireTable('vtiger_ticketcf')) {
				$query .= ' inner join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid';
			}
			if ($this->queryPlanner->requireTable('vtiger_entity_stats')) {
				$query .= ' inner join vtiger_entity_stats on vtiger_troubletickets.ticketid = vtiger_entity_stats.crmid';
			}
			if ($this->queryPlanner->requireTable('vtiger_crmentityRelHelpDesk', $matrix)) {
				$query .= ' left join vtiger_crmentity as vtiger_crmentityRelHelpDesk on vtiger_crmentityRelHelpDesk.crmid = vtiger_troubletickets.parent_id';
			}
			if ($this->queryPlanner->requireTable('vtiger_accountRelHelpDesk')) {
				$query .= ' left join vtiger_account as vtiger_accountRelHelpDesk on vtiger_accountRelHelpDesk.accountid=vtiger_crmentityRelHelpDesk.crmid';
			}
			if ($this->queryPlanner->requireTable('vtiger_contactdetailsRelHelpDesk')) {
				$query .= ' left join vtiger_contactdetails as vtiger_contactdetailsRelHelpDesk on vtiger_contactdetailsRelHelpDesk.contactid= vtiger_troubletickets.contact_id';
			}
			if ($this->queryPlanner->requireTable('vtiger_productsRel')) {
				$query .= ' left join vtiger_products as vtiger_productsRel on vtiger_productsRel.productid = vtiger_troubletickets.product_id';
			}
			if ($this->queryPlanner->requireTable('vtiger_projectRelHelpDesk')) {
				$query .= ' left join vtiger_project as vtiger_projectRelHelpDesk on vtiger_projectRelHelpDesk.projectid=vtiger_crmentityRelHelpDesk.crmid';
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsHelpDesk')) {
				$query .= ' left join vtiger_groups as vtiger_groupsHelpDesk on vtiger_groupsHelpDesk.groupid = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_usersHelpDesk')) {
				$query .= ' left join vtiger_users as vtiger_usersHelpDesk on vtiger_crmentity.smownerid=vtiger_usersHelpDesk.id';
			}

			$query .= ' left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid';
			$query .= ' left join vtiger_users on vtiger_crmentity.smownerid=vtiger_users.id';

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByHelpDesk')) {
				$query .= '  left join vtiger_users as vtiger_lastModifiedByHelpDesk on vtiger_lastModifiedByHelpDesk.id = vtiger_crmentity.modifiedby';
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyHelpDesk')) {
				$query .= ' left join vtiger_users as vtiger_createdbyHelpDesk on vtiger_createdbyHelpDesk.id = vtiger_crmentity.smcreatorid';
			}
			foreach ($this->queryPlanner->getCustomTables() as $customTable) {
				$query .= ' left join ' . $customTable['refTable'] . ' as ' . $customTable['reference'] . ' on ' . $customTable['reference'] . '.' . $customTable['refIndex'] . ' = ' . $customTable['table'] . '.' . $customTable['field'];
			}
			if ($this->queryPlanner->requireTable('u_yf_crmentity_showners')) {
				$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable("vtiger_shOwners$module")) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
			}
			$query .= ' ' . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
				getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
				' where vtiger_crmentity.deleted=0 ';
		} else if ($module == 'Calendar') {

			$matrix = $this->queryPlanner->newDependencyMatrix();
			$query = 'from vtiger_activity
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid';

			if ($this->queryPlanner->requireTable('vtiger_activitycf')) {
				$query .= ' left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable('vtiger_contactdetailsCalendar')) {
				$query .= ' left join vtiger_contactdetails as vtiger_contactdetailsCalendar on vtiger_contactdetailsCalendar.contactid= vtiger_activity.link';
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsCalendar')) {
				$query .= ' left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_usersCalendar')) {
				$query .= ' left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentity.smownerid';
			}

			$query .= ' left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid';
			$query .= ' left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid';

			if ($this->queryPlanner->requireTable('vtiger_activity_reminder')) {
				$query .= ' left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid';
			}
			if ($this->queryPlanner->requireTable('vtiger_accountRelCalendar')) {
				$query .= ' left join vtiger_account as vtiger_accountRelCalendar on vtiger_accountRelCalendar.accountid=vtiger_activity.link';
			}
			if ($this->queryPlanner->requireTable('vtiger_leaddetailsRelCalendar')) {
				$query .= ' left join vtiger_leaddetails as vtiger_leaddetailsRelCalendar on vtiger_leaddetailsRelCalendar.leadid = vtiger_activity.link';
			}
			if ($this->queryPlanner->requireTable('vtiger_troubleticketsRelCalendar')) {
				$query .= ' left join vtiger_troubletickets as vtiger_troubleticketsRelCalendar on vtiger_troubleticketsRelCalendar.ticketid = vtiger_activity.process';
			}
			if ($this->queryPlanner->requireTable('vtiger_campaignRelCalendar')) {
				$query .= ' left join vtiger_campaign as vtiger_campaignRelCalendar on vtiger_campaignRelCalendar.campaignid = vtiger_activity.process';
			}
			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByCalendar')) {
				$query .= ' left join vtiger_users as vtiger_lastModifiedByCalendar on vtiger_lastModifiedByCalendar.id = vtiger_crmentity.modifiedby';
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyCalendar')) {
				$query .= ' left join vtiger_users as vtiger_createdbyCalendar on vtiger_createdbyCalendar.id = vtiger_crmentity.smcreatorid';
			}
			foreach ($this->queryPlanner->getCustomTables() as $customTable) {
				$query .= ' left join ' . $customTable['refTable'] . ' as ' . $customTable['reference'] . ' on ' . $customTable['reference'] . '.' . $customTable['refIndex'] . ' = ' . $customTable['table'] . '.' . $customTable['field'];
			}
			if ($this->queryPlanner->requireTable('u_yf_crmentity_showners')) {
				$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable("vtiger_shOwners$module")) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
			}
			$query .= ' ' . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
				getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
				' WHERE vtiger_crmentity.deleted=0 ';
		} else if ($module == 'Campaigns') {
			$query = 'from vtiger_campaign
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_campaign.campaignid';
			if ($this->queryPlanner->requireTable('vtiger_campaignscf')) {
				$query .= ' inner join vtiger_campaignscf as vtiger_campaignscf on vtiger_campaignscf.campaignid=vtiger_campaign.campaignid';
			}
			if ($this->queryPlanner->requireTable('vtiger_entity_stats')) {
				$query .= ' inner join vtiger_entity_stats on vtiger_campaign.campaignid = vtiger_entity_stats.crmid';
			}
			if ($this->queryPlanner->requireTable('vtiger_productsCampaigns')) {
				$query .= ' left join vtiger_products as vtiger_productsCampaigns on vtiger_productsCampaigns.productid = vtiger_campaign.product_id';
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsCampaigns')) {
				$query .= ' left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_usersCampaigns')) {
				$query .= ' left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentity.smownerid';
			}

			$query .= ' left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid';
			$query .= ' left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid';

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedBy$module')) {
				$query .= ' left join vtiger_users as vtiger_lastModifiedBy' . $module . ' on vtiger_lastModifiedBy' . $module . '.id = vtiger_crmentity.modifiedby';
			}
			if ($this->queryPlanner->requireTable("vtiger_createdby$module")) {
				$query .= " left join vtiger_users as vtiger_createdby$module on vtiger_createdby$module.id = vtiger_crmentity.smcreatorid";
			}
			foreach ($this->queryPlanner->getCustomTables() as $customTable) {
				$query .= ' left join ' . $customTable['refTable'] . ' as ' . $customTable['reference'] . ' on ' . $customTable['reference'] . '.' . $customTable['refIndex'] . ' = ' . $customTable['table'] . '.' . $customTable['field'];
			}
			if ($this->queryPlanner->requireTable('u_yf_crmentity_showners')) {
				$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable("vtiger_shOwners$module")) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
			}
			$query .= ' ' . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
				getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
				' where vtiger_crmentity.deleted=0';
		} else if ($module == 'OSSTimeControl') {
			$query = 'FROM vtiger_osstimecontrol
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_osstimecontrol.osstimecontrolid';
			if ($this->queryPlanner->requireTable('vtiger_account')) {
				$query .= ' LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_osstimecontrol.link';
			}
			if ($this->queryPlanner->requireTable('vtiger_contactdetails')) {
				$query .= ' LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_osstimecontrol.link';
			}
			if ($this->queryPlanner->requireTable('vtiger_leaddetails')) {
				$query .= ' LEFT JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_osstimecontrol.link';
			}
			if ($this->queryPlanner->requireTable('vtiger_vendor')) {
				$query .= ' LEFT JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_osstimecontrol.link';
			}
			if ($this->queryPlanner->requireTable('u_yf_partners')) {
				$query .= ' LEFT JOIN u_yf_partners ON u_yf_partners.partnersid = vtiger_osstimecontrol.link';
			}
			if ($this->queryPlanner->requireTable('u_yf_competition')) {
				$query .= ' LEFT JOIN u_yf_competition ON u_yf_competition.competitionid = vtiger_osstimecontrol.link';
			}
			if ($this->queryPlanner->requireTable('vtiger_ossemployees')) {
				$query .= ' LEFT JOIN vtiger_ossemployees ON vtiger_ossemployees.ossemployeesid = vtiger_osstimecontrol.link';
			}
			if ($this->queryPlanner->requireTable('vtiger_usersOSSTimeControl')) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_usersOSSTimeControl ON vtiger_usersOSSTimeControl.id = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsOSSTimeControl')) {
				$query .= ' LEFT JOIN vtiger_groups AS vtiger_groupsOSSTimeControl ON vtiger_groupsOSSTimeControl.groupid = vtiger_crmentity.smownerid';
			}
			if ($this->queryPlanner->requireTable('u_yf_crmentity_showners')) {
				$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
			}
			if ($this->queryPlanner->requireTable("vtiger_shOwners$module")) {
				$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
			}
		} else {
			if ($module != '') {
				$focus = CRMEntity::getInstance($module);

				$query = $focus->generateReportsQuery($module, $this->queryPlanner) .
					$this->getRelatedModulesQuery($module, $this->secondarymodule) .
					getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
					' WHERE vtiger_crmentity.deleted=0';
			}
		}
		\App\Log::trace('ReportRun :: Successfully returned getReportsQuery' . $module);

		return $query;
	}

	/** function to get query for the given reportid,filterlist,type
	 *  @ param $reportid : Type integer
	 *  @ param $filtersql : Type Array
	 *  @ param $module : Type String
	 *  this returns join query for the report
	 */
	public function sGetSQLforReport($reportid, $filtersql, $type = '', $chartReport = false, $startLimit = false, $endLimit = false)
	{


		$columnlist = $this->getQueryColumnsList($reportid, $type);
		$groupslist = $this->getGroupingList($reportid);
		$groupTimeList = $this->getGroupByTimeList($reportid);
		$stdfilterlist = $this->getStdFilterList($reportid);
		$columnstotallist = $this->getColumnsTotal($reportid);
		if (isset($filtersql) && $filtersql !== false && $filtersql != '') {
			$advfiltersql = $filtersql;
		} else {
			$advfiltersql = $this->getAdvFilterSql($reportid);
		}
		$this->totallist = $columnstotallist;
		//Fix for ticket #4915.
		$selectlist = $columnlist;
		//columns list
		if (isset($selectlist)) {
			$selectedcolumns = implode(", ", $selectlist);
			if ($chartReport === true) {
				$selectedcolumns .= ", count(*) AS 'groupby_count'";
			}
		}
		//groups list
		if (isset($groupslist)) {
			$groupsquery = implode(', ', $groupslist);
		}
		if (isset($groupTimeList)) {
			$groupTimeQuery = implode(', ', $groupTimeList);
		}

		//standard list
		if (isset($stdfilterlist)) {
			$stdfiltersql = implode(', ', $stdfilterlist);
		}
		//columns to total list
		if (isset($columnstotallist)) {
			$columnstotalsql = implode(', ', $columnstotallist);
		}
		if ($stdfiltersql != '') {
			$wheresql = ' and ' . $stdfiltersql;
		}

		if ($advfiltersql != '') {
			$wheresql .= ' and ' . $advfiltersql;
		}

		$reportquery = $this->getReportsQuery($this->primarymodule, $type);

		// If we don't have access to any columns, let us select one column and limit result to shown we have not results
		// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad
		$allColumnsRestricted = false;

		if ($type == 'COLUMNSTOTOTAL') {
			if ($columnstotalsql != '') {
				$reportquery = sprintf('select %s %s %s ', $columnstotalsql, $reportquery, $wheresql);
			}
		} else {
			if ($selectedcolumns == '') {
				// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad

				$selectedcolumns = "''"; // "''" to get blank column name
				$allColumnsRestricted = true;
			}
			$reportquery = sprintf('select DISTINCT %s %s %s ', $selectedcolumns, $reportquery, $wheresql);
		}

		$reportquery = listQueryNonAdminChange($reportquery, $this->primarymodule);

		if (trim($groupsquery) != "" && $type !== 'COLUMNSTOTOTAL') {
			if ($chartReport === true) {
				$reportquery .= sprintf(' group by %s', $this->GetFirstSortByField($reportid));
			} else {
				$reportquery .= sprintf(' order by %s', $groupsquery);
			}
		}

		// Prasad: No columns selected so limit the number of rows directly.
		if ($allColumnsRestricted) {
			$reportquery .= " limit 0";
		} else if ($startLimit !== false && $endLimit !== false) {
			$reportquery .= " LIMIT $startLimit, $endLimit";
		}

		preg_match('/&amp;/', $reportquery, $matches);
		if (!empty($matches)) {
			$report = str_replace('&amp;', '&', $reportquery);
			$reportquery = $this->replaceSpecialChar($report);
		}
		\App\Log::trace("ReportRun :: Successfully returned sGetSQLforReport" . $reportid);

		$this->queryPlanner->initializeTempTables();

		return $reportquery;
	}

	public function getHeaderToRaport($adb, $fld, $modules_selected)
	{
		list($module, $fieldLabel) = explode('__', $fld->name, 2);
		$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
		$fieldType = null;
		if (!empty($fieldInfo)) {
			$field = WebserviceField::fromArray($adb, $fieldInfo);
			$fieldType = $field->getFieldDataType();
		}
		if (!empty($fieldInfo)) {
			$translatedLabel = \App\Language::translate($field->getFieldLabelKey(), $module);
		} else {
			$fieldLabel = str_replace("__", " ", $fieldLabel);
			$translatedLabel = \App\Language::translate($fieldLabel, $module);
		}
		/* STRING TRANSLATION starts */
		$moduleLabel = '';
		if (in_array($module, $modules_selected))
			$moduleLabel = \App\Language::translate($module, $module);

		if (empty($translatedLabel)) {
			$translatedLabel = \App\Language::translate(str_replace('__', " ", $fld->name), $module);
		}
		$headerLabel = $translatedLabel;
		if (!empty($this->secondarymodule)) {
			if ($moduleLabel != '') {
				$headerLabel = $translatedLabel . ' [' . $moduleLabel . ']';
			}
		}
		return $headerLabel;
	}

	/** function to get the report output in HTML,PDF,TOTAL,PRINT,PRINTTOTAL formats depends on the argument $outputformat
	 *  @ param $outputformat : Type String (valid parameters HTML,PDF,TOTAL,PRINT,PRINT_TOTAL)
	 *  @ param $filtersql : Type String
	 *  This returns HTML Report if $outputformat is HTML
	 *  		Array for PDF if  $outputformat is PDF
	 * 		HTML strings for TOTAL if $outputformat is TOTAL
	 * 		Array for PRINT if $outputformat is PRINT
	 * 		HTML strings for TOTAL fields  if $outputformat is PRINTTOTAL
	 * 		HTML strings for
	 */
	// Performance Optimization: Added parameter directOutput to avoid building big-string!
	public function GenerateReport($outputformat, $filtersql, $directOutput = false, $startLimit = false, $endLimit = false)
	{
		$adb = PearDatabase::getInstance();
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		global $modules;
		global $mod_strings;
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		$modules_selected = array();
		$modules_selected[] = $this->primarymodule;
		if (!empty($this->secondarymodule)) {
			$sec_modules = explode(':', $this->secondarymodule);
			$countSecModules = count($sec_modules);
			for ($i = 0; $i < $countSecModules; $i++) {
				$modules_selected[] = $sec_modules[$i];
			}
		}

		// Update Reference fields list list
		$referencefieldres = $adb->pquery("SELECT tabid, fieldlabel, uitype from vtiger_field WHERE uitype in (10,101)", array());
		if ($referencefieldres) {
			foreach ($referencefieldres as $referencefieldrow) {
				$uiType = $referencefieldrow['uitype'];
				$modprefixedlabel = \App\Module::getModuleName($referencefieldrow['tabid']) . ' ' . $referencefieldrow['fieldlabel'];
				$modprefixedlabel = str_replace(' ', '__', $modprefixedlabel);

				if ($uiType == 10 && !in_array($modprefixedlabel, $this->ui10_fields)) {
					$this->ui10_fields[] = $modprefixedlabel;
				} elseif ($uiType == 101 && !in_array($modprefixedlabel, $this->ui101_fields)) {
					$this->ui101_fields[] = $modprefixedlabel;
				}
			}
		}

		if ($outputformat == "HTML") {
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, $outputformat, false, $startLimit, $endLimit);
			$sSQL .= " LIMIT 0, " . (self::$HTMLVIEW_MAX_ROWS + 1); // Pull a record more than limit

			$result = $adb->query($sSQL);
			$error_msg = $adb->database->ErrorMsg();
			if (!$result && $error_msg != '') {
				// Performance Optimization: If direct output is requried
				if ($directOutput) {
					echo \App\Language::translate('LBL_REPORT_GENERATION_FAILED', $currentModule) . "<br>" . $error_msg;
					$error_msg = false;
				}
				// END
				return $error_msg;
			}

			// Performance Optimization: If direct output is required
			if ($directOutput) {
				echo '<table cellpadding="5" cellspacing="0" align="center" class="rptTable"><tr>';
			}
			// END

			if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
				$picklistarray = $this->getAccessPickListValues();
			if ($result) {
				$y = $adb->getFieldsCount($result);
				$arrayHeaders = Array();
				for ($x = 0; $x < $y; $x++) {
					$fld = $adb->columnMeta($result, $x);
					if (in_array($this->getLstringforReportHeaders($fld->name), $arrayHeaders)) {
						$headerLabel = str_replace("__", " ", $fld->name);
						$arrayHeaders[] = $headerLabel;
					} else {
						$headerLabel = str_replace($modules, " ", $this->getLstringforReportHeaders($fld->name));
						$headerLabel = str_replace("__", " ", $this->getLstringforReportHeaders($fld->name));
						$arrayHeaders[] = $headerLabel;
					}
					/* STRING TRANSLATION starts */
					$mod_name = explode(' ', $headerLabel, 2);
					$moduleLabel = '';
					if (in_array($mod_name[0], $modules_selected)) {
						$moduleLabel = \App\Language::translate($mod_name[0], $mod_name[0]);
					}

					if (!empty($this->secondarymodule)) {
						if ($moduleLabel != '') {
							$headerLabel_tmp = $moduleLabel . " " . \App\Language::translate($mod_name[1], $mod_name[0]);
						} else {
							$headerLabel_tmp = \App\Language::translate($mod_name[0] . " " . $mod_name[1]);
						}
					} else {
						if ($moduleLabel != '') {
							$headerLabel_tmp = \App\Language::translate($mod_name[1], $mod_name[0]);
						} else {
							$headerLabel_tmp = \App\Language::translate($mod_name[0] . " " . $mod_name[1]);
						}
					}
					if ($headerLabel == $headerLabel_tmp)
						$headerLabel = \App\Language::translate($headerLabel_tmp);
					else
						$headerLabel = $headerLabel_tmp;
					/* STRING TRANSLATION ends */
					$header .= "<td class='rptCellLabel'>" . $headerLabel . "</td>";

					// Performance Optimization: If direct output is required
					if ($directOutput) {
						echo $header;
						$header = '';
					}
					// END
				}

				// Performance Optimization: If direct output is required
				if ($directOutput) {
					echo '</tr><tr>';
				}
				// END

				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				$groupslist = $this->getGroupingList($this->reportid);

				$column_definitions = $adb->getFieldsDefinition($result);

				do {
					$arraylists = Array();
					if (count($groupslist) == 1) {
						$newvalue = $custom_field_values[0];
					} elseif (count($groupslist) == 2) {
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
					} elseif (count($groupslist) == 3) {
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
						$tnewvalue = $custom_field_values[2];
					}
					if ($newvalue == "")
						$newvalue = "-";

					if ($snewvalue == "")
						$snewvalue = "-";

					if ($tnewvalue == "")
						$tnewvalue = "-";

					$valtemplate .= "<tr>";

					// Performance Optimization
					if ($directOutput) {
						echo $valtemplate;
						$valtemplate = '';
					}
					// END

					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->columnMeta($result, $i);
						$fld_type = $column_definitions[$i]->type;
						$fieldvalue = getReportFieldValue($this, $picklistarray, $fld, $custom_field_values, $fld->name);

						//check for Roll based pick list
						$temp_val = $fld->name;

						if ($fieldvalue == "") {
							$fieldvalue = "-";
						} else if ($fld->name == $this->primarymodule . '__LBL_ACTION' && $fieldvalue != '-') {
							$fieldvalue = "<a href='index.php?module={$this->primarymodule}&action=DetailView&record={$fieldvalue}' target='_blank'>" . \App\Language::translate('LBL_VIEW_DETAILS', 'Reports') . "</a>";
						}

						if (($lastvalue == $fieldvalue) && $this->reporttype == "summary") {
							if ($this->reporttype == "summary") {
								$valtemplate .= "<td class='rptEmptyGrp'>&nbsp;</td>";
							} else {
								$valtemplate .= "<td class='rptData'>" . $fieldvalue . "</td>";
							}
						} else if (($secondvalue === $fieldvalue) && $this->reporttype == "summary") {
							if ($lastvalue === $newvalue) {
								$valtemplate .= "<td class='rptEmptyGrp'>&nbsp;</td>";
							} else {
								$valtemplate .= "<td class='rptGrpHead'>" . $fieldvalue . "</td>";
							}
						} else if (($thirdvalue === $fieldvalue) && $this->reporttype == "summary") {
							if ($secondvalue === $snewvalue) {
								$valtemplate .= "<td class='rptEmptyGrp'>&nbsp;</td>";
							} else {
								$valtemplate .= "<td class='rptGrpHead'>" . $fieldvalue . "</td>";
							}
						} else {
							if ($this->reporttype == "tabular") {
								$valtemplate .= "<td class='rptData'>" . $fieldvalue . "</td>";
							} else {
								$valtemplate .= "<td class='rptGrpHead'>" . $fieldvalue . "</td>";
							}
						}

						// Performance Optimization: If direct output is required
						if ($directOutput) {
							echo $valtemplate;
							$valtemplate = '';
						}
						// END
					}

					$valtemplate .= "</tr>";

					// Performance Optimization: If direct output is required
					if ($directOutput) {
						echo $valtemplate;
						$valtemplate = '';
					}
					// END

					$lastvalue = $newvalue;
					$secondvalue = $snewvalue;
					$thirdvalue = $tnewvalue;
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
				} while ($custom_field_values = $adb->fetch_array($result));

				// Performance Optimization: Provide feedback on export option if required
				// NOTE: We should make sure to pull at-least 1 row more than max-limit for this to work.
				if ($noofrows > self::$HTMLVIEW_MAX_ROWS) {
					// Performance Optimization: Output directly
					if ($directOutput) {
						echo '</tr></table><br><table width="100%" cellpading="0" cellspacing="0"><tr>';
						echo sprintf('<td colspan="%s" align="right"><span class="genHeaderGray">%s</span></td>', $y, \App\Language::translate('Only') . " " . self::$HTMLVIEW_MAX_ROWS .
							"+ " . \App\Language::translate('records found') . ". " . \App\Language::translate('Export to') . " <a href=\"javascript:;\" onclick=\"goToURL(CrearEnlace('ReportsAjax&file=CreateCSV',{$this->reportid}));\"><img style='vertical-align:text-top' src='themes/images/csv-file.png'></a> /" .
							" <a href=\"javascript:;\" onclick=\"goToURL(CrearEnlace('CreateXL',{$this->reportid}));\"><img style='vertical-align:text-top' src='themes/images/xls-file.jpg'></a>"
						);
					} else {
						$valtemplate .= '</tr></table><br><table width="100%" cellpading="0" cellspacing="0"><tr>';
						$valtemplate .= sprintf('<td colspan="%s" align="right"><span class="genHeaderGray">%s</span></td>', $y, \App\Language::translate('Only') . " " . self::$HTMLVIEW_MAX_ROWS .
							" " . \App\Language::translate('records found') . ". " . \App\Language::translate('Export to') . " <a href=\"javascript:;\" onclick=\"goToURL(CrearEnlace('ReportsAjax&file=CreateCSV',{$this->reportid}));\"><img style='vertical-align:text-top' src='themes/images/csv-file.png'></a> /" .
							" <a href=\"javascript:;\" onclick=\"goToURL(CrearEnlace('CreateXL',{$this->reportid}));\"><img style='vertical-align:text-top' src='themes/images/xls-file.jpg'></a>"
						);
					}
				}


				// Performance Optimization
				if ($directOutput) {

					$totalDisplayString = $noofrows;
					if ($noofrows > self::$HTMLVIEW_MAX_ROWS) {
						$totalDisplayString = self::$HTMLVIEW_MAX_ROWS . "+";
					}

					echo "</tr></table>";
					echo "<script type='text/javascript' id='__reportrun_directoutput_recordcount_script'>
						if($('_reportrun_total')) $('_reportrun_total').innerHTML='$totalDisplayString';</script>";
				} else {

					$sHTML = '<table cellpadding="5" cellspacing="0" align="center" class="rptTable">
					<tr>' .
						$header
						. '<!-- BEGIN values -->
					<tr>' .
						$valtemplate
						. '</tr>
					</table>';
				}
				//<<<<<<<<construct HTML>>>>>>>>>>>>
				$return_data[] = $sHTML;
				$return_data[] = $noofrows;
				$return_data[] = $sSQL;
				return $return_data;
			}
		} elseif ($outputformat == 'PDF') {
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, $outputformat, false, $startLimit, $endLimit);
			$result = $adb->pquery($sSQL, array());
			if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
				$picklistarray = $this->getAccessPickListValues();

			if ($result) {
				$y = $adb->getFieldsCount($result);
				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				$column_definitions = $adb->getFieldsDefinition($result);

				do {
					$arraylists = Array();
					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->columnMeta($result, $i);
						$fld_type = $column_definitions[$i]->type;
						$headerLabel = $this->getHeaderToRaport($adb, $fld, $modules_selected);

						// Check for role based pick list
						$temp_val = $fld->name;
						$fieldvalue = getReportFieldValue($this, $picklistarray, $fld, $custom_field_values, $temp_val);

						if ($fld->name == $this->primarymodule . '__LBL_ACTION' && $fieldvalue != '-') {
							$fieldvalue = "<a href='index.php?module={$this->primarymodule}&view=Detail&record={$fieldvalue}' target='_blank'>" . \App\Language::translate('LBL_VIEW_DETAILS', 'Reports') . "</a>";
						}
						if (false != strpos($fld->name, 'Share__with__users')) {
							$id = $custom_field_values[$this->primarymodule . '__LBL_ACTION'];
							$usersSqlFullName = \App\Module::getSqlForNameInDisplayFormat('Users');
							$query = sprintf('SELECT %s FROM  u_yf_crmentity_showners LEFT JOIN vtiger_users ON u_yf_crmentity_showners.userid = vtiger_users.id WHERE crmid = ?', $usersSqlFullName);
							$resultOwners = $adb->pquery($query, [$id]);
							$fieldvalue = implode(', ', $adb->getArrayColumn($resultOwners));
						}

						$arraylists[$headerLabel] = $fieldvalue;
					}
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
				} while ($custom_field_values = $adb->fetch_array($result));

				$data['data'] = $arr_val;
				$data['count'] = $noofrows;
				return $data;
			}
		} elseif ($outputformat == "TOTALXLS") {
			$escapedchars = Array('__SUM', '__AVG', '__MIN', '__MAX');
			$totalpdf = array();
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, "COLUMNSTOTOTAL");
			if (isset($this->totallist)) {
				if ($sSQL != "") {
					$result = $adb->query($sSQL);
					$y = $adb->getFieldsCount($result);
					$custom_field_values = $adb->fetch_array($result);

					foreach ($this->totallist as $key => $value) {
						$fieldlist = explode(":", $key);
						$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?", array($fieldlist[1], $fieldlist[2]));
						if ($adb->num_rows($mod_query) > 0) {
							$module_name = \App\Module::getModuleName($adb->query_result($mod_query, 0, 'tabid'));
							$fieldlabel = trim(str_replace($escapedchars, " ", $fieldlist[3]));
							$fieldlabel = str_replace("__", " ", $fieldlabel);
							if ($module_name) {
								$field = \App\Language::translate($module_name, $module_name) . " " . \App\Language::translate($fieldlabel, $module_name);
							} else {
								$field = \App\Language::translate($fieldlabel);
							}
						}
						// Since there are duplicate entries for this table
						if ($fieldlist[1] == 'vtiger_inventoryproductrel') {
							$module_name = $this->primarymodule;
						}
						$uitype_arr[str_replace($escapedchars, " ", $module_name . "__" . $fieldlist[3])] = $adb->query_result($mod_query, 0, "uitype");
						$totclmnflds[str_replace($escapedchars, " ", $module_name . "__" . $fieldlist[3])] = $field;
					}
					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->columnMeta($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$fld->name];
					}

					$rowcount = 0;
					foreach ($totclmnflds as $key => $value) {
						$col_header = trim(str_replace($modules, " ", $value));
						$fld_name_1 = $this->primarymodule . "__" . trim($value);
						$fld_name_2 = $this->secondarymodule . "__" . trim($value);
						if ($uitype_arr[$key] == 71 || $uitype_arr[$key] == 72 ||
							in_array($fld_name_1, $this->append_currency_symbol_to_value) || in_array($fld_name_2, $this->append_currency_symbol_to_value)) {
							$col_header .= " (" . \App\Language::translate('LBL_IN') . " " . $current_user->currency_symbol . ")";
							$convert_price = true;
						} else {
							$convert_price = false;
						}
						$value = trim($key);
						$arraykey = $value . '__SUM';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						}else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value . '__AVG';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						}else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value . '__MIN';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						}else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value . '__MAX';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						}else {
							$totalpdf[$rowcount][$arraykey] = '';
						}
						$rowcount++;
					}
				}
			}
			return $totalpdf;
		} elseif ($outputformat == "TOTALHTML") {
			$escapedchars = Array('__SUM', '__AVG', '__MIN', '__MAX');
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, "COLUMNSTOTOTAL");

			static $modulename_cache = array();

			if (isset($this->totallist)) {
				if ($sSQL != "") {
					$result = $adb->query($sSQL);
					$y = $adb->getFieldsCount($result);
					$custom_field_values = $adb->fetch_array($result);
					$coltotalhtml .= "<table align='center' width='60%' cellpadding='3' cellspacing='0' border='0' class='rptTable'><tr><td class='rptCellLabel'>" . $mod_strings[Totals] . "</td><td class='rptCellLabel'>" . $mod_strings[SUM] . "</td><td class='rptCellLabel'>" . $mod_strings[AVG] . "</td><td class='rptCellLabel'>" . $mod_strings[MIN] . "</td><td class='rptCellLabel'>" . $mod_strings[MAX] . "</td></tr>";

					// Performation Optimization: If Direct output is desired
					if ($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END

					foreach ($this->totallist as $key => $value) {
						$fieldlist = explode(":", $key);

						$module_name = NULL;
						$cachekey = $fieldlist[1] . ":" . $fieldlist[2];
						if (!isset($modulename_cache[$cachekey])) {
							$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?", array($fieldlist[1], $fieldlist[2]));
							if ($adb->num_rows($mod_query) > 0) {
								$module_name = \App\Module::getModuleName($adb->query_result($mod_query, 0, 'tabid'));
								$modulename_cache[$cachekey] = $module_name;
							}
						} else {
							$module_name = $modulename_cache[$cachekey];
						}
						if ($module_name) {
							$fieldlabel = trim(str_replace($escapedchars, " ", $fieldlist[3]));
							$fieldlabel = str_replace("__", " ", $fieldlabel);
							$field = \App\Language::translate($module_name, $module_name) . " " . \App\Language::translate($fieldlabel, $module_name);
						} else {
							$field = \App\Language::translate($fieldlabel);
						}

						$uitype_arr[str_replace($escapedchars, " ", $module_name . "__" . $fieldlist[3])] = $adb->query_result($mod_query, 0, "uitype");
						$totclmnflds[str_replace($escapedchars, " ", $module_name . "__" . $fieldlist[3])] = $field;
					}
					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->columnMeta($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}

					foreach ($totclmnflds as $key => $value) {
						$coltotalhtml .= '<tr class="rptGrpHead" valign=top>';
						$col_header = trim(str_replace($modules, " ", $value));
						$fld_name_1 = $this->primarymodule . "__" . trim($value);
						$fld_name_2 = $this->secondarymodule . "__" . trim($value);
						if ($uitype_arr[$key] == 71 || $uitype_arr[$key] == 72 ||
							in_array($fld_name_1, $this->append_currency_symbol_to_value) || in_array($fld_name_2, $this->append_currency_symbol_to_value)) {
							$col_header .= " (" . \App\Language::translate('LBL_IN') . " " . $current_user->currency_symbol . ")";
							$convert_price = true;
						} else {
							$convert_price = false;
						}
						$coltotalhtml .= '<td class="rptData">' . $col_header . '</td>';
						$value = trim($key);
						$arraykey = $value . '__SUM';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= '<td class="rptTotal">' . $conv_value . '</td>';
						}else {
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value . '__AVG';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= '<td class="rptTotal">' . $conv_value . '</td>';
						}else {
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value . '__MIN';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= '<td class="rptTotal">' . $conv_value . '</td>';
						}else {
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value . '__MAX';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= '<td class="rptTotal">' . $conv_value . '</td>';
						}else {
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$coltotalhtml .= '<tr>';

						// Performation Optimization: If Direct output is desired
						if ($directOutput) {
							echo $coltotalhtml;
							$coltotalhtml = '';
						}
						// END
					}

					$coltotalhtml .= "</table>";

					// Performation Optimization: If Direct output is desired
					if ($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END
				}
			}
			return $coltotalhtml;
		} elseif ($outputformat == "PRINT") {
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, $outputformat);
			$result = $adb->query($sSQL);
			if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
				$picklistarray = $this->getAccessPickListValues();

			if ($result) {
				$y = $adb->getFieldsCount($result);
				$arrayHeaders = Array();
				for ($x = 0; $x < $y - 1; $x++) {
					$fld = $adb->columnMeta($result, $x);
					$headerLabel = $this->getHeaderToRaport($adb, $fld, $modules_selected);
					$header .= "<th>" . $headerLabel . "</th>";
				}
				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				$groupslist = $this->getGroupingList($this->reportid);

				$column_definitions = $adb->getFieldsDefinition($result);

				do {
					$arraylists = Array();
					if (count($groupslist) == 1) {
						$newvalue = $custom_field_values[0];
					} elseif (count($groupslist) == 2) {
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
					} elseif (count($groupslist) == 3) {
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
						$tnewvalue = $custom_field_values[2];
					}

					if ($newvalue == "")
						$newvalue = "-";

					if ($snewvalue == "")
						$snewvalue = "-";

					if ($tnewvalue == "")
						$tnewvalue = "-";

					$valtemplate .= "<tr>";

					for ($i = 0; $i < $y - 1; $i++) {
						$fld = $adb->columnMeta($result, $i);
						$fld_type = $column_definitions[$i]->type;
						$fieldvalue = getReportFieldValue($this, $picklistarray, $fld, $custom_field_values, $fld->name);
						if (($lastvalue == $fieldvalue) && $this->reporttype == "summary") {
							if ($this->reporttype == "summary") {
								$valtemplate .= "<td style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							} else {
								$valtemplate .= "<td>" . $fieldvalue . "</td>";
							}
						} else if (($secondvalue == $fieldvalue) && $this->reporttype == "summary") {
							if ($lastvalue == $newvalue) {
								$valtemplate .= "<td style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							} else {
								$valtemplate .= "<td>" . $fieldvalue . "</td>";
							}
						} else if (($thirdvalue == $fieldvalue) && $this->reporttype == "summary") {
							if ($secondvalue == $snewvalue) {
								$valtemplate .= "<td style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							} else {
								$valtemplate .= "<td>" . $fieldvalue . "</td>";
							}
						} else {
							if ($this->reporttype == "tabular") {
								$valtemplate .= "<td>" . $fieldvalue . "</td>";
							} else {
								$valtemplate .= "<td>" . $fieldvalue . "</td>";
							}
						}
					}
					$valtemplate .= "</tr>";
					$lastvalue = $newvalue;
					$secondvalue = $snewvalue;
					$thirdvalue = $tnewvalue;
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
				} while ($custom_field_values = $adb->fetch_array($result));

				$sHTML = '<tr>' . $header . '</tr>' . $valtemplate;
				$return_data[] = $sHTML;
				$return_data[] = $noofrows;
				return $return_data;
			}
		} elseif ($outputformat == "PRINT_TOTAL") {
			$escapedchars = Array('__SUM', '__AVG', '__MIN', '__MAX');
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, "COLUMNSTOTOTAL");
			if (isset($this->totallist)) {
				if ($sSQL != "") {
					$result = $adb->query($sSQL);
					$y = $adb->getFieldsCount($result);
					$custom_field_values = $adb->fetch_array($result);

					$coltotalhtml .= "<br /><table align='center' width='60%' cellpadding='3' cellspacing='0' border='1' class='printReport'><tr><td class='rptCellLabel'>" . $mod_strings['Totals'] . "</td><td><b>" . $mod_strings['SUM'] . "</b></td><td><b>" . $mod_strings['AVG'] . "</b></td><td><b>" . $mod_strings['MIN'] . "</b></td><td><b>" . $mod_strings['MAX'] . "</b></td></tr>";

					// Performation Optimization: If Direct output is desired
					if ($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END

					foreach ($this->totallist as $key => $value) {
						$fieldlist = explode(":", $key);
						$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?", array($fieldlist[1], $fieldlist[2]));
						if ($adb->num_rows($mod_query) > 0) {
							$module_name = \App\Module::getModuleName($adb->query_result($mod_query, 0, 'tabid'));
							$fieldlabel = trim(str_replace($escapedchars, " ", $fieldlist[3]));
							$fieldlabel = str_replace("__", " ", $fieldlabel);
							if ($module_name) {
								$field = \App\Language::translate($module_name, $module_name) . " " . \App\Language::translate($fieldlabel, $module_name);
							} else {
								$field = \App\Language::translate($fieldlabel);
							}
						}
						$uitype_arr[str_replace($escapedchars, " ", $module_name . "__" . $fieldlist[3])] = $adb->query_result($mod_query, 0, "uitype");
						$totclmnflds[str_replace($escapedchars, " ", $module_name . "__" . $fieldlist[3])] = $field;
					}

					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->columnMeta($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}
					foreach ($totclmnflds as $key => $value) {
						$coltotalhtml .= '<tr class="rptGrpHead">';
						$col_header = \App\Language::translate(trim(str_replace($modules, " ", $value)));
						$fld_name_1 = $this->primarymodule . "__" . trim($value);
						$fld_name_2 = $this->secondarymodule . "__" . trim($value);
						if ($uitype_arr[$key] == 71 || $uitype_arr[$key] == 72 ||
							in_array($fld_name_1, $this->append_currency_symbol_to_value) || in_array($fld_name_2, $this->append_currency_symbol_to_value)) {
							$col_header .= " (" . \App\Language::translate('LBL_IN') . " " . $current_user->currency_symbol . ")";
							$convert_price = true;
						} else {
							$convert_price = false;
						}
						$coltotalhtml .= '<td class="rptData">' . $col_header . '</td>';
						$value = trim($key);
						$arraykey = $value . '__SUM';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= "<td class='rptTotal'>" . $conv_value . '</td>';
						}else {
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value . '__AVG';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= "<td class='rptTotal'>" . $conv_value . '</td>';
						}else {
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value . '__MIN';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= "<td class='rptTotal'>" . $conv_value . '</td>';
						}else {
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value . '__MAX';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= "<td class='rptTotal'>" . $conv_value . '</td>';
						}else {
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$coltotalhtml .= '</tr>';

						// Performation Optimization: If Direct output is desired
						if ($directOutput) {
							echo $coltotalhtml;
							$coltotalhtml = '';
						}
						// END
					}

					$coltotalhtml .= "</table>";
					// Performation Optimization: If Direct output is desired
					if ($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END
				}
			}
			return $coltotalhtml;
		}
	}

	//<<<<<<<new>>>>>>>>>>
	public function getColumnsTotal($reportid)
	{
		// Have we initialized it already?
		if ($this->_columnstotallist !== false) {
			return $this->_columnstotallist;
		}

		$adb = PearDatabase::getInstance();
		global $modules;

		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		static $modulename_cache = array();

		$query = "select * from vtiger_reportmodules where reportmodulesid =?";
		$res = $adb->pquery($query, array($reportid));
		$modrow = $adb->fetch_array($res);
		$premod = $modrow["primarymodule"];
		$secmod = $modrow["secondarymodules"];
		$coltotalsql = "select vtiger_reportsummary.* from vtiger_report";
		$coltotalsql .= " inner join vtiger_reportsummary on vtiger_report.reportid = vtiger_reportsummary.reportsummaryid";
		$coltotalsql .= " where vtiger_report.reportid =?";

		$result = $adb->pquery($coltotalsql, array($reportid));

		while ($coltotalrow = $adb->fetch_array($result)) {
			$fieldcolname = $coltotalrow["columnname"];
			if ($fieldcolname != "none") {
				$fieldlist = explode(":", $fieldcolname);
				$field_tablename = $fieldlist[1];
				$field_columnname = $fieldlist[2];

				$cachekey = $field_tablename . ":" . $field_columnname;
				if (!isset($modulename_cache[$cachekey])) {
					$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid from vtiger_field where tablename = ? and columnname=?", array($fieldlist[1], $fieldlist[2]));
					if ($adb->num_rows($mod_query) > 0) {
						$module_name = \App\Module::getModuleName($adb->query_result($mod_query, 0, 'tabid'));
						$modulename_cache[$cachekey] = $module_name;
					}
				} else {
					$module_name = $modulename_cache[$cachekey];
				}

				$fieldlabel = trim($fieldlist[3]);
				if ($field_tablename == 'vtiger_inventoryproductrel') {
					$field_columnalias = $premod . "__" . $fieldlist[3];
				} else {
					if ($module_name) {
						$field_columnalias = $module_name . "__" . $fieldlist[3];
					} else {
						$field_columnalias = $module_name . "__" . $fieldlist[3];
					}
				}

				$field_permitted = false;
				if (\App\Field::getColumnPermission($premod, $field_columnname)) {
					$field_permitted = true;
				} else {
					$mod = explode(':', $secmod);
					foreach ($mod as $key) {
						if (\App\Field::getColumnPermission($key, $field_columnname)) {
							$field_permitted = true;
						}
					}
				}

				//Calculation fields of "Events" module should show in Calendar related report
				$secondaryModules = explode(':', $secmod);
				if ($field_permitted === false && ($premod === 'Calendar' || in_array('Calendar', $secondaryModules)) && \App\Field::getColumnPermission('Events', $field_columnname)) {
					$field_permitted = true;
				}

				if ($field_permitted === true) {
					$field = $this->getColumnsTotalSQL($fieldlist, $premod);

					if ($fieldlist[4] == 2) {
						$stdfilterlist[$fieldcolname] = "sum($field) '" . $field_columnalias . "'";
					}
					if ($fieldlist[4] == 3) {
						$stdfilterlist[$fieldcolname] = "(sum($field)/count(*)) '" . $field_columnalias . "'";
					}
					if ($fieldlist[4] == 4) {
						$stdfilterlist[$fieldcolname] = "min($field) '" . $field_columnalias . "'";
					}
					if ($fieldlist[4] == 5) {
						$stdfilterlist[$fieldcolname] = "max($field) '" . $field_columnalias . "'";
					}

					$this->queryPlanner->addTable($field_tablename);
				}
			}
		}

		// Save the information
		$this->_columnstotallist = $stdfilterlist;

		\App\Log::trace("ReportRun :: Successfully returned getColumnsTotal" . $reportid);
		return $stdfilterlist;
	}

	//<<<<<<new>>>>>>>>>


	public function getColumnsTotalSQL($fieldlist, $premod)
	{
		// Added condition to support detail report calculations
		if ($fieldlist[0] == 'cb') {
			$field_tablename = $fieldlist[1];
			$field_columnname = $fieldlist[2];
		} else {
			$field_tablename = $fieldlist[0];
			$field_columnname = $fieldlist[1];
		}

		$field = $field_tablename . "." . $field_columnname;
		if ($field_tablename == 'vtiger_products' && $field_columnname == 'unit_price') {
			// Query needs to be rebuild to get the value in user preferred currency. [innerProduct and actual_unit_price are table and column alias.]
			$field = " innerProduct.actual_unit_price";
			$this->queryPlanner->addTable("innerProduct");
		}
		if ($field_tablename == 'vtiger_service' && $field_columnname == 'unit_price') {
			// Query needs to be rebuild to get the value in user preferred currency. [innerProduct and actual_unit_price are table and column alias.]
			$field = " innerService.actual_unit_price";
			$this->queryPlanner->addTable("innerService");
		}

		if ($field_tablename == 'vtiger_inventoryproductrel') {
			// Check added so that query planner can prepare query properly for inventory modules
			$this->lineItemFieldsInCalculation = true;
			$field = $field_tablename . $premod . '.' . $field_columnname;
			$itemTableName = 'vtiger_inventoryproductrel' . $premod;
			$this->queryPlanner->addTable($itemTableName);
			$primaryModuleInstance = CRMEntity::getInstance($premod);
			if ($field_columnname == 'listprice') {
				$field = $field . '/' . $primaryModuleInstance->table_name . '.conversion_rate';
			} else if ($field_columnname == 'discount_amount') {
				$field = ' CASE WHEN ' . $itemTableName . '.discount_amount is not null THEN ' . $itemTableName . '.discount_amount/' . $primaryModuleInstance->table_name . '.conversion_rate ' .
					'WHEN ' . $itemTableName . '.discount_percent IS NOT NULL THEN (' . $itemTableName . '.listprice*' . $itemTableName . '.quantity*' . $itemTableName . '.discount_percent/100/' . $primaryModuleInstance->table_name . '.conversion_rate) ELSE 0 END ';
			}
		}
		return $field;
	}

	/** function to get query for the columns to total for the given reportid
	 *  @ param $reportid : Type integer
	 *  This returns columnstoTotal query for the reportid
	 */
	public function getColumnsToTotalColumns($reportid)
	{
		$adb = PearDatabase::getInstance();
		global $modules;


		$sreportstdfiltersql = "select vtiger_reportsummary.* from vtiger_report";
		$sreportstdfiltersql .= " inner join vtiger_reportsummary on vtiger_report.reportid = vtiger_reportsummary.reportsummaryid";
		$sreportstdfiltersql .= " where vtiger_report.reportid =?";

		$result = $adb->pquery($sreportstdfiltersql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, "columnname");

			if ($fieldcolname != "none") {
				$fieldlist = explode(":", $fieldcolname);
				if ($fieldlist[4] == 2) {
					$sSQLList[] = "sum(" . $fieldlist[1] . "." . $fieldlist[2] . ") " . $fieldlist[3];
				}
				if ($fieldlist[4] == 3) {
					$sSQLList[] = "avg(" . $fieldlist[1] . "." . $fieldlist[2] . ") " . $fieldlist[3];
				}
				if ($fieldlist[4] == 4) {
					$sSQLList[] = "min(" . $fieldlist[1] . "." . $fieldlist[2] . ") " . $fieldlist[3];
				}
				if ($fieldlist[4] == 5) {
					$sSQLList[] = "max(" . $fieldlist[1] . "." . $fieldlist[2] . ") " . $fieldlist[3];
				}
			}
		}
		if (isset($sSQLList)) {
			$sSQL = implode(",", $sSQLList);
		}
		\App\Log::trace("ReportRun :: Successfully returned getColumnsToTotalColumns" . $reportid);
		return $sSQL;
	}

	/** Function to convert the Report Header Names into i18n
	 *  @param $fldname: Type Varchar
	 *  Returns Language Converted Header Strings
	 * */
	public function getLstringforReportHeaders($fldname)
	{
		global $modules;
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$rep_header = ltrim($fldname);
		$rep_header = decode_html($rep_header);
		$labelInfo = explode('__', $rep_header);
		$rep_module = $labelInfo[0];
		if (is_array($this->labelMapping) && !empty($this->labelMapping[$rep_header])) {
			$rep_header = $this->labelMapping[$rep_header];
		} else {
			if ($rep_module == 'LBL') {
				$rep_module = '';
			}
			array_shift($labelInfo);
			$fieldLabel = decode_html(implode("__", $labelInfo));
			$rep_header_temp = preg_replace("/\s+/", "__", $fieldLabel);
			$rep_header = "$rep_module $fieldLabel";
		}
		$curr_symb = "";
		$fieldLabel = ltrim(str_replace($rep_module, '', $rep_header), '__');
		$fieldInfo = getFieldByReportLabel($rep_module, $fieldLabel);
		if ($fieldInfo['uitype'] == '71') {
			$curr_symb = " (" . \App\Language::translate('LBL_IN') . " " . $current_user->currency_symbol . ")";
		}
		$rep_header .= $curr_symb;

		return $rep_header;
	}

	/** Function to get picklist value array based on profile
	 *          *  returns permitted fields in array format
	 * */
	public function getAccessPickListValues()
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$id = array(\App\Module::getModuleId($this->primarymodule));
		if ($this->secondarymodule != '')
			array_push($id, \App\Module::getModuleId($this->secondarymodule));

		$query = sprintf('select fieldname,columnname,fieldid,fieldlabel,tabid,uitype from vtiger_field where tabid in(%s) and uitype in (15,33,55)', generateQuestionMarks($id)); //and columnname in (?)';
		$result = $adb->pquery($query, $id); //,$select_column));
		$roleid = $current_user->roleid;
		$subrole = \App\PrivilegeUtil::getRoleSubordinates($roleid);
		if (count($subrole) > 0) {
			$roleids = $subrole;
			array_push($roleids, $roleid);
		} else {
			$roleids = $roleid;
		}

		$temp_status = Array();
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$fieldname = $adb->query_result($result, $i, "fieldname");
			$fieldlabel = $adb->query_result($result, $i, "fieldlabel");
			$tabid = $adb->query_result($result, $i, "tabid");
			$uitype = $adb->query_result($result, $i, "uitype");

			$fieldlabel1 = str_replace(" ", "__", $fieldlabel);
			$keyvalue = \App\Module::getModuleName($tabid) . "__" . $fieldlabel1;
			$fieldvalues = Array();
			if (count($roleids) > 1) {
				$mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid in (\"" . implode($roleids, "\",\"") . "\") and picklistid in (select picklistid from vtiger_$fieldname)"; // order by sortid asc - not requried
			} else {
				$mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid ='" . $roleid . "' and picklistid in (select picklistid from vtiger_$fieldname)"; // order by sortid asc - not requried
			}
			if ($fieldname != 'firstname')
				$mulselresult = $adb->query($mulsel);
			$countMulSelResult = $adb->num_rows($mulselresult);
			for ($j = 0; $j < $countMulSelResult; $j++) {
				$fldvalue = $adb->query_result($mulselresult, $j, $fieldname);
				if (in_array($fldvalue, $fieldvalues))
					continue;
				$fieldvalues[] = $fldvalue;
			}
			$field_count = count($fieldvalues);
			if ($uitype == 15 && $field_count > 0 && ($fieldname == 'activitystatus')) {
				$temp_count = count($temp_status[$keyvalue]);
				if ($temp_count > 0) {
					for ($t = 0; $t < $field_count; $t++) {
						$temp_status[$keyvalue][($temp_count + $t)] = $fieldvalues[$t];
					}
					$fieldvalues = $temp_status[$keyvalue];
				} else
					$temp_status[$keyvalue] = $fieldvalues;
			}

			if ($uitype == 33)
				$fieldlists[1][$keyvalue] = $fieldvalues;
			else if ($uitype == 55 && $fieldname == 'salutationtype')
				$fieldlists[$keyvalue] = $fieldvalues;
			else if ($uitype == 15)
				$fieldlists[$keyvalue] = $fieldvalues;
		}
		return $fieldlists;
	}

	/**
	 * Returns PhpExcel type constant based on value
	 *
	 * @param mixed $value any value
	 *
	 * @return string PhpExcel value type PHPExcel_Cell_DataType::TYPE_*
	 */
	private function getPhpExcelTypeFromValue($value)
	{
		if (is_integer($value) || is_float($value)) {
			return PHPExcel_Cell_DataType::TYPE_NUMERIC;
		} else if (is_null($value)) {
			return PHPExcel_Cell_DataType::TYPE_NULL;
		} else if (is_bool($value)) {
			return PHPExcel_Cell_DataType::TYPE_BOOL;
		}
		return PHPExcel_Cell_DataType::TYPE_STRING;
	}

	public function writeReportToExcelFile($fileName, $filterlist = '')
	{
		require_once("libraries/PHPExcel/PHPExcel.php");
		$workbook = new PHPExcel();
		$worksheet = $workbook->setActiveSheetIndex(0);
		$reportData = $this->GenerateReport("PDF", $filterlist);
		$arrayValues = $reportData['data'];
		$totalxls = $this->GenerateReport("TOTALXLS", $filterlist);
		$header_styles = array(
			'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E1E0F7')),
		);
		if (!empty($arrayValues)) {
			$count = 0;
			$rowcount = 1;
			//copy the first value details
			$arrayFirstRowValues = $arrayValues[0];
			array_pop($arrayFirstRowValues);   // removed action link in details
			foreach ($arrayFirstRowValues as $key => $value) {
				$worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, $key, true);
				$worksheet->getStyleByColumnAndRow($count, $rowcount)->applyFromArray($header_styles);
				$count = $count + 1;
			}
			$rowcount++;
			foreach ($arrayValues as $key => $array_value) {
				$count = 0;
				array_pop($array_value); // removed action link in details
				foreach ($array_value as $hdr => $value) {
					if ($hdr == 'ACTION') {
						continue;
					}
					if (is_string($value)) {
						$value = decode_html($value);
					}
					$worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, $value, $this->getPhpExcelTypeFromValue($value));
					$count = $count + 1;
				}
				$rowcount++;
			}
			// Summary Total
			$rowcount++;
			$count = 0;
			if (is_array($totalxls[0])) {
				$worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, App\Language::translate('LBL_FIELD_NAMES', 'Reports'));
				$worksheet->getStyleByColumnAndRow($count, $rowcount)->applyFromArray($header_styles);
				$count++;
				foreach ($totalxls[0] as $key => $value) {
					$operator = substr($key, -3, 3);
					$worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, App\Language::translate("LBL_$operator", 'Reports'));
					$worksheet->getStyleByColumnAndRow($count, $rowcount)->applyFromArray($header_styles);
					$count++;
				}
			}
			$rowcount++;
			foreach ($totalxls as $key => $array_value) {
				$count = 0;
				$labels = array_keys($array_value);
				$valueArray = explode('__', $labels[0], 2);
				$operator = substr($labels[0], -3, 3);
				$moduleName = $valueArray[0];
				$fieldLabel = str_replace("__$operator", '', $valueArray[1]);
				$fieldLabel = str_replace('__', '', $fieldLabel);
				$worksheet->setCellValueExplicitByColumnAndRow($count, $key + $rowcount, App\Language::translate($moduleName, $moduleName) . '-' . App\Language::translate($fieldLabel, $moduleName));
				$count++;
				foreach ($array_value as $hdr => $value) {
					$value = decode_html($value);
					$worksheet->setCellValueExplicitByColumnAndRow($count, $key + $rowcount, $value);
					$count = $count + 1;
				}
			}
		}
		$workbookWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel5');
		$workbookWriter->save($fileName);
	}

	public function writeReportToCSVFile($fileName, $filterlist = '')
	{
		$reportData = $this->GenerateReport("PDF", $filterlist);
		$arr_val = $reportData['data'];

		$fp = fopen($fileName, 'w+');
		fputs($fp, chr(239) . chr(187) . chr(191)); //UTF-8 byte order mark
		if (isset($arr_val)) {
			// Header
			$csv_values = array_keys($arr_val[0]);
			array_pop($csv_values);   //removed header in csv file
			fputcsv($fp, $csv_values);
			foreach ($arr_val as $key => $array_value) {
				array_pop($array_value); //removed action link
				$csv_values = array_map('decode_html', array_values($array_value));
				fputcsv($fp, $csv_values);
			}
		}
		fclose($fp);
	}

	public function getGroupByTimeList($reportId)
	{
		$adb = PearDatabase::getInstance();
		$groupByTimeQuery = "SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?";
		$groupByTimeRes = $adb->pquery($groupByTimeQuery, array($reportId));
		$num_rows = $adb->num_rows($groupByTimeRes);
		for ($i = 0; $i < $num_rows; $i++) {
			$sortColName = $adb->query_result($groupByTimeRes, $i, 'sortcolname');
			list($tablename, $colname, $module_field, $fieldname, $single) = explode(':', $sortColName);
			$groupField = $module_field;
			$groupCriteria = $adb->query_result($groupByTimeRes, $i, 'dategroupbycriteria');
			if (in_array($groupCriteria, array_keys($this->groupByTimeParent))) {
				$parentCriteria = $this->groupByTimeParent[$groupCriteria];
				foreach ($parentCriteria as $criteria) {
					$groupByCondition[] = $this->GetTimeCriteriaCondition($criteria, $groupField);
				}
			}
			$groupByCondition[] = $this->GetTimeCriteriaCondition($groupCriteria, $groupField);
			$this->queryPlanner->addTable($tablename);
		}
		return $groupByCondition;
	}

	public function GetTimeCriteriaCondition($criteria, $dateField)
	{
		$condition = "";
		if (strtolower($criteria) == 'year') {
			$condition = "DATE_FORMAT($dateField, '%Y' )";
		} else if (strtolower($criteria) == 'month') {
			$condition = "CEIL(DATE_FORMAT($dateField,'%m')%13)";
		} else if (strtolower($criteria) == 'quarter') {
			$condition = "CEIL(DATE_FORMAT($dateField,'%m')/3)";
		}
		return $condition;
	}

	public function GetFirstSortByField($reportid)
	{
		$adb = PearDatabase::getInstance();
		$groupByField = "";
		$sortFieldQuery = "SELECT * FROM vtiger_reportsortcol
                            LEFT JOIN vtiger_reportgroupbycolumn ON (vtiger_reportsortcol.sortcolid = vtiger_reportgroupbycolumn.sortid and vtiger_reportsortcol.reportid = vtiger_reportgroupbycolumn.reportid)
                            WHERE columnname!='none' and vtiger_reportsortcol.reportid=? ORDER By sortcolid";
		$sortFieldResult = $adb->pquery($sortFieldQuery, array($reportid));
		$inventoryModules = getInventoryModules();
		if ($adb->num_rows($sortFieldResult) > 0) {
			$fieldcolname = $adb->query_result($sortFieldResult, 0, 'columnname');
			list($tablename, $colname, $module_field, $fieldname, $typeOfData) = explode(":", $fieldcolname);
			list($modulename, $fieldlabel) = explode('__', $module_field, 2);
			$groupByField = $module_field;
			if ($typeOfData == "D") {
				$groupCriteria = $adb->query_result($sortFieldResult, 0, 'dategroupbycriteria');
				if (strtolower($groupCriteria) != 'none') {
					if (in_array($groupCriteria, array_keys($this->groupByTimeParent))) {
						$parentCriteria = $this->groupByTimeParent[$groupCriteria];
						foreach ($parentCriteria as $criteria) {
							$groupByCondition[] = $this->GetTimeCriteriaCondition($criteria, $groupByField);
						}
					}
					$groupByCondition[] = $this->GetTimeCriteriaCondition($groupCriteria, $groupByField);
					$groupByField = implode(", ", $groupByCondition);
				}
			} elseif (!\App\Field::getFieldPermission($modulename, $fieldname)) {
				if (!(in_array($modulename, $inventoryModules) && $fieldname === 'serviceid')) {
					$groupByField = $tablename . "." . $colname;
				}
			}
		}
		return $groupByField;
	}

	public function getReferenceFieldColumnList($moduleName, $fieldInfo)
	{
		$adb = PearDatabase::getInstance();

		$columnsSqlList = array();

		$fieldInstance = WebserviceField::fromArray($adb, $fieldInfo);
		$referenceModuleList = $fieldInstance->getReferenceList();
		$reportSecondaryModules = explode(':', $this->secondarymodule);

		if ($moduleName != $this->primarymodule && in_array($this->primarymodule, $referenceModuleList)) {
			$entityTableFieldNames = \App\Module::getEntityInfo($this->primarymodule);
			$entityTableName = $entityTableFieldNames['tablename'];
			$entityFieldNames = $entityTableFieldNames['fieldname'];

			$columnList = array();
			if (strpos(',', $entityFieldNames) !== false) {
				foreach ($entityTableFieldNames['fieldnameArr'] as $entityColumnName) {
					$columnList["$entityColumnName"] = "$entityTableName.$entityColumnName";
				}
			} else {
				$columnList[] = "$entityTableName.$entityFieldNames";
			}
			if (count($columnList) > 1) {
				$columnSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat($columnList, $this->primarymodule);
			} else {
				$columnSql = implode('', $columnList);
			}
			$columnsSqlList[] = $columnSql;
		} else {
			foreach ($referenceModuleList as $referenceModule) {
				$entityTableFieldNames = \App\Module::getEntityInfo($referenceModule);
				$entityTableName = $entityTableFieldNames['tablename'];
				$entityFieldNames = $entityTableFieldNames['fieldname'];

				$referenceTableName = '';
				$dependentTableName = '';

				if ($moduleName == 'HelpDesk' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountRelHelpDesk';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Products') {
					$referenceTableName = 'vtiger_productsRel';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Project') {
					$referenceTableName = 'vtiger_projectRelHelpDesk';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Leads') {
					$referenceTableName = 'vtiger_leaddetailsRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'HelpDesk') {
					$referenceTableName = 'vtiger_troubleticketsRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Campaigns') {
					$referenceTableName = 'vtiger_campaignRelCalendar';
				} elseif ($moduleName == 'Contacts' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountContacts';
				} elseif ($moduleName == 'Contacts' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsContacts';
				} elseif ($moduleName == 'Accounts' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountAccounts';
				} elseif ($moduleName == 'Campaigns' && $referenceModule == 'Products') {
					$referenceTableName = 'vtiger_productsCampaigns';
				} elseif ($moduleName == 'Faq' && $referenceModule == 'Products') {
					$referenceTableName = 'vtiger_productsFaq';
				} elseif ($moduleName == 'Products' && $referenceModule == 'Vendors') {
					$referenceTableName = 'vtiger_vendorRelProducts';
				} elseif ($moduleName == 'ModComments' && $referenceModule == 'Users') {
					$referenceTableName = 'vtiger_usersModComments';
				} elseif (in_array($referenceModule, $reportSecondaryModules) && $moduleName != vtlib\Functions::getModuleName($fieldInstance->getTabId())) {
					$referenceTableName = "{$entityTableName}Rel$referenceModule";
					$dependentTableName = "vtiger_crmentityRel{$referenceModule}{$fieldInstance->getFieldId()}";
				} elseif (in_array($moduleName, $reportSecondaryModules)) {
					$referenceTableName = "{$entityTableName}Rel$moduleName";
					$dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
				} elseif ($moduleName == 'OSSTimeControl' && $referenceModule == 'Accounts') {
					$referenceTableName = "vtiger_account";
					$dependentTableName = "vtiger_account";
				} elseif ($moduleName == 'OSSTimeControl' && $referenceModule == 'Contacts') {
					$referenceTableName = "vtiger_contactdetails";
					$dependentTableName = "vtiger_contactdetails";
				} elseif ($moduleName == 'OSSTimeControl' && $referenceModule == 'Leads') {
					$referenceTableName = "vtiger_leaddetails";
					$dependentTableName = "vtiger_leaddetails";
				} elseif ($moduleName == 'OSSTimeControl' && $referenceModule == 'Vendors') {
					$referenceTableName = "vtiger_vendor";
					$dependentTableName = "vtiger_vendor";
				} elseif ($moduleName == 'OSSTimeControl' && $referenceModule == 'Partners') {
					$referenceTableName = "u_yf_partners";
					$dependentTableName = "u_yf_partners";
				} elseif ($moduleName == 'OSSTimeControl' && $referenceModule == 'Competition') {
					$referenceTableName = "u_yf_competition";
					$dependentTableName = "u_yf_competition";
				} elseif ($moduleName == 'OSSTimeControl' && $referenceModule == 'OSSEmployees') {
					$referenceTableName = "vtiger_ossemployees";
					$dependentTableName = "vtiger_ossemployees";
				} else {
					$referenceTableName = "{$entityTableName}Rel{$moduleName}{$fieldInstance->getFieldId()}";
					$dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
					$this->queryPlanner->addCustomTable(
						array(
							'reference' => $referenceTableName,
							'field' => $fieldInstance->getColumnName(),
							'table' => $fieldInfo['tablename'],
							'refTable' => $entityTableFieldNames['tablename'],
							'refIndex' => $entityTableFieldNames['entityidfield']
						)
					);
				}

				$this->queryPlanner->addTable($referenceTableName);

				if (isset($dependentTableName)) {
					$this->queryPlanner->addTable($dependentTableName);
				}
				$columnList = array();
				if (strpos($entityFieldNames, ',') !== false) {
					foreach ($entityTableFieldNames['fieldnameArr'] as $entityColumnName) {
						$columnList[$entityColumnName] = "$referenceTableName.$entityColumnName";
					}
				} else {
					$columnList[] = "$referenceTableName.$entityFieldNames";
				}
				if (count($columnList) > 1) {
					$columnSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat($columnList, $referenceModule);
				} else {
					$columnSql = implode('', $columnList);
				}

				if ($referenceModule == 'Currency' && $fieldInstance->getFieldName() == 'currency_id') {
					$columnSql = "vtiger_currency_info$moduleName.currency_name";
					$this->queryPlanner->addTable("vtiger_currency_info$moduleName");
				}
				$columnsSqlList[] = "trim($columnSql)";
			}
		}
		return $columnsSqlList;
	}
}

?>
