<?php
/**
 * Magento cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Magento cron class.
 */
class Vtiger_Magento_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		foreach (App\Integrations\Magento\Config::getAllServers() as $serverId => $config) {
			if (0 === (int) $config['status']) {
				continue;
			}
			$connector = (new App\Integrations\Magento\Controller($serverId));
			if ($connector->config->get('sync_currency')) {
				$connector->synchronizeCurrencies();
			}
			if ($this->checkTimeout()) {
				return;
			}
			if ($connector->config->get('sync_categories')) {
				$connector->synchronizeCategories();
			}
			if ($this->checkTimeout()) {
				return;
			}
			if ($connector->config->get('sync_customers')) {
				$connector->synchronizeCustomers();
			}
			if ($this->checkTimeout()) {
				return;
			}
			if ($connector->config->get('sync_products')) {
				$connector->synchronizeProducts();
			}
			if ($this->checkTimeout()) {
				return;
			}
			if ($connector->config->get('sync_orders')) {
				$connector->synchronizeOrders();
			}
			if ($this->checkTimeout()) {
				return;
			}
			if ($connector->config->get('sync_invoices')) {
				$connector->synchronizeInvoices();
			}
		}
	}
}
