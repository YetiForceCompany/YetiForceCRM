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

	public static function userIsAdministrator($user)
	{
		return (isset($user->is_admin) && $user->is_admin == 'on');
	}

	public static function currentUserJSDateFormat($localformat)
	{
		$current_user = vglobal('current_user');
		switch ($current_user->date_format) {
			case 'dd-mm-yyyy': $dt_popup_fmt = "%d-%m-%Y";
				break;
			case 'mm-dd-yyyy': $dt_popup_fmt = "%m-%d-%Y";
				break;
			case 'yyyy-mm-dd': $dt_popup_fmt = "%Y-%m-%d";
				break;
			case 'dd.mm.yyyy': $dt_popup_fmt = "%d.%m.%Y";
				break;
			case 'mm.dd.yyyy': $dt_popup_fmt = "%m.%d.%Y";
				break;
			case 'yyyy.mm.dd': $dt_popup_fmt = "%Y.%m.%d";
				break;
			case 'dd/mm/yyyy': $dt_popup_fmt = "%d/%m/%Y";
				break;
			case 'mm/dd/yyyy': $dt_popup_fmt = "%m/%d/%Y";
				break;
			case 'yyyy/mm/dd': $dt_popup_fmt = "%Y/%m/%d";
				break;
		}
		return $dt_popup_fmt;
	}

	/**
	 * This function returns the date in user specified format.
	 * limitation is that mm-dd-yyyy and dd-mm-yyyy will be considered same by this API.
	 * As in the date value is on mm-dd-yyyy and user date format is dd-mm-yyyy then the mm-dd-yyyy
	 * value will be return as the API will be considered as considered as in same format.
	 * this due to the fact that this API tries to consider the where given date is in user date
	 * format. we need a better gauge for this case.
	 * @global Users $current_user
	 * @param Date $cur_date_val the date which should a changed to user date format.
	 * @return Date
	 */
	public static function currentUserDisplayDate($value)
	{
		$current_user = vglobal('current_user');
		$dat_fmt = $current_user->date_format;
		if ($dat_fmt == '') {
			$dat_fmt = 'yyyy-mm-dd';
		}
		$date = new \DateTimeField($value);
		return $date->getDisplayDate();
	}

	public static function currentUserDisplayDateNew()
	{
		$current_user = vglobal('current_user');
		$date = new \DateTimeField(null);
		return $date->getDisplayDate($current_user);
	}

	// i18n
	public static function getTranslatedString($str, $module = '')
	{
		return \Vtiger_Language_Handler::getTranslatedString($str, $module);
	}

	// CURRENCY
	protected static $userIdCurrencyIdCache = [];

	public static function userCurrencyId($userid)
	{
		$adb = \PearDatabase::getInstance();
		if (!isset(self::$userIdCurrencyIdCache[$userid])) {
			$result = $adb->pquery('SELECT id,currency_id FROM vtiger_users', []);
			while ($row = $adb->fetch_array($result)) {
				self::$userIdCurrencyIdCache[$row['id']] = $row['currency_id'];
			}
		}
		return self::$userIdCurrencyIdCache[$userid];
	}

	protected static $currencyInfoCache = [];

	protected static function getCurrencyInfo($currencyid)
	{
		if (!isset(self::$currencyInfoCache[$currencyid])) {
			$db = \PearDatabase::getInstance();
			$result = $db->query('SELECT * FROM vtiger_currency_info');
			while ($row = $db->fetch_array($result)) {
				self::$currencyInfoCache[$row['id']] = $row;
			}
		}
		return self::$currencyInfoCache[$currencyid];
	}

	public static function getAllCurrency($onlyActive = false)
	{
		if (count(self::$currencyInfoCache) == 0) {
			$db = \PearDatabase::getInstance();
			$result = $db->query('SELECT * FROM vtiger_currency_info');
			while ($row = $db->fetch_array($result)) {
				self::$currencyInfoCache[$row['id']] = $row;
			}
		}
		if ($onlyActive) {
			$currencies = [];
			foreach (self::$currencyInfoCache as $currency) {
				if ($currency['currency_status'] == 'Active') {
					$currencies[$currency['id']] = $currency;
				}
			}
			return $currencies;
		} else {
			return self::$currencyInfoCache;
		}
	}

	public static function getCurrencyName($currencyid, $show_symbol = true)
	{
		$currencyInfo = self::getCurrencyInfo($currencyid);
		if ($show_symbol) {
			return sprintf("%s : %s", Deprecated::getTranslatedCurrencyString($currencyInfo['currency_name']), $currencyInfo['currency_symbol']);
		}
		return $currencyInfo['currency_name'];
	}

	public static function getCurrencySymbolandRate($currencyid)
	{
		$currencyInfo = self::getCurrencyInfo($currencyid);
		$currencyRateSymbol = array(
			'rate' => $currencyInfo['conversion_rate'],
			'symbol' => $currencyInfo['currency_symbol']
		);
		return $currencyRateSymbol;
	}

	// MODULE
	protected static $moduleIdNameCache = [];
	protected static $moduleNameIdCache = [];
	protected static $moduleIdDataCache = [];

	protected static function getBasicModuleInfo($mixed)
	{
		$id = $name = NULL;
		if (is_numeric($mixed))
			$id = $mixed;
		else
			$name = $mixed;
		$reload = false;
		if ($name) {
			if (!isset(self::$moduleNameIdCache[$name])) {
				$reload = true;
			}
		} else if ($id) {
			if (!isset(self::$moduleIdNameCache[$id])) {
				$reload = true;
			}
		}
		if ($reload) {
			$adb = \PearDatabase::getInstance();
			$result = $adb->pquery('SELECT tabid, name, ownedby FROM vtiger_tab', []);
			while ($row = $adb->fetch_array($result)) {
				self::$moduleIdNameCache[$row['tabid']] = $row;
				self::$moduleNameIdCache[$row['name']] = $row;
			}
		}
		return $id ? self::$moduleIdNameCache[$id] : self::$moduleNameIdCache[$name];
	}

	public static function getAllModules($isEntityType = true, $showRestricted = false, $presence = false, $colorActive = false, $ownedby = false)
	{
		$moduleList = self::$moduleIdNameCache;
		if (empty($moduleList)) {
			$db = \PearDatabase::getInstance();
			$result = $db->query('SELECT * FROM vtiger_tab');
			while ($row = $db->fetch_array($result)) {
				self::$moduleIdNameCache[$row['tabid']] = $row;
				self::$moduleNameIdCache[$row['name']] = $row;
				self::$moduleIdDataCache[$row['tabid']] = $row;
			}
			$moduleList = self::$moduleIdNameCache;
		}
		$restrictedModules = array('SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter');
		foreach ($moduleList as $id => &$module) {
			if (!$showRestricted && in_array($module['name'], $restrictedModules)) {
				unset($moduleList[$id]);
			}
			if ($isEntityType && $module['isentitytype'] == 0) {
				unset($moduleList[$id]);
			}
			if ($presence !== false && $module['presence'] != $presence) {
				unset($moduleList[$id]);
			}
			if ($colorActive !== false && $module['coloractive'] != 1) {
				unset($moduleList[$id]);
			}
			if ($ownedby !== false && $module['ownedby'] != $ownedby) {
				unset($moduleList[$id]);
			}
		}
		return $moduleList;
	}

	public static function getModuleData($mixed)
	{
		if (empty($mixed)) {
			$log = \LoggerManager::getInstance();
			$log->error(__CLASS__ . ':' . __FUNCTION__ . ' - Required parameter missing');
			return false;
		}
		$id = $name = NULL;
		if (is_numeric($mixed))
			$id = $mixed;
		else
			$name = (string) $mixed;
		$reload = false;

		if ($name && !isset(self::$moduleNameIdCache[$name])) {
			$reload = true;
		} else if ($id && !isset(self::$moduleIdNameCache[$id])) {
			$reload = true;
		} else {
			if (!$id) {
				$id = self::$moduleNameIdCache[$name]['tabid'];
			}
			if (!isset(self::$moduleIdDataCache[$id])) {
				$reload = true;
			}
		}

		if ($reload) {
			$adb = \PearDatabase::getInstance();
			$result = $adb->query('SELECT * FROM vtiger_tab');
			while ($row = $adb->fetch_array($result)) {
				self::$moduleIdNameCache[$row['tabid']] = $row;
				self::$moduleNameIdCache[$row['name']] = $row;
				self::$moduleIdDataCache[$row['tabid']] = $row;
			}
			if ($name && isset(self::$moduleNameIdCache[$name])) {
				$id = self::$moduleNameIdCache[$name]['tabid'];
			}
		}
		return $id ? self::$moduleIdDataCache[$id] : NULL;
	}

	public static function getModuleId($name)
	{
		$moduleInfo = self::getModuleData($name);
		return $moduleInfo ? $moduleInfo['tabid'] : NULL;
	}

	public static function getModuleName($id)
	{
		$moduleInfo = self::getModuleData($id);
		return $moduleInfo ? $moduleInfo['name'] : NULL;
	}

	public static function getModuleOwner($name)
	{
		$moduleInfo = self::getModuleData($name);
		return $moduleInfo ? $moduleInfo['ownedby'] : NULL;
	}

	/**
	 * this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname)
	 * @param string $mixed - the module name
	 * @return string $fieldsname - the entity field name for the module
	 */
	public static function getEntityModuleSQLColumnString($mixed)
	{
		$data = [];
		$info = \includes\Modules::getEntityInfo($mixed);
		if ($info) {
			$data['tablename'] = $info['tablename'];
			$fieldnames = $info['fieldname'];
			if (strpos(',', $fieldnames) !== false) {
				$fieldnames = sprintf("concat(%s)", implode(",' ',", $info['fieldnameArr']));
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

	public static function getCRMRecordMetadata($mixedid)
	{
		$adb = \PearDatabase::getInstance();
		$multimode = is_array($mixedid);

		$ids = $multimode ? $mixedid : array($mixedid);
		$missing = [];
		foreach ($ids as $id) {
			if ($id && !isset(self::$crmRecordIdMetadataCache[$id])) {
				$missing[] = $id;
			}
		}
		if ($missing) {
			$sql = sprintf("SELECT crmid, setype, deleted, smcreatorid, smownerid, createdtime 
				FROM vtiger_crmentity WHERE %s ", implode(' || ', array_fill(0, count($missing), 'vtiger_crmentity.crmid=?')));
			$result = $adb->pquery($sql, $missing);
			while ($row = $adb->getRow($result)) {
				self::$crmRecordIdMetadataCache[$row['crmid']] = $row;
			}
		}

		$result = [];
		foreach ($ids as $id) {
			if (isset(self::$crmRecordIdMetadataCache[$id])) {
				$result[$id] = self::$crmRecordIdMetadataCache[$id];
			} else {
				$result[$id] = NULL;
			}
		}

		return $multimode ? $result : array_shift($result);
	}

	public static function getCRMRecordType($id)
	{
		$metadata = self::getCRMRecordMetadata($id);
		return $metadata ? $metadata['setype'] : NULL;
	}

	public static function getCRMRecordLabel($id, $default = '')
	{
		$label = \includes\Record::getLabel($id);
		return empty($label) ? $default : $label;
	}

	public static function getOwnerRecordLabel($id)
	{
		return \includes\fields\Owner::getLabel($id);
	}

	protected static $userIdNameCache = [];

	public static function getUserName($id)
	{
		$adb = \PearDatabase::getInstance();
		if (!self::$userIdNameCache[$id]) {
			$result = $adb->pquery('SELECT id, user_name FROM vtiger_users');
			while ($row = $adb->fetch_array($result)) {
				self::$userIdNameCache[$row['id']] = $row['user_name'];
			}
		}
		return (isset(self::$userIdNameCache[$id])) ? self::$userIdNameCache[$id] : NULL;
	}

	protected static $moduleFieldInfoByNameCache = [];

	public static function getModuleFieldInfos($mixed)
	{
		$adb = \PearDatabase::getInstance();

		$moduleInfo = self::getModuleData($mixed);
		$module = $moduleInfo['name'];

		if ($module && (!isset(self::$moduleFieldInfoByNameCache[$module]))) {
			$result = ($module == 'Calendar') ?
				$adb->pquery('SELECT * FROM vtiger_field WHERE tabid=? || tabid=?', array(9, 16)) :
				$adb->pquery('SELECT * FROM vtiger_field WHERE tabid=?', array(self::getModuleId($module)));

			self::$moduleFieldInfoByNameCache[$module] = [];
			while ($row = $adb->getRow($result)) {
				self::$moduleFieldInfoByNameCache[$module][$row['fieldname']] = $row;
			}
		}
		return isset(self::$moduleFieldInfoByNameCache[$module]) ? self::$moduleFieldInfoByNameCache[$module] : NULL;
	}

	public static function getModuleFieldInfoWithId($fieldid)
	{
		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery('SELECT * FROM vtiger_field WHERE fieldid=?', array($fieldid));
		return ($adb->num_rows($result)) ? $adb->fetch_array($result) : NULL;
	}

	public static function getModuleFieldInfo($moduleid, $mixed)
	{
		$field = NULL;
		if (empty($moduleid) && is_numeric($mixed)) {
			$field = self::getModuleFieldInfoWithId($mixed);
		} else {
			$fieldsInfo = self::getModuleFieldInfos($moduleid);
			if ($fieldsInfo) {
				if (is_numeric($mixed)) {
					foreach ($fieldsInfo as $name => $row) {
						if ($row['fieldid'] == $mixed) {
							$field = $row;
							break;
						}
					}
				} else {
					$field = isset($fieldsInfo[$mixed]) ? $fieldsInfo[$mixed] : NULL;
				}
			}
		}
		return $field;
	}

	public static function getModuleFieldId($moduleid, $mixed, $onlyactive = true)
	{
		$field = self::getModuleFieldInfo($moduleid, $mixed, $onlyactive);

		if ($field) {
			if ($onlyactive && ($field['presence'] != '0' && $field['presence'] != '2')) {
				$field = NULL;
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

	public static function fromHTML($string, $encode = true)
	{
		if (is_string($string)) {
			if (preg_match('/(script).*(\/script)/i', $string)) {
				$string = preg_replace(array('/</', '/>/', '/"/'), array('&lt;', '&gt;', '&quot;'), $string);
			}
		}
		return $string;
	}

	public static function fromHTML_FCK($string)
	{
		if (is_string($string)) {
			if (preg_match('/(script).*(\/script)/i', $string)) {
				$string = str_replace('script', '', $string);
			}
		}
		return $string;
	}

	public static function fromHTML_Popup($string, $encode = true)
	{
		$popup_toHtml = array(
			'"' => '&quot;',
			"'" => '&#039;',
		);
		//if($encode && is_string($string))$string = html_entity_decode($string, ENT_QUOTES);
		if ($encode && is_string($string)) {
			$string = addslashes(str_replace(array_values($popup_toHtml), array_keys($popup_toHtml), $string));
		}
		return $string;
	}

	public static function br2nl($str)
	{
		$str = preg_replace("/(\r\n)/", "\\r\\n", $str);
		$str = preg_replace("/'/", " ", $str);
		$str = preg_replace("/\"/", " ", $str);
		return $str;
	}

	public static function suppressHTMLTags($string)
	{
		return preg_replace(array('/</', '/>/', '/"/'), array('&lt;', '&gt;', '&quot;'), $string);
	}

	public static function getInventoryTermsAndCondition()
	{
		$adb = \PearDatabase::getInstance();
		$sql = "select tandc from vtiger_inventory_tandc";
		$result = $adb->pquery($sql, []);
		$tandc = $adb->query_result($result, 0, "tandc");
		return $tandc;
	}

	public static function initStorageFileDirectory($module = false)
	{
		$filepath = 'storage/';

		if ($module && in_array($module, array('Users', 'Contacts', 'Products', 'OSSMailView'))) {
			$filepath .= $module . '/';
		}
		if (!is_dir($filepath)) {
			//create new folder
			mkdir($filepath);
		}
		$year = date('Y');
		$month = date('F');
		$day = date('j');
		$week = '';
		$filepath .= $year;
		if (!is_dir($filepath)) {
			//create new folder
			mkdir($filepath);
		}
		$filepath .= '/' . $month;
		if (!is_dir($filepath)) {
			//create new folder
			mkdir($filepath);
		}

		if ($day > 0 && $day <= 7)
			$week = 'week1';
		elseif ($day > 7 && $day <= 14)
			$week = 'week2';
		elseif ($day > 14 && $day <= 21)
			$week = 'week3';
		elseif ($day > 21 && $day <= 28)
			$week = 'week4';
		else
			$week = 'week5';

		$filepath .= '/' . $week;
		if (!is_dir($filepath)) {
			//create new folder
			mkdir($filepath);
		}
		return $filepath . '/';
	}

	public static function getMergedDescriptionCustomVars($fields, $description)
	{
		foreach ($fields['custom'] as $columnname) {
			$token_data = '$custom-' . $columnname . '$';
			$token_value = '';
			switch ($columnname) {
				case 'currentdate': $token_value = date("F j, Y");
					break;
				case 'currenttime': $token_value = date("G:i:s T");
					break;
			}
			$description = str_replace($token_data, $token_value, $description);
		}
		return $description;
	}

	/** 	Function used to retrieve a single field value from database
	 * 	@param string $tablename - tablename from which we will retrieve the field value
	 * 	@param string $fieldname - fieldname to which we want to get the value from database
	 * 	@param string $idname	 - idname which is the name of the entity id in the table like, inoviceid, etc.,
	 * 	@param int    $id	 - entity id
	 * 	return string $fieldval  - field value of the needed fieldname from database will be returned
	 */
	public static function getSingleFieldValue($tablename, $fieldname, $idname, $id)
	{
		$adb = \PearDatabase::getInstance();
		$fieldval = $adb->query_result($adb->pquery("select $fieldname from $tablename where $idname = ?", array($id)), 0, $fieldname);
		return $fieldval;
	}

	/**
	 *  Function to get recurring info depending on the recurring type
	 *  return  $recurObj       - Object of class RecurringType
	 */
	public static function getRecurringObjValue()
	{
		$recurring_data = [];
		if (!\AppRequest::isEmpty('recurringtype') && \AppRequest::get('recurringtype') != '--None--') {
			if (!\AppRequest::isEmpty('date_start')) {
				$startDate = \AppRequest::get('date_start');
			}
			if (!\AppRequest::isEmpty('calendar_repeat_limit_date')) {
				$endDate = \AppRequest::get('calendar_repeat_limit_date');
				$recurring_data['recurringenddate'] = $endDate;
			} elseif (!\AppRequest::isEmpty('due_date')) {
				$endDate = \AppRequest::get('due_date');
			}
			if (!\AppRequest::isEmpty('time_start')) {
				$startTime = \AppRequest::get('time_start');
			}
			if (!\AppRequest::isEmpty('time_end')) {
				$endTime = \AppRequest::get('time_end');
			}

			$recurring_data['startdate'] = $startDate;
			$recurring_data['starttime'] = $startTime;
			$recurring_data['enddate'] = $endDate;
			$recurring_data['endtime'] = $endTime;

			$recurring_data['type'] = \AppRequest::get('recurringtype');
			if (\AppRequest::get('recurringtype') == 'Weekly') {
				if (!\AppRequest::isEmpty('sun_flag'))
					$recurring_data['sun_flag'] = true;
				if (!\AppRequest::isEmpty('mon_flag'))
					$recurring_data['mon_flag'] = true;
				if (!\AppRequest::isEmpty('tue_flag'))
					$recurring_data['tue_flag'] = true;
				if (!\AppRequest::isEmpty('wed_flag'))
					$recurring_data['wed_flag'] = true;
				if (!\AppRequest::isEmpty('thu_flag'))
					$recurring_data['thu_flag'] = true;
				if (!\AppRequest::isEmpty('fri_flag'))
					$recurring_data['fri_flag'] = true;
				if (!\AppRequest::isEmpty('sat_flag'))
					$recurring_data['sat_flag'] = true;
			}
			elseif (\AppRequest::get('recurringtype') == 'Monthly') {
				if (!\AppRequest::isEmpty('repeatMonth'))
					$recurring_data['repeatmonth_type'] = \AppRequest::get('repeatMonth');
				if ($recurring_data['repeatmonth_type'] == 'date') {
					if (!\AppRequest::isEmpty('repeatMonth_date'))
						$recurring_data['repeatmonth_date'] = \AppRequest::get('repeatMonth_date');
					else
						$recurring_data['repeatmonth_date'] = 1;
				}
				elseif ($recurring_data['repeatmonth_type'] == 'day') {
					$recurring_data['repeatmonth_daytype'] = \AppRequest::get('repeatMonth_daytype');
					switch (\AppRequest::get('repeatMonth_day')) {
						case 0 :
							$recurring_data['sun_flag'] = true;
							break;
						case 1 :
							$recurring_data['mon_flag'] = true;
							break;
						case 2 :
							$recurring_data['tue_flag'] = true;
							break;
						case 3 :
							$recurring_data['wed_flag'] = true;
							break;
						case 4 :
							$recurring_data['thu_flag'] = true;
							break;
						case 5 :
							$recurring_data['fri_flag'] = true;
							break;
						case 6 :
							$recurring_data['sat_flag'] = true;
							break;
					}
				}
			}
			if (!\AppRequest::isEmpty('repeat_frequency'))
				$recurring_data['repeat_frequency'] = \AppRequest::get('repeat_frequency');

			$recurObj = \RecurringType::fromUserRequest($recurring_data);
			return $recurObj;
		}
	}

	public static function getTicketComments($ticketid)
	{
		$adb = \PearDatabase::getInstance();
		$moduleName = self::getCRMRecordType($ticketid);
		$commentlist = '';
		$sql = "SELECT commentcontent FROM vtiger_modcomments WHERE related_to = ?";
		$result = $adb->pquery($sql, array($ticketid));
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$comment = $adb->query_result($result, $i, 'commentcontent');
			if ($comment != '') {
				$commentlist .= '<br><br>' . $comment;
			}
		}
		if ($commentlist != '')
			$commentlist = '<br><br>' . \includes\Language::translate("The comments are", $moduleName) . ' : ' . $commentlist;
		return $commentlist;
	}

	public static function generateRandomPassword()
	{
		$salt = "abcdefghijklmnopqrstuvwxyz0123456789";
		srand((double) microtime() * 1000000);
		$i = 0;
		while ($i <= 7) {
			$num = rand() % 33;
			$tmp = substr($salt, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}

	/** gives the option  to display  the tagclouds or not for the current user
	 * * @param $id -- user id:: Type integer
	 * * @returns true or false in $tag_cloud_view
	 * * Added to provide User based Tagcloud
	 * */
	public static function getTagCloudView($id = "")
	{
		$adb = \PearDatabase::getInstance();
		if ($id == '') {
			$tag_cloud_status = 1;
		} else {
			$query = "select visible from vtiger_homestuff where userid=? and stufftype='Tag Cloud'";
			$tag_cloud_status = $adb->query_result($adb->pquery($query, array($id)), 0, 'visible');
		}

		if ($tag_cloud_status == 0) {
			$tag_cloud_view = 'true';
		} else {
			$tag_cloud_view = 'false';
		}
		return $tag_cloud_view;
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

		static $new_field_details = Array(
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
			'vtiger_recurringevents:recurringtype' => 'V',
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
		);

		//If the Fields details does not match with the array, then we return the same typeofdata
		if (isset($new_field_details[$field])) {
			$type_of_data = $new_field_details[$field];
		}
		return $type_of_data;
	}

	public static function getActivityType($id)
	{
		$adb = \PearDatabase::getInstance();
		$query = "select activitytype from vtiger_activity where activityid=?";
		$res = $adb->pquery($query, array($id));
		$activity_type = $adb->query_result($res, 0, "activitytype");
		return $activity_type;
	}

	public static function mkCountQuery($query)
	{
		// Remove all the \n, \r and white spaces to keep the space between the words consistent.
		// This is required for proper pattern matching for words like ' FROM ', 'ORDER BY', 'GROUP BY' as they depend on the spaces between the words.
		$query = preg_replace("/[\n\r\s]+/", " ", $query);

		//Strip of the current SELECT fields and replace them by "select count(*) as count"
		// Space across FROM has to be retained here so that we do not have a clash with string "from" found in select clause
		$query = sprintf('SELECT count(*) AS count %s', substr($query, stripos($query, ' FROM '), strlen($query)));

		//Strip of any "GROUP BY" clause
		if (stripos($query, 'GROUP BY') > 0)
			$query = substr($query, 0, stripos($query, 'GROUP BY'));

		//Strip of any "ORDER BY" clause
		if (stripos($query, 'ORDER BY') > 0)
			$query = substr($query, 0, stripos($query, 'ORDER BY'));

		return $query;
	}

	/** Function to get unitprice for a given product id
	 * @param $productid -- product id :: Type integer
	 * @returns $up -- up :: Type string
	 */
	public static function getUnitPrice($productid, $module = 'Products')
	{
		$adb = \PearDatabase::getInstance();
		if ($module == 'Services') {
			$query = "select unit_price from vtiger_service where serviceid=?";
		} else {
			$query = "select unit_price from vtiger_products where productid=?";
		}
		$result = $adb->pquery($query, array($productid));
		$unitpice = $adb->query_result($result, 0, 'unit_price');
		return $unitpice;
	}

	public static function decimalTimeFormat($decTime)
	{
		$hour = floor($decTime);
		$min = round(60 * ($decTime - $hour));
		return array(
			'short' => $hour . vtranslate('LBL_H') . ' ' . $min . vtranslate('LBL_M'),
			'full' => $hour . vtranslate('LBL_HOURS') . ' ' . $min . vtranslate('LBL_MINUTES'),
		);
	}

	public static function getRangeTime($timeMinutesRange, $showEmptyValue = true)
	{
		$short = [];
		$full = [];
		$years = ($timeMinutesRange) / (60 * 24 * 365);
		$years = floor($years);
		if (!empty($years)) {
			$short[] = $years == 1 ? $years . vtranslate('LBL_Y') : $years . vtranslate('LBL_YRS');
			$full[] = $years == 1 ? $years . vtranslate('LBL_YEAR') : $years . vtranslate('LBL_YEARS');
		}
		$days = self::myBcmod(($timeMinutesRange), (60 * 24 * 365));
		$days = ($days) / (24 * 60);
		$days = floor($days);
		if (!empty($days)) {
			$short[] = $days . vtranslate('LBL_D');
			$full[] = $days == 1 ? $days . vtranslate('LBL_DAY') : $days . vtranslate('LBL_DAYS');
		}
		$hours = self::myBcmod(($timeMinutesRange), (24 * 60));
		$hours = ($hours) / (60);
		$hours = floor($hours);
		if (!empty($hours)) {
			$short[] = $hours . vtranslate('LBL_H');
			$full[] = $hours == 1 ? $hours . vtranslate('LBL_HOUR') : $hours . vtranslate('LBL_HOURS');
		}
		$minutes = self::myBcmod(($timeMinutesRange), (60));
		$minutes = floor($minutes);
		if (!empty($timeMinutesRange) || $showEmptyValue) {
			$short[] = $minutes . vtranslate('LBL_M');
			$full[] = $minutes == 1 ? $minutes . vtranslate('LBL_MINUTE') : $minutes . vtranslate('LBL_MINUTES');
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
	 * by Andrius Baranauskas and Laurynas Butkus :) Vilnius, Lithuania 
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
			$array = explode(",", $values);
		}
		return $array;
	}

	public static function replaceLinkAddress($string, $pattern, $replace)
	{
		require_once('include/simplehtmldom/simple_html_dom.php');
		$html = str_get_html($string);
		foreach ($html->find('a') as $index => &$element) {
			$element->href = preg_replace($pattern, $replace, $element->href);
		}
		return $html->__toString();
	}

	public static function throwNewException($message, $die = true, $tpl = 'OperationNotPermitted.tpl')
	{
		if (REQUEST_MODE == 'API') {
			throw new \APIException($message, 401);
		}
		$request = \AppRequest::init();
		if ($request->isAjax()) {
			$response = new \Vtiger_Response();
			$response->setEmitType(\Vtiger_Response::$EMIT_JSON);
			$response->setError($message);
			$response->emit();
		} else {
			$viewer = new \Vtiger_Viewer();
			$viewer->assign('MESSAGE', $message);
			$viewer->view($tpl, 'Vtiger');
		}
		if ($die) {
			trigger_error(print_r($message, true), E_USER_ERROR);
			throw new \Exception('');
		}
	}

	public static function removeHtmlTags(array $tags, $html)
	{
		$crmUrl = \AppConfig::main('site_URL');

		$doc = new \DOMDocument('1.0', 'UTF-8');
		$previousValue = libxml_use_internal_errors(TRUE);
		$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
		libxml_clear_errors();
		libxml_use_internal_errors($previousValue);

		foreach ($tags as $tag) {
			$xPath = new \DOMXPath($doc);
			$nodes = $xPath->query('//' . $tag);
			for ($i = 0; $i < $nodes->length; $i++) {
				if ('img' === $tag) {
					$htmlNode = $nodes->item($i)->ownerDocument->saveHTML($nodes->item($i));
					$imgDom = new \DOMDocument();
					$imgDom->loadHTML($htmlNode);
					$xpath = new \DOMXPath($imgDom);
					$src = $xpath->evaluate("string(//img/@src)");
					if ($src == '' || 0 !== strpos('index.php', $src) || FALSE === strpos($crmUrl, $src)) {
						$nodes->item($i)->parentNode->removeChild($nodes->item($i));
					}
				} else {
					$nodes->item($i)->parentNode->removeChild($nodes->item($i));
				}
			}
		}
		$savedHTML = $doc->saveHTML();
		$savedHTML = preg_replace('/<html[^>]+\>/', '', $savedHTML);
		$savedHTML = preg_replace('/<body[^>]+\>/', '', $savedHTML);
		$savedHTML = preg_replace('#<head(.*?)>(.*?)</head>#is', '', $savedHTML);
		$savedHTML = preg_replace('/<!--(.*)-->/Uis', '', $savedHTML);
		$savedHTML = str_replace(['</html>', '</body>', '<?xml encoding="utf-8" ?>'], ['', '', ''], $savedHTML);
		return $savedHTML;
	}

	public static function getHtmlOrPlainText($content)
	{
		if ($content != strip_tags($content)) {
			$content = decode_html($content);
		} else {
			$content = nl2br($content);
		}
		return $content;
	}

	/**
	 * Function to fetch the list of vtiger_groups from group vtiger_table
	 * Takes no value as input
	 * returns the query result set object
	 */
	public static function get_group_options()
	{
		$adb = \PearDatabase::getInstance();
		$sql = "select groupname,groupid from vtiger_groups";
		$result = $adb->pquery($sql, []);
		return $result;
	}

	public static function recurseDelete($src)
	{
		$rootDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		if (!file_exists($rootDir . $src))
			return;
		$dirs = [];
		@chmod($root_dir . $src, 0777);
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

	public function recurseCopy($src, $dest, $delete = false)
	{
		$rootDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		if (!file_exists($rootDir . $src))
			return;

		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isDir() && !file_exists($rootDir . $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
				mkdir($rootDir . $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
			} elseif (!$item->isDir()) {
				copy($item, $rootDir . $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
			}
		}
	}

	protected static $browerCache = false;

	public static function getBrowserInfo()
	{
		if (!self::$browerCache) {
			$HTTP_USER_AGENT = strtolower($_SERVER['HTTP_USER_AGENT']);

			$browser = new \stdClass;
			$browser->ver = 0;
			$browser->https = false;
			$browser->win = strpos($HTTP_USER_AGENT, 'win') != false;
			$browser->mac = strpos($HTTP_USER_AGENT, 'mac') != false;
			$browser->linux = strpos($HTTP_USER_AGENT, 'linux') != false;
			$browser->unix = strpos($HTTP_USER_AGENT, 'unix') != false;

			$browser->webkit = strpos($HTTP_USER_AGENT, 'applewebkit') !== false;
			$browser->opera = strpos($HTTP_USER_AGENT, 'opera') !== false || ($browser->webkit && strpos($HTTP_USER_AGENT, 'opr/') !== false);
			$browser->ns = strpos($HTTP_USER_AGENT, 'netscape') !== false;
			$browser->chrome = !$browser->opera && strpos($HTTP_USER_AGENT, 'chrome') !== false;
			$browser->ie = !$browser->opera && (strpos($HTTP_USER_AGENT, 'compatible; msie') !== false || strpos($HTTP_USER_AGENT, 'trident/') !== false);
			$browser->safari = !$browser->opera && !$browser->chrome && ($browser->webkit || strpos($HTTP_USER_AGENT, 'safari') !== false);
			$browser->mz = !$browser->ie && !$browser->safari && !$browser->chrome && !$browser->ns && !$browser->opera && strpos($HTTP_USER_AGENT, 'mozilla') !== false;

			if ($browser->opera) {
				if (preg_match('/(opera|opr)\/([0-9.]+)/', $HTTP_USER_AGENT, $regs)) {
					$browser->ver = (float) $regs[2];
				}
			} else if (preg_match('/(chrome|msie|version|khtml)(\s*|\/)([0-9.]+)/', $HTTP_USER_AGENT, $regs)) {
				$browser->ver = (float) $regs[3];
			} else if (preg_match('/rv:([0-9.]+)/', $HTTP_USER_AGENT, $regs)) {
				$browser->ver = (float) $regs[1];
			}

			if (preg_match('/ ([a-z]{2})-([a-z]{2})/', $HTTP_USER_AGENT, $regs))
				$browser->lang = $regs[1];
			else
				$browser->lang = 'en';

			if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
				$browser->https = true;
			}
			if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
				$browser->https = true;
			}
			$sp = strtolower($_SERVER['SERVER_PROTOCOL']);
			$protocol = substr($sp, 0, strpos($sp, '/')) . (($browser->https) ? 's' : '');
			$port = $_SERVER['SERVER_PORT'];
			$port = ((!$browser->https && $port == '80') || ($browser->https && $port == '443')) ? '' : ':' . $port;
			$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
			$host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $port;
			$browser->url = $protocol . '://' . $host . $_SERVER['REQUEST_URI'];
			$browser->requestUri = ltrim($_SERVER['REQUEST_URI'], '/');
			self::$browerCache = $browser;
		}
		return self::$browerCache;
	}

	public static function getRemoteIP($onlyIP = false)
	{
		$address = $_SERVER['REMOTE_ADDR'];

		// append the NGINX X-Real-IP header, if set
		if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
			$remote_ip[] = 'X-Real-IP: ' . $_SERVER['HTTP_X_REAL_IP'];
		}
		// append the X-Forwarded-For header, if set
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$remote_ip[] = 'X-Forwarded-For: ' . $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if (!empty($remote_ip) && $onlyIP == false) {
			$address .= '(' . implode(',', $remote_ip) . ')';
		}
		return $address;
	}

	public static function parseBytes($str)
	{
		if (is_numeric($str)) {
			return floatval($str);
		}

		if (preg_match('/([0-9\.]+)\s*([a-z]*)/i', $str, $regs)) {
			$bytes = floatval($regs[1]);
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

		return floatval($bytes);
	}

	public function showBytes($bytes, &$unit = null)
	{
		$bytes = self::parseBytes($bytes);
		if ($bytes >= 1073741824) {
			$unit = 'GB';
			$gb = $bytes / 1073741824;
			$str = sprintf($gb >= 10 ? "%d " : "%.1f ", $gb) . $unit;
		} else if ($bytes >= 1048576) {
			$unit = 'MB';
			$mb = $bytes / 1048576;
			$str = sprintf($mb >= 10 ? "%d " : "%.1f ", $mb) . $unit;
		} else if ($bytes >= 1024) {
			$unit = 'KB';
			$str = sprintf("%d ", round($bytes / 1024)) . $unit;
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
		foreach (explode(' ', $name) as $word)
			$initial .= strtoupper($word[0]);
		return $initial;
	}

	public static function getBacktrace($ignore = 1)
	{
		$trace = '';
		foreach (debug_backtrace() as $k => $v) {
			if ($k < $ignore) {
				continue;
			}
			$args = '';
			if (isset($v['args'])) {
				foreach ($v['args'] as &$arg) {
					if (!is_array($arg) && !is_object($arg) && !is_resource($arg)) {
						$args .= "'$arg'";
					} elseif (is_array($arg)) {
						$args .= '[';
						foreach ($arg as &$a) {
							$val = $a;
							if (is_array($a) || is_object($a) || is_resource($a)) {
								$val = gettype($a);
								if (is_object($a)) {
									$val .= '(' . get_class($a) . ')';
								}
							}
							$args .= $val . ',';
						}
						$args = rtrim($args, ',') . ']';
					}
					$args .= ',';
				}
				$args = rtrim($args, ',');
			}
			$file = str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', $v['file']);
			$trace .= '#' . ($k - $ignore) . ' ' . (isset($v['class']) ? $v['class'] . '->' : '') . $v['function'] . '(' . $args . ') in ' . $file . '(' . $v['line'] . '): ' . PHP_EOL;
		}

		return $trace;
	}

	public function getDiskSpace($dir = '')
	{
		if ($dir == '') {
			$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		}
		$total = disk_total_space($dir);
		$free = disk_free_space($dir);
		$used = $total - $free;
		return ['total' => $total, 'free' => $free, 'used' => $used];
	}

	public static function textLength($text, $length = false, $addDots = true)
	{
		if (!$length) {
			$length = \AppConfig::main('listview_max_textlength');
		}
		$newText = preg_replace("/(<\/?)(\w+)([^>]*>)/i", '', $text);
		if (function_exists('mb_strlen')) {
			if (mb_strlen(html_entity_decode($newText)) > $length) {
				$newText = mb_substr(preg_replace('/(<\/?)(\w+)([^>]*>)/i', '', html_entity_decode($newText)), 0, $length, \AppConfig::main('default_charset'));
				if ($addDots) {
					$newText .= '...';
				}
			}
		} elseif (strlen(html_entity_decode($text)) > $length) {
			$newText = substr(preg_replace('/(<\/?)(\w+)([^>]*>)/i', '', html_entity_decode($newText)), 0, $length);
			if ($addDots) {
				$newText .= '...';
			}
		}
		return $newText;
	}

	public static function getDefaultCurrencyInfo()
	{
		$allCurrencies = self::getAllCurrency(true);
		foreach ($allCurrencies as $currency) {
			if ($currency['defaultid'] === '-11') {
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
			$lastWorkingDay = date('Y-m-d', strtotime("-1 day", $date));
		} else if (date('D', $date) == 'Sun') { // switch to friday two days before
			$lastWorkingDay = date('Y-m-d', strtotime("-2 day", $date));
		} else {
			$lastWorkingDay = date('Y-m-d', $date);
		}

		return $lastWorkingDay;
	}

	public static function slug($str, $delimiter = '_')
	{
		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
		$str = mb_convert_encoding((string) $str, 'UTF-8', mb_list_encodings());
		$char_map = array(
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
			'Ĺˇ' => 's', 'Ĺ«' => 'u', 'Ĺľ' => 'z'
		);

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

	/**
	 * Function returning difference in minutes between date times
	 * @param string $startDateTime
	 * @param string $endDateTime
	 * @return int difference in minutes
	 */
	public static function getDateTimeMinutesDiff($startDateTime, $endDateTime)
	{
		$start = new \DateTime($startDateTime);
		$end = new \DateTime($endDateTime);
		$interval = $start->diff($end);

		$intervalInSeconds = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
		$intervalInMinutes = ($intervalInSeconds / 60);

		return $intervalInMinutes;
	}

	/**
	 * Function returning difference in hours between date times
	 * @param string $startDateTime
	 * @param string $endDateTime
	 * @return int difference in hours
	 */
	public static function getDateTimeHoursDiff($startDateTime, $endDateTime)
	{
		return self::getDateTimeMinutesDiff($startDateTime, $endDateTime) / 60;
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
					if (!empty($newDiff))
						$difference[$key] = $newDiff;
				}
			} else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
				$difference[$key] = $value;
			}
		}
		return $difference;
	}

	public static function varExportMin($var)
	{
		if (is_array($var)) {
			$toImplode = [];
			foreach ($var as $key => $value) {
				$toImplode[] = var_export($key, true) . '=>' . self::varExportMin($value);
			}
			$code = '[' . implode(',', $toImplode) . ']';
			return $code;
		} else {
			return var_export($var, true);
		}
	}
}
