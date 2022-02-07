<?php

/**
 * Synchronization customer file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer;

/**
 * Synchronization customer class.
 */
class Customer extends Record
{
	/** {@inheritdoc} */
	public function process()
	{
		$this->lastScan = $this->config->getLastScan('customer');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config->setScan('customer');
			$this->lastScan = $this->config->getLastScan('customer');
		}
		if ($this->import()) {
			$this->config->setEndScan('customer', $this->lastScan['start_date']);
		}
	}

	/**
	 * Import customers from Magento.
	 *
	 * @return bool
	 */
	public function import(): bool
	{
		$allChecked = false;
		try {
			if ($customers = $this->getCustomersFromApi()) {
				foreach ($customers as $customer) {
					if (empty($customer)) {
						\App\Log::error('Empty customer details', 'Integrations/Magento');
						continue;
					}
					\App\Cache::staticSave('MagentoGetFromApi|customers', $customer['id'], $customer);
					$className = $this->config->get('customer_map_class') ?: '\App\Integrations\Magento\Synchronizer\Maps\Customer';
					$mapModel = new $className($this);
					$mapModel->setData($customer);
					$dataCrm = $mapModel->getDataCrm();
					if ($dataCrm) {
						try {
							$dataCrm['parent_id'] = $this->syncAccount($dataCrm);
							$this->syncContact($dataCrm);
						} catch (\Throwable $ex) {
							$this->log('Saving customer', $ex);
							\App\Log::error('Error during saving customer: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
						}
					} else {
						\App\Log::error('Empty map customer details', 'Integrations/Magento');
					}
					$this->config->setScan('customer', 'id', $customer['id']);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			$this->log('Import customers', $ex);
			\App\Log::error('Error during import customer: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
		}
		return $allChecked;
	}

	/**
	 * Method to get customers form Magento.
	 *
	 * @return array
	 */
	public function getCustomersFromApi(): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/customers/search?' . $this->getSearchCriteria($this->config->get('customers_limit'))));
		if (!empty($data['items'])) {
			$items = $data['items'];
		}
		return $items;
	}
}
