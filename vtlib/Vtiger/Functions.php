<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

namespace vtlib;

class Functions
{
	protected static function getCurrencyInfo($currencyid)
	{
		if (\App\Cache::has('AllCurrency', 'All')) {
			$currencyInfo = \App\Cache::get('AllCurrency', 'All');
		} else {
			$currencyInfo = self::getAllCurrency();
		}

		return $currencyInfo[$currencyid];
	}

	public static function getAllCurrency($onlyActive = false)
	{
		if (\App\Cache::has('AllCurrency', 'All')) {
			$currencyInfo = \App\Cache::get('AllCurrency', 'All');
		} else {
			$currencyInfo = (new \App\Db\Query())->from('vtiger_currency_info')->indexBy('id')->all();
			\App\Cache::save('AllCurrency', 'All', $currencyInfo);
		}
		if ($onlyActive) {
			$currencies = [];
			foreach ($currencyInfo as $currencyId => $currency) {
				if ($currency['currency_status'] === 'Active') {
					$currencies[$currencyId] = $currency;
				}
			}

			return $currencies;
		} else {
			return $currencyInfo;
		}
	}

	public static function getCurrencyName($currencyid, $show_symbol = true)
	{
		$currencyInfo = self::getCurrencyInfo($currencyid);
		if ($show_symbol) {
			return sprintf('%s : %s', \App\Language::translate($currencyInfo['currency_name'], 'Currency'), $currencyInfo['currency_symbol']);
		}

		return $currencyInfo['currency_name'];
	}

	public static function getCurrencySymbolandRate($currencyid)
	{
		$currencyInfo = self::getCurrencyInfo($currencyid);
		$currencyRateSymbol = [
			'rate' => $currencyInfo['conversion_rate'],
			'symbol' => $currencyInfo['currency_symbol'],
		];

		return $currencyRateSymbol;
	}

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
				$moduleList[$row['tabid']] = $row;
			}
			\App\Cache::save('moduleTabs', 'all', $moduleList);
		}
		$restrictedModules = ['SMSNotifier', 'Dashboard', 'ModComments'];
		foreach ($moduleList as $id => $module) {
			if (!$showRestricted && in_array($module['name'], $restrictedModules)) {
				unset($moduleList[$id]);
			}
			if ($isEntityType && $module['isentitytype'] === 0) {
				unset($moduleList[$id]);
			}
			if ($presence !== false && $module['presence'] !== $presence) {
				unset($moduleList[$id]);
			}
			if ($colorActive !== false && $module['coloractive'] !== 1) {
				unset($moduleList[$id]);
			}
			if ($ownedby !== false && $module['ownedby'] !== $ownedby) {
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

	/**
	 * this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname).
	 *
	 * @param string $mixed - the module name
	 *
	 * @return string $fieldsname - the entity field name for the module
	 */
	public static function getEntityModuleSQLColumnString($mixed)
	{
		$data = [];
		$info = \App\Module::getEntityInfo($mixed);
		if ($info) {
			$data['tablename'] = $info['tablename'];
			$fieldnames = $info['fieldname'];
			if (strpos(',', $fieldnames) !== false) {
				$fieldnames = sprintf('concat(%s)', implode(",' ',", $info['fieldnameArr']));
			}
			$data['fieldname'] = $fieldnames;
			$colums = [];
			foreach ($info['fieldnameArr'] as $fieldname) {
				$colums[] = $info['tablename'] . '.' . $fieldname;
			}
			$data['colums'] = implode(',', $colums);
		}

		return $data;
	}

	// MODULE RECORD
	protected static $crmRecordIdMetadataCache = [];

	/**
	 * Function gets record metadata.
	 *
	 * @param int|array $mixedid
	 *
	 * @return array
	 */
	public static function getCRMRecordMetadata($mixedid)
	{
		$multimode = is_array($mixedid);

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
	 * Function get module field infos.
	 *
	 * @param int|string $mixed
	 * @param bool       $returnByColumn
	 *
	 * @return mixed[]
	 */
	public static function getModuleFieldInfos($module, $returnByColumn = false)
	{
		if (is_numeric($module)) {
			$module = \App\Module::getModuleName($module);
		}
		$cacheName = 'getModuleFieldInfosByName';
		if (!\App\Cache::has($cacheName, $module)) {
			$dataReader = (new \App\Db\Query())
				->from('vtiger_field')
				->where(['tabid' => $module === 'Calendar' ? [9, 16] : \App\Module::getModuleId($module)])
				->createCommand()->query();
			$fieldInfoByName = $fieldInfoByColumn = [];
			while ($row = $dataReader->read()) {
				$fieldInfoByName[$row['fieldname']] = $row;
				$fieldInfoByColumn[$row['columnname']] = $row;
			}
			\App\Cache::save($cacheName, $module, $fieldInfoByName);
			\App\Cache::save('getModuleFieldInfosByColumn', $module, $fieldInfoByColumn);
		}
		if ($returnByColumn) {
			return \App\Cache::get('getModuleFieldInfosByColumn', $module);
		}

		return \App\Cache::get($cacheName, $module);
	}

	/**
	 * Function to gets mudule field ID.
	 *
	 * @param string|int $moduleId
	 * @param string|int $mixed
	 * @param bool       $onlyactive
	 *
	 * @return int|bool
	 */
	public static function getModuleFieldId($moduleId, $mixed, $onlyactive = true)
	{
		$field = \App\Field::getFieldInfo($mixed, $moduleId);

		if ($field) {
			if ($onlyactive && ($field['presence'] != '0' && $field['presence'] != '2')) {
				$field = null;
			}
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
		if ($encode && is_string($string)) {
			$string = addslashes(str_replace(array_values($popup_toHtml), array_keys($popup_toHtml), $string));
		}

		return $string;
	}

	public static function br2nl($str)
	{
		$str = preg_replace("/(\r\n)/", '\\r\\n', $str);
		$str = preg_replace("/'/", ' ', $str);
		$str = preg_replace('/"/', ' ', $str);

		return $str;
	}

	public static function suppressHTMLTags($string)
	{
		return preg_replace(['/</', '/>/', '/"/'], ['&lt;', '&gt;', '&quot;'], $string);
	}

	/** 	Function used to retrieve a single field value from database
	 * 	@param string $tableName - tablename from which we will retrieve the field value
	 * 	@param string $fieldName - fieldname to which we want to get the value from database
	 * 	@param string $idName	 - idname which is the name of the entity id in the table like, inoviceid, etc.,
	 * 	@param int    $id	 - entity id
	 * 	return mixed $fieldval  - field value of the needed fieldname from database will be returned
	 */
	public static function getSingleFieldValue($tableName, $fieldName, $idName, $id)
	{
		return (new \App\Db\Query())->select([$fieldName])->from($tableName)->where([$idName => $id])->scalar();
	}

	/**     function used to change the Type of Data for advanced filters in custom view and Reports
	 * *     @param string $table_name - tablename value from field table
	 * *     @param string $column_nametable_name - columnname value from field table
	 * *     @param string $type_of_data - current type of data of the field. It is to return the same TypeofData
	 * *            if the  field is not matched with the $new_field_details array.
	 * *     return string $type_of_data - If the string matched with the $new_field_details array then the Changed
	 * *	       typeofdata will return, else the same typeofdata will return.
	 * *
	 * *     EXAMPLE: If you have a field entry like this:
	 * *
	 * * 		fieldlabel         | typeofdata | tablename            | columnname       |
	 * *	        -------------------+------------+----------------------+------------------+
	 * *		Potential Name     | I~O        | vtiger_quotes        | potentialid      |
	 * *
	 * *     Then put an entry in $new_field_details  like this:
	 * *
	 * *				"vtiger_quotes:potentialid"=>"V",
	 * *
	 * *	Now in customview and report's advance filter this field's criteria will be show like string.
	 * *
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
			'vtiger_seticketsrel:crmid' => 'V',
			'vtiger_seticketsrel:ticketid' => 'V',
			'vtiger_vendorcontactrel:vendorid' => 'V',
			'vtiger_vendorcontactrel:contactid' => 'V',
			'vtiger_pricebook:currency_id' => 'V',
		];

		//If the Fields details does not match with the array, then we return the same typeofdata
		if (isset($new_field_details[$field])) {
			$type_of_data = $new_field_details[$field];
		}

		return $type_of_data;
	}

	public static function getRangeTime($timeMinutesRange, $showEmptyValue = true)
	{
		$short = [];
		$full = [];
		$years = ((int) $timeMinutesRange) / (60 * 24 * 365);
		$years = floor($years);
		if (!empty($years)) {
			$short[] = $years == 1 ? $years . \App\Language::translate('LBL_Y') : $years . \App\Language::translate('LBL_YRS');
			$full[] = $years == 1 ? $years . \App\Language::translate('LBL_YEAR') : $years . \App\Language::translate('LBL_YEARS');
		}
		$days = self::myBcmod(($timeMinutesRange), (60 * 24 * 365));
		$days = ($days) / (24 * 60);
		$days = floor($days);
		if (!empty($days)) {
			$short[] = $days . \App\Language::translate('LBL_D');
			$full[] = $days == 1 ? $days . \App\Language::translate('LBL_DAY') : $days . \App\Language::translate('LBL_DAYS');
		}
		$hours = self::myBcmod(($timeMinutesRange), (24 * 60));
		$hours = ($hours) / (60);
		$hours = floor($hours);
		if (!empty($hours)) {
			$short[] = $hours . \App\Language::translate('LBL_H');
			$full[] = $hours == 1 ? $hours . \App\Language::translate('LBL_HOUR') : $hours . \App\Language::translate('LBL_HOURS');
		}
		$minutes = self::myBcmod(($timeMinutesRange), (60));
		$minutes = floor($minutes);
		if (!empty($timeMinutesRange) || $showEmptyValue) {
			$short[] = $minutes . \App\Language::translate('LBL_M');
			$full[] = $minutes == 1 ? $minutes . \App\Language::translate('LBL_MINUTE') : $minutes . \App\Language::translate('LBL_MINUTES');
		}

		return [
			'short' => implode(' ', $short),
			'full' => implode(' ', $full),
		];
	}

	/**
	 * myBcmod - get modulus (substitute for bcmod)
	 * string my_bcmod ( string left_operand, int modulus )
	 * left_operand can be really big, but be carefull with modulus :(
	 * by Andrius Baranauskas and Laurynas Butkus :) Vilnius, Lithuania.
	 * */
	public static function myBcmod($x, $y)
	{
		// how many numbers to take at once? carefull not to exceed (int)
		$take = 5;
		$mod = '';

		do {
			$a = (int) $mod . substr($x, 0, $take);
			$x = substr($x, $take);
			$mod = $a % $y;
		} while (strlen($x));

		return (int) $mod;
	}

	public static function getArrayFromValue($values)
	{
		if (is_array($values)) {
			return $values;
		}
		if ($values == '') {
			return [];
		}
		if (strpos($values, ',') === false) {
			$array[] = $values;
		} else {
			$array = explode(',', $values);
		}

		return $array;
	}

	public static function throwNewException($e, $die = true, $tpl = 'OperationNotPermitted.tpl')
	{
		$message = is_object($e) ? $e->getMessage() : $e;
		if (!is_array($message)) {
			if (strpos($message, '||') === false) {
				$message = \App\Language::translateSingleMod($message, 'Other.Exceptions');
			} else {
				$params = explode('||', $message);
				$message = call_user_func_array('vsprintf', [\App\Language::translateSingleMod(array_shift($params), 'Other.Exceptions'), $params]);
			}
		}
		if (\App\Config::$requestMode === 'API') {
			throw new \APIException($message, 401);
		}
		if (\App\Request::_isAjax()) {
			$response = new \Vtiger_Response();
			$response->setEmitType(\Vtiger_Response::$EMIT_JSON);
			$trace = '';
			if (\AppConfig::debug('DISPLAY_EXCEPTION_BACKTRACE') && is_object($e)) {
				$trace = str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', $e->getTraceAsString());
			}
			if (is_object($e)) {
				$response->setHeader('HTTP/1.1 ' . $e->getCode() . ' ' . $e->getMessage());
				$response->setError($e->getCode(), $e->getMessage(), $trace);
			} else {
				$response->setError('error', $message, $trace);
			}
			$response->emit();
		} else {
			$viewer = new \Vtiger_Viewer();
			$viewer->assign('MESSAGE', $message);
			$viewer->view($tpl, 'Vtiger');
		}
		if ($die) {
			trigger_error(print_r($message, true), E_USER_ERROR);
			if (is_object($message)) {
				throw new $message();
			} elseif (is_array($message)) {
				throw new \Exception($message['message']);
			} else {
				throw new \Exception($message);
			}
		}
	}

	public static function getHtmlOrPlainText($content)
	{
		if ($content !== strip_tags($content)) {
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
	public static function recurseDelete($src, $outsideRoot = false)
	{
		$rootDir = ($outsideRoot || strpos($src, ROOT_DIRECTORY) === 0) ? '' : ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		if (!file_exists($rootDir . $src)) {
			return;
		}
		$dirs = [];
		$dirs[] = $rootDir . $src;
		if (is_dir($src)) {
			foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isDir()) {
					$dirs[] = $rootDir . $src . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
				} else {
					unlink($rootDir . $src . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
				}
			}
			arsort($dirs);
			foreach ($dirs as $dir) {
				rmdir($dir);
			}
		} else {
			unlink($rootDir . $src);
		}
	}

	/**
	 * The function copies files.
	 *
	 * @param string $src
	 * @param string $dest
	 *
	 * @return string
	 */
	public static function recurseCopy($src, $dest)
	{
		$rootDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		if (!file_exists($rootDir . $src)) {
			return;
		}
		if ($dest && substr($dest, -1) !== '/' && substr($dest, -1) !== '\\') {
			$dest = $dest . DIRECTORY_SEPARATOR;
		}
		$dest = $rootDir . $dest;
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isDir() && !file_exists($dest . $iterator->getSubPathName())) {
				mkdir($dest . $iterator->getSubPathName(), 0755);
			} elseif (!$item->isDir()) {
				copy($item->getRealPath(), $dest . $iterator->getSubPathName());
			}
		}
	}

	public static function parseBytes($str)
	{
		if (is_numeric($str)) {
			return (float) $str;
		}
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
			}
		}

		return (float) $bytes;
	}

	public static function showBytes($bytes, &$unit = null)
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

	public static function getMaxUploadSize()
	{
		// find max filesize value
		$maxFileSize = self::parseBytes(ini_get('upload_max_filesize'));
		$maxPostSize = self::parseBytes(ini_get('post_max_size'));

		if ($maxPostSize && $maxPostSize < $maxFileSize) {
			$maxFileSize = $maxPostSize;
		}

		return $maxFileSize;
	}

	public static function getMinimizationOptions($type = 'js')
	{
		switch ($type) {
			case 'js':
				$return = \AppConfig::developer('MINIMIZE_JS');
				break;
			case 'css':
				$return = \AppConfig::developer('MINIMIZE_CSS');
				break;
		}

		return $return;
	}

	public static function getInitials($name)
	{
		$initial = '';
		foreach (explode(' ', $name) as $word) {
			$initial .= strtoupper($word[0]);
		}

		return $initial;
	}

	public static function getDiskSpace($dir = '')
	{
		if ($dir == '') {
			$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		}
		$total = disk_total_space($dir);
		$free = disk_free_space($dir);
		$used = $total - $free;

		return ['total' => $total, 'free' => $free, 'used' => $used];
	}

	public static function getDefaultCurrencyInfo()
	{
		$allCurrencies = self::getAllCurrency(true);
		foreach ($allCurrencies as $currency) {
			if ((int) $currency['defaultid'] === -11) {
				return $currency;
			}
		}

		return false;
	}

	/*
	 * Checks if given date is working day, if not returns last working day
	 * @param <Date> $date
	 * @return <Date> - last working y
	 */

	public static function getLastWorkingDay($date)
	{
		if (empty($date)) {
			$date = date('Y-m-d');
		}
		$date = strtotime($date);
		if (date('D', $date) == 'Sat') { // switch to friday the day before
			$lastWorkingDay = date('Y-m-d', strtotime('-1 day', $date));
		} elseif (date('D', $date) == 'Sun') { // switch to friday two days before
			$lastWorkingDay = date('Y-m-d', strtotime('-2 day', $date));
		} else {
			$lastWorkingDay = date('Y-m-d', $date);
		}

		return $lastWorkingDay;
	}

	public static function slug($str, $delimiter = '_')
	{
		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
		$str = mb_convert_encoding((string) $str, 'UTF-8', mb_list_encodings());
		$char_map = [
			// Latin
			'Ă€' => 'A', 'Ă' => 'A', 'Ă‚' => 'A', 'Ă' => 'A', 'Ă„' => 'A', 'Ă…' => 'A', 'Ă†' => 'AE', 'Ă‡' => 'C',
			'Ă' => 'E', 'Ă‰' => 'E', 'ĂŠ' => 'E', 'Ă‹' => 'E', 'ĂŚ' => 'I', 'ĂŤ' => 'I', 'ĂŽ' => 'I', 'ĂŹ' => 'I',
			'Ă' => 'D', 'Ă‘' => 'N', 'Ă’' => 'O', 'Ă“' => 'O', 'Ă”' => 'O', 'Ă•' => 'O', 'Ă–' => 'O', 'Ĺ' => 'O',
			'Ă' => 'O', 'Ă™' => 'U', 'Ăš' => 'U', 'Ă›' => 'U', 'Ăś' => 'U', 'Ĺ°' => 'U', 'Ăť' => 'Y', 'Ăž' => 'TH',
			'Ăź' => 'ss',
			'Ă ' => 'a', 'Ăˇ' => 'a', 'Ă˘' => 'a', 'ĂŁ' => 'a', 'Ă¤' => 'a', 'ĂĄ' => 'a', 'Ă¦' => 'ae', 'Ă§' => 'c',
			'Ă¨' => 'e', 'Ă©' => 'e', 'ĂŞ' => 'e', 'Ă«' => 'e', 'á»‡' => 'e', 'Ă¬' => 'i', 'Ă­' => 'i', 'Ă®' => 'i',
			'ĂŻ' => 'i', 'Ä©' => 'i', 'Ă°' => 'd', 'Ă±' => 'n', 'Ă˛' => 'o', 'Ăł' => 'o', 'Ă´' => 'o', 'á»™' => 'o',
			'Ăµ' => 'o', 'Ă¶' => 'o', 'Ĺ‘' => 'o', 'Ă¸' => 'o', 'Ăą' => 'u', 'Ăş' => 'u', 'Ă»' => 'u', 'ĂĽ' => 'u',
			'Ĺ±' => 'u', 'á»§' => 'u', 'Ă˝' => 'y', 'Ăľ' => 'th', 'Ăż' => 'y',
			// Latin symbols
			'Â©' => '(c)',
			// Greek
			'Î‘' => 'A', 'Î’' => 'B', 'Î“' => 'G', 'Î”' => 'D', 'Î•' => 'E', 'Î–' => 'Z', 'Î—' => 'H', 'Î' => '8',
			'Î™' => 'I', 'Îš' => 'K', 'Î›' => 'L', 'Îś' => 'M', 'Îť' => 'N', 'Îž' => '3', 'Îź' => 'O', 'Î ' => 'P',
			'Îˇ' => 'R', 'ÎŁ' => 'S', 'Î¤' => 'T', 'ÎĄ' => 'Y', 'Î¦' => 'F', 'Î§' => 'X', 'Î¨' => 'PS', 'Î©' => 'W',
			'Î†' => 'A', 'Î' => 'E', 'ÎŠ' => 'I', 'ÎŚ' => 'O', 'ÎŽ' => 'Y', 'Î‰' => 'H', 'ÎŹ' => 'W', 'ÎŞ' => 'I',
			'Î«' => 'Y',
			'Î±' => 'a', 'Î˛' => 'b', 'Îł' => 'g', 'Î´' => 'd', 'Îµ' => 'e', 'Î¶' => 'z', 'Î·' => 'h', 'Î¸' => '8',
			'Îą' => 'i', 'Îş' => 'k', 'Î»' => 'l', 'ÎĽ' => 'm', 'Î˝' => 'n', 'Îľ' => '3', 'Îż' => 'o', 'Ď€' => 'p',
			'Ď' => 'r', 'Ď' => 's', 'Ď„' => 't', 'Ď…' => 'y', 'Ď†' => 'f', 'Ď‡' => 'x', 'Ď' => 'ps', 'Ď‰' => 'w',
			'Î¬' => 'a', 'Î­' => 'e', 'ÎŻ' => 'i', 'ĎŚ' => 'o', 'ĎŤ' => 'y', 'Î®' => 'h', 'ĎŽ' => 'w', 'Ď‚' => 's',
			'ĎŠ' => 'i', 'Î°' => 'y', 'Ď‹' => 'y', 'Î' => 'i',
			// Turkish
			'Ĺž' => 'S', 'Ä°' => 'I', 'Ă‡' => 'C', 'Ăś' => 'U', 'Ă–' => 'O', 'Äž' => 'G',
			'Ĺź' => 's', 'Ä±' => 'i', 'Ă§' => 'c', 'ĂĽ' => 'u', 'Ă¶' => 'o', 'Äź' => 'g',
			// Russian
			'Đ' => 'A', 'Đ‘' => 'B', 'Đ’' => 'V', 'Đ“' => 'G', 'Đ”' => 'D', 'Đ•' => 'E', 'Đ' => 'Yo', 'Đ–' => 'Zh',
			'Đ—' => 'Z', 'Đ' => 'I', 'Đ™' => 'J', 'Đš' => 'K', 'Đ›' => 'L', 'Đś' => 'M', 'Đť' => 'N', 'Đž' => 'O',
			'Đź' => 'P', 'Đ ' => 'R', 'Đˇ' => 'S', 'Đ˘' => 'T', 'ĐŁ' => 'U', 'Đ¤' => 'F', 'ĐĄ' => 'H', 'Đ¦' => 'C',
			'Đ§' => 'Ch', 'Đ¨' => 'Sh', 'Đ©' => 'Sh', 'ĐŞ' => '', 'Đ«' => 'Y', 'Đ¬' => '', 'Đ­' => 'E', 'Đ®' => 'Yu',
			'ĐŻ' => 'Ya',
			'Đ°' => 'a', 'Đ±' => 'b', 'Đ˛' => 'v', 'Đł' => 'g', 'Đ´' => 'd', 'Đµ' => 'e', 'Ń‘' => 'yo', 'Đ¶' => 'zh',
			'Đ·' => 'z', 'Đ¸' => 'i', 'Đą' => 'j', 'Đş' => 'k', 'Đ»' => 'l', 'ĐĽ' => 'm', 'Đ˝' => 'n', 'Đľ' => 'o',
			'Đż' => 'p', 'Ń€' => 'r', 'Ń' => 's', 'Ń‚' => 't', 'Ń' => 'u', 'Ń„' => 'f', 'Ń…' => 'h', 'Ń†' => 'c',
			'Ń‡' => 'ch', 'Ń' => 'sh', 'Ń‰' => 'sh', 'ŃŠ' => '', 'Ń‹' => 'y', 'ŃŚ' => '', 'ŃŤ' => 'e', 'ŃŽ' => 'yu',
			'ŃŹ' => 'ya',
			// Russian by vovpff
			'Ж' => 'Zh', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ю' => 'Yu', 'Я' => 'Ya', 'А' => 'A', 'Б' => 'B',
			'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K',
			'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
			'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ъ' => '', 'Ы' => 'I', 'Ь' => '', 'Э' => 'E', 'ж' => 'zh', 'ч' => 'ch',
			'ш' => 'sh', 'щ' => 'sh', 'ю' => 'yu', 'я' => 'ya', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g',
			'д' => 'd', 'е' => 'e', 'ё' => 'e', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
			'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h',
			'ц' => 'c', 'ъ' => '', 'ы' => 'i', 'ь' => '', 'э' => 'e',
			// Ukrainian
			'Đ„' => 'Ye', 'Đ†' => 'I', 'Đ‡' => 'Yi', 'Ň' => 'G',
			'Ń”' => 'ye', 'Ń–' => 'i', 'Ń—' => 'yi', 'Ň‘' => 'g',
			// Czech
			'ÄŚ' => 'C', 'ÄŽ' => 'D', 'Äš' => 'E', 'Ĺ‡' => 'N', 'Ĺ' => 'R', 'Ĺ ' => 'S', 'Ĺ¤' => 'T', 'Ĺ®' => 'U',
			'Ĺ˝' => 'Z',
			'ÄŤ' => 'c', 'ÄŹ' => 'd', 'Ä›' => 'e', 'Ĺ' => 'n', 'Ĺ™' => 'r', 'Ĺˇ' => 's', 'ĹĄ' => 't', 'ĹŻ' => 'u',
			'Ĺľ' => 'z',
			// Polish
			'Ä„' => 'A', 'Ä†' => 'C', 'Ä' => 'e', 'Ĺ' => 'L', 'Ĺ' => 'N', 'Ă“' => 'o', 'Ĺš' => 'S', 'Ĺą' => 'Z',
			'Ĺ»' => 'Z',
			'Ä…' => 'a', 'Ä‡' => 'c', 'Ä™' => 'e', 'Ĺ‚' => 'l', 'Ĺ„' => 'n', 'Ăł' => 'o', 'Ĺ›' => 's', 'Ĺş' => 'z',
			'ĹĽ' => 'z',
			// Latvian
			'Ä€' => 'A', 'ÄŚ' => 'C', 'Ä’' => 'E', 'Ä˘' => 'G', 'ÄŞ' => 'i', 'Ä¶' => 'k', 'Ä»' => 'L', 'Ĺ…' => 'N',
			'Ĺ ' => 'S', 'ĹŞ' => 'u', 'Ĺ˝' => 'Z',
			'Ä' => 'a', 'ÄŤ' => 'c', 'Ä“' => 'e', 'ÄŁ' => 'g', 'Ä«' => 'i', 'Ä·' => 'k', 'ÄĽ' => 'l', 'Ĺ†' => 'n',
			'Ĺˇ' => 's', 'Ĺ«' => 'u', 'Ĺľ' => 'z',
		];

		// Transliterate characters to ASCII
		$str = str_replace(array_keys($char_map), $char_map, $str);
		// Replace non-alphanumeric characters with our delimiter
		$str = preg_replace('/[^\p{L}\p{Nd}\.]+/u', $delimiter, $str);
		// Remove delimiter from ends
		$str = trim($str, $delimiter);

		return $str;
	}

	/*
	 * Function that returns conversion info from default system currency to chosen one
	 * @param <Integer> $currencyId - id of currency for which we want to retrieve conversion rate to default currency
	 * @param <Date> $date - date of exchange rates, if empty then rate from yesterday
	 * @return <Array> - array containing:
	 * 		date - date of rate
	 * 		value - conversion 1 default currency -> $currencyId
	 * 		conversion - 1 $currencyId -> default currency
	 */

	public static function getConversionRateInfo($currencyId, $date = '')
	{
		$currencyUpdateModel = \Settings_CurrencyUpdate_Module_Model::getCleanInstance();
		$defaultCurrencyId = self::getDefaultCurrencyInfo()['id'];
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
			$value = $currencyUpdateModel->getCRMConversionRate($currencyId, $defaultCurrencyId, $date);
			$info['value'] = $value == 0 ? 1.0 : round($value, 5);
			$info['conversion'] = $value == 0 ? 1.0 : round(1 / $value, 5);
		}

		return $info;
	}

	public static function getQueryParams($url)
	{
		$queryStr = parse_url(htmlspecialchars_decode($url), PHP_URL_QUERY);
		parse_str($queryStr, $queryParams);

		return $queryParams;
	}

	public static function arrayDiffAssocRecursive($array1, $array2)
	{
		$difference = [];
		foreach ($array1 as $key => $value) {
			if (is_array($value)) {
				if (!isset($array2[$key]) || !is_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$newDiff = self::arrayDiffAssocRecursive($value, $array2[$key]);
					if (!empty($newDiff)) {
						$difference[$key] = $newDiff;
					}
				}
			} elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
				$difference[$key] = $value;
			}
		}

		return $difference;
	}
}
