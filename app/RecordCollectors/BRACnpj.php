<?php
/**
 * The Brazilian National Registry of Legal Entities API file.
 *
 * @package App
 *
 * @see https://www.receitaws.com.br/
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * The Brazilian National Registry of Legal Entities API class.
 */
class BRACnpj extends Base
{
	/** {@inheritdoc} */
	protected static $allowedModules = ['Accounts', 'Leads', 'Vendors', 'Partners', 'Competition'];

	/** {@inheritdoc} */
	public $icon = 'fas fa-futbol';

	/** {@inheritdoc} */
	public $label = 'LBL_BRA_CNPJ';

	/** {@inheritdoc} */
	public $displayType = 'FillFields';

	/** {@inheritdoc} */
	public $description = 'LBL_BRA_CNPJ_DESC';

	/** {@inheritdoc} */
	public $docUrl = 'https://www.receitaws.com.br/';

	/** {@inheritdoc} */
	protected $fields = [
		'cnpj' => [
			'labelModule' => 'Other.RecordCollector',
			'label' => 'LBL_BRA_CNPJ_NUMBER',
			'typeofdata' => 'V~O',
		]
	];

	/** {@inheritdoc} */
	protected $modulesFieldsMap = [
		'Accounts' => [
			'cnpj' => 'registration_number_1',
		]
	];

	/** {@inheritdoc} */
	public $formFieldsToRecordMap = [
		'Accounts' => [
			'nome' => 'accountname',
			'fantasia' => 'accountname',
			'email' => 'email1',
			'telefone' => 'phone',
			'capital_social' => 'annual_revenue',
			'atividade_principal0Code' => 'siccode',
			'cnpj' => 'registration_number_1',
			'numero' => 'buildingnumbera',
			'logradouro' => 'addresslevel8a',
			'cep' => 'addresslevel7a',
			'municipio' => 'addresslevel5a',
			'bairro' => 'addresslevel4a',
		],
		'Leads' => [
			'nome' => 'company',
			'fantasia' => 'company',
			'email' => 'email',
			'telefone' => 'phone',
			'cnpj' => 'registration_number_1',
			'numero' => 'buildingnumbera',
			'logradouro' => 'addresslevel8a',
			'cep' => 'addresslevel7a',
			'municipio' => 'addresslevel5a',
			'bairro' => 'addresslevel4a',
		],
		'Partners' => [
			'nome' => 'subject',
			'fantasia' => 'subject',
			'email' => 'email',
			'numero' => 'buildingnumbera',
			'logradouro' => 'addresslevel8a',
			'cep' => 'addresslevel7a',
			'municipio' => 'addresslevel5a',
			'bairro' => 'addresslevel4a',
		],
		'Vendors' => [
			'nome' => 'vendorname',
			'fantasia' => 'vendorname',
			'email' => 'email',
			'telefone' => 'phone',
			'cnpj' => 'registration_number_1',
			'numero' => 'buildingnumbera',
			'logradouro' => 'addresslevel8a',
			'cep' => 'addresslevel7a',
			'municipio' => 'addresslevel5a',
			'bairro' => 'addresslevel4a',
		],
		'Competition' => [
			'nome' => 'subject',
			'fantasia' => 'subject',
			'email' => 'email',
			'numero' => 'buildingnumbera',
			'logradouro' => 'addresslevel8a',
			'cep' => 'addresslevel7a',
			'municipio' => 'addresslevel5a',
			'bairro' => 'addresslevel4a',
		],
	];

	/** @var string CNJP sever address */
	private $url = 'https://receitaws.com.br/v1/cnpj/';

	/** {@inheritdoc} */
	public function search(): array
	{
		$cnpj = str_replace([' ', '/', '.', '-'], '', $this->request->getByType('cnpj', 'Text'));
		if (!$this->isActive() || empty($cnpj)) {
			return [];
		}

		$this->getDataFromApi($cnpj);
		if (!isset($this->response['error'])) {
			$this->loadData();
		}
		return $this->response;
	}

	/**
	 * Function fetching company data by CNPJ number.
	 *
	 * @param string $cnpj
	 *
	 * @return void
	 */
	private function getDataFromApi(string $cnpj): void
	{
		$response = [];
		try {
			$response = \App\RequestHttp::getClient()->get($this->url . $cnpj);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::warning($e->getMessage(), 'RecordCollectors');
			$this->response['error'] = $e->getMessage();
		}
		if (isset($response) && 'array' === gettype($response)) {
			$this->response['error'] = \App\Language::translate('LBL_BRA_CNPJ_ERROR', 'Other.RecordCollector');
		} else {
			$this->data = $this->parseData(\App\Json::decode($response->getBody()->getContents()));
		}
	}

	/**
	 * Function parsing data to fields from API.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function parseData(array $data): array
	{
		return \App\Utils::flattenKeys($data, 'ucfirst');
	}
}
