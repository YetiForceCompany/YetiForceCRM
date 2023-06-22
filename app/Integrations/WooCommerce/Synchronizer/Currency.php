<?php

/**
 * WooCommerce currencies synchronization file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\WooCommerce\Synchronizer;

/**
 * WooCommerce currencies synchronization class.
 */
class Currency extends Base
{
	/** {@inheritdoc} */
	public function process(): void
	{
		if ($this->config->get('logAll')) {
			$this->controller->log('Start import currencies', []);
		}
		try {
			if ($currency = $this->getCurrenciesFromApi()) {
				$this->connector->config->set('currency_code', $currency['code']);
				$this->connector->config->set('currency_name', $currency['name']);
				$all = array_column(\App\Fields\Currency::getAll(), 'id', 'currency_code');
				if (empty($all[$currency['code']])) {
					$all[$currency['code']] = \App\Fields\Currency::addCurrency($currency['code']);
				}
				if (empty($all[$currency['code']])) {
					throw new \App\Exceptions\AppException("No supported currency found: {$currency['code']}, {$currency['name']}");
				}
				$this->connector->config->set('currency_id', $all[$currency['code']]);
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Import currencies', $currency ?? null, $ex);
			\App\Log::error('Error during import currencies: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('End import currencies', [
				'currency' => $currency ?? '',
			]);
		}
	}

	/**
	 * Get currencies form WooCommerce API.
	 *
	 * @return array
	 */
	public function getCurrenciesFromApi(): array
	{
		return \App\Json::decode($this->connector->request('GET', 'data/currencies/current')) ?? [];
	}
}
