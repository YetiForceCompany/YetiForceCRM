<?php
/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Class for connection to Nation Bank of Romania currency exchange rates.
 */
class Settings_CurrencyUpdate_NBR_BankModel extends Settings_CurrencyUpdate_AbstractBank_Model
{
	// Returns bank name

	public function getName()
	{
		return 'NBR';
	}

	// Returns url sources from where exchange rates are taken from

	public function getSource($year = '')
	{
		if ('' == $year) {
			$source = 'http://www.bnr.ro/nbrfxrates10days.xml';
		} else {
			$source = 'http://www.bnr.ro/files/xml/years/nbrfxrates' . $year . '.xml';
		}

		return simplexml_load_file($source);
	}

	// Returns list of currencies supported by this bank

	public function getSupportedCurrencies()
	{
		$supportedCurrencies = [];
		$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($this->getMainCurrencyCode())] = $this->getMainCurrencyCode();
		$xml = $this->getSource();

		foreach ($xml->Body->Cube[0] as $currency) {
			$currencyCode = (string) $currency['currency'];
			$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($currencyCode)] = $currencyCode;
		}
		return $supportedCurrencies;
	}

	// Returns banks main currency

	public function getMainCurrencyCode()
	{
		return 'RON';
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

		// check if data is correct, currency rates can be retrieved only for working days
		$lastWorkingDay = vtlib\Functions::getLastWorkingDay($yesterday);

		$today = date('Y-m-d');
		$mainCurrency = \App\Fields\Currency::getDefault()['currency_code'];

		// how old is the currency rate
		$now = time(); // or your date as well
		$rateDay = strtotime($dateParam);
		$datediff = $now - $rateDay;
		if (floor($datediff / (60 * 60 * 24)) >= 10) {
			$year = date('Y', $rateDay);
			$xml = $this->getSource($year);
		} else {
			$xml = $this->getSource();
		}

		if (false === $xml) {
			return false;
		}

		$datePublicationOfFile = $dateParam;
		$exchangeRate = 1.0;

		// if currency is diffrent than RON we need to calculate rate for converting other currencies to this one from RON
		if ($mainCurrency != $this->getMainCurrencyCode()) {
			$foundRate = false;
			foreach ($xml->Body->Cube as $time) {
				if ($time['date'] == $dateParam) {
					foreach ($time->Rate as $rate) {
						if ($rate['currency'] == $mainCurrency) {
							$exchangeRate = $rate;
							$foundRate = true;
						}
						if ($foundRate) {
							break;
						}
					}
				}
				if ($foundRate) {
					break;
				}
			}
		}

		$foundRate = false;
		foreach ($xml->Body->Cube as $time) {
			if ($time['date'] == $dateParam) {
				$num = \count($time->Rate);
				for ($i = 0; $i < $num; ++$i) {
					$currency = (string) $time->Rate[$i]['currency'];   // currency code
					foreach ($otherCurrencyCode as $key => $currId) {
						if ($key == $currency && $currency != $mainCurrency) {
							$exchange = $time->Rate[$i];
							if ($time->Rate[$i]['multiplier']) {
								$exchange = (float) $time->Rate[$i] / (float) $time->Rate[$i]['multiplier'];
							}
							$exchangeVtiger = (float) $exchangeRate / (float) $exchange;
							$exchange = (float) $exchange / (float) $exchangeRate;

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
				$foundRate = true;
			}
			if ($foundRate) {
				break;
			}
		}

		// currency diffrent than RON, we need to add manually RON rates
		if ($mainCurrency != $this->getMainCurrencyCode()) {
			$exchange = 1.00000 / (float) $exchangeRate;
			$mainCurrencyId = false;
			foreach ($otherCurrencyCode as $code => $id) {
				if ($code == $this->getMainCurrencyCode()) {
					$mainCurrencyId = $id;
				}
			}

			if ($mainCurrencyId) {
				if (true === $cron || ((strtotime($dateParam) == strtotime($today)) || (strtotime($dateParam) == strtotime($lastWorkingDay)))) {
					$moduleModel->setCRMConversionRate($this->getMainCurrencyCode(), $exchange);
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
