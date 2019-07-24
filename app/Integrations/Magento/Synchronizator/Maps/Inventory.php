<?php

/**
 * Inventory map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

abstract class Inventory extends Base
{
	/**
	 * Customer model.
	 *
	 * @var object
	 */
	public $customer = false;
	/**
	 * Inventory fields.
	 *
	 * @var array
	 */
	public static $mappedFieldsInv = [];

	/**
	 * Return YetiForce inventory field name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getInvFieldNameCrm(string $name)
	{
		return array_flip(static::$mappedFieldsInv)[$name] ?? '';
	}

	/**
	 * Return Magento inventory field name.
	 *
	 * @param string $name
	 *
	 * @return mixed|string
	 */
	public function getInvFieldName(string $name)
	{
		return static::$mappedFieldsInv[$name] ?? '';
	}

	/**
	 * Get inventory field value.
	 *
	 * @param string $fieldName
	 *
	 * @return array|mixed
	 */
	public function getInvFieldValue(string $fieldName)
	{
		$fieldName = $this->getInvFieldName($fieldName);
		$fieldParsed = null;
		if (!empty($fieldName)) {
			$methodName = 'getCrmInv' . \ucfirst($fieldName);
			if (!\method_exists($this, $methodName)) {
				$fieldParsed = $this->data[$fieldName] ?? null;
			} else {
				$fieldParsed = $this->{$methodName}();
			}
		}
		return $fieldParsed;
	}

	/**
	 * Method to get parsed customer id.
	 *
	 * @return string
	 */
	public function getCrmContactid()
	{
		if (false === $this->customer) {
			$this->customer = new \App\Integrations\Magento\Synchronizator\Customer();
			$this->customer->getMapping('customer');
		}
		$value = '';
		if (!empty($this->data['customer_id']) && isset($this->customer->mapCrm['customer'][$this->data['customer_id']])) {
			$value = $this->customer->mapCrm['customer'][$this->data['customer_id']];
		}
		return $value;
	}
}
