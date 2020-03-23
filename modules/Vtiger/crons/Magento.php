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
		$connector->synchronizeCategories();
		$connector->synchronizeProducts();
		$connector->synchronizeCustomers();
		$connector->synchronizeOrders();
		$connector->synchronizeInvoices();
	}
}
