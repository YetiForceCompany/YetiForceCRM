<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class iCalLastImport
{

	public $tableName = 'vtiger_ical_import';
	public $fields = array('id', 'userid', 'entitytype', 'crmid');
	public $fieldData = [];

	public function clearRecords($userId)
	{
		$adb = PearDatabase::getInstance();
		if (vtlib\Utils::CheckTable($this->tableName)) {
			$query = sprintf('DELETE FROM %s WHERE userid = ?', $this->tableName);
			$adb->pquery($query, array($userId));
		}
	}

	public function setFields($data)
	{
		if (!empty($data)) {
			foreach ($data as $name => $value) {
				$this->fieldData[$name] = $value;
			}
		}
	}

	public function save()
	{
		$adb = PearDatabase::getInstance();

		if (count($this->fieldData) == 0)
			return;

		if (!vtlib\Utils::CheckTable($this->tableName)) {
			vtlib\Utils::CreateTable(
				$this->tableName, "(id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					userid INT NOT NULL,
					entitytype VARCHAR(200) NOT NULL,
					crmid INT NOT NULL)", true);
		}

		$fieldNames = array_keys($this->fieldData);
		$fieldValues = array_values($this->fieldData);
		$adb->pquery('INSERT INTO ' . $this->tableName . '(' . implode(',', $fieldNames) . ') VALUES (' . generateQuestionMarks($fieldValues) . ')', array($fieldValues));
	}

	public function undo($moduleName, $userId)
	{
		$adb = PearDatabase::getInstance();
		if (vtlib\Utils::CheckTable($this->tableName)) {
			$query = sprintf('UPDATE vtiger_crmentity SET deleted=1 WHERE crmid IN (SELECT crmid FROM %s WHERE userid = ? && entitytype = ?)', $this->tableName);
			$result = $adb->pquery($query, [$userId, $moduleName]);
			return $adb->getAffectedRowCount($result);
		}
	}
}

?>
