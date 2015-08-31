<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Settings_CurrencyUpdate_Module_Model extends Vtiger_Base_Model
{
	/*
	 * Returns objects instance
	 */
	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	/*
	 * Returns CRMS active currency name by currency code
	 * @return <String> - currency name
	 */
	public static function getCRMCurrencyName($code)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `currency_name` FROM `vtiger_currencies` WHERE `currency_code` = ? LIMIT 1;';
		$result = $db->pquery($query, [$code]);

		return $db->getSingleValue($result);
	}

	/*
	 * Returns list of active currencies in CRM
	 * @return <Integer> - number of currencies
	 */
	public function getCurrencyNum()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT COUNT(*) as num FROM `vtiger_currency_info` WHERE `currency_status` = ? LIMIT 1;';
		$result = $db->pquery($query, ['Active']);

		$num = $db->query_result($result, 0, 'num');
		return $num;
	}

	/*
	 * Returns informations about systems main currency
	 * @return <Array> - array containing currency info
	 */
	public function getMainCurrencyInfo()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `id`, `currency_name`, `currency_code`, `currency_symbol`, `conversion_rate` FROM `vtiger_currency_info` WHERE `defaultid` = ? LIMIT 1;';
		$result = $db->pquery($query, ['-11']);

		$curr = $db->fetch_array($result);
		return $curr;
	}

	/*
	 * Returns systems main currency code
	 * @return <String> - currency code
	 */
	public function getMainCurrencyCode()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `currency_code` FROM `vtiger_currency_info` WHERE `defaultid` = ? LIMIT 1;';
		$result = $db->pquery($query, ['-11']);

		$curr_code = $db->query_result($result, 0, 'currency_code');
		return $curr_code;
	}

	/*
	 * Returns systems main currency id
	 * @return <Integer> - currency id
	 */
	public function getMainCurrencyId()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `id` FROM `vtiger_currency_info` WHERE `defaultid` = ? LIMIT 1;';
		$result = $db->pquery($query, ['-11']);

		$id = $db->query_result($result, 0, 'id');
		return $id;
	}

	/*
	 * Returns currency exchange rates for systems active currencies from bank
	 * @param <Date> $date - date for which to fetch exchange rates
	 * @param <Boolean> $cron - true if fired by server, and so updates systems currency conversion rates
	 * @return <Boolean> - true if fetched new exchange rates, false otherwise
	 */
	function fetchCurrencyRates($dateCur, $cron = false)
	{

		$db = PearDatabase::getInstance();
		$notifyNewRates = false;
		$vtigerCurrencySql = 'SELECT `id`, `currency_code` FROM `vtiger_currency_info` WHERE `currency_status` = ? AND `deleted` = 0 AND `defaultid` != ?;';
		$vtigerCurrencyResult = $db->pquery($vtigerCurrencySql, ['Active', '-11']);
		$numToConvert = $db->num_rows($vtigerCurrencyResult);

		if ($numToConvert >= 1) {
			$selectBankId = $this->getActiveBankId();
			$activeBankName = $this->getActiveBankName();
			$currIds = [];
			$otherCurrencyCode = [];
			for ($i = 0; $i < $numToConvert; $i++) {
				$id = $db->query_result($vtigerCurrencyResult, $i, 'id');
				$code = $db->query_result($vtigerCurrencyResult, $i, 'currency_code');
				$currIds[] = $id;
				$otherCurrencyCode[$code] = $id;
			}

			$existSql = 'SELECT COUNT(*) as num FROM `yetiforce_currencyupdate` WHERE `exchange_date` = ? AND `currency_id` IN (' . generateQuestionMarks($currIds) . ') AND `bank_id` = ? LIMIT 1;';
			$params = [$dateCur];
			$params = array_merge($params, $currIds);
			$params[] = $selectBankId;
			$existResult = $db->pquery($existSql, $params);

			$currNum = $db->query_result($existResult, 0, 'num');
			// download only if its not in archives
			if ($currNum != $numToConvert) {
				vimport('~modules/Settings/CurrencyUpdate/models/BankModels/' . $activeBankName . '.php');
				$bank = new $activeBankName();
				$bank->getRates($otherCurrencyCode, $dateCur, false);
				$notifyNewRates = true;
			}
		}

		return $notifyNewRates;
	}

	/*
	 * Synchronises database banks list with the bank classes existing on ftp
	 */
	public function refreshBanks()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `id`, `bank_name` FROM `yetiforce_currencyupdate_banks`;';
		$result = $db->query($query);
		$num = $db->num_rows($result);

		if ($num > 0) {
			for ($i = 0; $i < $num; $i++) {
				$id = $db->query_result($result, $i, 'id');
				$bankName = $db->query_result($result, $i, 'bank_name');

				$bankPath = __DIR__ . '/BankModels/' . $bankName . '.php';
				if (!file_exists($bankPath)) { // delete bank from database
					$query = 'DELETE FROM `yetiforce_currencyupdate_banks` WHERE `id` = ? LIMIT 1;';
					$db->pquery($query, [$id]);
				}
			}
		}

		foreach (new DirectoryIterator(__DIR__ . '/BankModels/') as $fileInfo) {
			$fileName = $fileInfo->getFilename();
			$extension = end(explode('.', $fileName));
			$bankClassName = basename($fileName, '.' . $extension);
			if ($fileInfo->isDot() || $extension != 'php') {
				continue;
			}

			$query = 'SELECT COUNT(*) as num FROM `yetiforce_currencyupdate_banks` WHERE `bank_name` = ?;';
			$result = $db->pquery($query, [$bankClassName]);
			$bankExists = intval($db->query_result($result, 0, 'num'));

			if (!$bankExists) {
				$query = 'INSERT INTO `yetiforce_currencyupdate_banks` (`bank_name`, `active`) VALUES (?,?);';
				$db->pquery($query, [$bankClassName, 0]);
			}
		}

		$activeId = $this->getActiveBankId();

		if (!$activeId) {
			$query = 'UPDATE `yetiforce_currencyupdate_banks` SET `active` = 1 ORDER BY `id` ASC LIMIT 1;';
			$db->query($query);
		}
	}

	/*
	 * Checks if given date is working day, if not returns last working day
	 * @param <Date> $date
	 * @return <Date> - last working y
	 */
	public function getLastWorkingDay($date)
	{
		$date = strtotime($date);
		if (date('D', $date) == 'Sat') { // switch to friday the day before
			$lastWorkingDay = date('Y-m-d', strtotime("-1 day", $date));
		} else if (date('D', $date) == 'Sun') { // switch to friday two days before
			$lastWorkingDay = date('Y-m-d', strtotime("-2 day", $date));
		} else {
			$lastWorkingDay = date('Y-m-d', $date);
		}

		return $lastWorkingDay;
	}

	/*
	 * Update currency rate in archives
	 * @param <Integer> $id - exchange rate id
	 * @param <Float> $exchange - exchange rate
	 */
	public function updateCurrencyRate($id, $exchange)
	{
		$db = PearDatabase::getInstance();

		$query = 'UPDATE `yetiforce_currencyupdate` SET `exchange` = ? WHERE `id` = ? LIMIT 1;';
		$db->pquery($query, [$exchange, $id]);
	}

	/*
	 * Adds currency exchange rate to archive
	 * @param <Integer> $currId - currency id
	 * @param <Date> $exchangeDate - exchange date
	 * @param <Float> $exchange - exchange rate
	 * @param <Integer> $bankId - bank id
	 */
	public function addcurrencyRate($currId, $exchangeDate, $exchange, $bankId)
	{
		$db = PearDatabase::getInstance();

		$query = 'INSERT INTO `yetiforce_currencyupdate` (`id`, `currency_id`, `fetch_date`, `exchange_date`, `exchange`, `bank_id`) VALUES (NULL, ?, CURDATE(), ?, ?, ?)';
		$params = [$currId, $exchangeDate, $exchange, $bankId];
		$db->pquery($query, $params);
	}

	/*
	 * Returns currency exchange rate id
	 * @param <Integer> $currencyId - systems currency id
	 * @param <Date> $exchangeDate - date of exchange rate
	 * @param <Integer> $bankId - id of bank
	 * @return <Integer> - currency rate id
	 */
	public function getCurrencyRateId($currencyId, $exchangeDate, $bankId)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `id` FROM `yetiforce_currencyupdate` WHERE  `exchange_date` = ? AND `currency_id` = ? AND `bank_id` = ? LIMIT 1;';
		$params = [$exchangeDate, $currencyId, $bankId];
		$result = $db->pquery($query, $params);

		return intval($db->query_result($result, 0, 'id'));
	}

	/*
	 * Returns currency rates from archive
	 * @param <Integer> $bankId - bank id
	 * @param <Date> $dateCur - date, if empty show this months history
	 * @return <Array> - array containing currency rates
	 */
	public function getRatesHistory($bankId, $dateCur)
	{
		$request = new Vtiger_Request($_REQUEST);
		$db = PearDatabase::getInstance();
		$query = 'SELECT 
					`exchange`, 
					`currency_name`, 
					`currency_code`, 
					`currency_symbol`, 
					`fetch_date`, 
					`exchange_date` 
				FROM 
					`yetiforce_currencyupdate` 
					INNER JOIN `vtiger_currency_info` 
						ON `yetiforce_currencyupdate`.`currency_id` = `vtiger_currency_info`.`id` 
					INNER JOIN `yetiforce_currencyupdate_banks` 
						ON `yetiforce_currencyupdate_banks`.`id` = `yetiforce_currencyupdate`.`bank_id` 
				WHERE 
					`yetiforce_currencyupdate`.`bank_id` = ? ';
		$params = [$bankId];
		// past date for history
		$pastDate = date('Y-m-d', strtotime('first day of this month'));

		// filter by date - if not exists then display this months history
		$filter = $request->get('duedate');
		if ($filter == '' && $dateCur) {
			$query .= 'AND `exchange_date` BETWEEN ? AND ? ';
			$params[] = $pastDate;
			$params[] = $dateCur;
		} else {
			$query .= 'AND `exchange_date` = ? ';
			$params[] = $dateCur;
		}

		$query .= 'ORDER BY `exchange_date` DESC, `currency_code` ASC;';

		$result = $db->pquery($query, $params);

		$history = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$history[$i]['exchange'] = $db->query_result($result, $i, 'exchange');
			$history[$i]['currency_name'] = $db->query_result($result, $i, 'currency_name');
			$history[$i]['code'] = $db->query_result($result, $i, 'currency_code');
			$history[$i]['symbol'] = $db->query_result($result, $i, 'currency_symbol');
			$history[$i]['fetch_date'] = $db->query_result($result, $i, 'fetch_date');
			$history[$i]['exchange_date'] = $db->query_result($result, $i, 'exchange_date');
		}

		return $history;
	}

	/*
	 * Returns list of supported currencies by active bank
	 * @param <String> $bankName - bank name
	 * @return <Array> - array of supported currencies
	 */
	public function getSupportedCurrencies($bankName = null)
	{
		if (!$bankName) {
			$bankName = $this->getActiveBankName();
		}
		vimport('~modules/Settings/CurrencyUpdate/models/BankModels/' . $bankName . '.php');
		$bank = new $bankName();

		return $bank->getSupportedCurrencies();
	}

	/*
	 * Returns list of unsupported currencies by active bank
	 * @param <String> $bankName - bank name
	 * @return <Array> - array of unsupported currencies
	 */
	public function getUnSupportedCurrencies($bankName = null)
	{
		if (!$bankName) {
			$bankName = $this->getActiveBankName();
		}
		vimport('~modules/Settings/CurrencyUpdate/models/BankModels/' . $bankName . '.php');
		$bank = new $bankName();

		$supported = $bank->getSupportedCurrencies($bankName);
		$db = PearDatabase::getInstance();

		$query = 'SELECT `currency_name`, `currency_code` FROM vtiger_currency_info WHERE `currency_status` = "Active" AND `deleted` = 0;';
		$result = $db->query($query);
		$num = $db->num_rows($result);

		$unsupported = [];
		for ($i = 0; $i < $num; $i++) {
			$name = $db->query_result($result, $i, 'currency_name');
			$code = $db->query_result($result, $i, 'currency_code');

			$unsupported[$name] = $code;
		}

		return array_diff($unsupported, $supported);
	}

	/*
	 * Sets systems exchange rate for chosen currency
	 * @param <String> $currency - currency code
	 * @param <Float> $exchange - exchange rate
	 */
	public function setCRMConversionRate($currency, $exchange)
	{
		$db = PearDatabase::getInstance();

		$rate = (float) $exchange;

		$query = 'UPDATE `vtiger_currency_info` SET `conversion_rate` = ? WHERE `currency_code` = ? LIMIT 1;';
		$db->pquery($query, [$rate, $currency]);
	}
	
	/*
	 * Function that retrieves conversion rate from and to specified currency
	 * @param <String> $from - currency code
	 * @param <String> $to - currency code
	 * @param <Date> $date - date of the exchange rate
	 * @return <Float> - conversion rate
	 */
	public function getCRMConversionRate($from, $to, $date = '')
	{
		$db = PearDatabase::getInstance();
		$mainCurrencyCode = $this->getMainCurrencyCode();
		$activeBankId = $this->getActiveBankId();
		$exchange = false;
		// get present conversion rate from crm
		if (empty($date)) {
			$query = 'SELECT `conversion_rate` FROM `vtiger_currency_info` WHERE `currency_code` = ? LIMIT 1;';
			$result = $db->pquery($query, [$to]);
			$exchange = floatval($db->getSingleValue($result));

			if ($from != $mainCurrencyCode) {
				$convertToMainCurrency = 1/$exchange;
				$query = 'SELECT `conversion_rate` FROM `vtiger_currency_info` WHERE `currency_code` = ? LIMIT 1;';
				$result = $db->pquery($query, [$from]);
				$fromExchange = floatval($db->getSingleValue($result));
				
				$exchange = 1 / ($fromExchange * $convertToMainCurrency);
			}
		}
		// get conversion rate from archive
		else {
			$query = 'SELECT 
						COUNT(1) as num  
					FROM 
						`yetiforce_currencyupdate` yfc 
						INNER JOIN `vtiger_currency_info` cur 
							ON yfc.`currency_id` = cur.`id` AND cur.`deleted` = 0 
					WHERE 
						yfc.`exchange_date` = ? AND 
						yfc.`bank_id` = ? AND 
						cur.`currency_code` = ? 
					LIMIT 1;';
			$result = $db->pquery($query, [$date, $activeBankId, $to]);
			$num = floatval($db->getSingleValue($result));
			
			// no exchange rate in archive, fetch new rates
			if ($num == 0 ) {
				$this->fetchCurrencyRates($date);
			}
			$query = 'SELECT 
						yfc.`exchange` 
					FROM 
						`yetiforce_currencyupdate` yfc 
						INNER JOIN `vtiger_currency_info` cur 
							ON yfc.`currency_id` = cur.`id` AND cur.`deleted` = 0 
					WHERE 
						yfc.`exchange_date` = ? AND 
						yfc.`bank_id` = ? AND 
						cur.`currency_code` = ? 
					LIMIT 1;';
			$result = $db->pquery($query, [$date, $activeBankId, $to]);
			$exchange = floatval($db->getSingleValue($result));
			if ($exchange > 0) {
				$exchange = 1 / $exchange;
			}

			if ($from != $mainCurrencyCode) {
				$convertToMainCurrency = $exchange == 0 ? 1 : 1 / $exchange;
				$query = 'SELECT 
							yfc.`exchange` 
						FROM 
							`yetiforce_currencyupdate` yfc 
							INNER JOIN `vtiger_currency_info` cur 
								ON yfc.`currency_id` = cur.`id` AND cur.`deleted` = 0 
						WHERE 
							yfc.`exchange_date` = ? AND 
							yfc.`bank_id` = ? AND 
							cur.`currency_code` = ? 
						LIMIT 1;';
				$result = $db->pquery($query, [$date, $activeBankId, $from]);
				$fromExchange = floatval($db->getSingleValue($result));
				if ($from != $mainCurrencyCode && $to != $mainCurrencyCode) {
					$exchange = $fromExchange / $convertToMainCurrency;
				}
				else {
					$exchange = $fromExchange * $convertToMainCurrency;
				}
			}
		}

		return $exchange = round($exchange, 5);
	}

	/*
	 * Convert given amount in one currency to another
	 * @param <Float> $amount - number to convert
	 * @param <String> $from - currency code
	 * @param <String> $to - currency code
	 * @param <Date> $date - date of the exchange rate
	 * @return <Float> - floating point number
	 */
	public function convertFromTo($amount, $from, $to, $date=false) {
		return round($amount * $this->getCRMConversionRate($from, $to, $date), 5);
	}

	/*
	 * Returns id of active bank
	 * @return <Integer> - bank id
	 */
	public function getActiveBankId()
	{
		$db = PearDatabase::getInstance();

		$queryBank = 'SELECT `id` FROM `yetiforce_currencyupdate_banks` WHERE `active` = 1 LIMIT 1;';
		$resultBank = $db->query($queryBank);

		$bankInfo = $db->query_result($resultBank, 0, 'id');
		return $bankInfo;
	}

	/*
	 * Saves new active bank by id
	 * @param <Integer> $bankId - bank id
	 * @return <Boolean> - true on success or false
	 */
	public function setActiveBankById($bankId)
	{
		$db = PearDatabase::getInstance();

		$query = 'UPDATE `yetiforce_currencyupdate_banks` SET `active` = 0;';
		$db->query($query);

		$query = 'UPDATE `yetiforce_currencyupdate_banks` SET `active` = ? WHERE `id` = ? LIMIT 1;';
		$result = $db->pquery($query, [1, $bankId]);

		if ($db->getAffectedRowCount($result)) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * Returns active banks name
	 * @return <String> - bank name
	 */
	public function getActiveBankName()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `bank_name` FROM `yetiforce_currencyupdate_banks` WHERE `active` = 1 LIMIT 1;';
		$result = $db->query($query);

		return $db->query_result($result, 0, 'bank_name');
	}
}
