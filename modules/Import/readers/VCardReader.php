<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Import_VCardReader_Reader extends Import_FileReader_Reader
{

	protected $vCardPattern = '/BEGIN:VCARD.*?END:VCARD/si';
	protected $skipLabels = array('BEGIN', 'END', 'VERSION');
	static $fileContents = null;

	public function hasHeader()
	{
		return true;
	}

	public function getFirstRowData($hasHeader = true)
	{
		$default_charset = AppConfig::main('default_charset');

		$filePath = $this->getFilePath();
		if (empty(self::$fileContents)) {
			self::$fileContents = file_get_contents($filePath);
		}
		$fileContents = self::$fileContents;

		$data = null;
		$matches = array();
		preg_match_all($this->vCardPattern, $fileContents, $matches);

		$row = $matches[0][0];
		$fieldValueMappings = explode("\r\n", $row);
		$data = array();
		foreach ($fieldValueMappings as $fieldValueMapping) {
			list($label, $value) = explode(':', $fieldValueMapping, 2);
			$value = str_replace(';', ' ', $value);
			if (!in_array($label, $this->skipLabels)) {
				$data[$label] = $this->convertCharacterEncoding($value, $this->request->get('file_encoding'), $default_charset);
			}
		}
		return $data;
	}

	/**
	 * Function creates tables for import in database
	 */
	public function read()
	{
		$defaultCharset = AppConfig::main('default_charset');

		$filePath = $this->getFilePath();
		$this->createTable();

		$fieldMapping = $this->request->get('field_mapping');

		if (empty(self::$fileContents)) {
			self::$fileContents = file_get_contents($filePath);
		}
		$fileContents = self::$fileContents;

		$matches = array();
		preg_match_all($this->vCardPattern, $fileContents, $matches);
		$countMatches = count($matches[0]);
		for ($i = 0; $i < $countMatches; ++$i) {
			$row = $matches[0][$i];
			$fieldValueMappings = explode("\r\n", $row);
			$data = array();
			$valueCounter = 0;
			foreach ($fieldValueMappings as $fieldValueMapping) {
				list($label, $value) = explode(':', $fieldValueMapping, 2);
				$value = str_replace(';', ' ', $value);
				if (!in_array($label, $this->skipLabels)) {
					$data[$valueCounter++] = $value;
				}
			}
			$mappedData = array();
			$allValuesEmpty = true;
			foreach ($fieldMapping as $fieldName => $index) {
				$fieldValue = $data[$index];
				$mappedData[$fieldName] = $fieldValue;
				if ($this->request->get('file_encoding') !== $defaultCharset) {
					$mappedData[$fieldName] = $this->convertCharacterEncoding($fieldValue, $this->request->get('file_encoding'), $defaultCharset);
				}
				if (!empty($fieldValue))
					$allValuesEmpty = false;
			}
			if ($allValuesEmpty)
				continue;
			$fieldNames = array_keys($mappedData);
			$fieldValues = array_values($mappedData);
			$this->addRecordToDB($fieldNames, $fieldValues);
		}
	}
}
