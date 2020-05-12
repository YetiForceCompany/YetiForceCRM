<?php
/**
 * Gus class for downloading REGON registry open public data. For BIR 1.1 version.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Gus record collector class.
 */
class Gus extends Base
{
	/**
	 * {@inheritdoc}
	 */
	protected $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];
	/**
	 * {@inheritdoc}
	 */
	public $icon = 'yfi yfi-gus';
	/**
	 * {@inheritdoc}
	 */
	public $label = 'GUS';
	/**
	 * {@inheritdoc}
	 */
	public $displayType = 'FillFields';
	/**
	 * {@inheritdoc}
	 */
	protected $fields = [
		'vatId' => [
			'labelModule' => '_Base',
			'label' => 'Vat ID'
		],
		'ncr' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 1'
		],
		'taxNumber' => [
			'labelModule' => '_Base',
			'label' => 'Registration number 2'
		]
	];

	/**
	 * {@inheritdoc}
	 */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'vatId' => 'vat_id',
			'taxNumber' => 'registration_number_2',
			'ncr' => 'registration_number_1'
		],
		'Leads' => [
			'vatId' => 'vat_id',
			'taxNumber' => 'registration_number_2',
			'ncr' => 'registration_number_1'
		],
		'Vendors' => [
			'vatId' => 'vat_id',
			'taxNumber' => 'registration_number_2',
			'ncr' => 'registration_number_1'
		],
		'Competition' => [
			'vatId' => 'vat_id',
			'taxNumber' => 'registration_number_2',
			'ncr' => 'registration_number_1'
		]
	];
	/**
	 * {@inheritdoc}
	 */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'Regon' => 'registration_number_2',
			'Krs' => 'registration_number_1',
			'Nip' => 'vat_id',
			'Nazwa' => 'accountname',
			'Wojewodztwo' => 'addresslevel2a',
			'Powiat' => 'addresslevel3a',
			'Gmina' => 'addresslevel4a',
			'Miejscowosc' => 'addresslevel5a',
			'KodPocztowy' => 'addresslevel7a',
			'Ulica' => 'addresslevel8a',
			'NumerBudynku' => 'buildingnumbera',
			'NumerLokalu' => 'localnumbera',
			'FormaPrawna' => 'legal_form',
			'Kraj' => 'addresslevel1a',
		],
		'Leads' => [
			'Regon' => 'registration_number_2',
			'Nazwa' => 'company',
			'Wojewodztwo' => 'addresslevel2a',
			'Powiat' => 'addresslevel3a',
			'Gmina' => 'addresslevel4a',
			'Miejscowosc' => 'addresslevel5a',
			'KodPocztowy' => 'addresslevel7a',
			'Ulica' => 'addresslevel8a',
			'NumerBudynku' => 'buildingnumbera'
		],
		'Partners' => [
			'Nazwa' => 'subject',
			'Wojewodztwo' => 'addresslevel2a',
			'Powiat' => 'addresslevel3a',
			'Gmina' => 'addresslevel4a',
			'Miejscowosc' => 'addresslevel5a',
			'KodPocztowy' => 'addresslevel7a',
			'Ulica' => 'addresslevel8a',
			'NumerBudynku' => 'buildingnumbera'
		],
		'Vendors' => [
			'Nazwa' => 'vendorname',
			'Regon' => 'registration_number_2',
			'Wojewodztwo' => 'addresslevel2a',
			'Powiat' => 'addresslevel3a',
			'Gmina' => 'addresslevel4a',
			'Miejscowosc' => 'addresslevel5a',
			'KodPocztowy' => 'addresslevel7a',
			'Ulica' => 'addresslevel8a',
			'NumerBudynku' => 'buildingnumbera'
		],
		'Competition' => [
			'Nazwa' => 'subject',
			'Wojewodztwo' => 'addresslevel2a',
			'Powiat' => 'addresslevel3a',
			'Gmina' => 'addresslevel4a',
			'Miejscowosc' => 'addresslevel5a',
			'KodPocztowy' => 'addresslevel7a',
			'Ulica' => 'addresslevel8a',
			'NumerBudynku' => 'buildingnumbera'
		],
	];

	/**
	 * {@inheritdoc}
	 */
	public function isActive(): bool
	{
		return \App\YetiForce\Shop::check('YetiForcePlGus') && \in_array($this->moduleName, $this->allowedModules);
	}

	/**
	 * {@inheritdoc}
	 */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$vatId = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatId', 'Text'));
		$taxNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('taxNumber', 'Text'));
		$ncr = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('ncr', 'Text'));
		$response = [];
		$client = \App\RecordCollectors\Helper\GusClient::getInstance();
		try {
			$infoFromGus = $client->search($vatId, $ncr, $taxNumber);
			foreach ($infoFromGus as &$info) {
				$client->getAdvanceData($info);
				unset($info['SilosID'], $info['Typ']);
			}
			$moduleName = $this->request->getModule();
			$response['formFieldsToRecordMap'] = $this->formFieldsToRecordMap[$moduleName];
			if ($recordId = $this->request->getInteger('record')) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				$response['recordModel'] = $recordModel;
			}
			if ($infoFromGus) {
				$response['fields'] = $info;
			}
		} catch (\SoapFault $e) {
			\App\Log::warning($e->faultstring, 'RecordCollectors');
			$response['error'] = $e->faultstring;
		}
		return $response;
	}
}
