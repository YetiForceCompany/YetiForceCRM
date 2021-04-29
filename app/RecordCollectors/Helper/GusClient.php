<?php
/**
 * Gus client.
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

namespace App\RecordCollectors\Helper;

/**
 * Gus client class.
 */
class GusClient extends \SoapClient
{
	/**
	 * Report names for entity.
	 *
	 * @var array
	 */
	private static $reportName = [
		'F' => [
			'1' => 'BIR11OsFizycznaDzialalnoscCeidg',
			'2' => 'BIR11OsFizycznaDzialalnoscRolnicza',
			'3' => 'BIR11OsFizycznaDzialalnoscPozostala',
			'4' => 'BIR11OsFizycznaDzialalnoscSkreslonaDo20141108'
		],
		'LF' => 'BIR11OsFizycznaDzialalnoscSkreslonaDo20141108',
		'P' => 'BIR11OsPrawna',
		'LP' => 'BIR11JednLokalnaOsPrawnej'
	];
	/**
	 * Mapping field from report to number local field in record.
	 *
	 * @var array
	 */
	private static $reportToNumberLocal = [
		'BIR11OsPrawna' => 'praw_adSiedzNumerNieruchomosci',
		'BIR11OsFizycznaDzialalnoscSkreslonaDo20141108' => 'fiz_adSiedzNumerNieruchomosci',
		'BIR11OsFizycznaDzialalnoscPozostala' => 'fiz_adSiedzNumerNieruchomosci',
		'BIR11OsFizycznaDzialalnoscRolnicza' => 'fiz_adSiedzNumerNieruchomosci',
		'BIR11OsFizycznaDzialalnoscCeidg' => 'fiz_adSiedzNumerNieruchomosci'
	];
	/**
	 * Variable for mapping report names to value prefix.
	 *
	 * @var string[]
	 */
	private static $reportPrefix = [
		'BIR11OsPrawna' => 'praw_',
		'BIR11JednLokalnaOsPrawnej' => 'lokpraw_',
	];
	/**
	 * PKD report map.
	 *
	 * @var string[]
	 */
	private static $pkdReports = [
		'BIR11OsPrawna' => 'BIR11OsPrawnaPkd',
		'BIR11OsFizycznaDzialalnoscCeidg' => 'BIR11OsFizycznaPkd',
		'BIR11OsFizycznaDzialalnoscRolnicza' => 'BIR11OsFizycznaPkd',
		'BIR11OsFizycznaDzialalnoscPozostala' => 'BIR11OsFizycznaPkd',
		'BIR11OsFizycznaDzialalnoscSkreslonaDo20141108' => 'BIR11OsFizycznaPkd',
		'BIR11JednLokalnaOsPrawnej' => 'BIR11JednLokalnaOsPrawnejPkd',
	];
	/**
	 * Client session.
	 *
	 * @var string
	 */
	public $sessionId;
	/**
	 * Stream context.
	 */
	public $streamContext;
	/**
	 * Namespace for header.
	 *
	 * @var string
	 */
	public static $namespaceHeader = 'http://www.w3.org/2005/08/addressing';
	/**
	 * Params.
	 *
	 * @var array
	 */
	public $params;
	/**
	 * Client contection details.
	 *
	 * @var array
	 */
	public static $config = [
		'apiKey' => 'd2df36a7394c432e88ea',
		'addressToService' => 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc',
		'addresToWsdl' => 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/wsdl/UslugaBIRzewnPubl-ver11-prod.wsdl',
	];

	/**
	 * Get instance.
	 *
	 * @param array $params
	 *
	 * @return self
	 */
	public static function getInstance(array $params = []): self
	{
		$context = stream_context_create([]);
		$options = \App\RequestHttp::getSoapOptions();
		$options['soap_version'] = SOAP_1_2;
		$options['encoding'] = 'utf-8';
		$options['stream_context'] = $context;
		$instance = new self(self::$config['addresToWsdl'], $options);
		$instance->streamContext = $context;
		$instance->params = $params;
		return $instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __doRequest($req, $location, $action, $version = SOAP_1_2, $oneWay = null): string
	{
		$response = parent::__doRequest($req, self::$config['addressToService'], $action, $version);
		$matches = [];
		preg_match('/<s:Envelope.*<\\/s:Envelope>/s', $response, $matches);
		return $matches[0];
	}

	/**
	 * Get address to action.
	 *
	 * @param string $action
	 *
	 * @return string
	 */
	public function getAddressToAction(string $action): string
	{
		return 'http://CIS/BIR/PUBL/2014/07/IUslugaBIRzewnPubl/' . $action;
	}

	/**
	 * Parsing response.
	 *
	 * @param string $response
	 *
	 * @return array
	 */
	public function parseResponse(string $response): array
	{
		preg_match_all('/(<([\\w]+)[^>]*>)(.*?)(<\\/\\2>)/', $response, $matches, PREG_SET_ORDER);
		$fields = [];
		$totalFields = [];
		foreach ($matches as $val) {
			if (isset($fields[$val[2]])) {
				$totalFields[] = $fields;
				$fields = [];
			}
			$fields[$val[2]] = \App\Purifier::decodeHtml($val[3]);
		}
		$totalFields[] = $fields;
		return $totalFields;
	}

	/**
	 * Start session - login.
	 *
	 * @return void
	 */
	public function startSession()
	{
		$header[] = new \SoapHeader(self::$namespaceHeader, 'Action', $this->getAddressToAction('Zaloguj'), 0);
		$header[] = new \SoapHeader(self::$namespaceHeader, 'To', self::$config['addressToService'], 0);
		$this->__setSoapHeaders($header);
		$params = ['pKluczUzytkownika' => self::$config['apiKey']];
		$result = $this->Zaloguj($params);
		$this->sessionId = $result->ZalogujResult;
	}

	/**
	 * End session - logout.
	 *
	 * @return void
	 */
	public function endSession()
	{
		$header[] = new \SoapHeader(self::$namespaceHeader, 'Action', $this->getAddressToAction('Wyloguj'), 0);
		$header[] = new \SoapHeader(self::$namespaceHeader, 'To', self::$config['addressToService'], 0);
		$this->__setSoapHeaders();
		$this->__setSoapHeaders($header);
		$params = ['pIdentyfikatorSesji' => $this->sessionId];
		$this->Wyloguj($params);
	}

	/**
	 * Search entity.
	 *
	 * @param string|null $vatId
	 * @param string|null $ncr
	 * @param string|null $taxNumber
	 *
	 * @return array
	 */
	public function search(?string $vatId, ?string $ncr = null, ?string $taxNumber = null): array
	{
		if (!$vatId && !$taxNumber && !$ncr) {
			return [];
		}
		$this->startSession();
		$header[] = new \SoapHeader(self::$namespaceHeader, 'Action', $this->getAddressToAction('DaneSzukajPodmioty'), true);
		$header[] = new \SoapHeader(self::$namespaceHeader, 'To', self::$config['addressToService'], true);
		stream_context_set_option($this->streamContext, ['http' => ['header' => 'sid: ' . $this->sessionId]]);
		$this->__setSoapHeaders();
		$this->__setSoapHeaders($header);
		$result = $this->DaneSzukajPodmioty(['pParametryWyszukiwania' => ['Nip' => $vatId,  'Krs' => $ncr, 'Regon' => $taxNumber]]);
		$response = $this->parseResponse($result->DaneSzukajPodmiotyResult);
		foreach ($response as &$info) {
			$this->getAdvanceData($info);
		}
		$this->endSession();
		return $response;
	}

	/**
	 * Get data for record fields from gus reports.
	 *
	 * @param array $response
	 *
	 * @return void
	 */
	public function getAdvanceData(array &$response): void
	{
		if (isset($response['Typ'], $response['SilosID']) && $reportName = $this->getReportName($response['Typ'], $response['SilosID'])) {
			$header[] = new \SoapHeader(self::$namespaceHeader, 'Action', $this->getAddressToAction('DanePobierzPelnyRaport'), true);
			$header[] = new \SoapHeader(self::$namespaceHeader, 'To', self::$config['addressToService'], true);
			$this->__setSoapHeaders();
			$this->__setSoapHeaders($header);
			$result = $this->DanePobierzPelnyRaport(['pRegon' => $response['Regon'], 'pNazwaRaportu' => $reportName]);
			$responseFromGus = $this->parseResponse($result->DanePobierzPelnyRaportResult);
			if (empty($responseFromGus)) {
				return;
			}
			$responseFromGus = reset($responseFromGus);
			$prefixName = self::$reportPrefix[$reportName] ?? 'fiz_';
			$response['NumerBudynku'] = $responseFromGus[self::$reportToNumberLocal[$reportName]] ?? '';
			$response['NumerLokalu'] = $responseFromGus[$prefixName . 'adSiedzNumerLokalu'] ?? '';
			$response['Krs'] = $responseFromGus[$prefixName . 'numerWrejestrzeEwidencji'] ?? '';
			$response['Kraj'] = $responseFromGus[$prefixName . 'adSiedzKraj_Nazwa'] ?? '';
			$response['Kraj'] = 'POLSKA' === $response['Kraj'] ? 'Poland' : $response['Kraj'];
			$response['NumerTelefonu'] = $responseFromGus[$prefixName . 'numerTelefonu'] ?? '';
			$response['NumerFaksu'] = $responseFromGus[$prefixName . 'numerFaksu'] ?? '';
			$response['AdresEmail'] = mb_strtolower($responseFromGus[$prefixName . 'adresEmail'] ?? '');
			$response['AdresStronyInternetowej'] = mb_strtolower($responseFromGus[$prefixName . 'adresStronyinternetowej'] ?? '');
			$response['PodstawowaFormaPrawnaNazwa'] = mb_convert_case($responseFromGus[$prefixName . 'podstawowaFormaPrawna_Nazwa'] ?? '', MB_CASE_TITLE, 'UTF-8');
			$response['PodstawowaFormaPrawnaKod'] = $responseFromGus[$prefixName . 'podstawowaFormaPrawna_Symbol'] ?? '';
			$response['PodstawowaFormaPrawna'] = "{$response['PodstawowaFormaPrawnaKod']} - {$response['PodstawowaFormaPrawnaNazwa']}";
			$response['SzczegolnaFormaPrawnaNazwa'] = mb_convert_case($responseFromGus[$prefixName . 'szczegolnaFormaPrawna_Nazwa'] ?? '', MB_CASE_TITLE, 'UTF-8');
			$response['SzczegolnaFormaPrawnaKod'] = $responseFromGus[$prefixName . 'szczegolnaFormaPrawna_Symbol'] ?? '';
			$response['SzczegolnaFormaPrawna'] = "{$response['SzczegolnaFormaPrawnaKod']} - {$response['SzczegolnaFormaPrawnaNazwa']}";
			$response['DataRozpoczeciaDzialalnosci'] = $responseFromGus[$prefixName . 'dataRozpoczeciaDzialalnosci'] ?? '';
			$response['FormaFinansowania'] = $responseFromGus[$prefixName . 'formaFinansowania_Nazwa'] ?? '';
			$response['FormaWlasnosci'] = $responseFromGus[$prefixName . 'formaWlasnosci_Nazwa'] ?? '';
			$response['DataPowstania'] = $responseFromGus[$prefixName . 'dataPowstania'] ?? '';
			$response['DataRozpoczeciaDzialalnosci'] = $responseFromGus[$prefixName . 'dataRozpoczeciaDzialalnosci'] ?? '';
			$response['DataWpisuDoREGON'] = $responseFromGus[$prefixName . 'dataWpisuDoREGON'] ?? '';
			$response['DataZawieszeniaDzialalnosci'] = $responseFromGus[$prefixName . 'dataZawieszeniaDzialalnosci'] ?? '';
			$response['DataWznowieniaDzialalnosci'] = $responseFromGus[$prefixName . 'dataWznowieniaDzialalnosci'] ?? '';
			$response['DataZaistnieniaZmiany'] = $responseFromGus[$prefixName . 'dataZaistnieniaZmiany'] ?? '';
			$response['DataZakonczeniaDzialalnosci'] = $responseFromGus[$prefixName . 'dataZakonczeniaDzialalnosci'] ?? '';
			$response['DataWpisuDoRejestruEwidencji'] = $responseFromGus[$prefixName . 'dataWpisuDoRejestruEwidencji'] ?? '';
			$response['DataSkresleniazRegon'] = $responseFromGus[$prefixName . 'dataSkresleniazRegon'] ?? '';
			if (isset($responseFromGus[$prefixName . 'nip'])) {
				$response['Nip'] = $responseFromGus[$prefixName . 'nip'];
			}
			if ('fiz_' === $prefixName) {
				$resultFiz = $this->DanePobierzPelnyRaport(['pRegon' => $response['Regon'], 'pNazwaRaportu' => 'BIR11OsFizycznaDaneOgolne']);
				$responseFromGusFiz = $this->parseResponse($resultFiz->DanePobierzPelnyRaportResult);
				$response['Nip'] = $responseFromGusFiz[0][$prefixName . 'nip'];
			}
			if (\in_array('pkd', $this->params)) {
				$result = $this->DanePobierzPelnyRaport(['pRegon' => $response['Regon'], 'pNazwaRaportu' => self::$pkdReports[$reportName]]);
				$additional = [];
				$responsePkd = $this->parseResponse($result->DanePobierzPelnyRaportResult);
				foreach ($responsePkd as $value) {
					$name = mb_convert_case($value[$prefixName . 'pkdNazwa'] ?? '', MB_CASE_TITLE, 'UTF-8');
					if ($value[$prefixName . 'pkdPrzewazajace']) {
						$response['PKDPodstawowyKod'] = $value[$prefixName . 'pkdKod'];
						$response['PKDPodstawowyNazwa'] = $name;
					} else {
						$additional[] = "{$value[$prefixName . 'pkdKod']} - {$name}";
					}
				}
				$response['PKDPozostale'] = implode(' ## ', $additional);
			}
		} else {
			$response = [];
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @param string $type
	 * @param string $silosId
	 *
	 * @return string
	 */
	private function getReportName(string $type, string $silosId): string
	{
		if ('F' === $type) {
			if (isset(self::$reportName[$type][$silosId])) {
				return self::$reportName[$type][$silosId];
			}
		}
		if (isset(self::$reportName[$type])) {
			return self::$reportName[$type];
		}
		return '';
	}
}
