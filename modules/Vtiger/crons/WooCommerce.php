<?php
/**
 * Integration WooCommerce cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Integration WooCommerce cron class.
 */
class Vtiger_WooCommerce_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$bathCallback = fn (): bool => $this->checkTimeout() ? false : true;
		foreach (App\Integrations\WooCommerce\Config::getAllServers() as $serverId => $config) {
			if (0 === (int) $config['status']) {
				continue;
			}
			$this->updateLastActionTime();
			$connector = (new App\Integrations\WooCommerce($serverId, $bathCallback));
			foreach ([
				'sync_currency' => 'Currency',
				'sync_categories' => 'ProductCategory',
				'sync_tags' => 'ProductTags',
			] as $key => $value) {
				if ($connector->config->get($key)) {
					$connector->getSync($value)->process();
				}
				if ($this->checkTimeout()) {
					return;
				}
			}
			if ($connector->config->get('sync_products')) {
				$connector->getSync('ProductAttributes')->process();
				$connector->getSync('Product')->process();
			}
			if ($this->checkTimeout()) {
				return;
			}
			if ($connector->config->get('sync_orders')) {
				$connector->getSync('OrdersPayment')->process();
				$connector->getSync('Orders')->process();
			}
		}
	}
}
