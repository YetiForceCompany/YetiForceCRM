<?php

/**
 * Synchronization orders file.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer;

/**
 * Synchronization orders class.
 */
class Order extends Record
{
	/**
	 * {@inheritdoc}
	 */
	protected static $updateFields = [
		'ssingleorders_status', 'status_magento'
	];

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$this->lastScan = $this->config->getLastScan('order');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config->setScan('order');
			$this->lastScan = $this->config->getLastScan('order');
		}
		if ($this->import()) {
			$this->config->setEndScan('order', $this->lastScan['start_date']);
		}
	}

	/**
	 * Import orders from magento.
	 *
	 * @return bool
	 */
	public function import(): bool
	{
		$allChecked = false;
		try {
			if ($orders = $this->getOrdersFromApi()) {
				foreach ($orders as $id => $order) {
					if (empty($order)) {
						\App\Log::error('Empty order details', 'Integrations/Magento');
						continue;
					}
					$className = $this->config->get('order_map_class') ?: '\App\Integrations\Magento\Synchronizer\Maps\Order';
					$mapModel = new $className($this);
					$mapModel->setData($order);
					$dataCrm = $mapModel->getDataCrm();
					if ($dataCrm) {
						try {
							if ($crmId = $mapModel->getCrmId($order['entity_id'])) {
								$this->updateOrderInCrm($crmId, $mapModel);
							} else {
								$parentOrder = $dataCrm['parent_id'];
								if (1 === (int) $order['customer_is_guest']) {
									$dataCrm['parent_id'] = $this->syncAccount($dataCrm);
									$dataCrm['contactid'] = $this->syncContact($dataCrm);
								} else {
									$customer = $this->getFromApi('customers', $order['customer_id']);
									$customerClassName = $this->config->get('customer_map_class') ?: '\App\Integrations\Magento\Synchronizer\Maps\Customer';
									$customerMapModel = new $customerClassName($this);
									$customerMapModel->setData($customer);
									$customerDataCrm = $customerMapModel->getDataCrm();
									$dataCrm['parent_id'] = $this->syncAccount($customerDataCrm);
									$dataCrm['contactid'] = $this->syncContact($customerDataCrm);
								}
								$dataCrm['accountid'] = $dataCrm['parent_id'];
								$dataCrm['parent_id'] = $parentOrder;
								unset($dataCrm['birthday'],$dataCrm['leadsource'],$dataCrm['mobile'],$dataCrm['mobile_extra'],$dataCrm['phone'],$dataCrm['phone_extra'],$dataCrm['salutationtype']);
								$dataCrm['magento_id'] = $order['entity_id'];
								$mapModel->setDataCrm($dataCrm);
								$crmId = $this->createOrderInCrm($mapModel);
								\App\Cache::staticSave('CrmIdByMagentoIdSSingleOrders', $order['entity_id'], $crmId);
							}
						} catch (\Throwable $ex) {
							\App\Log::error('Error during saving customer: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
						}
					} else {
						\App\Log::error('Empty map customer details', 'Integrations/Magento');
					}
					$this->config->setScan('order', 'id', $id);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			\App\Log::error('Error during import customer: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
		}
		return $allChecked;
	}

	/**
	 * Method to get orders form Magento.
	 *
	 * @return array
	 */
	public function getOrdersFromApi(): array
	{
		$items = [];
		\App\Log::beginProfile('GET|orders', 'Integrations/MagentoApi');
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/orders?' . $this->getSearchCriteria($this->config->get('orderLimit'))));
		\App\Log::endProfile('GET|orders', 'Integrations/MagentoApi');
		if (!empty($data['items'])) {
			foreach ($data['items'] as $item) {
				$items[$item['entity_id']] = $item;
			}
		}
		return $items;
	}

	/**
	 * Create order in crm.
	 *
	 * @param \App\Integrations\Magento\Synchronizer\Maps\Inventory $mapModel
	 *
	 * @return mixed|int
	 */
	public function createOrderInCrm(Maps\Inventory $mapModel)
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('SSingleOrders');
		if ($this->config->get('storage_id')) {
			$recordModel->set('istorageaddressid', $this->config->get('storage_id'));
		}
		$recordModel->set('magento_server_id', $this->config->get('id'));
		$fields = $recordModel->getModule()->getFields();
		foreach ($mapModel->dataCrm as $key => $value) {
			if (isset($fields[$key])) {
				$recordModel->set($key, $value);
			}
		}
		if (!$this->saveInventoryCrm($recordModel, $mapModel)) {
			\App\Log::error('Error during parse inventory order id: [' . $mapModel->data['entity_id'] . ']', 'Integrations/Magento');
		}
		$recordModel->save();
		return $recordModel->getId();
	}

	/**
	 * Method to update order in YetiForce.
	 *
	 * @param int                                                   $id
	 * @param \App\Integrations\Magento\Synchronizer\Maps\Inventory $mapModel
	 *
	 * @throws \Exception
	 */
	public function updateOrderInCrm(int $id, Maps\Inventory $mapModel): void
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'SSingleOrders');
		$fields = $recordModel->getModule()->getFields();
		foreach (self::$updateFields as $fieldname) {
			if (isset($mapModel->dataCrm[$fieldname], $fields[$fieldname])) {
				$recordModel->set($fieldname, $mapModel->dataCrm[$fieldname]);
			}
		}
		$recordModel->save();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSearchCriteria(int $pageSize = 10): string
	{
		$searchCriteria[] = parent::getSearchCriteria($pageSize);
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][value]=' . $this->config->get('store_id');
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][field]=store_id';
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][conditionType]=eq';
		return implode('&', $searchCriteria);
	}
}
