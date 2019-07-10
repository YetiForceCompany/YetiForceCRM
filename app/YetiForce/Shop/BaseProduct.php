<?php
/**
 * YetiForce shop BaseProduct file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop;

/**
 * YetiForce shop BaseProduct class.
 */
abstract class BaseProduct
{
	/**
	 * Product name.
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Price table depending on the size of the company.
	 *
	 * @var int[]
	 */
	public $prices = [];
	/**
	 * Price type (table,manual).
	 *
	 * @var string
	 */
	public $pricesType = 'table';

	/**
	 * Construct.
	 *
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 * Get product price.
	 *
	 * @return int|bool
	 */
	public function getPrice()
	{
		if ('manual' === $this->pricesType) {
			return false;
		}
		return $this->prices[\App\Company::getSize()];
	}

	/**
	 * Get product label.
	 *
	 * @return string
	 */
	public function getLabel(): string
	{
		return \App\Language::translate('LBL_SHOP_' . \strtoupper($this->name), 'Settings:YetiForce');
	}

	/**
	 * Get product description.
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return \App\Language::translate('LBL_SHOP_' . \strtoupper($this->name) . '_DESC', 'Settings:YetiForce');
	}

	/**
	 * Get product image.
	 *
	 * @return string
	 */
	public function getImage(): string
	{
		return $this->name;
	}
}
