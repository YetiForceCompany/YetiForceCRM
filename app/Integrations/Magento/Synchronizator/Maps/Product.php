<?php

/**
 * Product field map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

class Product extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public static $mappedFields = [
		'ean' => 'sku',
		'productname' => 'name',
		'unit_price' => 'price',
		'qtyinstock' => 'extension_attributes|stock_item|qty',
		'description' => 'custom_attributes|description',
		'usageunit' => 'custom_attributes|rozmiar',
		'weight' => 'weight',
	];
	/**
	 * {@inheritdoc}
	 */
	public static $nonEditableFields = ['ean' => 'sku'];

	/**
	 * Usageunit value map.
	 *
	 * @var array
	 */
	public static $usageunit = [
		'5' => 'pcs',
		'6' => 'pack',
		'7' => 'kg'
	];

	/**
	 * Method to get sku or name if ean does not exist.
	 *
	 * @param mixed $parsedStructure
	 *
	 * @return array
	 */
	public function getSku($parsedStructure = false): array
	{
		$fieldValue = !empty($this->dataCrm['ean']) ? $this->dataCrm['ean'] : \App\TextParser::textTruncate($this->dataCrm['productname'], 60, false);
		if ($parsedStructure) {
			$data = ['sku' => $fieldValue];
		}
		return $data ?? $fieldValue;
	}

	/**
	 * Get description parsed for CRM.
	 *
	 * @return string
	 */
	public function getCrmDescription(): string
	{
		return $this->getCustomAttributeValue('description') ?? '';
	}

	/**
	 * Get description parsed for Magento.
	 *
	 * @param mixed $parsedStructure
	 *
	 * @return array|string
	 */
	public function getDescription($parsedStructure = false)
	{
		return $parsedStructure ? $this->getFieldStructure('description', $this->dataCrm['description']) : $this->dataCrm['description'];
	}

	/**
	 * Get quantity parsed for Magento.
	 *
	 * @param mixed $parsedStructure
	 *
	 * @return array|int
	 */
	public function getQty($parsedStructure = false)
	{
		return $parsedStructure ? $this->getFieldStructure('qtyinstock', $this->dataCrm['qtyinstock']) : $this->dataCrm['qtyinstock'];
	}

	/**
	 * Get usageunit parsed for CRM.
	 *
	 * @return mixed
	 */
	public function getCrmUsageunit()
	{
		return static::$usageunit[$this->getCustomAttributeValue('rozmiar')] ?? '';
	}

	/**
	 * Get usageunit parsed for Magento.
	 *
	 * @param bool $parsedStructure
	 *
	 * @return array
	 */
	public function getRozmiar($parsedStructure = false)
	{
		$map = \array_flip(static::$usageunit);
		$value = $map[$this->dataCrm['usageunit']] ?? 0;
		return $parsedStructure ? $this->getFieldStructure('usageunit', $value) : $value;
	}
}
