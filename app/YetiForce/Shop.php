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
	 *
	 * @return \App\YetiForce\Shop\AbstractBaseProduct[]
	 */
	public static function getProducts($state = 'all'): array
	{
		$products = [];
		foreach ((new \DirectoryIterator(\ROOT_DIRECTORY . '/app/YetiForce/Shop/Product')) as $item) {
			if (!$item->isDir()) {
				$fileName = $item->getBasename('.php');
				$className = "\\App\\YetiForce\\Shop\\Product\\$fileName";
				$instance = new $className($fileName);
				if (!$instance instanceof \App\YetiForce\Shop\AbstractBaseProduct) {
					throw new \App\Exception\IllegalValue('ERR_ILLEGAL_VALUE||' . $className, 406);
				}
				if ('featured' === $state && !$instance->featured) {
					continue;
				}
				$products[$fileName] = $instance;
			}
		}
		return $products;
	}

	/**
	 * Get variable payments.
	 *
	 * @return array
	 */
	public static function getVariablePayments(): array
	{
		return [
			'cmd' => '_xclick-subscriptions',
			'business' => 'paypal-facilitator@yetiforce.com',
			'currency_code' => 'USD',
			'no_shipping' => 1,
			'src' => 1,
			'sra' => 1,
			'rm' => 2,
			't3' => 'M',
			'p3' => \date('d'),
			'return' => \Config\Main::$site_URL . 'index.php?module=YetiForce&parent=Settings&view=Shop&status=success',
			'cancel_return' => \Config\Main::$site_URL . 'index.php?module=YetiForce&parent=Settings&view=Shop&status=fail',
			'notify_url' => 'https://public.yetiforce.com/shop/xxx.php',
			'image_url' => 'https://public.yetiforce.com/shop/logo.png',
			'custom' => \App\YetiForce\Register::getInstanceKey() . '|' . \App\YetiForce\Register::getCrmKey(),
		];
	}

	/**
	 * Get variable product.
	 *
	 * @param \App\YetiForce\Shop\AbstractBaseProduct $product
	 *
	 * @return array
	 */
	public static function getVariableProduct(Shop\AbstractBaseProduct $product): array
	{
		return [
			'a3' => $product->getPrice(),
			'item_name' => $product->name,
			'item_number' => 'ccc',
			'on0' => 'Package',
			'os0' => \strtoupper(\App\Company::getSize()),
		];
	}
}
