<?php

 /* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

namespace vtlib;

class Functions
{
	public static function getAllModules($isEntityType = true, $showRestricted = false, $presence = false, $colorActive = false, $ownedby = false)
	{
		if (\App\Cache::has('moduleTabs', 'all')) {
			$moduleList = \App\Cache::get('moduleTabs', 'all');
		} else {
			$moduleList = [];
			$rows = (new \App\Db\Query())->from('vtiger_tab')->all();
			foreach ($rows as $row) {
				if (!\App\Cache::has('moduleTabById', $row['tabid'])) {
					\App\Cache::save('moduleTabById', $row['tabid'], $row);
				}
				if (!\App\Cache::has('moduleTabByName', $row['name'])) {
					\App\Cache::save('moduleTabByName', $row['name'], $row);
				}
				$row['tabid'] = (int) $row['tabid'];
				$row['presence'] = (int) $row['presence'];
				$row['tabsequence'] = (int) $row['tabsequence'];
				$row['customized'] = (int) $row['customized'];
				$row['ownedby'] = (int) $row['ownedby'];
				$row['isentitytype'] = (int) $row['isentitytype'];
				$row['coloractive'] = (int) $row['coloractive'];
				$row['type'] = (int) $row['type'];
				$row['premium'] = (int) $row['premium'];
				$moduleList[$row['tabid']] = $row;
			}
			\App\Cache::save('moduleTabs', 'all', $moduleList);
		}
		$restrictedModules = ['Dashboard', 'ModComments'];
		foreach ($moduleList as $id => $module) {
			if (!$showRestricted && \in_array($module['name'], $restrictedModules)) {
				unset($moduleList[$id]);
			}
			if ($isEntityType && 0 === (int) $module['isentitytype']) {
				unset($moduleList[$id]);
			}
			if (false !== $presence && (int) $module['presence'] !== $presence) {
				unset($moduleList[$id]);
			}
			if (false !== $colorActive && 1 !== (int) $module['coloractive']) {
				unset($moduleList[$id]);
			}
			if (false !== $ownedby && (int) $module['ownedby'] !== $ownedby) {
				unset($moduleList[$id]);
			}
		}
		return $moduleList;
	}

	public static function getModuleData($mixed)
	{
		if (empty($mixed)) {
			\App\Log::error(__METHOD__ . ' - Required parameter missing');

			return false;
		}
		$id = $name = null;
		if (is_numeric($mixed)) {
			$id = $mixed;
			if (\App\Cache::has('moduleTabById', $mixed)) {
				return \App\Cache::get('moduleTabById', $mixed);
			}
		} else {
			$name = (string) $mixed;
			if (\App\Cache::has('moduleTabByName', $name)) {
				return \App\Cache::get('moduleTabByName', $name);
			}
		}
		$moduleList = [];
		$rows = (new \App\Db\Query())->from('vtiger_tab')->all();
		foreach ($rows as $row) {
			\App\Cache::save('moduleTabById', $row['tabid'], $row);
			\App\Cache::save('moduleTabByName', $row['name'], $row);
			$moduleList[$row['tabid']] = $row;
		}
		\App\Cache::save('moduleTabs', 'all', $moduleList);
		if ($name && \App\Cache::has('moduleTabByName', $name)) {
			return \App\Cache::get('moduleTabByName', $name);
		}
		return $id ? \App\Cache::get('moduleTabById', $id) : null;
	}

	// MODULE RECORD
	protected static $crmRecordIdMetadataCache = [];

	/**
	 * Clear cache meta data for records.
	 *
	 * @param int $id
	 */
	public static function clearCacheMetaDataRecord($id)
	{
		if (isset(static::$crmRecordIdMetadataCache[$id])) {
			unset(static::$crmRecordIdMetadataCache[$id]);
		}
	}

	/**
	 * Function gets record metadata.
	 *
	 * @param array|int $mixedid
	 *
	 * @return array
	 */
	public static function getCRMRecordMetadata($mixedid)
	{
		$multimode = \is_array($mixedid);

		$ids = $multimode ? $mixedid : [$mixedid];
		$missing = [];
		foreach ($ids as $id) {
			if ($id && !isset(self::$crmRecordIdMetadataCache[$id])) {
				$missing[] = $id;
			}
		}
		if ($missing) {
			$query = (new \App\Db\Query())
				->select(['crmid', 'setype', 'deleted', 'smcreatorid', 'smownerid', 'createdtime', 'private'])
				->from('vtiger_crmentity')
				->where(['in', 'crmid', $missing]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$row['deleted'] = (int) $row['deleted'];
				$row['smownerid'] = (int) $row['smownerid'];
				$row['smcreatorid'] = (int) $row['smcreatorid'];
				$row['crmid'] = (int) $row['crmid'];
				$row['private'] = (int) $row['private'];
				self::$crmRecordIdMetadataCache[$row['crmid']] = $row;
			}
		}
		$result = [];
		foreach ($ids as $id) {
			if (isset(self::$crmRecordIdMetadataCache[$id])) {
				$result[$id] = self::$crmRecordIdMetadataCache[$id];
			} else {
				$result[$id] = null;
			}
		}
		return $multimode ? $result : array_shift($result);
	}

	public static function getCRMRecordLabel($id, $default = '')
	{
		$label = \App\Record::getLabel($id);

		return empty($label) ? $default : $label;
	}

	/**
	 * Function to gets mudule field ID.
	 *
	 * @param int|string $moduleId
	 * @param int|string $mixed
	 * @param bool       $onlyactive
	 *
	 * @return bool|int
	 */
	public static function getModuleFieldId($moduleId, $mixed, $onlyactive = true)
	{
		$field = \App\Field::getFieldInfo($mixed, $moduleId);

		if ($field && $onlyactive && ('0' !== $field['presence'] && '2' !== $field['presence'])) {
			$field = null;
		}
		return $field ? $field['fieldid'] : false;
	}

	// Utility
	public static function formatDecimal($value)
	{
		$fld_value = explode('.', $value);
		if (!empty($fld_value[1])) {
			$fld_value = rtrim($value, '0');
			$value = rtrim($fld_value, '.');
		}
		return $value;
	}

	public static function fromHtmlPopup($string, $encode = true)
	{
		$popup_toHtml = [
			'"' => '&quot;',
			"'" => '&#039;',
		];
		if ($encode && \is_string($string)) {
			$string = addslashes(str_replace(array_values($popup_toHtml), array_keys($popup_toHtml), $string));
		}
		return $string;
	}

	public static function br2nl($str)
	{
		$str = preg_replace("/(\r\n)/", '\\r\\n', $str);
		$str = preg_replace("/'/", ' ', $str);
		return preg_replace('/"/', ' ', $str);
	}

	public static function suppressHTMLTags($string)
	{
		return preg_replace(['/</', '/>/', '/"/'], ['&lt;', '&gt;', '&quot;'], $string);
	}

	/**    Function used to retrieve a single field value from database.
	 * @param string $tableName - tablename from which we will retrieve the field value
	 * @param string $fieldName - fieldname to which we want to get the value from database
	 * @param string $idName    - idname which is the name of the entity id in the table like, inoviceid, etc.,
	 * @param int    $id        - entity id
	 *                          return mixed $fieldval  - field value of the needed fieldname from database will be returned
	 */
	public static function getSingleFieldValue($tableName, $fieldName, $idName, $id)
	{
		return (new \App\Db\Query())->select([$fieldName])->from($tableName)->where([$idName => $id])->scalar();
	}

	/**     function used to change the Type of Data for advanced filters in custom view and Reports.
	 * *     @param string $table_name - tablename value from field table
	 * *     @param string $column_nametable_name - columnname value from field table
	 * *     @param string $type_of_data - current type of data of the field. It is to return the same TypeofData
	 * *            if the  field is not matched with the $new_field_details array.
	 * *     return string $type_of_data - If the string matched with the $new_field_details array then the Changed
	 * *           typeofdata will return, else the same typeofdata will return.
	 * *
	 * *     EXAMPLE: If you have a field entry like this:
	 * *
	 * *        fieldlabel         | typeofdata | tablename            | columnname       |
	 * *            -------------------+------------+----------------------+------------------+
	 * *        Potential Name     | I~O        | vtiger_quotes        | potentialid      |
	 * *
	 * *     Then put an entry in $new_field_details  like this:
	 * *
	 * *                "vtiger_quotes:potentialid"=>"V",
	 * *
	 * *    Now in customview and report's advance filter this field's criteria will be show like string.
	 * @param mixed $column_name
	 * */
	public static function transformFieldTypeOfData($table_name, $column_name, $type_of_data)
	{
		$field = $table_name . ':' . $column_name;
		//Add the field details in this array if you want to change the advance filter field details

		static $new_field_details = [
			//Contacts Related Fields
			'vtiger_contactdetails:parentid' => 'V',
			'vtiger_contactsubdetails:birthday' => 'D',
			'vtiger_contactdetails:email' => 'V',
			'vtiger_contactdetails:secondaryemail' => 'V',
			//Account Related Fields
			'vtiger_account:parentid' => 'V',
			'vtiger_account:email1' => 'V',
			'vtiger_account:email2' => 'V',
			//Lead Related Fields
			'vtiger_leaddetails:email' => 'V',
			'vtiger_leaddetails:secondaryemail' => 'V',
			//Documents Related Fields
			'vtiger_senotesrel:crmid' => 'V',
			//HelpDesk Related Fields
			'vtiger_troubletickets:parent_id' => 'V',
			'vtiger_troubletickets:product_id' => 'V',
			//Product Related Fields
			'vtiger_products:discontinued' => 'C',
			'vtiger_products:vendor_id' => 'V',
			'vtiger_products:parentid' => 'V',
			//Faq Related Fields
			'vtiger_faq:product_id' => 'V',
			//Vendor Related Fields
			'vtiger_vendor:email' => 'V',
			//Campaign Related Fields
			'vtiger_campaign:product_id' => 'V',
			//Related List Entries(For Report Module)
			'vtiger_activityproductrel:activityid' => 'V',
			'vtiger_activityproductrel:productid' => 'V',
			'vtiger_campaign_records:campaignid' => 'V',
			'vtiger_campaign_records:crmid' => 'V',
			'vtiger_pricebookproductrel:pricebookid' => 'V',
			'vtiger_pricebookproductrel:productid' => 'V',
			'vtiger_senotesrel:crmid' => 'V',
			'vtiger_senotesrel:notesid' => 'V',
			'vtiger_seproductsrel:crmid' => 'V',
			'vtiger_seproductsrel:productid' => 'V',
			'vtiger_pricebook:currency_id' => 'V',
		];

		//If the Fields details does not match with the array, then we return the same typeofdata
		if (isset($new_field_details[$field])) {
			$type_of_data = $new_field_details[$field];
		}
		return $type_of_data;
	}

	public static function getArrayFromValue($values)
	{
		if (\is_array($values)) {
			return $values;
		}
		if ('' === $values) {
			return [];
		}
		if (false === strpos($values, ',')) {
			$array[] = $values;
		} else {
			$array = explode(',', $values);
		}
		return $array;
	}

	public static function throwNewException($e, $die = true, $messageHeader = 'LBL_ERROR')
	{
		if (!headers_sent() && \App\Config::security('cspHeaderActive')) {
			header("content-security-policy: default-src 'self' 'nonce-" . \App\Session::get('CSP_TOKEN') . "'; object-src 'none';base-uri 'self'; frame-ancestors 'self';");
		}
		$message = \is_object($e) ? $e->getMessage() : $e;
		$code = 500;
		if (!\is_array($message)) {
			if (false === strpos($message, '||')) {
				$message = \App\Language::translateSingleMod($message, 'Other.Exceptions');
			} else {
				$params = explode('||', $message);
				$label = \App\Language::translateSingleMod(array_shift($params), 'Other.Exceptions');
				$params = array_pad($params, substr_count($label, '%'), '');
				$message = \call_user_func_array('vsprintf', [$label, $params]);
			}
		}
		if ('API' === \App\Process::$requestMode) {
			throw new \App\Exceptions\ApiException($message, 401);
		}
		if (\App\Request::_isAjax() && \App\Request::_isJSON()) {
			$response = new \Vtiger_Response();
			$response->setEmitType(\Vtiger_Response::$EMIT_JSON);

			if (\is_object($e)) {
				$response->setException($e);
			} else {
				$trace = '';
				if (\App\Config::debug('DISPLAY_EXCEPTION_BACKTRACE') && \is_object($e)) {
					$trace = str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', "->{$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}");
				}
				$response->setError($code, $message, $trace);
			}
			$response->emit();
		} else {
			if (\PHP_SAPI !== 'cli') {
				if (\App\Config::debug('DISPLAY_EXCEPTION_BACKTRACE') && \is_object($e)) {
					$message = [
						'message' => $message,
						'trace' => str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', "-> {$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}"),
					];
					$code = $e->getCode();
				}
				http_response_code($code);
				$viewer = new \Vtiger_Viewer();
				$viewer->assign('MESSAGE', \Config\Debug::$EXCEPTION_ERROR_TO_SHOW ? $message : \App\Language::translate('ERR_OCCURRED_ERROR'));
				$viewer->assign('MESSAGE_EXPANDED', \is_array($message));
				$viewer->assign('HEADER_MESSAGE', \App\Language::translate($messageHeader));
				$viewer->view('Exceptions/ExceptionError.tpl', 'Vtiger');
			} else {
				echo(\Config\Debug::$EXCEPTION_ERROR_TO_SHOW ? $message : \App\Language::translate('ERR_OCCURRED_ERROR')) . PHP_EOL;
			}
		}
		if ($die) {
			trigger_error(print_r($message, true), E_USER_ERROR);
			if (\is_object($message)) {
				throw new $message();
			}
			if (\is_array($message)) {
				throw new \App\Exceptions\AppException($message['message']);
			}
			throw new \App\Exceptions\AppException($message);
		}
	}

	/**
	 * Get html rr plain text.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function getHtmlOrPlainText(string $content)
	{
		if (\App\Utils::isHtml($content)) {
			$content = \App\Purifier::decodeHtml($content);
		} else {
			$content = nl2br($content);
		}
		return $content;
	}

	/**
	 * Function to delete files and dirs.
	 *
	 * @param string $src
	 * @param bool   $outsideRoot
	 */
	public static function recurseDelete($src, $outsideRoot = false): int
	{
		$rootDir = ($outsideRoot || 0 === strpos($src, ROOT_DIRECTORY)) ? '' : ROOT_DIRECTORY . \DIRECTORY_SEPARATOR;
		if (!file_exists($rootDir . $src)) {
			return 0;
		}
		$i = 0;
		if (is_dir($rootDir . $src)) {
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootDir . $src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $filename => $file) {
				if ($file->isFile()) {
					unlink($filename);
					++$i;
				} else {
					rmdir($filename);
				}
			}
			rmdir($rootDir . $src);
		} else {
			unlink($rootDir . $src);
			++$i;
		}
		return $i;
	}

	/**
	 * The function copies files.
	 *
	 * @param string $src
	 * @param string $dest
	 *
	 * @return int
	 */
	public static function recurseCopy($src, $dest)
	{
		$rootDir = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR;
		if (!file_exists($rootDir . $src)) {
			return 0;
		}
		if ($dest && '/' !== substr($dest, -1) && '\\' !== substr($dest, -1)) {
			$dest = $dest . \DIRECTORY_SEPARATOR;
		}
		$i = 0;
		$dest = $rootDir . $dest;
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isDir() && !file_exists($dest . $iterator->getSubPathName())) {
				mkdir($dest . $iterator->getSubPathName(), 0755);
			} elseif (!$item->isDir()) {
				copy($item->getRealPath(), $dest . $iterator->getSubPathName());
				++$i;
			}
		}
		return $i;
	}

	/**
	 * Parse bytes.
	 *
	 * @param mixed $str
	 *
	 * @return float
	 */
	public static function parseBytes($str): float
	{
		if (is_numeric($str)) {
			return (float) $str;
		}
		$bytes = 0;
		if (preg_match('/([0-9\.]+)\s*([a-z]*)/i', $str, $regs)) {
			$bytes = (float) ($regs[1]);
			switch (strtolower($regs[2])) {
				case 'g':
				case 'gb':
					$bytes *= 1073741824;
					break;
				case 'm':
				case 'mb':
					$bytes *= 1048576;
					break;
				case 'k':
				case 'kb':
					$bytes *= 1024;
					break;
				default:
					break;
			}
		}
		return (float) $bytes;
	}

	/**
	 * Show bytes.
	 *
	 * @param mixed       $bytes
	 * @param string|null $unit
	 *
	 * @return string
	 */
	public static function showBytes($bytes, &$unit = null): string
	{
		$bytes = self::parseBytes($bytes);
		if ($bytes >= 1073741824) {
			$unit = 'GB';
			$gb = $bytes / 1073741824;
			$str = sprintf($gb >= 10 ? '%d ' : '%.2f ', $gb) . $unit;
		} elseif ($bytes >= 1048576) {
			$unit = 'MB';
			$mb = $bytes / 1048576;
			$str = sprintf($mb >= 10 ? '%d ' : '%.2f ', $mb) . $unit;
		} elseif ($bytes >= 1024) {
			$unit = 'KB';
			$str = sprintf('%d ', round($bytes / 1024)) . $unit;
		} else {
			$unit = 'B';
			$str = sprintf('%d ', $bytes) . $unit;
		}
		return $str;
	}

	public static function getMinimizationOptions($type = 'js')
	{
		switch ($type) {
			case 'js':
				$return = \App\Config::developer('MINIMIZE_JS');
				break;
			case 'css':
				$return = \App\Config::developer('MINIMIZE_CSS');
				break;
			default:
				break;
		}
		return $return;
	}

	/**
	 * Checks if given date is working day, if not returns last working day.
	 *
	 * @param <Date> $date
	 *
	 * @return <Date> - last working y
	 */
	public static function getLastWorkingDay($date)
	{
		if (empty($date)) {
			$date = date('Y-m-d');
		}
		$date = strtotime($date);
		if ('Sat' === date('D', $date)) { // switch to friday the day before
			$lastWorkingDay = date('Y-m-d', strtotime('-1 day', $date));
		} elseif ('Sun' === date('D', $date)) { // switch to friday two days before
			$lastWorkingDay = date('Y-m-d', strtotime('-2 day', $date));
		} else {
			$lastWorkingDay = date('Y-m-d', $date);
		}
		return $lastWorkingDay;
	}

	/**
	 * Function that returns conversion info from default system currency to chosen one.
	 *
	 * @param <Integer> $currencyId - id of currency for which we want to retrieve conversion rate to default currency
	 * @param <Date>    $date       - date of exchange rates, if empty then rate from yesterday
	 *
	 * @return <Array> - array containing:
	 *                 date - date of rate
	 *                 value - conversion 1 default currency -> $currencyId
	 *                 conversion - 1 $currencyId -> default currency
	 */
	public static function getConversionRateInfo($currencyId, $date = '')
	{
		$defaultCurrencyId = \App\Fields\Currency::getDefault()['id'];
		$info = [];
		if (empty($date)) {
			$yesterday = date('Y-m-d', strtotime('-1 day'));
			$date = self::getLastWorkingDay($yesterday);
		}
		$info['date'] = $date;
		if ($currencyId == $defaultCurrencyId) {
			$info['value'] = 1.0;
			$info['conversion'] = 1.0;
		} else {
			$value = \Settings_CurrencyUpdate_Module_Model::getCleanInstance()->getCRMConversionRate($currencyId, $defaultCurrencyId, $date);
			$info['value'] = empty($value) ? 1.0 : round($value, 5);
			$info['conversion'] = empty($value) ? 1.0 : round(1 / $value, 5);
		}
		return $info;
	}

	/**
	 * Getting parameters from URL.
	 *
	 * @param string|null $url
	 *
	 * @return array
	 */
	public static function getQueryParams($url): array
	{
		$queryParams = [];
		if (!empty($url) && $queryStr = parse_url(htmlspecialchars_decode($url), PHP_URL_QUERY)) {
			parse_str($queryStr, $queryParams);
		}

		return $queryParams;
	}

	public static function arrayDiffAssocRecursive($array1, $array2)
	{
		$difference = [];
		foreach ($array1 as $key => $value) {
			if (\is_array($value)) {
				if (!isset($array2[$key]) || !\is_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$newDiff = self::arrayDiffAssocRecursive($value, $array2[$key]);
					if (!empty($newDiff)) {
						$difference[$key] = $newDiff;
					}
				}
			} elseif (!\array_key_exists($key, $array2) || $array2[$key] !== $value) {
				$difference[$key] = $value;
			}
		}
		return $difference;
	}
}
