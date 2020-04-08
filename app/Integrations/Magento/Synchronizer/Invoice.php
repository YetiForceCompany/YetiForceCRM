<?php

/**
 * Synchronize invoices.
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
 * Category class.
 */
class Invoice extends Record
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$this->lastScan = $this->config->getLastScan('invoice');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config->setScan('invoice');
			$this->lastScan = $this->config->getLastScan('invoice');
		}
		if ($this->checkInvoices()) {
			$this->config->setEndScan('invoice', $this->lastScan['start_date']);
		}
	}

	/**
	 * Check invoices.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \ReflectionException
	 * @throws \yii\db\Exception
	 *
	 * @return bool
	 */
	public function checkInvoices(): bool
	{
		$allChecked = true;
		$invoices = $this->getInvoices();
		if (!empty($invoices)) {
			foreach ($invoices as $id => $invoice) {
				if (!isset($this->mapCrm['invoice'][$id])) {
					$this->saveInvoiceCrm($invoice);
				} else {
					$this->updateInvoiceCrm($this->mapCrm['order'][$id], $invoice);
				}
				$this->config->setScan('invoice', 'id', $id);
			}
			$allChecked = false;
		}
		return $allChecked;
	}

	/**
	 * Method to save invoice to YetiForce.
	 *
	 * @param array $data
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed|int
	 */
	public function saveInvoiceCrm(array $data)
	{
		$order = \App\Json::decode($this->connector->request('GET', 'all/V1/orders/' . $data['order_id']));
		$data['billing_address'] = $order['billing_address'];
		$data['extension_attributes'] = $order['extension_attributes'];
		$data['payment'] = $order['payment'];
		$data['customer_id'] = $order['customer_id'] ?? '';
		$className = $this->config->get('invoiceMapClassName');
		$invoiceFields = new $className();
		$invoiceFields->setData($data);
		$dataCrm = $invoiceFields->getDataCrm();
		$value = 0;
		if (!empty($dataCrm)) {
			try {
				$recordModel = \Vtiger_Record_Model::getCleanInstance('FInvoice');
				$recordModel->setData($dataCrm);
				if ($this->saveInventoryCrm($recordModel, $order['items'], $data['extension_attributes']['shipping_assignments'], $invoiceFields)) {
					$recordModel->save();
				} else {
					\App\Log::error('Error during saving YetiForce invoice id: [' . $data['entity_id'] . ']', 'Integrations/Magento');
				}
				$value = $recordModel->getId();
			} catch (\Throwable $ex) {
				\App\Log::error('Error during saving YetiForce invoice id: [' . $data['entity_id'] . ']' . $ex->getMessage(), 'Integrations/Magento');
			}
		}
		return $value;
	}

	/**
	 * Method to update invoice in YetiForce.
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @throws \Exception
	 */
	public function updateInvoiceCrm(int $id, array $data): void
	{
		try {
			$order = \App\Json::decode($this->connector->request('GET', 'all/V1/orders/' . $data['order_id']));
			$data['billing_address'] = $order['billing_address'];
			$data['extension_attributes'] = $order['extension_attributes'];
			$data['payment'] = $order['payment'];
			$data['customer_id'] = $order['customer_id'] ?? '';
			$className = $this->config->get('invoiceMapClassName');
			$invoiceFields = new $className();
			$invoiceFields->setData($data);
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'FInvoice');
			foreach ($invoiceFields->getDataCrm(true) as $key => $value) {
				$recordModel->set($key, $value);
			}
			$recordModel->save();
		} catch (\Throwable $ex) {
			\App\Log::error('Error during updating yetiforce invoice: ' . $ex->getMessage(), 'Integrations/Magento');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSearchCriteria(int $pageSize = 10): string
	{
		$searchCriteria[] = parent::getSearchCriteria($pageSize);
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][value]=' . $this->config->get('store_id');
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][field]=store_id';
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][conditionType]=in';
		return implode('&', $searchCriteria);
	}

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
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/invoices?' . $this->getSearchCriteria($this->config->get('invoiceLimit'))));
		if (!empty($data['items'])) {
			foreach ($data['items'] as $item) {
				$items[$item['entity_id']] = $item;
			}
		}
		return $items;
	}
}
