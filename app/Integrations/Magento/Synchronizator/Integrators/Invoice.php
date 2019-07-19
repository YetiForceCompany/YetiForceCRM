<?php

/**
 * Invoice integration.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Integrators;

/**
 * Magento Invoice class.
 */
abstract class Invoice extends \App\Integrations\Magento\Synchronizator\Record
{
	/**
	 * Method to get invoices form Magento.
	 *
	 * @param array $ids
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \ReflectionException
	 *
	 * @return array
	 */
	public function getInvoices(array $ids = []): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/invoices?' . $this->getSearchCriteria($ids, \App\Config::component('Magento', 'invoiceLimit'))));
		if (!empty($data['items'])) {
			foreach ($data['items'] as $item) {
				$items[$item['entity_id']] = $item;
			}
		}
		return $items;
	}
}
