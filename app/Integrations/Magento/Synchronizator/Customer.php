<?php

/**
 * Synchronize orders.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

/**
 * Order class.
 */
class Customer extends Integrators\Customer
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$this->config = \App\Integrations\Magento\Config::getInstance();
		$this->lastScan = $this->config::getLastScan('customer');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && 0 === (int) $this->lastScan['idcrm'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config::setScan('customer');
			$this->lastScan = $this->config::getLastScan('customer');
		}
		$this->getMapping('customer');
		$resultCrm = $this->checkCustomersCrm();
		$result = $this->checkCustomers();
		if ($resultCrm && $result) {
			$this->config::setEndScan('customer', $this->lastScan['start_date']);
		}
	}

	/**
	 * Method to save, update or delete customers from YetiForce.
	 */
	public function checkCustomersCrm(): bool
	{
		$result = false;
		$customersCrm = $this->getCustomersCrm();
		if (!empty($customersCrm)) {
			$customers = $this->getCustomers($this->getFormattedRecordsIds(array_keys($customersCrm), self::MAGENTO, 'customer'));
			foreach ($customersCrm as $id => $customerCrm) {
				if (isset($this->map['customer'][$id], $customers[$this->map['customer'][$id]])) {
					$customerData = $customers[$this->map['customer'][$id]];
					if (!empty($customerData) && $this->hasChanges($customerCrm, $customerData)) {
						if (self::MAGENTO === $this->whichToUpdate($customerCrm, $customerData)) {
							$this->updateCustomer($this->map['customer'][$id], $customerCrm);
						} else {
							$this->updateCustomerCrm($id, $customerData);
						}
					}
				} elseif (isset($this->map['customer'][$id]) && !isset($customers[$this->map['customer'][$id]])) {
					$this->deleteCustomerCrm($id);
				} else {
					$this->saveCustomer($customerCrm);
				}
				$this->config::setScan('customer', 'idcrm', $id);
			}
		} else {
			$result = true;
		}
		return $result;
	}

	/**
	 * Method to get customers form YetiForce.
	 *
	 * @param array $ids
	 *
	 * @throws \App\Exceptions\NotAllowedMethod
	 * @throws \ReflectionException
	 *
	 * @return array
	 */
	public function getCustomersCrm(array $ids = []): array
	{
		$queryGenerator = (new \App\QueryGenerator('Contacts'));
		$queryGenerator->addCondition('leadsource', 'PLL_MAGENTO', 'e');
		$query = $queryGenerator->createQuery();
		$customersCrm = [];
		if (!empty($ids)) {
			$query->andWhere(['IN', 'contactid', $ids]);
		} else {
			$query->andWhere(['>', 'contactid', $this->lastScan['idcrm']]);
			$query->andWhere(['<=', 'modifiedtime', $this->lastScan['start_date']]);
			if (!empty($this->lastScan['end_date'])) {
				$query->andWhere(['>=', 'modifiedtime', $this->lastScan['end_date']]);
			}
			$query->limit(\App\Config::component('Magento', 'productLimit'));
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$customersCrm[$row['contactid']] = $row;
		}
		return $customersCrm;
	}

	/**
	 * Method to save, update or delete customers from Magento.
	 *
	 * @return bool
	 */
	public function checkCustomers(): bool
	{
		$allChecked = false;
		try {
			$customers = $this->getCustomers();
			if (!empty($customers)) {
				$customersCrm = $this->getCustomersCrm($this->getFormattedRecordsIds(array_keys($customers), self::YETIFORCE, 'customer'));
				foreach ($customers as $id => $customerData) {
					if (empty($customerData)) {
						continue;
					}

					if (isset($this->mapCrm['customer'][$id], $customersCrm[$this->mapCrm['customer'][$id]])) {
						if ($this->hasChanges($customersCrm[$this->mapCrm['customer'][$id]], $customerData)) {
							if (self::MAGENTO === $this->whichToUpdate($customersCrm[$this->mapCrm['customer'][$id]], $customerData)) {
								$this->updateCustomer($id, $customersCrm[$this->mapCrm['customer'][$id]]);
							} else {
								$this->updateCustomerCrm($this->mapCrm['customer'][$id], $customerData);
							}
						}
					} elseif (isset($this->mapCrm['customer'][$id]) && !isset($customersCrm[$this->mapCrm['customer'][$id]])) {
						$this->deleteCustomer($id);
					} else {
						$this->saveCustomerCrm($customerData);
					}
					$this->config::setScan('customer', 'id', $id);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			\App\Log::error('Error during saving magento customer to yetiforce: ' . $ex->getMessage(), 'Integrations/Magento');
			$allChecked = false;
		}
		return $allChecked;
	}

	/**
	 * Method to save product to YetiForce.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function saveCustomerCrm(array $data): int
	{
		$customerFields = new \App\Integrations\Magento\Synchronizator\Maps\Customer();
		$customerFields->setData($data);
		$customerFields->setSynchronizator($this);
		$dataCrm = $customerFields->getDataCrm();
		$value = 0;
		if (!empty($dataCrm)) {
			try {
				$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
				$recordModel->setData($dataCrm);
				$recordModel->save();
				$this->saveMapping($data['id'], $recordModel->getId(), 'customer');
				$value = $recordModel->getId();
			} catch (\Throwable $ex) {
				\App\Log::error('Error during saving yetiforce customer: ' . $ex->getMessage(), 'Integrations/Magento');
			}
		}
		return $value;
	}

	/**
	 * Method to update product in YetiForce.
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @throws \Exception
	 */
	public function updateCustomerCrm(int $id, array $data): void
	{
		try {
			$customerFields = new \App\Integrations\Magento\Synchronizator\Maps\Customer();
			$customerFields->setData($data);
			$customerFields->setSynchronizator($this);
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Contacts');
			foreach ($customerFields->getDataCrm(true) as $key => $value) {
				$recordModel->set($key, $value);
			}
			$recordModel->save();
		} catch (\Throwable $ex) {
			\App\Log::error('Error during updating yetiforce customer: ' . $ex->getMessage(), 'Integrations/Magento');
		}
	}

	/**
	 * Method to delete product in YetiForce.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function deleteCustomerCrm(int $id): bool
	{
		try {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Contacts');
			$recordModel->delete();
			$this->deleteMapping($this->map['customer'][$id], $id, 'customer');
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during deleting yetiforce customer: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}

	/**
	 * Method to compare changes of given two records.
	 *
	 * @param array $dataCrm
	 * @param array $data
	 *
	 * @return bool
	 */
	public function hasChanges(array $dataCrm, array $data): bool
	{
		$hasChanges = false;
		$customerFields = new \App\Integrations\Magento\Synchronizator\Maps\Customer();
		$customerFields->setSynchronizator($this);
		$customerFields->setData($data);
		foreach ($customerFields->getFields(true) as $fieldCrm => $field) {
			$fieldValue = $customerFields->getFieldValue($field);
			if ($dataCrm[$fieldCrm] != $fieldValue) {
				$hasChanges = true;
				break;
			}
		}
		return $hasChanges;
	}
}
