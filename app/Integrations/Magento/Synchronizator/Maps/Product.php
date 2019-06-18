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
		'multicategory' => 'custom_attributes|category_ids',
		'unit_price' => 'price',
		'qtyinstock' => 'extension_attributes|stock_item|qty',
		'description' => 'custom_attributes|description',
		'usageunit' => 'custom_attributes|rozmiar',
		'flag' => 'custom_attributes|flag',
		'weight' => 'weight',
	];
	/**
	 * {@inheritdoc}
	 */
	public static $fieldsType = [
		'discontinued' => 'map',
		'ean' => 'value',
		'productname' => 'value',
		'unit_price' => 'value',
		'qtyinstock' => 'value',
		'description' => 'value',
		'usageunit' => 'map',
		'flag' => 'map',
		'weight' => 'value',
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
	 * Flag value map.
	 *
	 * @var array
	 */
	public static $flag = [
		'135' => 'PLL_NEW',
		'136' => 'PLL_PROMOTION',
		'137' => 'PLL_BESTSELLER',
		'186' => 'PLL_NONE'
	];
	/**
	 * Discontinued value map.
	 *
	 * @var array
	 */
	public static $discontinued = [
		'1' => '1',
		'0' => '2',
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
	 * @param mixed $parsedStructure
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
		$categories = array_filter(explode(',', str_replace('T', '', $this->dataCrm['multicategory'])));
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
	public function getCrmMulticategory(): string
	{
		$parsedCategories = '';
		if (false === $this->category) {
			$this->category = new \App\Integrations\Magento\Synchronizator\Category();
			$this->category->getCategoryMapping();
		}
		$categories = $this->getCustomAttributeValue('category_ids');
		foreach ($categories as $category) {
			$parsedCategories .= ',T' . $this->category->mapCategoryYF[$category];
		}
		return $parsedCategories;
	}
}
