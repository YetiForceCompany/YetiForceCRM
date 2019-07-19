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
	 * Fields which are not exist in Magento but needed in YetiForce.
	 *
	 * @var array
	 */
	public static $additionalFieldsCrm = [];
	/**
	 * Fields which are not exist in YetiForce but needed in Magento.
	 *
	 * @var array
	 */
	public static $additionalFields = [];
	/**
	 * Mapped fields.
	 *
	 * @var array
	 */
	public static $mappedFields = [];
	/**
	 * Mapped fields type.
	 *
	 * @var array
	 */
	public static $fieldsType = [];
	/**
	 * Fields default value.
	 *
	 * @var array
	 */
	public static $fieldsDefaultValue = [];

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
	 * Data from YetiForce.
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
		$fieldName = explode('|', static::$mappedFields[$name] ?? $name);
		return end($fieldName);
	}

	/**
	 * Return YetiForce field name.
	 *
	 * @param string $name
	 *
	 * @return mixed|string
	 */
	public function getFieldNameCrm(string $name)
	{
		return array_flip(static::$mappedFields)[$name] ?? $name;
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
	 * Return additional Magento fields list.
	 *
	 * @return array
	 */
	public function getAdditionalFields(): array
	{
		return static::$additionalFields;
	}

	/**
	 * Return additional YetiForce fields list.
	 *
	 * @return array
	 */
	public function getAdditionalFieldsCrm(): array
	{
		return static::$additionalFieldsCrm;
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
	 * Set data YetiForce.
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
			$data = \array_merge_recursive($data, $this->getFieldValueCrm($fieldCrm, true));
		}
		foreach ($this->getAdditionalFields() as $name => $value) {
			$data = \array_merge_recursive($data, $this->getFieldStructure($name, !empty($value) ? $value : $this->getFieldValueCrm($name, false)));
		}
		return ['product' => $data];
	}

	/**
	 * Return parsed data in YetiForce format.
	 *
	 * @param bool $onEdit
	 *
	 * @return array
	 */
	public function getDataCrm(bool $onEdit = false): array
	{
		$data = [];
		foreach ($this->getFields($onEdit) as $fieldCrm => $field) {
			$data[$fieldCrm] = $this->getFieldValue($field) ?? null;
		}
		if (!$onEdit) {
			foreach ($this->getAdditionalFieldsCrm() as $name => $value) {
				$data[$name] = !empty($value) ? $value : $this->getFieldValue($name);
			}
		}
		return $data;
	}

	/**
	 * Get field value from YetiForce.
	 *
	 * @param string $fieldName
	 * @param mixed  $parsedStructure
	 *
	 * @return array|mixed
	 */
	public function getFieldValueCrm(string $fieldName, $parsedStructure = false)
	{
		$methodName = 'get' . \ucfirst($this->getFieldName($fieldName));
		if (!\method_exists($this, $methodName)) {
			$fieldParsed = $this->dataCrm;
			foreach (explode('|', $fieldName) as $fieldLevel) {
				$fieldParsed = $fieldParsed[$fieldLevel] ?? null;
			}
			if (isset(static::$fieldsType[$fieldName]) && 'map' === static::$fieldsType[$fieldName]) {
				$fieldParsed = array_flip(static::${$fieldName})[$fieldParsed] ?? null;
			}
		} else {
			$fieldParsed = $this->{$methodName}();
		}
		if ($parsedStructure) {
			$fieldParsed = $this->getFieldStructure($fieldName, $fieldParsed);
		}
		return $fieldParsed;
	}

	/**
	 * Get field value from Magento.
	 *
	 * @param string $fieldName
	 *
	 * @return array|mixed
	 */
	public function getFieldValue(string $fieldName)
	{
		$parsedFieldName = $this->getFieldNameCrm($fieldName);
		$methodName = 'getCrm' . \ucfirst($parsedFieldName);
		$fieldLevels = explode('|', $fieldName);
		if (!\method_exists($this, $methodName)) {
			$fieldParsed = $this->data;
			if (\count($fieldLevels) > 1) {
				if ('custom_attributes' === $fieldLevels[0]) {
					$fieldParsed = $this->getCustomAttributeValue(end($fieldLevels));
				} else {
					foreach ($fieldLevels as $fieldLevel) {
						if (\array_key_exists($fieldLevel, $fieldParsed)) {
							$fieldParsed = $fieldParsed[$fieldLevel];
						} else {
							break;
						}
					}
				}
			} else {
				$fieldParsed = $fieldParsed[$fieldName] ?? '';
			}
			if (null !== $fieldParsed && isset(static::$fieldsType[$parsedFieldName])) {
				switch (static::$fieldsType[$parsedFieldName]) {
					case 'map':
						$fieldParsed = static::${$parsedFieldName}[$fieldParsed] ?? null;
						break;
					case 'implode':
						$fieldParsed = implode(', ', $fieldParsed);
						break;
					case 'country':
						$fieldParsed = \App\Fields\Country::getCountryName($fieldParsed);
						break;
					case 'date':
						$fieldParsed = \App\Fields\Date::formatToDb($fieldParsed, true);
						break;
				}
			} else {
				$fieldParsed = !\is_array($fieldParsed) ? $fieldParsed : null;
			}
		} else {
			$fieldParsed = $this->{$methodName}();
		}
		return $fieldParsed;
	}

	/**
	 * Get custom attribute value.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getCustomAttributeValue(string $name)
	{
		$value = '';
		$customAttributes = $this->data['custom_attributes'];
		if (!empty($customAttributes)) {
			foreach ($customAttributes as $customAttribute) {
				if ($name === $customAttribute['attribute_code']) {
					$value = $customAttribute['value'];
				}
			}
		}
		return $value;
	}

	/**
	 * Get field value in structure.
	 *
	 * @param string $name
	 * @param $value
	 *
	 * @return mixed|array
	 */
	public function getFieldStructure(string $name, $value)
	{
		if (empty($value)) {
			$value = static::$fieldsDefaultValue[$name] ?? 0;
		}
		$fieldStructure = [];
		$fieldMap = static::$mappedFields[$name] ?? $name;
		if (!empty($fieldMap)) {
			$fieldLevels = array_reverse(explode('|', $fieldMap));
			if (\in_array('custom_attributes', $fieldLevels)) {
				$fieldStructure[end($fieldLevels)][] = ['attribute_code' => array_shift($fieldLevels), 'value' => $value];
			} else {
				$fieldStructure[array_shift($fieldLevels)] = $value;
				foreach ($fieldLevels as $level) {
					$fieldStructure[$level] = $fieldStructure;
					unset($fieldStructure[key($fieldStructure)]);
				}
			}
		}
		return $fieldStructure;
	}
}
