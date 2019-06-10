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
	];
	/**
	 * {@inheritdoc}
	 */
	public static $nonEditableFields = ['ean' => 'sku'];

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
		$customAttributes = $this->data['custom_attributes'];
		if (!empty($customAttributes)) {
			foreach ($customAttributes as $customAttribute) {
				if ('description' === $customAttribute['attribute_code']) {
					return $customAttribute['value'];
				}
			}
		}
		return '';
	}

	/**
	 * Get description parsed for Magento.
	 *
	 * @param mixed $parsedStructure
	 *
	 * @return array
	 */
	public function getDescription($parsedStructure = false): array
	{
		if ($parsedStructure) {
			$data = ['custom_attributes' => ['description' => $this->dataCrm['description']]];
		}
		return $data ?? $this->dataCrm['description'];
	}

	/**
	 * Get quantity parsed for Magento.
	 *
	 * @param mixed $parsedStructure
	 *
	 * @return array
	 */
	public function getQty($parsedStructure = false): array
	{
		if ($parsedStructure) {
			$data = ['extension_attributes' => ['stock_item' => ['qty' => $this->dataCrm['qtyinstock']]]];
		}
		return $data ?? $this->dataCrm['qtyinstock'];
	}
}
