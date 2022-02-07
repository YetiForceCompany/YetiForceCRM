<?php

/**
 * Product map file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer\Maps;

/**
 * Product map class.
 */
class Product extends Base
{
	/** {@inheritdoc} */
	public static $mappedFields = [
		'discontinued' => 'status',
		'ean' => 'sku',
		'productname' => 'name',
		'categories' => 'custom_attributes|category_ids',
		'description' => 'custom_attributes|description',
	];

	/** {@inheritdoc} */
	public static $additionalFieldsCrm = [
		'unit_price' => '0',
		'purchase' => '0',
	];

	/** {@inheritdoc} */
	public static $fieldsType = [
		'discontinued' => 'map',
	];

	/** {@inheritdoc} */
	public static $fieldsDefaultValue = [
		'description' => '',
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
	 * @var \App\Integrations\Magento\Synchronizer\Category
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
			$this->category = new \App\Integrations\Magento\Synchronizer\Category($this->synchronizer->controller);
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
