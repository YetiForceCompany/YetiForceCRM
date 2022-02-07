<?php
/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 */

/**
 * Class for connection to European Central Bank currency exchange rates.
 */
class Settings_CurrencyUpdate_ECB_BankModel extends Settings_CurrencyUpdate_AbstractBank_Model
{
	// Returns bank name

	public function getName()
	{
		return 'ECB';
	}

	// Returns url sources from where exchange rates are taken from

	public function getSource()
	{
		return ['http://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml', 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml'];
	}

	// Returns list of currencies supported by this bank

	public function getSupportedCurrencies()
	{
		$supportedCurrencies = [];
		$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($this->getMainCurrencyCode())] = $this->getMainCurrencyCode();
		$source = $this->getSource();

		$XML = simplexml_load_file($source[0]);

		foreach ($XML->Cube->Cube[0] as $currency) {
			$currencyCode = (string) $currency['currency'];
			$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($currencyCode)] = $currencyCode;
		}
		return $supportedCurrencies;
	}

	// Returns banks main currency

	public function getMainCurrencyCode()
	{
		return 'EUR';
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

		// source, ECB has 2 sources for older rates
		// 0 - last 90 days
		// 1 - historical data from year 1999
		// we speed script choosing the smaller file for our needs
		$source = $this->getSource();
		// how old is the currency rate
		$now = time(); // or your date as well
		$rateDay = strtotime($dateParam);
		$datediff = $now - $rateDay;

		if (floor($datediff / (60 * 60 * 24)) >= 90) {
			$sourceURL = $source[1];
		} else {
			$sourceURL = $source[0];
		}

		$XML = simplexml_load_file($sourceURL); // European Central Bank xml only contains business days! oh well....

		if (false === $XML) {
			return false;
		}
		$datePublicationOfFile = $dateParam;
		$exchangeRate = 1.0;
		// if currency is diffrent than EUR we need to calculate rate for converting other currencies to this one from EUR
		if ($mainCurrency != $this->getMainCurrencyCode()) {
			$foundRate = false;
			foreach ($XML->Cube->Cube as $time) {
				if ($time['time'] == $dateParam) {
					foreach ($time->Cube as $rate) {
						if ($rate['currency'] == $mainCurrency) {
							$exchangeRate = $rate['rate'];
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
		foreach ($XML->Cube->Cube as $time) {
			if ($time['time'] == $dateParam) {
				$num = \count($time->Cube);
				for ($i = 0; $i < $num; ++$i) {
					$currency = (string) $time->Cube[$i]['currency'];   // currency code
					foreach ($otherCurrencyCode as $key => $currId) {
						if ($key == $currency && $currency != $mainCurrency) {
							$exchange = $time->Cube[$i]['rate'];
							$exchangeVtiger = (float) $exchange / (float) $exchangeRate;
							$exchange = (float) $exchangeRate / (float) $exchange;

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

		// currency diffrent than EUR, we need to add manually EUR rates
		if ($mainCurrency != $this->getMainCurrencyCode()) {
			$yfRate = 1.00000 / (float) $exchangeRate;
			$exchange = (float) $exchangeRate;
			$mainCurrencyId = false;
			foreach ($otherCurrencyCode as $code => $id) {
				if ($code == $this->getMainCurrencyCode()) {
					$mainCurrencyId = $id;
				}
			}

			if ($mainCurrencyId) {
				if (true === $cron || ((strtotime($dateParam) == strtotime($today)) || (strtotime($dateParam) == strtotime($lastWorkingDay)))) {
					$moduleModel->setCRMConversionRate($this->getMainCurrencyCode(), $yfRate);
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
