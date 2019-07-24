<?php

/**
 * Magento product map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Integrators;

/**
 * Magento Product map class.
 */
abstract class Customer extends \App\Integrations\Magento\Synchronizator\Record
{
	public static $groups = [];

	/**
	 * Method to update customer in Magento.
	 *
	 * @param array $customer
	 * @param int   $customerId
	 *
	 * @return bool
	 */
	public function updateCustomer(int $customerId, array $customer): bool
	{
		$result = false;
		if (!empty($customerId)) {
			try {
				$className = \App\Config::component('Magento', 'customerMapClassName');
				$customerFields = new $className();
				$customerFields->setDataCrm($customer);
				$customerFields->setSynchronizator($this);
				$data = $customerFields->getData(true);
				$data['customer']['id'] = $customerId;
				$this->connector->request('PUT', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/customers/' . urlencode($customerId), $data);
				$result = true;
			} catch (\Throwable $ex) {
				\App\Log::error('Error during updating magento product: ' . $ex->getMessage(), 'Integrations/Magento');
			}
		}
		return $result;
	}

	/**
	 * Get group id.
	 *
	 * @param string $groupName
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \ReflectionException
	 *
	 * @return false|int|string
	 */
	public function getGroupId(string $groupName)
	{
		if (empty(static::$groups)) {
			$this->getAllGroups();
		}
		return array_search($groupName, static::$groups);
	}

	/**
	 * Get all groups.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \ReflectionException
	 */
	public function getAllGroups(): void
	{
		$customerGroups = [];
		$result = \App\Json::decode($this->connector->request('GET', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/customerGroups/search?searchCriteria', []));
		foreach ($result['items'] as $groupInfo) {
			$customerGroups[$groupInfo['id']] = $groupInfo['code'];
		}
		static::$groups = $customerGroups;
	}

	public function getGroupName(int $groupId)
	{
		if (empty(static::$groups)) {
			$this->getAllGroups();
		}
		if (isset(static::$groups[$groupId])) {
			return static::$groups[$groupId];
		}
		return '';
	}

	/**
	 * Method to save customer to Magento.
	 *
	 * @param array $customer
	 *
	 * @throws \ReflectionException
	 *
	 * @return bool
	 */
	public function saveCustomer(array $customer): bool
	{
		$className = \App\Config::component('Magento', 'customerMapClassName');
		$customerFields = new $className();
		$customerFields->setDataCrm($customer);
		$customerFields->setSynchronizator($this);
		try {
			$customerRequest = \App\Json::decode($this->connector->request('POST', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/customers/', $customerFields->getData()));
			$this->saveMapping($customerRequest['id'], $customer['contactid'], 'customer');
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during saving magento product: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}

	/**
	 * Method to delete customer in Magento.
	 *
	 * @param int $customerId
	 *
	 * @return bool
	 */
	public function deleteCustomer(int $customerId): bool
	{
		try {
			if ($customerId) {
				$this->connector->request('DELETE', '/rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/customers/' . urlencode($customerId), []);
			}
			$this->deleteMapping($customerId, $this->mapCrm['customer'][$customerId], 'customer');
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during deleting magento customer: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}

	/**
	 * Method to get customers form Magento.
	 *
	 * @param array $ids
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \ReflectionException
	 *
	 * @return array
	 */
	public function getCustomers(array $ids = []): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/customers/search?' . $this->getSearchCriteria($ids, \App\Config::component('Magento', 'customerLimit'))));
		if (!empty($data['items'])) {
			foreach ($data['items'] as $item) {
				$items[$item['id']] = $item;
			}
		}
		return $items;
	}
}
