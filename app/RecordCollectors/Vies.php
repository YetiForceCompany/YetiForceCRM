<?php
/**
 * Vies record collector file.
 *
 * @package   App
 *
 * http://ec.europa.eu/taxation_customs/vies/checkVatTestService.wsdl
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Vies record collector class.
 */
class Vies extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'yfi yfi-vies';

	/** {@inheritdoc} */
	public $label = 'Vies';

	/** {@inheritdoc} */
	public $displayType = 'Summary';

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
			'typeofdata' => 'V~M'
		],
		'vatNumber' => [
			'labelModule' => '_Base',
			'label' => 'Vat ID',
			'typeofdata' => 'V~M'
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
		]
	];

	/**
	 * Vies server address.
	 *
	 * @var string
	 */
	protected $url = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	/** {@inheritdoc} */
	public function getFields(): array
	{
		$fields = parent::getFields();
		foreach (['addresslevel1a', 'addresslevel1b', 'addresslevel1c'] as $value) {
			if (!$this->request->isEmpty($value, true) && ($code = \App\Fields\Country::getCountryCode($this->request->getByType($value, 'Text')))) {
				$fields['countryCode']->set('fieldvalue', $code);
				break;
			}
		}
		return $fields;
	}

	/** {@inheritdoc} */
	public function search(): array
	{
		$vatNumber = str_replace([' ', ',', '.', '-'], '', $this->request->getByType('vatNumber', 'Text'));
		if (!$vatNumber) {
			return [];
		}
		$countryCode = $this->request->getByType('countryCode', 'Standard');
		$response = [];
		if ($client = new \SoapClient($this->url, \App\RequestHttp::getSoapOptions())) {
			$params = ['countryCode' => $countryCode, 'vatNumber' => $vatNumber];
			try {
				$r = $client->checkVat($params);
				if ($r->valid) {
					$response['fields'] = [
						'Vat ID' => $r->countryCode . $r->vatNumber,
						'LBL_COMPANY_NAME' => $r->name,
						'Address details' => $r->address,
					];
				}
			} catch (\SoapFault $e) {
				\App\Log::warning($e->faultstring, 'RecordCollectors');
				$response['error'] = $e->faultstring;
			}
		}
		return $response;
	}
}
