<?php
/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 */

/**
 * Class for connection to Central Bank of Russia currency exchange rates.
 */
class Settings_CurrencyUpdate_CBR_BankModel extends Settings_CurrencyUpdate_AbstractBank_Model
{
	// Returns bank name

	public function getName()
	{
		return 'CBR';
	}

	// Returns url sources from where exchange rates are taken from

	public function getSource()
	{
		return ['http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL'];
	}

	// Returns list of currencies supported by this bank

	public function getSupportedCurrencies()
	{
		$supportedCurrencies = [];
		$supportedCurrencies[Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($this->getMainCurrencyCode())] = $this->getMainCurrencyCode();
		$source = $this->getSource();
		$client = new \SoapClient($source[0], \App\RequestHttp::getSoapOptions());
		$curs = $client->GetCursOnDate(['On_date' => date('Y-m-d')]);
		$ratesXml = new \SimpleXMLElement($curs->GetCursOnDateResult->any);

		foreach ($ratesXml->ValuteData[0] as $currency) {
			$currencyCode = (string) $currency->VchCode;
			$currencyName = Settings_CurrencyUpdate_Module_Model::getCRMCurrencyName($currencyCode);
			if ($currencyName) {
				$supportedCurrencies[$currencyName] = $currencyCode;
			}
		}
		return $supportedCurrencies;
	}

	// Returns banks main currency

	public function getMainCurrencyCode()
	{
		return 'RUB';
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

		$dateCur = $dateParam;
		$source = $this->getSource();
		$client = new \SoapClient($source[0], \App\RequestHttp::getSoapOptions());
		$curs = $client->GetCursOnDate(['On_date' => $dateCur]);
		$ratesXml = new \SimpleXMLElement($curs->GetCursOnDateResult->any);

		$datePublicationOfFile = $dateCur;

		$exchangeRate = 1.0;
		// if currency is diffrent than RUB we need to calculate rate for converting other currencies to this one from RUB
		if ($mainCurrency != $this->getMainCurrencyCode()) {
			foreach ($ratesXml->ValuteData[0] as $currencyRate) {
				if ($currencyRate->VchCode == $mainCurrency) {
					$exchangeRate = $currencyRate->Vcurs;
				}
			}
		}

		foreach ($ratesXml->ValuteData[0] as $currencyRate) {
			$currency = (string) $currencyRate->VchCode;
			foreach ($otherCurrencyCode as $key => $currId) {
				if ($key == $currency && $currency != $mainCurrency) {
					$curs = (string) $currencyRate->Vcurs;
					$nom = (string) $currencyRate->Vnom;
					$exchange = $curs / $nom;

					$exchangeVtiger = $exchangeRate / $exchange;
					$exchange = $exchange / $exchangeRate;

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

		// currency diffrent than RUB, we need to add manually RUB rates
		if ($mainCurrency != $this->getMainCurrencyCode()) {
			$exchange = 1.00000 / $exchangeRate;
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
