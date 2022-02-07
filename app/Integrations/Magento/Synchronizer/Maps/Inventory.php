<?php

/**
 * Abstract inventory map file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer\Maps;

/**
 * Abstract inventory map class.
 */
abstract class Inventory extends Base
{
	/**
	 * Inventory item data from Magento.
	 *
	 * @var array
	 */
	public $dataInv = [];
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
	 * Set inventory item data from Magento.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function setDataInv(array $data): void
	{
		$this->dataInv = $data;
	}

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
				$fieldParsed = $this->dataInv[$fieldName] ?? null;
			} else {
				$fieldParsed = $this->{$methodName}();
			}
		}
		return $fieldParsed;
	}

	/**
	 * Get currency id.
	 *
	 * @return int
	 */
	public function getCurrency()
	{
		return (int) \App\Fields\Currency::getIdByCode($this->data['order_currency_code']);
	}

	/** {@inheritdoc} */
	public function getDataCrm(bool $onEdit = false): array
	{
		parent::getDataCrm($onEdit);
		$this->dataCrm['currency_id'] = $this->getCurrency();
		return $this->dataCrm;
	}

	/**
	 * Create product.
	 *
	 * @param array $record
	 *
	 * @return int
	 */
	public function createProduct(array $record): int
	{
		$productSynchronizer = new \App\Integrations\Magento\Synchronizer\Product($this->synchronizer->controller);
		return $productSynchronizer->importByEan($record['sku']);
	}
}
