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
		'email' => 'email',
		'phone' => 'phone',
		'phone_extra' => 'phone_extra',
		'fax' => 'mobile',
		'fax_extra' => 'mobile_extra',
		'buildingnumbera' => 'buildingnumbera',
		'addresslevel1a' => 'addresslevel1a',
		'addresslevel2a' => 'addresslevel2a',
		'addresslevel3a' => 'addresslevel3a',
		'addresslevel4a' => 'addresslevel4a',
		'addresslevel5a' => 'addresslevel5a',
		'addresslevel6a' => 'addresslevel6a',
		'addresslevel7a' => 'addresslevel7a',
		'addresslevel8a' => 'addresslevel8a',
		'buildingnumberb' => 'buildingnumberb',
		'addresslevel1b' => 'addresslevel1b',
		'addresslevel2b' => 'addresslevel2b',
		'addresslevel3b' => 'addresslevel3b',
		'addresslevel4b' => 'addresslevel4b',
		'addresslevel5b' => 'addresslevel5b',
		'addresslevel6b' => 'addresslevel6b',
		'addresslevel7b' => 'addresslevel7b',
		'addresslevel8b' => 'addresslevel8b',
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
					$className = $this->config->get('customer_map_class') ?: '\App\Integrations\Magento\Synchronizator\Maps\Customer';
					$customerFields = new $className($this);
					$customerFields->setData($customer);
					$dataCrm = $customerFields->getDataCrm();
					if ($dataCrm) {
						try {
							$dataCrm['parent_id'] = $this->createAccount($dataCrm);
							$this->createContact($dataCrm);
						} catch (\Throwable $ex) {
							\App\Log::error('Error during saving customer: ' . $ex->getMessage(), 'Integrations/Magento');
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
	public function getCustomersFromApi(array $ids = []): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/customers/search?' . $this->getSearchCriteria($ids, $this->config->get('customerLimit'))));
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
		$id = $this->findAccount($data);
		if (!$id) {
			$recordModel = \Accounts_Record_Model::getCleanInstance('Accounts');
			$fields = $recordModel->getModule()->getFields();
			if (!($companyName = $data['company_name_a'] ?: $data['company_name_b'] ?: false)) {
				$companyName = $data['firstname'] . '|##|' . $data['lastname'];
				$recordModel->set('legal_form', 'PLL_NATURAL_PERSON');
			}
			$recordModel->set('accountname', $companyName);
			$recordModel->set('vat_id', $data['vat_id_a'] ?: $data['vat_id_b'] ?: '');
			foreach ($this->accountFieldsMap as  $target => $source) {
				if (isset($data[$source], $fields[$target])) {
					$recordModel->set($target, $data[$source]);
				}
			}
			$recordModel->save();
			$id = $recordModel->getId();
		}
		return $id;
	}

	/**
	 * Find account record id by vat id or email fields.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function findAccount(array $data): int
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Accounts');
		$vatIdField = $recordModel->getModule()->getFieldByName('vat_id', true);
		if ($vatIdField && $vatIdField->isActiveField() && ($vatId = $data['vat_id_a'] ?: $data['vat_id_b'] ?: false)) {
			$id = (new \App\Db\Query())->select(['accountid'])->from('vtiger_account')
				->innerJoin('vtiger_crmentity', 'vtiger_account.accountid = vtiger_crmentity.crmid')
				->where(['vtiger_account.vat_id' => $vatId])->scalar();
			if ($id) {
				return $id;
			}
		}
		$fields = $recordModel->getModule()->getFieldsByType('email', true);
		$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		foreach ($fields as $fieldModel) {
			$queryGenerator->addCondition($fieldModel->getName(), $data['email'], 'e', false);
		}
		if ($recordModel->getId()) {
			$queryGenerator->addCondition('id', $recordModel->getId(), 'n', true);
		}
		return $queryGenerator->createQuery()->scalar() ?: 0;
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
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$fields = $recordModel->getModule()->getFields();
		$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		foreach ($recordModel->getModule()->getFieldsByType('email', true) as $fieldModel) {
			$queryGenerator->addCondition($fieldModel->getName(), $data['email'], 'e', false);
		}
		if ($recordModel->getId()) {
			$queryGenerator->addCondition('id', $recordModel->getId(), 'n', true);
		}
		$id = $queryGenerator->createQuery()->scalar() ?: 0;
		if (!$id) {
			foreach ($data as  $fieldName => $value) {
				if (isset($fields[$fieldName])) {
					$recordModel->set($fieldName, $value);
				}
			}
			$recordModel->save();
			$id = $recordModel->getId();
		}
		return $id;
	}
}
