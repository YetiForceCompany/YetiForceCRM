<?php

/**
 * YetiForce shop file.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 */

namespace App\YetiForce;

use App\Cache;

/**
 * YetiForce shop class.
 */
final class Shop extends AbstractBase
{
	/** @var string URL */
	public const URL = 'https://api.yetiforce.eu/store';

	/** @var string[] Premium icons. */
	public const PREMIUM_ICONS = [
		1 => 'yfi-premium color-red-600',
		2 => 'yfi-enterprise color-yellow-600',
		3 => 'yfi-partners color-grey-600',
	];

	/** @var array Product categories. */
	public const PRODUCT_CATEGORIES = [
		'All' => ['label' => 'LBL_CAT_ALL', 'icon' => 'yfi-all-shop'],
		'Extensions' => ['label' => 'LBL_CAT_ADDONS', 'icon' => 'yfi-adds-on-shop'],
		'Integrations' => ['label' => 'LBL_CAT_INTEGRATIONS', 'icon' => 'yfi-integration-shop'],
	];

	/**
	 * Product instance cache.
	 *
	 * @var array
	 */
	public static array $productCache = [];

	/**
	 * Get products.
	 *
	 * @param string $state
	 * @param string $section
	 *
	 * @return Shop\AbstractBaseProduct[]
	 */
	public function getProducts(): array
	{
		if (empty(self::$productCache)) {
			$this->load();
		}

		return self::$productCache;
	}

	/**
	 * Get variable payments.
	 *
	 * @return array
	 */
	public static function getVariablePayments(): array
	{
		return [
			'business' => 'paypal@yetiforce.com',
			'rm' => 2,
			'image_url' => 'https://public.yetiforce.com/shop/logo.png',
			'return' => \Config\Main::$site_URL . 'index.php?module=YetiForce&parent=Settings&view=Shop&status=success',
			'cancel_return' => \Config\Main::$site_URL . 'index.php?module=YetiForce&parent=Settings&view=Shop&status=fail',
			'custom' => \App\YetiForce\Register::getInstanceKey() . '|' . \App\YetiForce\Register::getCrmKey(),
		];
	}

	/**
	 * Verification of product.
	 *
	 * @param string $productName
	 *
	 * @return bool
	 */
	public static function check(string $productName): bool
	{
		$className = self::getProductClass($productName);
		$product = new $className($productName);
		return $product->getStatus();
	}

	/**
	 * Verification of product with a message.
	 *
	 * @param string $productName
	 *
	 * @return array
	 */
	public static function checkWithMessage(string $productName): array
	{
		if (Cache::staticHas('Shop|checkWithMessage', $productName)) {
			return Cache::staticGet('Shop|checkWithMessage', $productName);
		}
		$status = $message = false;
		$productDetails = \App\YetiForce\Register::getProduct($productName);
		if ($productDetails) {
			$interval = (new \DateTime('now', new \DateTimeZone('GMT')))->diff(new \DateTime($productDetails['expires_at'], new \DateTimeZone('GMT')));
			$status = $interval->invert && $interval->days > 0;
			if (!$status) {
				$message = 'LBL_SUBSCRIPTION_HAS_EXPIRED';
			}
		}

		Cache::staticSave('Shop|checkWithMessage', $productName, [$status, $message]);
		return [$status, $message];
	}

	/**
	 * @todo remove or replace
	 * Check alert to show for product.
	 *
	 * @param string $productName
	 *
	 * @return string
	 */
	public static function checkAlert(string $productName): string
	{
		$className = self::getProductClass($productName);
		$product = new $className($productName);
		return $product->getAlertMessage();
	}

	/**
	 * Get paypal URL.
	 * https://www.paypal.com/cgi-bin/webscr
	 * https://www.sandbox.paypal.com/cgi-bin/webscr.
	 *
	 * @return string
	 */
	public static function getPaypalUrl(): string
	{
		return 'https://www.paypal.com/cgi-bin/webscr';
	}

	/**
	 * Verify or show a message about invalid products.
	 *
	 * @param bool $onlyNames
	 * @param bool $getNames
	 *
	 * @return string
	 */
	public static function verify(bool $getNames = false): string
	{
		$names = [];
		$products = \App\YetiForce\Register::getProducts();
		foreach ($products ?? [] as $row) {
			$productName = $row['product'];
			$className = self::getProductClass($productName);
			$product = new $className($productName);
			if ($product->isExpired()) {
				$names[$productName] = $productName;
				if (!$getNames) {
					$names[$productName] = \App\Language::translate($productName, 'Settings::YetiForce');
				}
			}
		}

		return implode(', ', $names);
	}

	/**
	 * Get all available products.
	 *
	 * @return void
	 */
	public function load()
	{
		self::$productCache = [];
		$this->success = false;
		try {
			$client = new ApiClient();
			$client->send(self::URL . '/' . \App\Version::get() . '/products', 'GET');
			$this->error = $client->getError();
			if (!$this->error && 200 === $client->getStatusCode() && !\App\Json::isEmpty($client->getResponseBody())) {
				$this->setProducts(\App\Json::decode($client->getResponseBody()));
				$this->success = true;
			}
		} catch (\Throwable $e) {
			$this->success = false;
			$this->error = $e->getMessage();
			\App\Log::error($e->getMessage(), __METHOD__);
		}
	}

	/**
	 * Get product by ID.
	 *
	 * @param string $productId
	 *
	 * @return void
	 */
	public function loadProduct(string $productId)
	{
		$this->success = false;
		try {
			$client = new ApiClient();
			$client->send(self::URL . '/' . \App\Version::get() . "/products/{$productId}", 'GET');
			$this->error = $client->getError();
			if (!$this->error && 200 === $client->getStatusCode() && !\App\Json::isEmpty($client->getResponseBody())) {
				$this->setProducts([\App\Json::decode($client->getResponseBody())]);
				$this->success = true;
			}
		} catch (\Throwable $e) {
			$this->success = false;
			$this->error = $e->getMessage();
			\App\Log::error($e->getMessage(), __METHOD__);
		}
	}

	/**
	 * Get products.
	 *
	 * @param string $name
	 * @param string $section
	 * @param string $productId
	 *
	 * @return Shop\AbstractBaseProduct
	 */
	public static function getProduct(string $name, string $productId = ''): ?Shop\AbstractBaseProduct
	{
		if (empty(self::$productCache[$name])) {
			if ($productId) {
				(new self())->loadProduct($productId);
			} else {
				(new self())->load();
			}
		}

		return self::$productCache[$name] ?? null;
	}

	/**
	 * Get product class.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private static function getProductClass(string $name): string
	{
		$className = '\\App\\YetiForce\\Shop\\Product\\' . $name;
		if (!class_exists($className)) {
			$className = '\\App\\YetiForce\\Shop\\Product\\YetiForceBase';
		}
		return $className;
	}

	/**
	 * Set Products to cache.
	 *
	 * @param array $products
	 *
	 * @return void
	 */
	private function setProducts(array $products)
	{
		foreach ($products as $productData) {
			$name = $productData['name'] ?? '';
			$className = self::getProductClass($name);
			if (!empty($productData['packages']) && ($product = $className::fromArray($productData)) && $product->isAvailable()) {
				self::$productCache[$product->getName()] = $product;
			}
		}
	}
}
