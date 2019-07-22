<?php
/**
 * YetiForce shop AbstractBaseProduct file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop;

/**
 * YetiForce shop AbstractBaseProduct class.
 */
abstract class AbstractBaseProduct
{
	/**
	 * Product name.
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Is the product featured.
	 *
	 * @var bool
	 */
	public $featured = false;
	/**
	 * Product category.
	 *
	 * @var string
	 */
	public $category = '';
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
	 * Currency code.
	 *
	 * @var string
	 */
	public $currencyCode = 'EUR';

	/**
	 * Expiration date.
	 *
	 * @var string|null
	 */
	public $expirationDate;

	/**
	 * Paid package.
	 *
	 * @var string|null
	 */
	public $paidPackage;

	/**
	 * Verify the product.
	 *
	 * @return bool
	 */
	abstract protected function verify(): bool;

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
	 * Get price type.
	 *
	 * @return string
	 */
	public function getPriceType(): string
	{
		return $this->pricesType;
	}

	/**
	 * Get product price.
	 *
	 * @return int
	 */
	public function getPrice(): int
	{
		return $this->prices[\strtolower(\App\Company::getSize())] ?? 0;
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
	public function getImage(): ?string
	{
		$filePath = null;
		$file = 'modules/Settings/YetiForce/' . $this->name . '.png';
		if (\file_exists(\ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'public_html' . \DIRECTORY_SEPARATOR . $file)) {
			$filePath = \App\Layout::getPublicUrl($file, true);
		}
		return $filePath;
	}

	/**
	 * The period for which the service is purchased.
	 *
	 * @return void
	 */
	public function getPeriodLabel(): string
	{
		return 'LBL_PERIOD_OF_MONTH';
	}

	/**
	 * Loading configuration.
	 *
	 * @param array $config
	 *
	 * @return void
	 */
	public function loadConfig(array $config)
	{
		if (\App\YetiForce\Shop::verifyProductKey($config['key'])) {
			$this->expirationDate = $config['date'];
			$this->paidPackage = $config['package'];
		}
	}

	/**
	 * Get variable product.
	 *
	 * @return array
	 */
	public function getVariable(): array
	{
		return [
			'cmd' => '_xclick-subscriptions',
			'no_shipping' => 1,
			'src' => 1,
			'sra' => 1,
			't3' => 'M',
			'p3' => \date('d'),
			'a3' => $this->getPrice(),
			'item_name' => $this->name,
			'currency_code' => $this->currencyCode,
			'item_number' => 'ccc',
			'on0' => 'Package',
			'os0' => \App\Company::getSize(),
		];
	}
}
