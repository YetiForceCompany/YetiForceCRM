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

class ReportRunQueryPlanner
{
	// Turn-off the query planning to revert back - backward compatiblity
	protected $disablePlanner = false;
	protected $tables = [];
	protected $customTables = [];
	protected $tempTables = [];
	protected $tempTablesInitialized = false;
	// Turn-off in case the query result turns-out to be wrong.
	protected $allowTempTables = true;
	protected $tempTablePrefix = 'vtiger_reptmptbl_';
	protected static $tempTableCounter = 0;
	protected $registeredCleanup = false;
	public static $existTables = [];

	public function addTable($table)
	{
		if (!empty($table)) {
			$this->tables[$table] = $table;
		}
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
		} elseif ($dependencies instanceof ReportRunQueryDependencyMatrix) {
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
			$keyColumns = is_array($keyColumns) ? array_unique($keyColumns) : [$keyColumns];
			// Minor optimization to avoid re-creating similar temporary table.
			$uniqueName = null;
			foreach ($this->tempTables as $tmpUniqueName => $tmpTableInfo) {
				if (strcasecmp($query, $tmpTableInfo['query']) === 0) {
					// Capture any additional key columns
					$tmpTableInfo['keycolumns'] = array_unique(array_merge($tmpTableInfo['keycolumns'], $keyColumns));
					$uniqueName = $tmpUniqueName;
					break;
				}
			}

			if ($uniqueName === null) {
				$uniqueName = $this->tempTablePrefix .
					str_replace('.', '', uniqid(\App\User::getCurrentUserId(), true)) . (self::$tempTableCounter++);

				$this->tempTables[$uniqueName] = [
					'query' => $query,
					'keycolumns' => is_array($keyColumns) ? array_unique($keyColumns) : [$keyColumns],
				];
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
			register_shutdown_function([$this, 'cleanup']);
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
			$adb->pquery('DROP TABLE ' . $uniqueName, []);
		}
		$adb->dieOnError = $oldDieOnError;

		$this->tempTables = [];
	}
}
