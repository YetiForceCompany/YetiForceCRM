<?php

/**
 * Synchronize invoices.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

/**
 * Category class.
 */
class Invoice extends Integrators\Invoice
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$this->getMapping('invoice');
		$this->getMapping('product');
		$this->config = \App\Integrations\Magento\Config::getInstance();
		$this->lastScan = $this->config::getLastScan('invoice');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config::setScan('invoice');
			$this->lastScan = $this->config::getLastScan('invoice');
		}
		if ($this->checkInvoices()) {
			$this->config::setEndScan('invoice', $this->lastScan['start_date']);
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
				}
				$this->config::setScan('invoice', 'id', $id);
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
		$order = \App\Json::decode($this->connector->request('GET', '/rest/all/V1/orders/' . $data['order_id']));
		$data['billing_address'] = $order['billing_address'];
		$data['extension_attributes'] = $order['extension_attributes'];
		$data['payment'] = $order['payment'];
		$data['customer_id'] = $order['customer_id'];
		$invoiceFields = new \App\Integrations\Magento\Synchronizator\Maps\Invoice();
		$invoiceFields->setData($data);
		$dataCrm = $invoiceFields->getDataCrm();
		$value = 0;
		if (!empty($dataCrm)) {
			try {
				$recordModel = \Vtiger_Record_Model::getCleanInstance('FInvoice');
				$recordModel->setData($dataCrm);
				if ($this->saveInventoryCrm($recordModel, $order['items'], $data['extension_attributes']['shipping_assignments'], $invoiceFields)) {
					$recordModel->save();
					$this->saveMapping($data['entity_id'], $recordModel->getId(), 'invoice');
				}
				$value = $recordModel->getId();
			} catch (\Throwable $ex) {
				\App\Log::error('Error during saving yetiforce invoice: ' . $ex->getMessage(), 'Integrations/Magento');
			}
		}
		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSearchCriteria($ids, int $pageSize = 10): string
	{
		$searchCriteria[] = parent::getSearchCriteria($ids, $pageSize);
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][value]=' . \App\Config::component('Magento', 'storeId');
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][field]=store_id';
		$searchCriteria[] = 'searchCriteria[filter_groups][3][filters][0][conditionType]=in';
		return implode('&', $searchCriteria);
	}
}
