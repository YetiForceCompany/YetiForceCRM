<?php
/**
 * Record abstract class.
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
 * Record class to synchronization.
 */
abstract class Record extends Base
{
	/**
	 * Account fields map .
	 *
	 * @var string[]
	 */
	public $accountFieldsMap = [
		'email1' => 'email',
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
	 * Method to get search criteria Magento records.
	 *
	 * @param int $pageSize
	 *
	 * @return string
	 */
	public function getSearchCriteria(int $pageSize = 10): string
	{
		$searchCriteria = [];
		$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][field]=entity_id';
		$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][value]=' . $this->lastScan['id'];
		$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][condition_type]=gt';
		$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][field]=updated_at';
		$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][value]=' . $this->getFormattedTime($this->lastScan['start_date']);
		$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][condition_type]=lteq';
		if (!empty($this->lastScan['end_date'])) {
			$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][field]=updated_at';
			$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][value]=' . $this->getFormattedTime($this->lastScan['end_date']);
			$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][condition_type]=gteq';
		}
		$searchCriteria[] = 'searchCriteria[pageSize]=' . $pageSize;
		$searchCriteria = implode('&', $searchCriteria);
		return $searchCriteria ?? 'searchCriteria';
	}

	/**
	 * Method to create/update account.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function syncAccount(array $data): int
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Accounts');
		if ($id = $this->findAccount($data, $recordModel)) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Accounts');
		}
		$fields = $recordModel->getModule()->getFields();
		if (!($companyName = $data['company_name_a'] ?: $data['company_name_b'] ?: false)) {
			if (empty($data['firstname'])) {
				$data['firstname'] = $data['first_name_a'] ?? $data['first_name_b'];
			}
			if (empty($data['lastname'])) {
				$data['lastname'] = $data['last_name_a'] ?? $data['last_name_b'];
			}
			$companyName = $data['firstname'] . '|##|' . $data['lastname'];
			if ('|##|' === $companyName) {
				$companyName = $data['email'] ?? $data['email_a'];
			}
			$recordModel->set('legal_form', 'PLL_NATURAL_PERSON');
		}
		$recordModel->set('accountname', $companyName);
		$recordModel->set('vat_id', $data['vat_id_a'] ?: $data['vat_id_b'] ?: '');
		foreach ($this->accountFieldsMap as $target => $source) {
			if (isset($data[$source], $fields[$target])) {
				$recordModel->set($target, $data[$source]);
			}
		}
		$recordModel->save();
		$id = $recordModel->getId();
		if ($recordModel->get('vat_id')) {
			\App\Cache::staticSave('MagentoFindAccount', $recordModel->get('vat_id'), $id);
		}
		if ($data['email'] ?? $data['email_a']) {
			\App\Cache::staticSave('MagentoFindByEmailAccounts', $data['email'] ?? $data['email_a'], $id);
		}
		return $id;
	}

	/**
	 * Find account record id by vat id or email fields.
	 *
	 * @param array                $data
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function findAccount(array $data, \Vtiger_Record_Model $recordModel): int
	{
		$vatIdField = $recordModel->getModule()->getFieldByName('vat_id');
		if ($vatIdField && $vatIdField->isActiveField() && ($vatId = $data['vat_id_a'] ?: $data['vat_id_b'] ?: false)) {
			if (\App\Cache::staticHas('MagentoFindAccount', $vatId)) {
				$id = \App\Cache::staticGet('MagentoFindAccount', $vatId);
			} else {
				$id = (new \App\Db\Query())->select(['accountid'])->from('vtiger_account')
					->innerJoin('vtiger_crmentity', 'vtiger_account.accountid = vtiger_crmentity.crmid')
					->where(['vtiger_account.vat_id' => $vatId])->scalar();
			}
			if ($id) {
				return $id;
			}
		}
		return $this->findByEmail($data, $recordModel);
	}

	/**
	 * Find  record id by email fields.
	 *
	 * @param array                $data
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function findByEmail(array $data, \Vtiger_Record_Model $recordModel): int
	{
		$email = $data['email'] ?? $data['email_a'];
		$cacheKey = 'MagentoFindByEmail' . $recordModel->getModuleName();
		if (\App\Cache::staticHas($cacheKey, $email)) {
			$id = \App\Cache::staticGet($cacheKey, $email);
		} else {
			$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			foreach ($recordModel->getModule()->getFieldsByType('email', true) as $fieldModel) {
				$queryGenerator->addCondition($fieldModel->getName(), $email, 'e', false);
			}
			$id = $queryGenerator->createQuery()->scalar() ?: 0;
			if ($id) {
				\App\Cache::staticSave($cacheKey, $email, $id);
			}
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
	public function syncContact(array $data): int
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Contacts');
		if ($id = $this->findByEmail($data, $recordModel)) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Contacts');
		}
		$fields = $recordModel->getModule()->getFields();
		unset($data['attention']);
		if (empty($data['firstname'])) {
			$data['firstname'] = $data['first_name_a'] ?? $data['first_name_b'];
		}
		if (empty($data['lastname'])) {
			$data['lastname'] = $data['last_name_a'] ?? $data['last_name_b'];
		}
		foreach ($data as $fieldName => $value) {
			if (isset($fields[$fieldName])) {
				$recordModel->set($fieldName, $value);
			}
		}
		$recordModel->save();
		$id = $recordModel->getId();
		\App\Cache::staticSave('MagentoFindByEmailContacts', $data['email'] ?? $data['email_a'], $id);
		return $id;
	}

	/**
	 * Get data by id from api.
	 *
	 * @param string $type
	 * @param string $id
	 *
	 * @return array
	 */
	public function getFromApi(string $type, string $id): array
	{
		if (\App\Cache::staticHas('MagentoGetFromApi|' . $type, $id)) {
			return \App\Cache::staticGet('MagentoGetFromApi|' . $type, $id);
		}
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . "/V1/{$type}/$id"));
		\App\Cache::staticSave('MagentoGetFromApi|' . $type, $id, $data);
		return $data;
	}
}
