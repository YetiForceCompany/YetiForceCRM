<?php

/**
 * Comarch account synchronization map file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Comarch\Xl\Maps;

/**
 * Comarch account synchronization map class.
 */
class Account extends \App\Integrations\Comarch\Map
{
	/** {@inheritdoc} */
	const API_NAME_ID = 'knt_GidNumer';
	/** {@inheritdoc} */
	protected $moduleName = 'Accounts';
	/** {@inheritdoc} */
	protected $fieldMap = [
		'accountname' => ['names' => ['get' => 'knt_Nazwa1', 'create' => 'Nazwa1', 'update' => 'Nazwa1']],
		'vat_id' => ['names' => ['get' => 'knt_Nip', 'create' => 'Nip', 'update' => 'Nip'], 'fn' => 'convertVatId'],
		'registration_number_2' => ['names' => ['get' => 'knt_Regon', 'create' => 'Regon', 'update' => 'Regon']],
		'account_short_name' => ['names' => ['get' => 'knt_Akronim', 'create' => 'Akronim', 'update' => 'Akronim']],
		'account_second_name' => ['names' => ['get' => 'knt_Nazwa2', 'create' => 'Nazwa2', 'update' => 'Nazwa2']],
		'account_third_name' => ['names' => ['get' => 'knt_Nazwa3', 'create' => 'Nazwa3', 'update' => 'Nazwa3']],
		'accounttype' => [
			'names' => ['get' => 'knt_Rodzaj', 'create' => 'Rodzaj', 'update' => 'Rodzaj'],
			'fn' => 'findBySynchronizer', 'synchronizer' => 'AccountTypes'
		],
		'payment_methods' => [
			'names' => ['get' => 'knt_FormaPl', 'create' => 'FormaPl', 'update' => 'FormaPl'],
			'fn' => 'findBySynchronizer', 'synchronizer' => 'PaymentMethods'
		],
		'crmactivity' => ['names' => ['get' => 'knt_SpTerminPlSpr', 'create' => 'TerminPlSpr', 'update' => 'LimitOkres']],
		'addresslevel1a' => [
			'names' => ['get' => 'knt_Kraj', 'create' => 'Kraj', 'update' => 'Kraj'], 'fn' => 'convertCountry'
		],
		'addresslevel2a' => ['names' => ['get' => 'knt_Wojewodztwo', 'create' => 'Wojewodztwo', 'update' => 'Wojewodztwo']],
		'addresslevel3a' => ['names' => ['get' => 'knt_Powiat', 'create' => 'Powiat', 'update' => 'Powiat']],
		'addresslevel4a' => ['names' => ['get' => 'knt_Gmina', 'create' => 'Gmina', 'update' => 'Gmina']],
		'addresslevel5a' => ['names' => ['get' => 'knt_Miasto', 'create' => 'Miasto', 'update' => 'Miasto']],
		'addresslevel7a' => ['names' => ['get' => 'knt_KodP', 'create' => 'KodP', 'update' => 'KodP']],
		'addresslevel8a' => ['names' => ['get' => 'knt_Ulica', 'create' => 'Ulica', 'update' => 'Ulica']],
		'email1' => ['names' => ['get' => 'knt_Email', 'create' => 'Email', 'update' => 'Email']],
		'phone' => [
			'names' => ['get' => 'knt_Telefon1', 'create' => 'Telefon1', 'update' => 'Telefon1'],
			'fn' => 'convertPhone'
		],
		'otherphone' => [
			'names' => ['get' => 'knt_Gsm', 'create' => 'Gsm', 'update' => 'Gsm'],
			'fn' => 'convertPhone', 'optional' => true
		],
		'fax' => [
			'names' => ['get' => 'knt_Fax', 'create' => 'Fax', 'update' => 'Fax'],
			'fn' => 'convertPhone', 'optional' => true
		],
	];
	/** {@inheritdoc} */
	protected $defaultDataYf = [
		'fieldMap' => [
			'legal_form' => 'PLL_COMPANY'
		]
	];
	/** {@inheritdoc} */
	protected $dependentSynchronizations = ['BankAccounts'];

	/** {@inheritdoc} */
	public function findRecordInYf(): ?int
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		if (!empty($this->dataYf[self::FIELD_NAME_ID])) {
			$queryGenerator->addCondition('comarch_server_id', $this->synchronizer->config->get('id'), 'e');
			$queryGenerator->addCondition(self::FIELD_NAME_ID, $this->dataYf[self::FIELD_NAME_ID], 'e');
		} elseif (!empty($this->dataYf['account_short_name'])) {
			$queryGenerator->addCondition('account_short_name', $this->dataYf['account_short_name'], 'e');
		} elseif (!empty($this->dataYf['vat_id'])) {
			$queryGenerator->addCondition('vat_id', $this->dataYf['vat_id'], 'e');
		}
		return $queryGenerator->createQuery()->scalar() ?: null;
	}

	/** {@inheritdoc} */
	public function saveInApi(): void
	{
		if (empty($this->dataApi['id'])) {
			$response = $this->synchronizer->controller->getConnector()
				->request('POST', 'customer/create', $this->dataApi);
			$response = \App\Json::decode($response);
			$this->recordModel->set(self::FIELD_NAME_ID, $response['id']);
			$this->recordModel->save();
			$this->dataYf[self::FIELD_NAME_ID] = $this->dataApi['id'] = $response['id'];
		} else {
			$id = $this->dataApi['id'];
			unset($this->dataApi['id']);
			$this->synchronizer->controller->getConnector()
				->request('PUT', 'Customer/Update/' . $id, $this->dataApi);
			$this->dataApi['id'] = $id;
		}
		$this->synchronizer->updateMapIdCache(
			$this->recordModel->getModuleName(),
			$this->dataApi['id'],
			$this->recordModel->getId()
		);
		$this->runDependentSynchronizer(false);
	}

	/** {@inheritdoc} */
	public function findRecordInApi(): int
	{
		$response = '';
		if (!empty($this->dataYf['account_short_name'])) {
			try {
				$response = $this->synchronizer->controller->getConnector()
					->request('GET', 'Customer/Get?akronim=' . $this->dataYf['account_short_name']);
				if ($response && ($response = \App\Json::decode($response))) {
					return $response[0]['knt_GidNumer'];
				}
			} catch (\Throwable $th) {
				$response = 0;
			}
		}
		if (!empty($this->dataApi['Nip'])) {
			try {
				$vatId = str_replace([' ', ',', '.', '-'], '', $this->dataApi['Nip']);
				$response = $this->synchronizer->controller->getConnector()
					->request('GET', 'Customer/Get?nip=' . $vatId);
				if ($response && ($response = \App\Json::decode($response))) {
					return $response[0]['knt_GidNumer'];
				}
			} catch (\Throwable $th) {
				$response = 0;
			}
		}
		return 0;
	}

	/**
	 * Convert Vat Id.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return string
	 */
	protected function convertVatId($value, array $field, bool $fromApi): string
	{
		if ($fromApi) {
			$value = ($this->dataApi['knt_NipPrefiks'] ?? '') . $value;
		} else {
			$this->dataApi['NipPrefiks'] = '';
			if (($pre = substr($value, 0, 2)) && !is_numeric($pre)) {
				$value = substr($value, 2);
				$this->dataApi['NipPrefiks'] = $pre;
			}
		}
		return $value;
	}
}
