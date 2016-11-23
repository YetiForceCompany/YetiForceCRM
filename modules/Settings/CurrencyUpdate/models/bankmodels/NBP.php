<?php
/**
 * @package YetiForce.models
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */

/**
 * Class for connection to Narodowy Bank Polski currency exchange rates
 */
class Settings_CurrencyUpdate_models_NBP_BankModel extends Settings_CurrencyUpdate_AbstractBank_Model
{
	/*
	 * Returns bank name
	 */

	public function getName()
	{
		return 'NBP';
	}
	/*
	 * Returns url sources from where exchange rates are taken from
	 */

	public function getSource()
	{
		return ['http://nbp.pl/kursy/xml/LastA.xml'];
	}
	/*
	 * Returns list of currencies supported by this bank
	 */

	public function getSupportedCurrencies()
	{
		$supportedCurrencies = [];
		$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($this->getMainCurrencyCode())] = $this->getMainCurrencyCode();
		$dateCur = date('Y-m-d', strtotime('last monday'));
		$date = str_replace('-', '', $dateCur);
		$date = substr($date, 2);

		$txtSrc = 'http://www.nbp.pl/kursy/xml/dir.txt';
		$xmlSrc = 'http://nbp.pl/kursy/xml/';
		$newXmlSrc = '';

		$file = file($txtSrc);
		$fileNum = count($file);
		$numberOfDays = 1;
		$stateA = false;

		while (!$stateA) {
			for ($i = 0; $i < $fileNum; $i++) {
				$lineStart = strstr($file[$i], $date, true);
				if ($lineStart && $lineStart[0] == 'a') {
					$stateA = true;
					$newXmlSrc = $xmlSrc . $lineStart . $date . '.xml';
				}
			}

			if (!$stateA) {
				$newDate = strtotime("-$numberOfDays day", strtotime($dateCur));
				$newDate = date('Y-m-d', $newDate);

				$date = str_replace('-', '', $newDate);
				$date = substr($date, 2);
				$numberOfDays++;
			}
		}

		$xml = simplexml_load_file($newXmlSrc);

		$xmlObj = $xml->children();

		$num = count($xmlObj->pozycja);

		for ($i = 0; $i <= $num; $i++) {
			if (!$xmlObj->pozycja[$i]->nazwa_waluty) {
				continue;
			}
			$currencyCode = (string) $xmlObj->pozycja[$i]->kod_waluty;

			if ($currencyCode == 'XDR') {
				continue;
			}

			$currencyName = Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($currencyCode);
			$supportedCurrencies[$currencyName] = $currencyCode;
		}

		return $supportedCurrencies;
	}
	/*
	 * Returns banks main currency 
	 */

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
		$date = str_replace('-', '', $dateCur);
		$date = substr($date, 2);

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
			for ($i = 0; $i < $fileNum; $i++) {
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
				$numberOfDays++;
			}
		}

		$xml = simplexml_load_file($newXmlSrc);

		$xmlObj = $xml->children();

		$num = count($xmlObj->pozycja);
		$datePublicationOfFile = (string) $xmlObj->data_publikacji;

		$exchangeRate = 1.0;
		// if currency is diffrent than PLN we need to calculate rate for converting other currencies to this one from PLN
		if ($mainCurrency != $this->getMainCurrencyCode()) {
			for ($i = 0; $i <= $num; $i++) {
				if ($xmlObj->pozycja[$i]->kod_waluty == $mainCurrency) {
					$exchangeRate = str_replace(',', '.', $xmlObj->pozycja[$i]->kurs_sredni);
				}
			}
		}

		for ($i = 0; $i <= $num; $i++) {
			if (!$xmlObj->pozycja[$i]->nazwa_waluty) {
				continue;
			}
			$currency = (string) $xmlObj->pozycja[$i]->kod_waluty;
			foreach ($otherCurrencyCode as $key => $currId) {
				if ($key == $currency && $currency != $mainCurrency) {
					$exchange = str_replace(',', '.', $xmlObj->pozycja[$i]->kurs_sredni);
					$exchange = $exchange / $xmlObj->pozycja[$i]->przelicznik;
					$exchangeVtiger = $exchangeRate / $exchange;
					$exchange = $exchange / $exchangeRate;

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
			$exchange = 1.00000 / $exchangeRate;
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
