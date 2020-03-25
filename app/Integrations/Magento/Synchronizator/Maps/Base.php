<?php

/**
 * Field base map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

abstract class Base
{
	/**
	 * Fields which are not exist in Magento but needed in YetiForce.
	 *
	 * @var string[]
	 */
	public static $additionalFieldsCrm = [];
	/**
	 * Mapped fields.
	 *
	 * @var string[]
	 */
	public static $mappedFields = [];
	/**
	 * Mapped fields type.
	 *
	 * @var string[]
	 */
	public static $fieldsType = [];
	/**
	 * Fields default value.
	 *
	 * @var string[]
	 */
	public static $fieldsDefaultValue = [];

	/**
	 * Fields which can not be updated.
	 *
	 * @var string[]
	 */
	public static $nonEditableFields = [];
	/**
	 * Data from Magento.
	 *
	 * @var string[]
	 */
	public $data = [];
	/**
	 * Data from YetiForce.
	 *
	 * @var string[]
	 */
	public $dataCrm = [];
	/**
	 * Synchronizer.
	 *
	 * @var \App\Integrations\Magento\Synchronizator\Base
	 */
	protected $synchronizer;

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\Magento\Synchronizator\Base $synchronizer
	 */
	public function __construct(\App\Integrations\Magento\Synchronizator\Base $synchronizer)
	{
		$this->synchronizer = $synchronizer;
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
	 * Return parsed data in YetiForce format.
	 *
	 * @param bool $onEdit
	 *
	 * @return array
	 */
	public function getDataCrm(bool $onEdit = false): array
	{
		$parsedData = [];
		foreach ($this->getFields($onEdit) as $fieldCrm => $field) {
			$parsedData[$fieldCrm] = $this->getFieldValue($field) ?? null;
		}
		if (!$onEdit) {
			foreach ($this->getAdditionalFieldsCrm() as $name => $value) {
				$parsedData[$name] = !empty($value) ? $value : $this->getFieldValue($name);
			}
		}
		return $parsedData;
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
					$elements = \count($fieldLevels);
					foreach ($fieldLevels as $level => $fieldLevel) {
						if (\array_key_exists($fieldLevel, $fieldParsed)) {
							$fieldParsed = $fieldParsed[$fieldLevel];
						} else {
							if ($elements !== $level + 1) {
								$fieldParsed = '';
							}
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
}
