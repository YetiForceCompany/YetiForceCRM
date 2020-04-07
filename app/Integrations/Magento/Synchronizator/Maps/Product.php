<?php

/**
 * Product map file.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

/**
 * Product map class.
 */
class Product extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public static $mappedFields = [
		'discontinued' => 'status',
		'ean' => 'sku',
		'productname' => 'name',
		'categories' => 'custom_attributes|category_ids',
		'description' => 'custom_attributes|description',
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
	 * Method to get parsed categories ids.
	 *
	 * @return array
	 */
	public function getCrmCategories(): array
	{
		if (false === $this->category) {
			$this->category = new \App\Integrations\Magento\Synchronizator\Category($this->synchronizer->controller);
		}
		$categories = $this->getCustomAttributeValue('category_ids') ?: [];
		$parsedCategories = [];
		foreach ($categories as $category) {
			$crmId = $this->category->getCrmId($category);
			if (empty($crmId)) {
				$crmId = $this->category->createCategory($category);
			}
			$parsedCategories[] = $crmId;
		}
		return $parsedCategories;
	}
}
