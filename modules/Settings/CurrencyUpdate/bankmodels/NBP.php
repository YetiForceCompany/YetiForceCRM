<?php
/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 */

/**
 * Class for connection to Narodowy Bank Polski currency exchange rates.
 */
class Settings_CurrencyUpdate_NBP_BankModel extends Settings_CurrencyUpdate_AbstractBank_Model
{
	// Returns bank name

	public function getName()
	{
		return 'NBP';
	}

	// Returns url sources from where exchange rates are taken from

	public function getSource()
	{
		return ['http://nbp.pl/kursy/xml/LastA.xml'];
	}

	// Returns list of currencies supported by this bank

	public function getSupportedCurrencies()
	{
		$supportedCurrencies = [];
		$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($this->getMainCurrencyCode())] = $this->getMainCurrencyCode();
		$dateCur = date('Y-m-d', strtotime('last monday'));
		$tableUrl = 'http://api.nbp.pl/api/exchangerates/tables/a/';

		$numberOfDays = 1;
		$iterationsLimit = 60;
		$stateA = false;
		while (!$stateA) {
			$url = $tableUrl . $dateCur . '/?format=json';
			try {
				\App\Log::beginProfile("GET|NBP::getSupportedCurrencies|{$url}", 'CurrencyUpdate');
				$tryTable = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->get($url, ['timeout' => 20, 'connect_timeout' => 10]);
				\App\Log::endProfile("GET|NBP::getSupportedCurrencies|{$url}", 'CurrencyUpdate');
				if (200 == $tryTable->getStatusCode()) {
					$stateA = true;
					$tableBody = $tryTable->getBody();
				}
			} catch (\Throwable $ex) {
				\App\Log::info('Error during downloading table: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'CurrencyUpdate');
			}
			if (!$stateA) {
				$newDate = strtotime("-$numberOfDays day", strtotime($dateCur));
				$dateCur = date('Y-m-d', $newDate);
				++$numberOfDays;
				if ($numberOfDays > $iterationsLimit) {
					throw new \App\Exceptions\IntegrationException('ERR_ITERATIONS_LIMIT_EXCEEDED');
				}
			}
		}
		if ($stateA && $tableBody) {
			$json = \App\Json::decode($tableBody);
			if (!empty($json) && !empty($json[0]) && !empty($json[0]['rates'])) {
				foreach ($json[0]['rates'] as $rawCurrency) {
					if (empty($rawCurrency['currency'])) {
						continue;
					}
					if ('XDR' === $rawCurrency['code']) {
						continue;
					}
					$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($rawCurrency['code'])] = $rawCurrency['code'];
				}
			} else {
				\App\Log::error('Cannot parse server response' . $tableBody, __METHOD__);
			}
		} else {
			throw new \App\Exceptions\IntegrationException('ERR_CANNOT_CONNECT_TO_REMOTE' . $tableBody);
		}
		return $supportedCurrencies;
	}

	// Returns banks main currency

	public function getMainCurrencyCode()
	{
		return 'PLN';
	}

	/**
	 * Fetch exchange rates.
	 *
	 * @param <Array> $currencies        - list of systems active currencies
	 * @param <Date>  $date              - date for which exchange is fetched
	 * @param bool    $cron              - if true then it is fired by server and crms currency conversion rates are updated
	 * @param mixed   $otherCurrencyCode
	 * @param mixed   $dateParam
	 */
	public function getRates($otherCurrencyCode, $dateParam, $cron = false)
	{
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
		$selectedBank = $moduleModel->getActiveBankId();
		$yesterday = date('Y-m-d', strtotime('-1 day'));
		$dateCur = $dateParam;
		// check if data is correct, currency rates can be retrieved only for working days
		$lastWorkingDay = vtlib\Functions::getLastWorkingDay($yesterday);

		$today = date('Y-m-d');
		$mainCurrency = \App\Fields\Currency::getDefault()['currency_code'];
		$tableUrl = 'http://api.nbp.pl/api/exchangerates/tables/a/';

		$numberOfDays = 1;
		$iterationsLimit = 60;
		$stateA = false;
		while (!$stateA) {
			$url = $tableUrl . $dateCur . '/?format=json';
			try {
				\App\Log::beginProfile("GET|NBP|{$url}", __NAMESPACE__);
				$tryTable = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->get($url, ['timeout' => 20, 'connect_timeout' => 10]);
				\App\Log::endProfile("GET|NBP|{$url}", __NAMESPACE__);
				if (200 == $tryTable->getStatusCode()) {
					$stateA = true;
					$tableBody = $tryTable->getBody();
				}
			} catch (\Throwable $exc) {
			}
			if (false === $stateA) {
				$dateCur = strtotime("-$numberOfDays day", strtotime($dateCur));
				$dateCur = date('Y-m-d', $dateCur);
				++$numberOfDays;
				if ($numberOfDays > $iterationsLimit) {
					break;
				}
			}
		}

		$json = \App\Json::decode($tableBody);
		$datePublicationOfFile = (string) $json[0]['effectiveDate'];

		$exchangeRate = 1.0;
		// if currency is diffrent than PLN we need to calculate rate for converting other currencies to this one from PLN
		if ($mainCurrency !== $this->getMainCurrencyCode()) {
			foreach ($json[0]['rates'] as $item) {
				if ($item['code'] === $mainCurrency) {
					$exchangeRate = (float) $item['mid'];
				}
			}
		}
		foreach ($json[0]['rates'] as $item) {
			$currency = $item['code'];
			foreach ($otherCurrencyCode as $key => $currId) {
				if ($key == $currency && $currency != $mainCurrency) {
					$exchange = $item['mid'];
					$exchangeVtiger = $exchangeRate / $exchange;
					$exchange = $exchangeRate ? ($exchange / $exchangeRate) : 0;
					if (true === $cron || ((strtotime($dateParam) == strtotime($today)) || (strtotime($dateParam) == strtotime($lastWorkingDay)))) {
						$moduleModel->setCRMConversionRate($currency, $exchangeVtiger);
					}
					$existingId = $moduleModel->getCurrencyRateId($currId, $datePublicationOfFile, $selectedBank);
					if ($existingId > 0) {
						$moduleModel->updateCurrencyRate($existingId, $exchange);
					} else {
						$moduleModel->addCurrencyRate($currId, $datePublicationOfFile, $exchange, $selectedBank);
					}
				}
			}
		}

		// currency diffrent than PLN, we need to add manually PLN rates
		if ($mainCurrency != $this->getMainCurrencyCode()) {
			$exchange = $exchangeRate ? (1.00000 / $exchangeRate) : 0;
			$mainCurrencyId = false;
			foreach ($otherCurrencyCode as $code => $id) {
				if ($code == $this->getMainCurrencyCode()) {
					$mainCurrencyId = $id;
				}
			}

			if ($mainCurrencyId) {
				if (true === $cron || ((strtotime($dateParam) == strtotime($today)) || (strtotime($dateParam) == strtotime($lastWorkingDay)))) {
					$moduleModel->setCRMConversionRate($this->getMainCurrencyCode(), $exchangeRate);
				}

				$existingId = $moduleModel->getCurrencyRateId($mainCurrencyId, $datePublicationOfFile, $selectedBank);

				if ($existingId > 0) {
					$moduleModel->updateCurrencyRate($existingId, $exchange);
				} else {
					$moduleModel->addCurrencyRate($mainCurrencyId, $datePublicationOfFile, $exchange, $selectedBank);
				}
			}
		}
	}
}
