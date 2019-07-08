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
		'retail_price' => 'price',
		'qtyinstock' => 'extension_attributes|stock_item|qty',
		'description' => 'custom_attributes|description',
		'usageunit' => 'custom_attributes|rozmiar',
		'flag' => 'custom_attributes|flag',
		'weight' => 'weight',
		'collection' => 'custom_attributes|kolekcja',
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
		'usageunit' => 'map',
		'flag' => 'map',
		'collection' => 'map'
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
		'2' => '0',
	];
	/**
	 * Collection map.
	 *
	 * @var array
	 */
	public static $collection = [
		'13' => 'Black and White',
		'14' => 'Allure',
		'15' => 'Special Day',
		'16' => 'Hottie',
		'17' => 'Ocean Dream',
		'18' => 'Tropical Drinks',
		'19' => 'Sweets and Love',
		'43' => 'Semi Hardi',
		'44' => 'Unique',
		'123' => 'My Story',
		'175' => 'DanceFlow',
		'187' => 'Flavours',
		'202' => 'SemiBeats by Margaret',
		'205' => 'PasTells',
		'207' => 'Cat Eye',
		'208' => 'Sharm',
		'234' => 'Térmicos',
		'213' => 'Nailstagram',
		'214' => 'Purple Mania',
		'215' => 'Nails on Fleek',
		'216' => 'Business Line',
		'217' => 'Platinum',
		'239' => 'Celebrate',
		'235' => 'Legendary Six',
		'236' => 'All In My Hands',
		'240' => 'America GO!',
		'251' => 'Sweater Weather',
		'255' => 'City Break',
		'256' => 'Manos',
		'257' => 'Ojos',
		'258' => 'Labiales Mate',
		'260' => 'Brochas de Maquillaje',
		'259' => 'Labiales Clásicos',
		'280' => 'Base de Maquillaje',
		'282' => 'Polvos Compactos',
		'283' => 'AcrylGel',
		'284' => 'Colorete',
		'286' => 'Correctores Antiojeras',
		'290' => 'Base de maquillaje para ojos',
		'287' => 'Sombras de Ojos',
		'289' => 'Sombra de Ojos en Crema',
		'288' => 'Bronceador e Iluminador',
		'291' => 'Máscaras para Cejas',
	];
	/**
	 * Category model.
	 *
	 * @var object
	 */
	public $category = false;

	/**
	 * Method to get flag.
	 *
	 * @return array
	 */
	public function getFlag(): array
	{
		return [array_flip(self::$flag)[$this->dataCrm['flag']] ?? 186];
	}

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
		return $parsedCategories;
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
