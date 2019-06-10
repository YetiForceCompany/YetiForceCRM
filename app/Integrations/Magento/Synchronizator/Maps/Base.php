<?php

/**
 * Field base map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

abstract class Base
{
	/**
	 * Mapped fields.
	 *
	 * @var array
	 */
	public static $mappedFields = [];
	/**
	 * Fields which can not be updated.
	 *
	 * @var array
	 */
	public static $nonEditableFields = [];
	/**
	 * Data from Magento.
	 *
	 * @var array
	 */
	public $data = [];
	/**
	 * Data from Yetiforce.
	 *
	 * @var array
	 */
	public $dataCrm = [];

	/**
	 * Return Magento field name.
	 *
	 * @param string $name
	 *
	 * @return mixed|string
	 */
	public function getFieldName(string $name)
	{
		$fieldName = explode('|', static::$mappedFields[$name]);
		return end($fieldName) ?? '';
	}

	/**
	 * Return Yetiforce field name.
	 *
	 * @param string $name
	 *
	 * @return mixed|string
	 */
	public function getFieldNameCrm(string $name)
	{
		return array_flip(static::$mappedFields)[$name] ?? '';
	}

	/**
	 * Return fields list.
	 *
	 * @param bool $onEdit
	 *
	 * @return array
	 */
	public function getFields(bool $onEdit = false): array
	{
		if ($onEdit) {
			$fields = array_diff(static::$mappedFields, static::$nonEditableFields);
		} else {
			$fields = static::$mappedFields;
		}
		return $fields;
	}

	/**
	 * Set data.
	 *
	 * @param array $data
	 */
	public function setData(array $data): void
	{
		$this->data = $data;
	}

	/**
	 * Set data Yetiforce.
	 *
	 * @param array $data
	 */
	public function setDataCrm(array $data): void
	{
		$this->dataCrm = $data;
	}

	/**
	 * Return parsed data in Magento format.
	 *
	 * @param bool $onEdit
	 *
	 * @return array
	 */
	public function getData(bool $onEdit = false): array
	{
		$data = [];
		foreach ($this->getFields($onEdit) as $fieldCrm => $field) {
			$data = \array_merge($data, $this->getFieldValueCrm($fieldCrm, true));
		}
		return $data;
	}

	/**
	 * Return parsed data in Yetiforce format.
	 *
	 * @param bool $onEdit
	 *
	 * @return array
	 */
	public function getDataCrm(bool $onEdit = false): array
	{
		$data = [];
		foreach ($this->getFields($onEdit) as $fieldCrm => $field) {
			$data[$fieldCrm] = $this->getFieldValue($field, true) ?? false;
		}
		return $data;
	}

	/**
	 * @param $fieldName
	 * @param mixed $parsedStructure
	 *
	 * @return array
	 */
	public function getFieldValueCrm($fieldName, $parsedStructure = false): array
	{
		$methodName = 'get' . \ucfirst($this->getFieldName($fieldName));
		if (!\method_exists($this, $methodName)) {
			$fieldParsed = $this->dataCrm;
			foreach (explode('|', $fieldName) as $fieldLevel) {
				$fieldParsed = $fieldParsed[$fieldLevel];
			}
			if ($parsedStructure) {
				$data[$this->getFieldName($fieldName)] = $fieldParsed;
			} else {
				$data = $fieldParsed;
			}
		} else {
			$data = $this->{$methodName}($parsedStructure);
		}
		return $data ?? [];
	}

	/**
	 * @param $fieldName
	 * @param bool $parsedStructure
	 *
	 * @return array|mixed
	 */
	public function getFieldValue($fieldName, $parsedStructure = false)
	{
		$methodName = 'getCrm' . \ucfirst($this->getFieldNameCrm($fieldName));
		if (!\method_exists($this, $methodName)) {
			$fieldParsed = $this->data;
			foreach (explode('|', $fieldName) as $fieldLevel) {
				if (isset($fieldParsed[$fieldLevel])) {
					$fieldParsed = $fieldParsed[$fieldLevel];
				} else {
					break;
				}
			}
		} else {
			$fieldParsed = $this->{$methodName}($parsedStructure);
		}
		return $fieldParsed ?? [];
	}
}
