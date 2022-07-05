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
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors\Helper;

/**
 * Gus client class.
 */
class GusClient extends \SoapClient
{
	/** @var array Report names for entity. */
	const REPORT_NAME = [
		'F' => [
			'1' => 'BIR11OsFizycznaDzialalnoscCeidg',
			'2' => 'BIR11OsFizycznaDzialalnoscRolnicza',
			'3' => 'BIR11OsFizycznaDzialalnoscPozostala',
			'4' => 'BIR11OsFizycznaDzialalnoscSkreslonaDo20141108',
		],
		'LF' => 'BIR11OsFizycznaDzialalnoscSkreslonaDo20141108',
		'P' => 'BIR11OsPrawna',
		'LP' => 'BIR11JednLokalnaOsPrawnej',
	];

	/** @var string[] Mapping field from report to number local field in record. */
	const REPORT_TO_NUMBER_LOCAL = [
		'BIR11OsPrawna' => 'praw_adSiedzNumerNieruchomosci',
		'BIR11OsFizycznaDzialalnoscSkreslonaDo20141108' => 'fiz_adSiedzNumerNieruchomosci',
		'BIR11OsFizycznaDzialalnoscPozostala' => 'fiz_adSiedzNumerNieruchomosci',
		'BIR11OsFizycznaDzialalnoscRolnicza' => 'fiz_adSiedzNumerNieruchomosci',
		'BIR11OsFizycznaDzialalnoscCeidg' => 'fiz_adSiedzNumerNieruchomosci',
	];

	/** @var string[] Variable for mapping report names to value prefix. */
	const REPORT_PREFIX = [
		'BIR11OsPrawna' => 'praw_',
		'BIR11JednLokalnaOsPrawnej' => 'lokpraw_',
	];

	/** @var string[] PKD report map. */
	const PKD_REPORTS = [
		'BIR11OsPrawna' => 'BIR11OsPrawnaPkd',
		'BIR11OsFizycznaDzialalnoscCeidg' => 'BIR11OsFizycznaPkd',
		'BIR11OsFizycznaDzialalnoscRolnicza' => 'BIR11OsFizycznaPkd',
		'BIR11OsFizycznaDzialalnoscPozostala' => 'BIR11OsFizycznaPkd',
		'BIR11OsFizycznaDzialalnoscSkreslonaDo20141108' => 'BIR11OsFizycznaPkd',
		'BIR11JednLokalnaOsPrawnej' => 'BIR11JednLokalnaOsPrawnejPkd',
	];

	/** @var string Namespace for header. */
	const HEADER_NAMESPACE = 'http://www.w3.org/2005/08/addressing';

	/** @var string[] Client connection details. */
	const CONFIG = [
		'apiKey' => 'd2df36a7394c432e88ea',
		'addressToService' => 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc',
		'addressToWsdl' => 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/wsdl/UslugaBIRzewnPubl-ver11-prod.wsdl',
	];

	/** @var self[] Namespace for header. */
	public static $cache = [];

	/** @var string Client session. */
	private $sessionId;

	/** @var resource Stream context. */
	private $streamContext;

	/** @var array Params. */
	private $params;

	/**
	 * Get instance.
	 *
	 * @param array $params
	 *
	 * @return self
	 */
	public static function getInstance(array $params = []): self
	{
		$cacheKey = \App\Json::encode($params);
		if (isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$context = stream_context_create([]);
		$options = \App\RequestHttp::getSoapOptions();
		$options['soap_version'] = SOAP_1_2;
		$options['encoding'] = 'utf-8';
		$options['stream_context'] = $context;
		$instance = new self(self::CONFIG['addressToWsdl'], $options);
		$instance->streamContext = $context;
		$instance->params = $params;
		return self::$cache[$cacheKey] = $instance;
	}

	/** {@inheritdoc} */
	public function __doRequest($req, $location, $action, $version = SOAP_1_2, $oneWay = null): string
	{
		$response = parent::__doRequest($req, self::CONFIG['addressToService'], $action, $version);
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
		$totalFields = $fields = [];
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
		if (empty($this->sessionId)) {
			$header[] = new \SoapHeader(self::HEADER_NAMESPACE, 'Action', $this->getAddressToAction('Zaloguj'), 0);
			$header[] = new \SoapHeader(self::HEADER_NAMESPACE, 'To', self::CONFIG['addressToService'], 0);
			$this->__setSoapHeaders($header);
			$result = $this->Zaloguj(['pKluczUzytkownika' => self::CONFIG['apiKey']]);
			$this->sessionId = $result->ZalogujResult;
		}
	}

	/**
	 * End session - logout.
	 *
	 * @return void
	 */
	public function endSession()
	{
		if (empty($this->register)) {
			register_shutdown_function(function () {
				try {
					$header[] = new \SoapHeader(self::HEADER_NAMESPACE, 'Action', $this->getAddressToAction('Wyloguj'), 0);
					$header[] = new \SoapHeader(self::HEADER_NAMESPACE, 'To', self::CONFIG['addressToService'], 0);
					$this->__setSoapHeaders();
					$this->__setSoapHeaders($header);
					$this->Wyloguj(['pIdentyfikatorSesji' => $this->sessionId]);
				} catch (\Throwable $e) {
					\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
					throw $e;
				}
			});
			$this->register = true;
		}
	}

	/**
	 * Get data from API.
	 *
	 * @param string $type
	 * @param array  $params
	 *
	 * @return array
	 */
	public function getData(string $type, array $params): array
	{
		if (empty($this->sessionId)) {
			$this->startSession();
		}
		$header[] = new \SoapHeader(self::HEADER_NAMESPACE, 'Action', $this->getAddressToAction($type), true);
		$header[] = new \SoapHeader(self::HEADER_NAMESPACE, 'To', self::CONFIG['addressToService'], true);
		stream_context_set_option($this->streamContext, ['http' => ['header' => 'sid: ' . $this->sessionId]]);
		$this->__setSoapHeaders();
		$this->__setSoapHeaders($header);
		$result = $this->{$type}($params);
		return $this->parseResponse($result->{"{$type}Result"});
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
		$response = $this->getData('DaneSzukajPodmioty', ['pParametryWyszukiwania' => ['Nip' => $vatId,  'Krs' => $ncr, 'Regon' => $taxNumber]]);
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
		if (isset($response['Typ'], $response['SilosID']) && ($reportName = $this->getReportName($response['Typ'], $response['SilosID']))) {
			$responseFromGus = $this->getData('DanePobierzPelnyRaport', ['pRegon' => $response['Regon'], 'pNazwaRaportu' => $reportName]);
			if (empty($responseFromGus)) {
				return;
			}
			$responseFromGus = reset($responseFromGus);
			$prefixName = self::REPORT_PREFIX[$reportName] ?? 'fiz_';
			if ('fiz_' === $prefixName) {
				$resultFiz = $this->DanePobierzPelnyRaport(['pRegon' => $response['Regon'], 'pNazwaRaportu' => 'BIR11OsFizycznaDaneOgolne']);
				$responseFromGusFiz = $this->parseResponse($resultFiz->DanePobierzPelnyRaportResult);
				$responseFromGusFiz = reset($responseFromGusFiz);
				$responseFromGus = array_merge($responseFromGus, $responseFromGusFiz);
			}
			$response['NumerBudynku'] = $responseFromGus[self::REPORT_TO_NUMBER_LOCAL[$reportName]] ?? '';
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
			if (\in_array('pkd', $this->params)) {
				$result = $this->DanePobierzPelnyRaport(['pRegon' => $response['Regon'], 'pNazwaRaportu' => self::PKD_REPORTS[$reportName]]);
				$additional = [];
				foreach ($this->parseResponse($result->DanePobierzPelnyRaportResult) as $value) {
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
		$name = '';
		if ('F' === $type && isset(self::REPORT_NAME[$type][$silosId])) {
			$name = self::REPORT_NAME[$type][$silosId];
		} elseif (isset(self::REPORT_NAME[$type])) {
			$name = self::REPORT_NAME[$type];
		}
		return $name;
	}
}
