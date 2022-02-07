<?php

/**
 * Synchronization invoices file.
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
 * Synchronization invoices class.
 */
class Invoice extends Record
{
	/** {@inheritdoc} */
	public function process()
	{
		$this->lastScan = $this->config->getLastScan('invoice');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config->setScan('invoice');
			$this->lastScan = $this->config->getLastScan('invoice');
		}
		if ($this->import()) {
			$this->config->setEndScan('invoice', $this->lastScan['start_date']);
		}
	}

	/**
	 * Import invoices from magento.
	 *
	 * @return bool
	 */
	public function import(): bool
	{
		$allChecked = false;
		try {
			if ($invoices = $this->getInvoicesFromApi()) {
				foreach ($invoices as $id => $invoice) {
					if (empty($invoice)) {
						\App\Log::error('Empty invoice details', 'Integrations/Magento');
						continue;
					}
					$className = $this->config->get('invoice_map_class') ?: '\App\Integrations\Magento\Synchronizer\Maps\Invoice';
					$mapModel = new $className($this);
					try {
						if (!$mapModel->getCrmId($invoice['entity_id'])) {
							$order = $this->getFromApi('orders', $invoice['order_id']);
							$invoice['billing_address'] = $order['billing_address'];
							$invoice['extension_attributes'] = $order['extension_attributes'];
							$invoice['payment'] = $order['payment'];
							$invoice['customer_id'] = $order['customer_id'] ?? '';
							$mapModel->setData($invoice);
							$dataCrm = $mapModel->getDataCrm();
							if ($dataCrm) {
								$dataCrm['magento_id'] = $invoice['entity_id'];
								$dataCrm['ssingleordersid'] = $mapModel->getCrmId($invoice['order_id'], 'SSingleOrders');
								if (empty($invoice['customer_id'])) {
									$dataCrm['accountid'] = $this->syncAccount($dataCrm);
								} else {
									$customer = $this->getFromApi('customers', $invoice['customer_id']);
									$customerClassName = $this->config->get('customer_map_class') ?: '\App\Integrations\Magento\Synchronizer\Maps\Customer';
									$customerMapModel = new $customerClassName($this);
									$customerMapModel->setData($customer);
									$customerDataCrm = $customerMapModel->getDataCrm();
									$dataCrm['accountid'] = $this->syncAccount($customerDataCrm);
								}
								unset($dataCrm['birthday'],$dataCrm['leadsource'],$dataCrm['mobile'],$dataCrm['mobile_extra'],$dataCrm['phone'],$dataCrm['phone_extra'],$dataCrm['salutationtype']);
								$mapModel->setDataCrm($dataCrm);
								$crmId = $this->createInvoiceInCrm($mapModel);
								\App\Cache::staticSave('CrmIdByMagentoIdSSingleOrders', $invoice['entity_id'], $crmId);
							} else {
								\App\Log::error('Empty map invoice details', 'Integrations/Magento');
							}
						}
					} catch (\Throwable $ex) {
						$this->log('Saving invoice', $ex);
						\App\Log::error('Error during saving invoice: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
					}
					$this->config->setScan('invoice', 'id', $id);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			$this->log('Import invoices', $ex);
			\App\Log::error('Error during import invoice: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
		}
		return $allChecked;
	}

	/**
	 * Method to save invoice to YetiForce.
	 *
	 * @param Maps\Inventory $mapModel
	 *
	 * @return int
	 */
	public function createInvoiceInCrm(Maps\Inventory $mapModel): int
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('FInvoice');
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
			$this->log('Skipped saving record, problem with inventory products | invoice id: [' . $mapModel->data['entity_id'] . ']');
			\App\Log::error('Skipped saving record, problem with inventory products | invoice id: [' . $mapModel->data['entity_id'] . ']', 'Integrations/Magento');
		}
		return $recordModel->getId();
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
	 * Method to get invoices form Magento.
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function getInvoicesFromApi(array $ids = []): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/invoices?' . $this->getSearchCriteria($this->config->get('invoices_limit'))));
		if (!empty($data['items'])) {
			foreach ($data['items'] as $item) {
				$items[$item['entity_id']] = $item;
			}
		}
		return $items;
	}
}
