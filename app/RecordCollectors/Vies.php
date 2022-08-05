<?php
/**
 * Vies record collector file.
 *
 * @see https://ec.europa.eu/taxation_customs/vies/checkVatTestService.wsdl
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Vies record collector class.
 */
class Vies extends Base
{
	/** {@inheritdoc} */
	public $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi yfi-vies';

	/** {@inheritdoc} */
	public $label = 'LBL_VIES';

	/** {@inheritdoc} */
	public $displayType = 'Summary';

	/** {@inheritdoc} */
	public $description = 'LBL_VIES_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://ec.europa.eu/taxation_customs/vies/technicalInformation.html';

	/** @var string Vies server address. */
	protected $url = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	/** {@inheritdoc} */
	protected $fields = [
		'countryCode' => [
			'label' => 'Country',
			'labelModule' => '_Base',
			'picklistModule' => 'Other.Country',
			'uitype' => 16,
			'picklistValues' => [
				'AT' => 'Austria',
				'BE' => 'Belgium',
				'BG' => 'Bulgaria',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DE' => 'Germany',
				'DK' => 'Denmark',
				'EE' => 'Estonia',
				'EL' => 'Greece',
				'ES' => 'Spain',
				'FI' => 'Finland',
				'FR' => 'France',
				'GB' => 'United Kingdom',
				'HR' => 'Croatia',
				'HU' => 'Hungary',
				'IE' => 'Ireland',
				'IT' => 'Italy',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'LV' => 'Latvia',
				'MT' => 'Malta',
				'NL' => 'Netherlands',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'RO' => 'Romania',
				'SE' => 'Sweden',
				'SI' => 'Slovenia',
				'SK' => 'Slovakia',
			],
			'typeofdata' => 'V~M',
		],
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'Vat ID',
			'typeofdata' => 'V~M',
		],
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'vatNumber' => 'vat_id',
		],
		'Leads' => [
			'vatNumber' => 'vat_id',
		],
		'Vendors' => [
			'vatNumber' => 'vat_id',
		],
		'Competition' => [
			'vatNumber' => 'vat_id',
		],
	];

	/** {@inheritdoc} */
	public function getFields(): array
	{
		$fieldsModels = parent::getFields();
		foreach (['addresslevel1a', 'addresslevel1b', 'addresslevel1c'] as $value) {
			if (!$this->request->isEmpty($value, true) && ($code = \App\Fields\Country::getCountryCode($this->request->getByType($value, 'Text')))) {
				$fieldsModels['countryCode']->set('fieldvalue', $code);
				break;
			}
		}
		return $fieldsModels;
	}

	/** {@inheritdoc} */
	public function search(): array
	{
		if (!$this->isActive()) {
			return [];
		}
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		if (!$vatNumber) {
			return [];
		}
		$countryCode = $this->request->getByType('countryCode', 'Standard');
		$response = [];
		try {
			if ($client = new \SoapClient($this->url, \App\RequestHttp::getSoapOptions())) {
				$r = $client->checkVatApprox([
					'countryCode' => $countryCode,
					'vatNumber' => $vatNumber,
					'requesterCountryCode' => $countryCode,
					'requesterVatNumber' => $vatNumber
				]);
				if ($r->valid) {
					$response['fields'] = [
						'Country' => $r->countryCode,
						'Vat ID' => $r->countryCode . $r->vatNumber,
						'LBL_COMPANY_NAME' => $r->traderName,
						'Address details' => $r->traderAddress,
						'LBL_REQUEST_DATE' => $r->requestDate,
						'LBL_REQUEST_ID' => $r->requestIdentifier
					];
				}
			}
		} catch (\SoapFault $e) {
			\App\Log::warning($e->faultstring, 'RecordCollectors');
			$response['error'] = $e->faultstring;
		}
		return $response;
	}
}
