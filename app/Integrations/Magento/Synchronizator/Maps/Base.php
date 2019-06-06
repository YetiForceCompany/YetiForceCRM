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
		return static::$mappedFields[$name] ?? '';
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
			$methodName = 'get' . \ucfirst($field);
			if (!\method_exists($this, $methodName)) {
				$data[$field] = $this->dataCrm[$fieldCrm] ?? false;
			} else {
				$data[$field] = $this->{$methodName}();
			}
		}
		return $data;
	}

	/**
	 * Return parsed data in Yetiforce format.
	 *
	 * @return array
	 */
	public function getDataCrm(): array
	{
		$data = [];
		foreach ($this->getFields() as $fieldCrm => $field) {
			$data[$fieldCrm] = $this->data[$field] ?? false;
		}
		return $data;
	}
}
