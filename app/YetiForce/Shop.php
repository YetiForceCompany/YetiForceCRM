<?php

/**
 * YetiForce shop file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce shop class.
 */
class Shop
{
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
		$config = self::getConfig();
		$products = [];
		$path = \ROOT_DIRECTORY . '/app/YetiForce/Shop/Product/' . $department;
		foreach ((new \DirectoryIterator($path)) as $item) {
			if (!$item->isDir()) {
				$fileName = $item->getBasename('.php');
				$instance = static::getProduct($fileName, $department, $config);
				if ('featured' === $state && !$instance->featured) {
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
	 * @param string $state
	 * @param string $department
	 *
	 * @return \App\YetiForce\Shop\AbstractBaseProduct[]
	 */
	public static function getProduct(string $name, string $department, array $config): object
	{
		if ($department) {
			$className = "\\App\\YetiForce\\Shop\\Product\\$department\\$name";
		} else {
			$className = "\\App\\YetiForce\\Shop\\Product\\$name";
		}
		$instance = new $className($name);
		if (isset($config[$name]) && $config[$name]['product'] === $name) {
			$instance->loadConfig($config[$name]);
		}
		return $instance;
	}

	/**
	 * Get variable payments.
	 *
	 * @return array
	 */
	public static function getVariablePayments(): array
	{
		return [
			'business' => 'paypal-facilitator@yetiforce.com',
			'rm' => 2,
			'return' => \Config\Main::$site_URL . 'index.php?module=YetiForce&parent=Settings&view=Shop&status=success',
			'cancel_return' => \Config\Main::$site_URL . 'index.php?module=YetiForce&parent=Settings&view=Shop&status=fail',
			'notify_url' => 'https://api.yetiforce.com/shop',
			'image_url' => 'https://public.yetiforce.com/shop/logo.png',
			'custom' => \App\YetiForce\Register::getInstanceKey() . '|' . \App\YetiForce\Register::getCrmKey(),
		];
	}

	/**
	 * Get additional configuration.
	 *
	 * @return array
	 */
	public static function getConfig(): array
	{
		$rows = [];
		if (\is_dir(ROOT_DIRECTORY . '/app_data/shop/')) {
			foreach ((new \DirectoryIterator(ROOT_DIRECTORY . '/app_data/shop/')) as $item) {
				if (!$item->isDir() && 'php' === $item->getExtension()) {
					$rows[$item->getBasename('.php')] = require ROOT_DIRECTORY . '/app_data/shop/' . $item->getBasename();
				}
			}
		}
		foreach (\App\YetiForce\Register::getProducts() as  $row) {
			$rows[$row['product']] = $row;
		}
		return $rows;
	}

	/**
	 * Verification of product activity.
	 *
	 * @param string $productName
	 *
	 * @return bool
	 */
	public static function check(string $productName): bool
	{
		$productDetails = false;
		if (($products = \App\YetiForce\Register::getProducts()) && isset($products[$productName])) {
			$productDetails = $products[$productName];
		} elseif (file_exists(ROOT_DIRECTORY . "/app_data/shop/$productName.php")) {
			$productDetails = require ROOT_DIRECTORY . "/app_data/shop/$productName.php";
		}
		$status = false;
		if ($productDetails) {
			$status = self::verifyProductKey($productDetails['key']);
			if ($status) {
				$status = strtotime('now') < strtotime($productDetails['date']);
			}
			if ($status) {
				$status = \App\Company::getSize() === $productDetails['package'];
			}
		}
		return $status;
	}

	/**
	 * Get paypal URL.
	 *
	 * @return string
	 */
	public static function getPaypalUrl(): string
	{
		return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
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
		$l1 = substr($key, 0, 5);
		$r1 = substr($key, -2);
		$m = rtrim(ltrim($key, $l1), $r1);
		$p = substr($m, -1);
		$m = rtrim($m, $p);
		$d = substr($m, -10);
		$m = rtrim($m, $d);
		$s = substr($m, -5);
		$m = rtrim($m, $s);
		return substr(crc32($m), 2, 5) === $l1
			&& substr(sha1($d . $p), 5, 5) === $s
			&& $r1 === substr(sha1(substr(crc32($m), 2, 5) . $m . substr(sha1($d . $p), 5, 5) . $d . $p), 1, 2);
	}

	/**
	 * Verify or show a message about invalid products.
	 *
	 * @return bool
	 */
	public static function verify(): bool
	{
		foreach (self::getProducts() as $row) {
			if (!$row->verify()) {
				return false;
			}
		}
		return true;
	}
}
