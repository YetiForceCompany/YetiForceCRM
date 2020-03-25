<?php

/**
 * Synchronize orders.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

/**
 * Order class.
 */
class Customer extends Record
{
	/**
	 * Account fields map .
	 *
	 * @var string[]
	 */
	public $accountFieldsMap = [
		'addresslevel1a',
		'addresslevel2a',
		'addresslevel3a',
		'addresslevel4a',
		'addresslevel5a',
		'addresslevel6a',
		'addresslevel7a',
		'addresslevel8a',
		'addresslevel1b',
		'addresslevel2b',
		'addresslevel3b',
		'addresslevel4b',
		'addresslevel5b',
		'addresslevel6b',
		'addresslevel7b',
		'addresslevel8b',
	];

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$this->lastScan = $this->config->getLastScan('customer');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && 0 === (int) $this->lastScan['idcrm'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config->setScan('customer');
			$this->lastScan = $this->config->getLastScan('customer');
		}
		if ($this->import()) {
			$this->config->setEndScan('customer', $this->lastScan['start_date']);
		}
	}

	/**
	 * Method to save, update or delete customers from Magento.
	 *
	 * @return bool
	 */
	public function import(): bool
	{
		$allChecked = false;
		try {
			if ($customers = $this->getCustomers()) {
				foreach ($customers as $customer) {
					if (empty($customer)) {
						continue;
					}
					$className = $this->config->get('customerMapClassName') ?: '\App\Integrations\Magento\Synchronizator\Maps\Customer';
					$customerFields = new $className($this);
					$customerFields->setData($customer);
					$dataCrm = $customerFields->getDataCrm();
					if ($dataCrm) {
						try {
							$dataCrm['parent_id'] = $this->createAccount($dataCrm);
							$id = $this->createContact($dataCrm);
							$this->saveMapping($customer['id'], $id, 'customer');
						} catch (\Throwable $ex) {
							\App\Log::error('Error during saving customer: ' . $ex->getMessage(), 'Integrations/Magento');
						}
					}
					$this->config->setScan('customer', 'id', $customer['id']);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			\App\Log::error('Error during import customer: ' . $ex->getMessage(), 'Integrations/Magento');
			$allChecked = false;
		}
		return $allChecked;
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
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('storeCode') . '/V1/customers/search?' . $this->getSearchCriteria($ids, $this->config->get('customerLimit'))));
		if (!empty($data['items'])) {
			$items = $data['items'];
		}
		return $items;
	}

	/**
	 * Method to create account.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function createAccount(array $data): int
	{
		$vatId = $data['vat_id_a'] ?: $data['vat_id_b'] ?: false;
		$companyName = $data['company_name_a'] ?: $data['company_name_b'] ?: false;
		$id = 0;
		if ($vatId) {
			$id = (new \App\Db\Query())->select(['accountid'])->from('vtiger_account')
				->innerJoin('vtiger_crmentity', 'vtiger_account.accountid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, 'vtiger_account.vat_id' => $vatId])->scalar() ?: 0;
		}
		if (!$id && $vatId && $companyName) {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('Accounts');
			$recordModel->set('accountname', $companyName);
			$recordModel->set('vat_id', $vatId);
			foreach ($this->accountFieldsMap as $fieldName) {
				if (isset($data[$fieldName])) {
					$recordModel->set($fieldName, $data[$fieldName]);
				}
			}
			$recordModel->save();
			$id = $recordModel->getId();
		}
		return $id;
	}

	/**
	 * Method to create contact.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function createContact(array $data): int
	{
		$id = (new \App\Db\Query())->select(['contactid'])->from('vtiger_contactdetails')
			->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_contactdetails.email' => $data['email']])->scalar();
		if (!$id) {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
			$recordModel->setData($data);
			$recordModel->save();
			$id = $recordModel->getId();
		}
		return $id;
	}
}
