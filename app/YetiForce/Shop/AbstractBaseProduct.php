<?php

/**
 * YetiForce shop AbstractBaseProduct file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop;

/**
 * YetiForce shop AbstractBaseProduct class.
 */
abstract class AbstractBaseProduct
{
	/**
	 * Product label.
	 *
	 * @var string
	 */
	public $label;
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
	public $active = true;
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
	public $category;
	/**
	 * Product website.
	 *
	 * @var string
	 */
	public $website;
	/**
	 * Price table depending on the size of the company.
	 *
	 * @var int[]
	 */
	public $prices = [];

	/**
	 * Custom prices label.
	 *
	 * @var array
	 */
	public $customPricesLabel = [];

	/**
	 * Price type (table,manual,selection).
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
	 * Custom Fields.
	 *
	 * @var array
	 */
	public $customFields = [];

	/**
	 * Verify the product.
	 *
	 * @return array
	 */
	public function verify(): array
	{
		return ['status' => true];
	}

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
		return $this->prices[\App\Company::getSize()] ?? $this->prices[0] ?? 0;
	}

	/**
	 * Get product label.
	 *
	 * @return string
	 */
	public function getLabel(): string
	{
		if (!empty($this->label)) {
			return $this->label;
		}
		return \App\Language::translate('LBL_SHOP_' . \strtoupper($this->name), 'Settings:YetiForce');
	}

	/**
	 * Get product name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get product description.
	 *
	 * @return string
	 */
	public function getIntroduction(): string
	{
		return \App\Language::translate('LBL_SHOP_' . \strtoupper($this->name) . '_INTRO', 'Settings:YetiForce');
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
		if (\file_exists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'public_html' . \DIRECTORY_SEPARATOR . $file)) {
			$filePath = \App\Layout::getPublicUrl($file);
		}
		return $filePath;
	}

	/**
	 * Get price label.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getPriceLabel($key): string
	{
		return \App\Language::translate('LBL_SHOP_COMPANY_SIZE_' . \strtoupper($key), 'Settings::YetiForce');
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
		$productSelection = 'selection' === $this->pricesType;
		$data = [
			'cmd' => '_xclick-subscriptions',
			'no_shipping' => 1,
			'no_note' => 1,
			'src' => 1,
			'sra' => 1,
			't3' => 'M',
			'p3' => 1,
			'item_name' => $this->name,
			'currency_code' => $this->currencyCode,
			'on0' => 'Package',
		];
		if (!$productSelection) {
			$data['os0'] = \App\Company::getSize();
		}
		if ('manual' !== $this->pricesType && !$productSelection) {
			$data['a3'] = $this->getPrice();
		}
		return array_merge($data, \App\YetiForce\Shop::getVariablePayments($this->isCustom()));
	}

	/**
	 * Get product custom fields.
	 *
	 * @return array
	 */
	public function getCustomFields(): array
	{
		return $this->customFields;
	}

	/**
	 * Is custom fields.
	 *
	 * @return bool
	 */
	public function isCustom(): bool
	{
		return !empty($this->customFields);
	}

	/**
	 * Show alert.
	 *
	 * @return array
	 */
	public function showAlert(): array
	{
		$return = ['status' => false];
		if (isset($this->paidPackage, $this->expirationDate)) {
			if (strtotime('now') > strtotime($this->expirationDate)) {
				$return = ['status' => true, 'type' => 'LBL_SHOP_RENEW', 'message' => 'LBL_SUBSCRIPTION_HAS_EXPIRED'];
			} elseif (!\App\Company::compareSize($this->paidPackage)) {
				$return = ['status' => true, 'type' => 'LBL_SHOP_RENEW', 'message' => 'LBL_SIZE_OF_YOUR_COMPANY_HAS_CHANGED'];
			} elseif ($analyze = $this->analyzeConfiguration()) {
				$return = array_merge(['status' => true], $analyze);
			}
		} else {
			$check = $this->verify();
			if (!$check['status']) {
				$return = ['status' => true, 'type' => 'LBL_SHOP_RENEW', 'message' => $check['message']];
			}
		}
		return $return;
	}

	/**
	 * Analyze the configuration.
	 *
	 * @return array
	 */
	public function analyzeConfiguration(): array
	{
		return [];
	}

	/**
	 * Product modal additional buttons.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getAdditionalButtons(): array
	{
		return [];
	}

	/**
	 * Switch button to activate/deactivate service.
	 *
	 * @return \Vtiger_Link_Model|null
	 */
	public function getSwitchButton(): ?\Vtiger_Link_Model
	{
		return null;
	}

	/**
	 * Check if the service is active.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return false;
	}
}
