<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Class IcalLastImport.
 */
class IcalLastImport
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $tableName = 'vtiger_ical_import';

	/**
	 * Fields.
	 *
	 * @var array
	 */
	public $fields = ['id', 'userid', 'entitytype', 'crmid'];

	/**
	 * Field data.
	 *
	 * @var array
	 */
	public $fieldData = [];

	/**
	 * Clear user records.
	 *
	 * @param int $userId
	 */
	public function clearRecords($userId)
	{
		if (vtlib\Utils::checkTable($this->tableName)) {
			\App\Db::getInstance()->createCommand()->delete($this->tableName, ['userid' => $userId])->execute();
		}
	}

	/**
	 * Set fields.
	 *
	 * @param array $data
	 */
	public function setFields($data)
	{
		if (!empty($data)) {
			foreach ($data as $name => $value) {
				$this->fieldData[$name] = $value;
			}
		}
	}

	/**
	 * Save.
	 */
	public function save()
	{
		$adb = PearDatabase::getInstance();

		if (count($this->fieldData) == 0) {
			return;
		}

		if (!vtlib\Utils::checkTable($this->tableName)) {
			vtlib\Utils::createTable(
				$this->tableName, '(id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					userid INT NOT NULL,
					entitytype VARCHAR(200) NOT NULL,
					crmid INT NOT NULL)', true);
		}

		$fieldNames = array_keys($this->fieldData);
		$fieldValues = array_values($this->fieldData);
		$adb->pquery('INSERT INTO ' . $this->tableName . '(' . implode(',', $fieldNames) . ') VALUES (' . $adb->generateQuestionMarks($fieldValues) . ')', [$fieldValues]);
	}

	/**
	 * Undo.
	 *
	 * @param string $moduleName
	 * @param int    $userId
	 *
	 * @return int|bool
	 */
	public function undo($moduleName, $userId)
	{
		if (vtlib\Utils::checkTable($this->tableName)) {
			$selectResult = (new \App\Db\Query())->select(['crmid'])->from($this->tableName)->where(['userid' => $userId, 'entitytype' => $moduleName])->column();

			return \App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['deleted' => 1], ['crmid' => $selectResult])->execute();
		}
	}
}
