<?php
/**
 * Gus class for downloading REGON registry open public data. For BIR 1.1 version.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
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
			'SzczegolnaFormaPrawna' => 'legal_form',
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
			'FormaPrawna' => 'legal_form',
			'Kraj' => 'addresslevel1a',
			'NumerTelefonu' => 'phone',
			'NumerFaksu' => 'fax',
			'AdresEmail' => 'email1',
		],
		'Leads' => [
			'Nazwa' => 'company',
			'SzczegolnaFormaPrawna' => 'legal_form',
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
		$client = \App\RecordCollectors\Helper\GusClient::getInstance();
		try {
			$infoFromGus = $client->search($vatId, $ncr, $taxNumber);
			foreach ($infoFromGus as &$info) {
				$client->getAdvanceData($info);
			}
			$moduleName = $this->request->getModule();
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
							if (empty($skip[$fieldName]['label'])) {
								$skip[$fieldName]['label'] = \App\Language::translate($fields[$fieldName]->getFieldLabel(), $moduleName);
							}
							$skip[$fieldName]['data'][$key]['display'] = $row[$apiName] ?? '';
							unset($row[$apiName]);
							continue;
						}
						if (isset($row[$apiName])) {
							if (empty($data[$fieldName]['label'])) {
								$data[$fieldName]['label'] = \App\Language::translate($fields[$fieldName]->getFieldLabel(), $moduleName);
							}
							$data[$fieldName]['data'][$key] = [
								'raw' => $row[$apiName],
								'display' => $fields[$fieldName]->getDisplayValue($row[$apiName]),
							];
							unset($row[$apiName]);
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
}
