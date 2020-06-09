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
 * Class ICalLastImport.
 */
class ICalLastImport
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
		if (0 === \count($this->fieldData)) {
			return;
		}
		$db = \App\Db::getInstance();
		if (!$db->isTableExists($this->tableName)) {
			$importer = new \App\Db\Importers\Base();
			$db->createTable($this->tableName, [
				'id' => 'pk',
				'userid' => $importer->integer(10)->notNull(),
				'entitytype' => $importer->stringType('200')->notNull(),
				'crmid' => $importer->integer(10)->notNull()]
			);
		}
		\App\Db::getInstance()->createCommand()->insert($this->tableName, $this->fieldData)->execute();
	}

	/**
	 * Undo.
	 *
	 * @param string $moduleName
	 * @param int    $userId
	 *
	 * @return bool|int
	 */
	public function undo($moduleName, $userId)
	{
		if (vtlib\Utils::checkTable($this->tableName)) {
			$selectResult = (new \App\Db\Query())->select(['crmid'])->from($this->tableName)->where(['userid' => $userId, 'entitytype' => $moduleName])->column();

			return \App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['deleted' => 1], ['crmid' => $selectResult])->execute();
		}
	}
}
