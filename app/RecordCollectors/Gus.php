<?php
/**
 * Gus class for downloading REGON registry open public data. For BIR 1.1 version.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package App
 *
 * @see https://api.stat.gov.pl/Home/RegonApi
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Gus record collector class.
 */
class Gus extends Base
{
	/** {@inheritdoc} */
	public static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi yfi-gus';

	/** {@inheritdoc} */
	public $label = 'GUS';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
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

	/** {@inheritdoc} */
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

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'Nazwa' => 'accountname',
			'SzczegolnaFormaPrawnaNazwa' => 'legal_form',
			'Regon' => 'registration_number_2',
			'Krs' => 'registration_number_1',
			'Nip' => 'vat_id',
			'Wojewodztwo' => 'addresslevel2a',
			'Powiat' => 'addresslevel3a',
			'Gmina' => 'addresslevel4a',
			'Miejscowosc' => 'addresslevel5a',
			'KodPocztowy' => 'addresslevel7a',
			'Ulica' => 'addresslevel8a',
			'NumerBudynku' => 'buildingnumbera',
			'NumerLokalu' => 'localnumbera',
			'Kraj' => 'addresslevel1a',
			'NumerTelefonu' => 'phone',
			'NumerFaksu' => 'fax',
			'AdresEmail' => 'email1',
			'AdresStronyInternetowej' => 'website',
		],
		'Leads' => [
			'Nazwa' => 'company',
			'SzczegolnaFormaPrawnaNazwa' => 'legal_form',
			'Regon' => 'registration_number_2',
			'Wojewodztwo' => 'addresslevel2a',
			'Powiat' => 'addresslevel3a',
			'Gmina' => 'addresslevel4a',
			'Miejscowosc' => 'addresslevel5a',
			'KodPocztowy' => 'addresslevel7a',
			'Ulica' => 'addresslevel8a',
			'NumerBudynku' => 'buildingnumbera',
			'NumerTelefonu' => 'phone',
			'NumerFaksu' => 'fax',
			'AdresEmail' => 'email',
			'AdresStronyInternetowej' => 'website',
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

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return parent::isActive() && \App\YetiForce\Shop::check('YetiForcePlGus');
	}

	/** {@inheritdoc} */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$vatId = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatId', 'Text'));
		$taxNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('taxNumber', 'Text'));
		$ncr = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('ncr', 'Text'));
		$response = [];
		$moduleName = $this->request->getModule();
		$client = \App\RecordCollectors\Helper\GusClient::getInstance($this->getParams($moduleName));
		try {
			$infoFromGus = $client->search($vatId, $ncr, $taxNumber);
			if ($recordId = $this->request->getInteger('record')) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				$response['recordModel'] = $recordModel;
				$fields = $recordModel->getModule()->getFields();
			} else {
				$fields = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
			}
			if ($infoFromGus && isset($this->formFieldsToRecordMap[$moduleName])) {
				$additional = $data = $skip = [];
				foreach ($infoFromGus as $key => &$row) {
					foreach ($this->formFieldsToRecordMap[$moduleName] as $apiName => $fieldName) {
						if (empty($fields[$fieldName]) || !$fields[$fieldName]->isActiveField()) {
							if (isset($fields[$fieldName]) && empty($skip[$fieldName]['label'])) {
								$skip[$fieldName]['label'] = \App\Language::translate($fields[$fieldName]->getFieldLabel(), $moduleName);
							} else {
								$skip[$fieldName]['label'] = $fieldName;
							}
							$skip[$fieldName]['data'][$key]['display'] = $row[$apiName] ?? '';
							unset($row[$apiName]);
							continue;
						}
						if (isset($row[$apiName])) {
							$value = $row[$apiName];
							unset($row[$apiName]);
							$fieldModel = $fields[$fieldName];
							if ($value && 'phone' === $fieldModel->getFieldDataType()) {
								$details = $fieldModel->getUITypeModel()->getPhoneDetails($value, 'PL');
								$value = $details['number'];
								if ($fieldName !== $details['fieldName']) {
									$fieldName = $details['fieldName'];
									$fieldModel = $fields[$fieldName];
								}
							}
							if (isset($fields[$fieldName]) && empty($data[$fieldName]['label'])) {
								$data[$fieldName]['label'] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
							} else {
								$skip[$fieldName]['label'] = $fieldName;
							}
							$data[$fieldName]['data'][$key] = [
								'raw' => $fieldModel->getEditViewDisplayValue($value),
								'display' => $fieldModel->getDisplayValue($value),
							];
						}
					}
					foreach ($row as $name => $value) {
						$additional[$name][$key] = \App\Purifier::encodeHtml($value);
					}
				}
				$response['fields'] = $data;
				$response['additional'] = $additional;
				$response['keys'] = array_keys($infoFromGus);
				$response['skip'] = $skip;
			}
		} catch (\SoapFault $e) {
			\App\Log::warning($e->faultstring, 'RecordCollectors');
			$response['error'] = $e->faultstring;
		}
		return $response;
	}

	/**
	 * Get params.
	 *
	 * @param string $moduleName
	 *
	 * @return string[]
	 */
	public function getParams(string $moduleName): array
	{
		$params = [];
		if (isset($this->formFieldsToRecordMap[$moduleName]['PKDPodstawowyKod']) || isset($this->formFieldsToRecordMap[$moduleName]['PKDPodstawowyKod']) || isset($this->formFieldsToRecordMap[$moduleName]['PKDPozostaleNazwy']) || isset($this->formFieldsToRecordMap[$moduleName]['PKDPozostaleKodyNazwy'])) {
			$params[] = 'pkd';
		}
		return $params;
	}
}
