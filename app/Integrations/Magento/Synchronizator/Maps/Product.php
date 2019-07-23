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
		'discontinued' => 'status',
		'ean' => 'sku',
		'productname' => 'name',
		'category_multipicklist' => 'custom_attributes|category_ids',
		'qtyinstock' => 'extension_attributes|stock_item|qty',
	];
	/**
	 * {@inheritdoc}
	 */
	public static $additionalFields = [
		'type_id' => 'simple',
		'attribute_set_id' => '4',
		'extension_attributes|stock_item|is_in_stock' => '',
	];
	/**
	 * {@inheritdoc}
	 */
	public static $additionalFieldsCrm = [
		'unit_price' => '0',
		'purchase' => '0',
	];

	/**
	 * {@inheritdoc}
	 */
	public static $fieldsType = [
		'discontinued' => 'map',
	];
	/**
	 * {@inheritdoc}
	 */
	public static $fieldsDefaultValue = [
		'description' => ''
	];
	/**
	 * {@inheritdoc}
	 */
	public static $nonEditableFields = ['ean' => 'sku'];

	/**
	 * Discontinued value map.
	 *
	 * @var array
	 */
	public static $discontinued = [
		'1' => '1',
		'2' => '0',
	];

	/**
	 * Category model.
	 *
	 * @var object
	 */
	public $category = false;

	/**
	 * Method to get sku or name if ean does not exist.
	 *
	 * @return string
	 */
	public function getSku(): string
	{
		return !empty($this->dataCrm['ean']) ? $this->dataCrm['ean'] : \App\TextParser::textTruncate($this->dataCrm['productname'], 60, false);
	}

	/**
	 * Method to get parsed categories ids.
	 *
	 * @return array
	 */
	public function getCategory_ids(): array
	{
		$parsedCategories = [];
		if (false === $this->category) {
			$this->category = new \App\Integrations\Magento\Synchronizator\Category();
			$this->category->getCategoryMapping();
		}
		$categories = array_filter(explode(',', str_replace('T', '', $this->dataCrm['category_multipicklist'])));
		foreach ($categories as $category) {
			if (isset($this->category->mapCategoryMagento[$category])) {
				$parsedCategories[] = $this->category->mapCategoryMagento[$category];
			}
		}
		return $parsedCategories;
	}

	/**
	 * Method to get parsed categories ids.
	 *
	 * @return string
	 */
	public function getCrmCategory_multipicklist(): string
	{
		$parsedCategories = '';
		if (false === $this->category) {
			$this->category = new \App\Integrations\Magento\Synchronizator\Category();
			$this->category->getCategoryMapping();
		}
		$categories = $this->getCustomAttributeValue('category_ids');
		foreach ($categories as $category) {
			if (!empty($this->category->mapCategoryYF[$category])) {
				$parsedCategories .= ',T' . $this->category->mapCategoryYF[$category];
			}
		}
		return !empty($parsedCategories) ? $parsedCategories . ',' : '';
	}

	/**
	 * Method to get sku or name if ean does not exist.
	 *
	 * @throws \Exception
	 *
	 * @return string
	 */
	public function getCrmEan(): string
	{
		$record = \Vtiger_Record_Model::getCleanInstance('Products');
		$length = $record->getField('ean')->get('maximumlength');
		return !empty($this->data['sku']) ? \App\TextParser::textTruncate($this->data['sku'], $length, false) : '';
	}

	/**
	 * Is product in stock.
	 *
	 * @return bool
	 */
	public function getIs_in_stock()
	{
		return $this->dataCrm['qtyinstock'] > 0;
	}
}
