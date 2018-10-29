<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$newXmlSrc = '';

		$numberOfDays = 1;
		$iterationsLimit = 60;
		$stateA = false;
		while (!$stateA) {
			$url = $tableUrl . $dateCur . '/?format=json';
			try {
				$tryTable = (new \GuzzleHttp\Client())->get($url, ['timeout' => 5, 'connect_timeout' => 1]);
				if ($tryTable->getStatusCode() == 200) {
					$stateA = true;
					$newXmlSrc = $url;
					$tableBody = $tryTable->getBody();
				}
			} catch (\Throwable $exc) {
				throw new \App\Exceptions\IntegrationException('Error when downloading NBP currency table: ' . $url . ' | ' . $exc->getMessage(), __CLASS__);
			}

			if (!$stateA) {
				$newDate = strtotime("-$numberOfDays day", strtotime($dateCur));
				$dateCur = date('Y-m-d', $newDate);
				++$numberOfDays;
				if ($numberOfDays > $iterationsLimit) {
					break;
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
					if ($rawCurrency['code'] === 'XDR') {
						continue;
					}
					$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($rawCurrency['code'])] = $rawCurrency['code'];
				}
			} else {
				throw new \App\Exceptions\IntegrationException('Can not parse server response' . $newXmlSrc);
			}
		} else {
			throw new \App\Exceptions\IntegrationException('Can not connect to the server' . $newXmlSrc);
		}
		return $supportedCurrencies;
	}

	// Returns banks main currency

	public function getMainCurrencyCode()
	{
		return 'PLN';
	}

	/*
	 * Fetch exchange rates
	 * @param <Array> $currencies - list of systems active currencies
	 * @param <Date> $date - date for which exchange is fetched
	 * @param boolean $cron - if true then it is fired by server and crms currency conversion rates are updated
	 */

	public function getRates($otherCurrencyCode, $dateParam, $cron = false)
	{
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
		$selectedBank = $moduleModel->getActiveBankId();
		$yesterday = date('Y-m-d', strtotime('-1 day'));

		// check if data is correct, currency rates can be retrieved only for working days
		$lastWorkingDay = vtlib\Functions::getLastWorkingDay($yesterday);

		$today = date('Y-m-d');
		$mainCurrency = vtlib\Functions::getDefaultCurrencyInfo()['currency_code'];
		$dateCur = $dateParam;
		$chosenYear = date('Y', strtotime($dateCur));
		$date = substr(str_replace('-', '', $dateCur), 2);

		if (date('Y') == $chosenYear) {
			$txtSrc = 'http://www.nbp.pl/kursy/xml/dir.txt';
		} else {
			$txtSrc = 'http://www.nbp.pl/kursy/xml/dir' . $chosenYear . '.txt';
		}
		$xmlSrc = 'http://nbp.pl/kursy/xml/';
		$newXmlSrc = '';

		$file = file($txtSrc);
		$fileNum = count($file);
		$numberOfDays = 1;
		$stateA = false;

		while (!$stateA && $file) {
			for ($i = 0; $i < $fileNum; ++$i) {
				$lineStart = strstr($file[$i], $date, true);
				if ($lineStart && $lineStart[0] == 'a') {
					$stateA = true;
					$newXmlSrc = $xmlSrc . $lineStart . $date . '.xml';
				}
			}
			if ($stateA === false) {
				$newDate = strtotime("-$numberOfDays day", strtotime($dateCur));
				$newDate = date('Y-m-d', $newDate);

				$date = str_replace('-', '', $newDate);
				$date = substr($date, 2);
				++$numberOfDays;
			}
		}

		$xml = simplexml_load_file($newXmlSrc);

		$xmlObj = $xml->children();

		$num = count($xmlObj->pozycja);
		$datePublicationOfFile = (string) $xmlObj->data_publikacji;

		$exchangeRate = 1.0;
		// if currency is diffrent than PLN we need to calculate rate for converting other currencies to this one from PLN
		if ($mainCurrency !== $this->getMainCurrencyCode()) {
			for ($i = 0; $i <= $num; ++$i) {
				if ((string) $xmlObj->pozycja[$i]->kod_waluty === $mainCurrency) {
					$exchangeRate = (float) str_replace(',', '.', $xmlObj->pozycja[$i]->kurs_sredni);
				}
			}
		}
		for ($i = 0; $i <= $num; ++$i) {
			if (!isset($xmlObj->pozycja[$i])) {
				continue;
			}
			$currency = (string) $xmlObj->pozycja[$i]->kod_waluty;
			foreach ($otherCurrencyCode as $key => $currId) {
				if ($key == $currency && $currency != $mainCurrency) {
					$exchange = str_replace(',', '.', $xmlObj->pozycja[$i]->kurs_sredni);
					$exchange = ((float) $exchange) / ((float) $xmlObj->pozycja[$i]->przelicznik);
					$exchangeVtiger = $exchangeRate / $exchange;
					$exchange = $exchangeRate ? ($exchange / $exchangeRate) : 0;

					if ($cron === true || ((strtotime($dateParam) == strtotime($today)) || (strtotime($dateParam) == strtotime($lastWorkingDay)))) {
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
				if ($cron === true || ((strtotime($dateParam) == strtotime($today)) || (strtotime($dateParam) == strtotime($lastWorkingDay)))) {
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
