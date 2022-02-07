<?php

/**
 * Synchronization orders file.
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
 * Synchronization orders class.
 */
class Order extends Record
{
	/** {@inheritdoc} */
	protected static $updateFields = [
		'ssingleorders_status', 'status_magento',
	];

	/** {@inheritdoc} */
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
		$this->lastScan = $this->config->getLastScan('crm_order');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config->setScan('crm_order');
			$this->lastScan = $this->config->getLastScan('crm_order');
		}
		$this->config->setScan('crm_order');
		if ($this->export()) {
			$this->config->setEndScan('crm_order', $this->lastScan['start_date']);
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
								if (empty($order['customer_id'])) {
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
							$this->log('Saving order', $ex);
							\App\Log::error('Error during saving order: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
						}
					} else {
						\App\Log::error('Empty map order details', 'Integrations/Magento');
					}
					$this->config->setScan('order', 'id', $id);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			$this->log('Import orders', $ex);
			\App\Log::error('Error during import order: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
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
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/orders?' . $this->getSearchCriteria($this->config->get('orders_limit'))));
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
		if ($this->saveInventoryCrm($recordModel, $mapModel)) {
			$recordModel->save();
		} else {
			$this->log('Skipped saving record, problem with inventory products | order id: [' . $mapModel->data['entity_id'] . ']');
			\App\Log::error('Skipped saving record, problem with inventory products | order id: [' . $mapModel->data['entity_id'] . ']', 'Integrations/Magento');
		}
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

	/** {@inheritdoc} */
	public function getSearchCriteria(int $pageSize = 10): string
	{
		$searchCriteria[] = parent::getSearchCriteria($pageSize);
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][value]=' . $this->config->get('store_id');
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][field]=store_id';
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][conditionType]=eq';
		return implode('&', $searchCriteria);
	}

	/**
	 * Export orders to magento.
	 *
	 * @return bool
	 */
	public function export(): bool
	{
		$allChecked = true;
		try {
			foreach ($this->getChanges() as $row) {
				$allChecked = false;
				$this->updateOrderInMagento($row);
				$this->config->setScan('crm_order', 'id', $row['id']);
			}
		} catch (\Throwable $ex) {
			$allChecked = false;
			$this->log('Export orders', $ex);
			\App\Log::error('Error during export order: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
		}
		return $allChecked;
	}

	/**
	 * Get changes for update.
	 *
	 * @return \Generator
	 */
	public function getChanges(): \Generator
	{
		$queryGenerator = (new \App\QueryGenerator('SSingleOrders'));
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id', 'magento_id', 'ssingleorders_status', 'subject'])->permissions = false;
		$queryGenerator->addCondition('magento_server_id', $this->config->get('id'), 'e');
		$query = $queryGenerator->createQuery();
		$query->andWhere(new \yii\db\Expression('modifiedtime <> createdtime'));
		if (!empty($this->lastScan['id'])) {
			$query->andWhere(['>', 'ssingleordersid', $this->lastScan['id']]);
		}
		if (!empty($this->lastScan['end_date'])) {
			$query->andWhere(['>=', 'modifiedtime', $this->lastScan['end_date']]);
		}
		$query->andWhere(['<=', 'modifiedtime', $this->lastScan['start_date']]);

		$query->limit(10);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			yield $row;
		}
	}

	/**
	 * Update order in magento.
	 *
	 * @param array $row
	 *
	 * @return void
	 */
	public function updateOrderInMagento(array $row): void
	{
		$className = $this->config->get('order_map_class') ?: '\App\Integrations\Magento\Synchronizer\Maps\Order';
		$mapModel = new $className($this);
		$mapModel->setDataCrm($row);
		if ($updateData = $mapModel->getUpdateData()) {
			$this->connector->request('POST', $this->config->get('store_code') . '/V1/orders/', $updateData);
		} else {
			\App\Log::error("No status mapping for: crmid: {$row['id']} | magento_id: {$row['magento_id']} | status: {$row['ssingleorders_status']}", 'Integrations/Magento');
			throw new \Exception('No status mapping (in self::$statusForMagento): ' . $mapModel->dataCrm['ssingleorders_status']);
		}
	}
}
