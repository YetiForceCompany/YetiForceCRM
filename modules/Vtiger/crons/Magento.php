<?php
/**
 * Magento cron.
 *
 * @package   Cron
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_Magento_Cron class.
 */
class Vtiger_Magento_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$connector = (new App\Integrations\Magento\Controller());
		if (\App\Config::component('Magento', 'synchronizeCategories')) {
			$connector->synchronizeCategories();
		}
		if (\App\Config::component('Magento', 'synchronizeProducts')) {
			$connector->synchronizeProducts();
		}
		if (\App\Config::component('Magento', 'synchronizeCustomers')) {
			$connector->synchronizeCustomers();
		}
		if (\App\Config::component('Magento', 'synchronizeOrders')) {
			$connector->synchronizeOrders();
		}
		if (\App\Config::component('Magento', 'synchronizeInvoices')) {
			$connector->synchronizeInvoices();
		}
	}
}
