<?php

/**
 * YetiForce shop file.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce;

use App\Cache;

/**
 * YetiForce shop class.
 */
class Shop
{
	/** @var string[] Premium icons. */
	const PREMIUM_ICONS = [
		1 => 'yfi-premium color-red-600',
		2 => 'yfi-enterprise color-yellow-600',
		3 => 'yfi-partners color-grey-600',
	];

	/** @var array Product categories. */
	const PRODUCT_CATEGORIES = [
		'All' => ['label' => 'LBL_CAT_ALL', 'icon' => 'yfi-all-shop'],
		'CloudHosting' => ['label' => 'LBL_CAT_CLOUD_HOSTING', 'icon' => 'yfi-hosting-cloud-shop'],
		'Support' => ['label' => 'LBL_CAT_SUPPORT', 'icon' => 'yfi-support-shop'],
		'Addons' => ['label' => 'LBL_CAT_ADDONS', 'icon' => 'yfi-adds-on-shop'],
		'Integrations' => ['label' => 'LBL_CAT_INTEGRATIONS', 'icon' => 'yfi-integration-shop'],
		'RecordCollectors' => ['label' => 'LBL_CAT_RECORD_COLLECTORS', 'icon' => 'yfi-record-collectors'],
		'PartnerSolutions' => ['label' => 'LBL_CAT_PARTNER_SOLUTIONS', 'icon' => 'yfi-partner-solution-shop'],
	];

	/**
	 * Product instance cache.
	 *
	 * @var \App\YetiForce\Shop\AbstractBaseProduct[]
	 */
	public static $productCache = [];

	/**
	 * Get products.
	 *
	 * @param string $state
	 * @param string $department
	 *
	 * @return \App\YetiForce\Shop\AbstractBaseProduct[]
	 */
	public static function getProducts(string $state = '', string $department = ''): array
	{
		$products = [];
		$path = ROOT_DIRECTORY . '/app/YetiForce/Shop/Product/' . $department;
		foreach ((new \DirectoryIterator($path)) as $item) {
			if (!$item->isDir()) {
				$fileName = $item->getBasename('.php');
				$instance = static::getProduct($fileName, $department);
				if (!$instance->active || ('featured' === $state && !$instance->featured)) {
					continue;
				}
				$products[$fileName] = $instance;
			}
		}
		return $products;
	}

	/**
	 * Get products.
	 *
	 * @param string $name
	 * @param string $department
	 *
	 * @return Shop\AbstractBaseProduct
	 */
	public static function getProduct(string $name, string $department = ''): Shop\AbstractBaseProduct
	{
		if ($department) {
			$className = "\\App\\YetiForce\\Shop\\Product\\$department\\$name";
		} else {
			$className = "\\App\\YetiForce\\Shop\\Product\\$name";
		}
		if (isset(self::$productCache[$className])) {
			return self::$productCache[$className];
		}
		$instance = new $className($name);
		if ($config = self::getConfig($name)) {
			$instance->loadConfig($config);
		}
		return self::$productCache[$className] = $instance;
	}

	/**
	 * Get variable payments.
	 *
	 * @param bool $isCustom
	 *
	 * @return array
	 */
	public static function getVariablePayments($isCustom = false): array
	{
		$crmData = [];
		if (!$isCustom) {
			$crmData = [
				'return' => \Config\Main::$site_URL . 'index.php?module=YetiForce&parent=Settings&view=Shop&status=success',
				'cancel_return' => \Config\Main::$site_URL . 'index.php?module=YetiForce&parent=Settings&view=Shop&status=fail',
				'custom' => \App\YetiForce\Register::getInstanceKey() . '|' . \App\YetiForce\Register::getCrmKey(),
			];
		}
		return array_merge([
			'business' => 'paypal@yetiforce.com',
			'rm' => 2,
			'image_url' => 'https://public.yetiforce.com/shop/logo.png',
		], $crmData);
	}

	/**
	 * Get additional configuration.
	 *
	 * @param string $name
	 *
	 * @return array
	 */
	public static function getConfig(string $name): array
	{
		$config = \App\YetiForce\Register::getProducts($name);
		if (!$config && \is_dir(ROOT_DIRECTORY . '/app_data/shop/') && \file_exists(ROOT_DIRECTORY . "/app_data/shop/{$name}.php")) {
			$config = require ROOT_DIRECTORY . "/app_data/shop/{$name}.php";
		}
		return $config;
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
		return self::checkWithMessage($productName)[0] ?? false;
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
		if ($productDetails = self::getConfig($productName)) {
			$status = self::verifyProductKey($productDetails['key']);
			if ($status) {
				$status = strtotime(date('Y-m-d') . ' -1 day') <= strtotime($productDetails['date']);
				if (!$status) {
					$message = 'LBL_SUBSCRIPTION_HAS_EXPIRED';
				}
			}
			if ($status) {
				$status = \App\Company::compareSize($productDetails['package']);
				if (!$status) {
					$message = 'LBL_SUBSCRIPTION_COMPANY_SIZE_HAS_CHANGED';
				}
			}
		}
		Cache::staticSave('Shop|checkWithMessage', $productName, [$status, $message]);
		return [$status, $message];
	}

	/**
	 * Check alert to show for product.
	 *
	 * @param string $productName
	 *
	 * @return string
	 */
	public static function checkAlert(string $productName): string
	{
		$message = '';
		$check = self::getProduct($productName)->verify();
		if (false === $check['status']) {
			$message = $check['message'];
		}
		return $message;
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
	 * Verification of the product key.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function verifyProductKey(string $key): bool
	{
		$key = base64_decode($key);
		$m = substr(substr($key, 5), 0, -2);
		$p = substr($m, -5);
		$m = substr($m, 0, -5);
		$d = substr($m, -10);
		$m = substr($m, 0, -10);
		$s = substr($m, -5);
		$m = substr($m, 0, -5);
		return substr(crc32($m), 2, 5) === substr($key, 0, 5)
			&& substr(sha1($d . $p), 5, 5) === $s
			&& substr($key, -2) === substr(sha1(substr(crc32($m), 2, 5) . $m . substr(sha1($d . $p), 5, 5) . $d . $p), 1, 2);
	}

	/**
	 * Verify or show a message about invalid products.
	 *
	 * @param bool $cache
	 * @param bool $onlyNames
	 *
	 * @return string
	 */
	public static function verify(bool $cache = true, bool $onlyNames = false): string
	{
		$products = '';
		if ($cache && ($cacheData = self::getFromCache())) {
			foreach ($cacheData as $product => $verify) {
				if (!$verify['status']) {
					$products .= \App\Language::translate($product, 'Settings::YetiForce');
					if ($onlyNames) {
						$products .= ',';
					} else {
						$products .= '<br>' . \App\Language::translate(($verify['message'] ?? 'LBL_YETIFORCE_SHOP_PRODUCT_CANCELED'), 'Settings::YetiForce') . '<hr>';
					}
				}
			}
		} else {
			foreach (self::getProducts() as $product) {
				$verify = $product->verify();
				if (!$verify['status']) {
					$products .= \App\Language::translate($product->getLabel(), 'Settings::YetiForce');
					if ($onlyNames) {
						$products .= ',';
					} else {
						$products .= '<br>' . \App\Language::translate(($verify['message'] ?? 'LBL_YETIFORCE_SHOP_PRODUCT_CANCELED'), 'Settings::YetiForce') . '<hr>';
					}
				}
			}
		}
		return rtrim($products, '<hr>,');
	}

	/**
	 * Generate cache.
	 */
	public static function generateCache(): void
	{
		$content = [];
		foreach (self::getProducts() as $product) {
			$verify = $product->verify();
			if ($verify['status']) {
				unset($verify['message']);
			}
			$content['products'][$product->getLabel()] = $verify;
		}
		$content['key'] = md5(json_encode($content['products']));
		\App\Utils::saveToFile(ROOT_DIRECTORY . '/app_data/shopCache.php', $content, 'Modifying this file will breach the licence terms!!!', 0, true);
	}

	/**
	 * Get from cache.
	 *
	 * @return array
	 */
	public static function getFromCache(): array
	{
		$content = [];
		if (\file_exists(ROOT_DIRECTORY . '/app_data/shopCache.php')) {
			$content = include ROOT_DIRECTORY . '/app_data/shopCache.php';
		}
		if (empty($content['products']) || ($content['key'] ?? '') !== md5(json_encode($content['products']))) {
			$content['products'] = [];
		}
		return $content['products'];
	}
}
