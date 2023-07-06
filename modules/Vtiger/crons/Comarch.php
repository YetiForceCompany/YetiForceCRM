<?php
/**
 * Integration Comarch cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Integration Comarch cron class.
 */
class Vtiger_Comarch_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$bathCallback = fn (): bool => $this->checkTimeout() ? false : true;
		foreach (App\Integrations\Comarch\Config::getAllServers() as $serverId => $config) {
			if (0 == $config['status']) {
				continue;
			}
			$this->updateLastActionTime();
			$connector = (new App\Integrations\Comarch($serverId, $bathCallback));
			if ($message = $connector->testConnection()) {
				$this->addErrorLog($message);
				continue;
			}
			foreach ([
				'sync_accounts' => 'Accounts',
				'sync_products' => 'Products',
			] as $key => $value) {
				if ($connector->config->get($key)) {
					$connector->getSync($value)->process();
				}
				if ($this->checkTimeout()) {
					return;
				}
			}
		}
	}
}
