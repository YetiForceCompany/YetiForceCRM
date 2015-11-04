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

/**
 * TODO need to organize into classes based on functional grouping.
 */
class Vtiger_Functions
{

	static function userIsAdministrator($user)
	{
		return (isset($user->is_admin) && $user->is_admin == 'on');
	}

	static function currentUserJSDateFormat($localformat)
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
	static function currentUserDisplayDate($value)
	{
		$current_user = vglobal('current_user');
		$dat_fmt = $current_user->date_format;
		if ($dat_fmt == '') {
			$dat_fmt = 'yyyy-mm-dd';
		}
		$date = new DateTimeField($value);
		return $date->getDisplayDate();
	}

	static function currentUserDisplayDateNew()
	{
		$current_user = vglobal('current_user');
		$date = new DateTimeField(null);
		return $date->getDisplayDate($current_user);
	}

	// i18n
	static function getTranslatedString($str, $module = '')
	{
		return Vtiger_Language_Handler::getTranslatedString($str, $module);
	}

	// CURRENCY
	protected static $userIdCurrencyIdCache = array();

	static function userCurrencyId($userid)
	{
		$adb = PearDatabase::getInstance();
		if (!isset(self::$userIdCurrencyIdCache[$userid])) {
			$result = $adb->pquery('SELECT id,currency_id FROM vtiger_users', array());
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
			$db = PearDatabase::getInstance();
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
			$db = PearDatabase::getInstance();
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

	static function getCurrencyName($currencyid, $show_symbol = true)
	{
		$currencyInfo = self::getCurrencyInfo($currencyid);
		if ($show_symbol) {
			return sprintf("%s : %s", Vtiger_Deprecated::getTranslatedCurrencyString($currencyInfo['currency_name']), $currencyInfo['currency_symbol']);
		}
		return $currencyInfo['currency_name'];
	}

	static function getCurrencySymbolandRate($currencyid)
	{
		$currencyInfo = self::getCurrencyInfo($currencyid);
		$currencyRateSymbol = array(
			'rate' => $currencyInfo['conversion_rate'],
			'symbol' => $currencyInfo['currency_symbol']
		);
		return $currencyRateSymbol;
	}

	// MODULE
	protected static $moduleIdNameCache = array();
	protected static $moduleNameIdCache = array();
	protected static $moduleIdDataCache = array();

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
			$adb = PearDatabase::getInstance();
			$result = $adb->pquery('SELECT tabid, name, ownedby FROM vtiger_tab', array());
			while ($row = $adb->fetch_array($result)) {
				self::$moduleIdNameCache[$row['tabid']] = $row;
				self::$moduleNameIdCache[$row['name']] = $row;
			}
		}
		return $id ? self::$moduleIdNameCache[$id] : self::$moduleNameIdCache[$name];
	}

	static function getModuleData($mixed)
	{
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
			if (!$id)
				$id = self::$moduleNameIdCache[$name]['tabid'];
			if (!isset(self::$moduleIdDataCache[$id])) {
				$reload = true;
			}
		}

		if ($reload) {
			$adb = PearDatabase::getInstance();
			$result = $adb->pquery('SELECT * FROM vtiger_tab', array());
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

	static function getModuleId($name)
	{
		$moduleInfo = self::getBasicModuleInfo($name);
		return $moduleInfo ? $moduleInfo['tabid'] : NULL;
	}

	static function getModuleName($id)
	{
		$moduleInfo = self::getBasicModuleInfo($id);
		return $moduleInfo ? $moduleInfo['name'] : NULL;
	}

	static function getModuleOwner($name)
	{
		$moduleInfo = self::getBasicModuleInfo($name);
		return $moduleInfo ? $moduleInfo['ownedby'] : NULL;
	}

	protected static $moduleEntityCache = array();

	static function getEntityModuleInfo($mixed)
	{
		$name = NULL;
		if (is_numeric($mixed))
			$name = self::getModuleName($mixed);
		else
			$name = $mixed;

		if ($name && !isset(self::$moduleEntityCache[$name])) {
			$adb = PearDatabase::getInstance();
			$result = $adb->pquery('SELECT fieldname,modulename,tablename,entityidfield,entityidcolumn,searchcolumn from vtiger_entityname', array());
			while ($row = $adb->fetch_array($result)) {
				self::$moduleEntityCache[$row['modulename']] = $row;
			}
		}

		return isset(self::$moduleEntityCache[$name]) ?
			self::$moduleEntityCache[$name] : NULL;
	}

	static function getEntityModuleSQLColumnString($mixed)
	{
		$data = [];
		$info = self::getEntityModuleInfo($mixed);
		if ($info) {
			$data['tablename'] = $info['tablename'];
			$fieldnames = $info['fieldname'];
			if (strpos(',', $fieldnames) !== false) {
				$fieldnames = sprintf("concat(%s)", implode(",' ',", explode(',', $fieldnames)));
			}
			$data['fieldname'] = $fieldnames;
			$colums = [];
			foreach (explode(',', $info['fieldname']) as $fieldname) {
				$colums[] = $info['tablename'] . '.' . $fieldname;
			}
			$data['colums'] = implode(',', $colums);
		}
		return $data;
	}

	static function getEntityModuleInfoFieldsFormatted($mixed)
	{
		$info = self::getEntityModuleInfo($mixed);
		$fieldnames = $info ? $info['fieldname'] : NULL;
		if ($fieldnames && stripos($fieldnames, ',') !== false) {
			$fieldnames = explode(',', $fieldnames);
		}
		$info['fieldname'] = $fieldnames;
		return $info;
	}

	// MODULE RECORD
	protected static $crmRecordIdMetadataCache = array();

	public static function getCRMRecordMetadata($mixedid)
	{
		$adb = PearDatabase::getInstance();

		$multimode = is_array($mixedid);

		$ids = $multimode ? $mixedid : array($mixedid);
		$missing = array();
		foreach ($ids as $id) {
			if ($id && !isset(self::$crmRecordIdMetadataCache[$id])) {
				$missing[] = $id;
			}
		}

		if ($missing) {
			$sql = sprintf("SELECT crmid, setype, deleted, smownerid, shownerid, label, searchlabel FROM vtiger_crmentity WHERE %s", implode(' OR ', array_fill(0, count($missing), 'crmid=?')));
			$result = $adb->pquery($sql, $missing);
			while ($row = $adb->fetch_array($result)) {
				self::$crmRecordIdMetadataCache[$row['crmid']] = $row;
			}
		}

		$result = array();
		foreach ($ids as $id) {
			if (isset(self::$crmRecordIdMetadataCache[$id])) {
				$result[$id] = self::$crmRecordIdMetadataCache[$id];
			} else {
				$result[$id] = NULL;
			}
		}

		return $multimode ? $result : array_shift($result);
	}

	static function getCRMRecordType($id)
	{
		$metadata = self::getCRMRecordMetadata($id);
		return $metadata ? $metadata['setype'] : NULL;
	}

	static function getCRMRecordLabel($id, $default = '')
	{
		$metadata = self::getCRMRecordMetadata($id);
		return $metadata ? $metadata['label'] : $default;
	}

	static function getUserRecordLabel($id, $default = '')
	{
		$labels = self::getCRMRecordLabels('Users', $id);
		return isset($labels[$id]) ? $labels[$id] : $default;
	}

	static function getGroupRecordLabel($id, $default = '')
	{
		$labels = self::getCRMRecordLabels('Groups', $id);
		return isset($labels[$id]) ? $labels[$id] : $default;
	}

	static function getCRMRecordLabels($module, $ids)
	{
		if (!is_array($ids))
			$ids = array($ids);

		if ($module == 'Users' || $module == 'Groups') {
			// TODO Cache separately?
			return self::computeCRMRecordLabels($module, $ids);
		} else {
			$metadatas = self::getCRMRecordMetadata($ids);
			$result = array();
			foreach ($metadatas as $data) {
				$result[$data['crmid']] = $data['label'];
			}
			return $result;
		}
	}

	static function updateCRMRecordLabel($module, $id, $label)
	{
		$adb = PearDatabase::getInstance();
		$labelInfo = self::computeCRMRecordLabels($module, $id);
		if ($labelInfo) {
			$label = decode_html($labelInfo[$id]);
			$adb->pquery('UPDATE vtiger_crmentity SET label=? WHERE crmid=?', array($label, $id));
			self::$crmRecordIdMetadataCache[$id] = array(
				'setype' => $module,
				'crmid' => $id,
				'label' => $labelInfo[$id]
			);
		}
	}

	protected static $ownerRecordLabelCache = [];

	static function getOwnerRecordLabel($id)
	{
		if (!isset(self::$ownerRecordLabelCache[$id])) {
			$result = self::getOwnerRecordLabels($id);
			self::$ownerRecordLabelCache[$id] = $result ? array_shift($result) : NULL;
		}
		return self::$ownerRecordLabelCache[$id];
	}

	static function getOwnerRecordLabels($ids)
	{
		if (!is_array($ids))
			$ids = array($ids);

		$nameList = array();
		if ($ids) {
			$nameList = self::getCRMRecordLabels('Users', $ids);
			$groups = array();
			$diffIds = array_diff($ids, array_keys($nameList));
			if ($diffIds) {
				$groups = self::getCRMRecordLabels('Groups', array_values($diffIds));
			}
			if ($groups) {
				foreach ($groups as $id => $label) {
					$nameList[$id] = $label;
				}
			}
		}

		return $nameList;
	}

	static function computeCRMRecordLabels($module, $ids, $search = false)
	{
		$adb = PearDatabase::getInstance();

		if (!is_array($ids))
			$ids = array($ids);

		if ($module == 'Events') {
			$module = 'Calendar';
		}

		if ($module) {
			$entityDisplay = array();

			if ($ids) {

				if ($module == 'Groups') {
					$metainfo = array('tablename' => 'vtiger_groups', 'entityidfield' => 'groupid', 'fieldname' => 'groupname');
					/* } else if ($module == 'DocumentFolders') { 
					  $metainfo = array('tablename' => 'vtiger_attachmentsfolder','entityidfield' => 'folderid','fieldname' => 'foldername'); */
				} else {
					$metainfo = self::getEntityModuleInfo($module);
				}

				$table = $metainfo['tablename'];
				$idcolumn = $metainfo['entityidfield'];
				$columns_name = explode(',', $metainfo['fieldname']);
				$columns_search = explode(',', $metainfo['searchcolumn']);
				$columns = array_unique(array_merge($columns_name, $columns_search));

				$sql = sprintf('SELECT ' . implode(',', array_filter($columns)) . ', %s AS id FROM %s WHERE %s IN (%s)', $idcolumn, $table, $idcolumn, generateQuestionMarks($ids));
				$result = $adb->pquery($sql, $ids);

				$moduleInfo = self::getModuleFieldInfos($module);
				$moduleInfoExtend = [];
				if (count($moduleInfo) > 0) {
					foreach ($moduleInfo as $field => $fieldInfo) {
						$moduleInfoExtend[$fieldInfo['columnname']] = $fieldInfo;
					}
				}
				for ($i = 0; $i < $adb->num_rows($result); $i++) {
					$row = $adb->raw_query_result_rowdata($result, $i);
					$label_name = array();
					$label_search = array();
					foreach ($columns_name as $columnName) {
						if ($moduleInfoExtend && in_array($moduleInfoExtend[$columnName]['uitype'], array(10, 51, 75, 81)))
							$label_name[] = Vtiger_Functions::getCRMRecordLabel($row[$columnName]);
						else
							$label_name[] = $row[$columnName];
					}
					if ($search) {
						foreach ($columns_search as $columnName) {
							if ($moduleInfoExtend && in_array($moduleInfoExtend[$columnName]['uitype'], array(10, 51, 75, 81)))
								$label_search[] = Vtiger_Functions::getCRMRecordLabel($row[$columnName]);
							else
								$label_search[] = $row[$columnName];
						}
						$entityDisplay[$row['id']] = array('name' => implode(' ', $label_name), 'search' => implode(' ', $label_search));
					}else {
						$entityDisplay[$row['id']] = trim(implode(' ', $label_name));
					}
				}
			}
			return $entityDisplay;
		}
	}

	protected static $groupIdNameCache = array();

	static function getGroupName($id)
	{
		$adb = PearDatabase::getInstance();
		if (!self::$groupIdNameCache[$id]) {
			$result = $adb->pquery('SELECT groupid, groupname FROM vtiger_groups');
			while ($row = $adb->fetch_array($result)) {
				self::$groupIdNameCache[$row['groupid']] = $row['groupname'];
			}
		}
		$result = array();
		if (isset(self::$groupIdNameCache[$id])) {
			$result[] = decode_html(self::$groupIdNameCache[$id]);
			$result[] = $id;
		}
		return $result;
	}

	protected static $userIdNameCache = array();

	static function getUserName($id)
	{
		$adb = PearDatabase::getInstance();
		if (!self::$userIdNameCache[$id]) {
			$result = $adb->pquery('SELECT id, user_name FROM vtiger_users');
			while ($row = $adb->fetch_array($result)) {
				self::$userIdNameCache[$row['id']] = $row['user_name'];
			}
		}
		return (isset(self::$userIdNameCache[$id])) ? self::$userIdNameCache[$id] : NULL;
	}

	protected static $moduleFieldInfoByNameCache = array();

	static function getModuleFieldInfos($mixed)
	{
		$adb = PearDatabase::getInstance();

		$moduleInfo = self::getBasicModuleInfo($mixed);
		$module = $moduleInfo['name'];

		$no_of_fields = $adb->pquery('SELECT COUNT(fieldname) AS count FROM vtiger_field WHERE tabid=?', array(self::getModuleId($module)));
		$fields_count = $adb->query_result($no_of_fields, 0, 'count');

		$cached_fields_count = isset(self::$moduleFieldInfoByNameCache[$module]) ? count(self::$moduleFieldInfoByNameCache[$module]) : NULL;

		if ($module && (!isset(self::$moduleFieldInfoByNameCache[$module]) || ((int) $fields_count != (int) $cached_fields_count))) {
			$result = ($module == 'Calendar') ?
				$adb->pquery('SELECT * FROM vtiger_field WHERE tabid=? OR tabid=?', array(9, 16)) :
				$adb->pquery('SELECT * FROM vtiger_field WHERE tabid=?', array(self::getModuleId($module)));

			self::$moduleFieldInfoByNameCache[$module] = array();
			while ($row = $adb->fetch_array($result)) {
				self::$moduleFieldInfoByNameCache[$module][$row['fieldname']] = $row;
			}
		}
		return isset(self::$moduleFieldInfoByNameCache[$module]) ? self::$moduleFieldInfoByNameCache[$module] : NULL;
	}

	static function getModuleFieldInfoWithId($fieldid)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT * FROM vtiger_field WHERE fieldid=?', array($fieldid));
		return ($adb->num_rows($result)) ? $adb->fetch_array($result) : NULL;
	}

	static function getModuleFieldInfo($moduleid, $mixed)
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

	static function getModuleFieldId($moduleid, $mixed, $onlyactive = true)
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
	static function formatDecimal($value)
	{
		$fld_value = explode('.', $value);
		if ($fld_value[1] != '') {
			$fld_value = rtrim($value, '0');
			$value = rtrim($fld_value, '.');
		}
		return $value;
	}

	static function fromHTML($string, $encode = true)
	{
		if (is_string($string)) {
			if (preg_match('/(script).*(\/script)/i', $string)) {
				$string = preg_replace(array('/</', '/>/', '/"/'), array('&lt;', '&gt;', '&quot;'), $string);
			}
		}
		return $string;
	}

	static function fromHTML_FCK($string)
	{
		if (is_string($string)) {
			if (preg_match('/(script).*(\/script)/i', $string)) {
				$string = str_replace('script', '', $string);
			}
		}
		return $string;
	}

	static function fromHTML_Popup($string, $encode = true)
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

	static function br2nl($str)
	{
		$str = preg_replace("/(\r\n)/", "\\r\\n", $str);
		$str = preg_replace("/'/", " ", $str);
		$str = preg_replace("/\"/", " ", $str);
		return $str;
	}

	static function suppressHTMLTags($string)
	{
		return preg_replace(array('/</', '/>/', '/"/'), array('&lt;', '&gt;', '&quot;'), $string);
	}

	static function getInventoryTermsAndCondition()
	{
		$adb = PearDatabase::getInstance();
		$sql = "select tandc from vtiger_inventory_tandc";
		$result = $adb->pquery($sql, array());
		$tandc = $adb->query_result($result, 0, "tandc");
		return $tandc;
	}

	static function initStorageFileDirectory($module = false)
	{
		$filepath = 'storage/';

		if ($module && in_array($module, array("Users", "Contacts", "Products", "OSSMailView"))) {
			$filepath = $filepath . $module . "/";
		}
		$year = date('Y');
		$month = date('F');
		$day = date('j');
		$week = '';

		if (!is_dir($filepath . $year)) {
			//create new folder
			mkdir($filepath . $year);
		}
		if (!is_dir($filepath . $year . "/" . $month)) {
			//create new folder
			mkdir($filepath . "$year/$month");
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

		if (!is_dir($filepath . $year . "/" . $month . "/" . $week)) {
			//create new folder
			mkdir($filepath . "$year/$month/$week");
		}
		$filepath = $filepath . $year . "/" . $month . "/" . $week . "/";
		return $filepath;
	}

	static public function validateImage($fileDetails)
	{
		$allowedImageFormats = ['jpeg', 'png', 'jpg', 'pjpeg', 'x-png', 'gif', 'bmp'];
		$mimeTypesList = array_merge($allowedImageFormats, ['x-ms-bmp']); //bmp another format

		$fileTypeDetails = explode('/', $fileDetails['type']);
		$fileType = $fileTypeDetails['1'];
		if ($fileType) {
			$fileType = strtolower($fileType);
		}

		$saveImage = 'true';
		if (!in_array($fileType, $allowedImageFormats)) {
			$saveImage = 'false';
		}

		//mime type check
		$mimeType = self::getMimeContentType($fileDetails['tmp_name']);
		$mimeTypeContents = explode('/', $mimeType);
		if (!$fileDetails['size'] || !in_array($mimeTypeContents[1], $mimeTypesList)) {
			$saveImage = 'false';
		}

		// Check for php code injection
		$imageContents = file_get_contents($fileDetails['tmp_name']);
		if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
			$saveImage = 'false';
		}
		return $saveImage;
	}

	static function getMergedDescriptionCustomVars($fields, $description)
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

	static function getSingleFieldValue($tablename, $fieldname, $idname, $id)
	{
		$adb = PearDatabase::getInstance();
		$fieldval = $adb->query_result($adb->pquery("select $fieldname from $tablename where $idname = ?", array($id)), 0, $fieldname);
		return $fieldval;
	}

	static function getRecurringObjValue()
	{
		$recurring_data = array();
		if (isset($_REQUEST['recurringtype']) && $_REQUEST['recurringtype'] != null && $_REQUEST['recurringtype'] != '--None--') {
			if (!empty($_REQUEST['date_start'])) {
				$startDate = $_REQUEST['date_start'];
			}
			if (!empty($_REQUEST['calendar_repeat_limit_date'])) {
				$endDate = $_REQUEST['calendar_repeat_limit_date'];
				$recurring_data['recurringenddate'] = $endDate;
			} elseif (isset($_REQUEST['due_date']) && $_REQUEST['due_date'] != null) {
				$endDate = $_REQUEST['due_date'];
			}
			if (!empty($_REQUEST['time_start'])) {
				$startTime = $_REQUEST['time_start'];
			}
			if (!empty($_REQUEST['time_end'])) {
				$endTime = $_REQUEST['time_end'];
			}

			$recurring_data['startdate'] = $startDate;
			$recurring_data['starttime'] = $startTime;
			$recurring_data['enddate'] = $endDate;
			$recurring_data['endtime'] = $endTime;

			$recurring_data['type'] = $_REQUEST['recurringtype'];
			if ($_REQUEST['recurringtype'] == 'Weekly') {
				if (isset($_REQUEST['sun_flag']) && $_REQUEST['sun_flag'] != null)
					$recurring_data['sun_flag'] = true;
				if (isset($_REQUEST['mon_flag']) && $_REQUEST['mon_flag'] != null)
					$recurring_data['mon_flag'] = true;
				if (isset($_REQUEST['tue_flag']) && $_REQUEST['tue_flag'] != null)
					$recurring_data['tue_flag'] = true;
				if (isset($_REQUEST['wed_flag']) && $_REQUEST['wed_flag'] != null)
					$recurring_data['wed_flag'] = true;
				if (isset($_REQUEST['thu_flag']) && $_REQUEST['thu_flag'] != null)
					$recurring_data['thu_flag'] = true;
				if (isset($_REQUEST['fri_flag']) && $_REQUEST['fri_flag'] != null)
					$recurring_data['fri_flag'] = true;
				if (isset($_REQUEST['sat_flag']) && $_REQUEST['sat_flag'] != null)
					$recurring_data['sat_flag'] = true;
			}
			elseif ($_REQUEST['recurringtype'] == 'Monthly') {
				if (isset($_REQUEST['repeatMonth']) && $_REQUEST['repeatMonth'] != null)
					$recurring_data['repeatmonth_type'] = $_REQUEST['repeatMonth'];
				if ($recurring_data['repeatmonth_type'] == 'date') {
					if (isset($_REQUEST['repeatMonth_date']) && $_REQUEST['repeatMonth_date'] != null)
						$recurring_data['repeatmonth_date'] = $_REQUEST['repeatMonth_date'];
					else
						$recurring_data['repeatmonth_date'] = 1;
				}
				elseif ($recurring_data['repeatmonth_type'] == 'day') {
					$recurring_data['repeatmonth_daytype'] = $_REQUEST['repeatMonth_daytype'];
					switch ($_REQUEST['repeatMonth_day']) {
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
			if (isset($_REQUEST['repeat_frequency']) && $_REQUEST['repeat_frequency'] != null)
				$recurring_data['repeat_frequency'] = $_REQUEST['repeat_frequency'];

			$recurObj = RecurringType::fromUserRequest($recurring_data);
			return $recurObj;
		}
	}

	static function getTicketComments($ticketid)
	{
		$adb = PearDatabase::getInstance();
		$moduleName = getSalesEntityType($ticketid);
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
			$commentlist = '<br><br>' . getTranslatedString("The comments are", $moduleName) . ' : ' . $commentlist;
		return $commentlist;
	}

	static function generateRandomPassword()
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

	static function getTagCloudView($id = "")
	{
		$adb = PearDatabase::getInstance();
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

	static function transformFieldTypeOfData($table_name, $column_name, $type_of_data)
	{
		$field = $table_name . ":" . $column_name;
		//Add the field details in this array if you want to change the advance filter field details

		static $new_field_details = Array(
			//Contacts Related Fields
			"vtiger_contactdetails:parentid" => "V",
			"vtiger_contactsubdetails:birthday" => "D",
			"vtiger_contactdetails:email" => "V",
			"vtiger_contactdetails:secondaryemail" => "V",
			//Potential Related Fields
			"vtiger_potential:campaignid" => "V",
			//Account Related Fields
			"vtiger_account:parentid" => "V",
			"vtiger_account:email1" => "V",
			"vtiger_account:email2" => "V",
			//Lead Related Fields
			"vtiger_leaddetails:email" => "V",
			"vtiger_leaddetails:secondaryemail" => "V",
			//Documents Related Fields
			"vtiger_senotesrel:crmid" => "V",
			"vtiger_recurringevents:recurringtype" => "V",
			//HelpDesk Related Fields
			"vtiger_troubletickets:parent_id" => "V",
			"vtiger_troubletickets:product_id" => "V",
			//Product Related Fields
			"vtiger_products:discontinued" => "C",
			"vtiger_products:vendor_id" => "V",
			"vtiger_products:parentid" => "V",
			//Faq Related Fields
			"vtiger_faq:product_id" => "V",
			//Vendor Related Fields
			"vtiger_vendor:email" => "V",
			//Quotes Related Fields
			"vtiger_quotes:potentialid" => "V",
			"vtiger_quotes:inventorymanager" => "V",
			"vtiger_quotes:accountid" => "V",
			//Purchase Order Related Fields
			"vtiger_purchaseorder:vendorid" => "V",
			"vtiger_purchaseorder:contactid" => "V",
			//SalesOrder Related Fields
			"vtiger_salesorder:potentialid" => "V",
			"vtiger_salesorder:quoteid" => "V",
			"vtiger_salesorder:contactid" => "V",
			"vtiger_salesorder:accountid" => "V",
			//Invoice Related Fields
			"vtiger_invoice:salesorderid" => "V",
			"vtiger_invoice:contactid" => "V",
			"vtiger_invoice:accountid" => "V",
			//Campaign Related Fields
			"vtiger_campaign:product_id" => "V",
			//Related List Entries(For Report Module)
			"vtiger_activityproductrel:activityid" => "V",
			"vtiger_activityproductrel:productid" => "V",
			"vtiger_campaigncontrel:campaignid" => "V",
			"vtiger_campaigncontrel:contactid" => "V",
			"vtiger_campaignleadrel:campaignid" => "V",
			"vtiger_campaignleadrel:leadid" => "V",
			"vtiger_contpotentialrel:contactid" => "V",
			"vtiger_contpotentialrel:potentialid" => "V",
			"vtiger_pricebookproductrel:pricebookid" => "V",
			"vtiger_pricebookproductrel:productid" => "V",
			"vtiger_senotesrel:crmid" => "V",
			"vtiger_senotesrel:notesid" => "V",
			"vtiger_seproductsrel:crmid" => "V",
			"vtiger_seproductsrel:productid" => "V",
			"vtiger_seticketsrel:crmid" => "V",
			"vtiger_seticketsrel:ticketid" => "V",
			"vtiger_vendorcontactrel:vendorid" => "V",
			"vtiger_vendorcontactrel:contactid" => "V",
			"vtiger_pricebook:currency_id" => "V",
		);

		//If the Fields details does not match with the array, then we return the same typeofdata
		if (isset($new_field_details[$field])) {
			$type_of_data = $new_field_details[$field];
		}
		return $type_of_data;
	}

	static function getPickListValuesFromTableForRole($tablename, $roleid)
	{
		$adb = PearDatabase::getInstance();
		$query = "select $tablename from vtiger_$tablename inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$tablename.picklist_valueid where roleid=? and picklistid in (select picklistid from vtiger_picklist) order by sortid";
		$result = $adb->pquery($query, array($roleid));
		$fldVal = Array();
		while ($row = $adb->fetch_array($result)) {
			$fldVal [] = $row[$tablename];
		}
		return $fldVal;
	}

	static function getActivityType($id)
	{
		$adb = PearDatabase::getInstance();
		$query = "select activitytype from vtiger_activity where activityid=?";
		$res = $adb->pquery($query, array($id));
		$activity_type = $adb->query_result($res, 0, "activitytype");
		return $activity_type;
	}

	static function getInvoiceStatus($id)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT invoicestatus FROM vtiger_invoice where invoiceid=?", array($id));
		$invoiceStatus = $adb->query_result($result, 0, 'invoicestatus');
		return $invoiceStatus;
	}

	static function mkCountQuery($query)
	{
		// Remove all the \n, \r and white spaces to keep the space between the words consistent.
		// This is required for proper pattern matching for words like ' FROM ', 'ORDER BY', 'GROUP BY' as they depend on the spaces between the words.
		$query = preg_replace("/[\n\r\s]+/", " ", $query);

		//Strip of the current SELECT fields and replace them by "select count(*) as count"
		// Space across FROM has to be retained here so that we do not have a clash with string "from" found in select clause
		$query = "SELECT count(*) AS count " . substr($query, stripos($query, ' FROM '), strlen($query));

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
	static function getUnitPrice($productid, $module = 'Products')
	{
		$adb = PearDatabase::getInstance();
		if ($module == 'Services') {
			$query = "select unit_price from vtiger_service where serviceid=?";
		} else {
			$query = "select unit_price from vtiger_products where productid=?";
		}
		$result = $adb->pquery($query, array($productid));
		$unitpice = $adb->query_result($result, 0, 'unit_price');
		return $unitpice;
	}

	static function decimalTimeFormat($decTime)
	{
		$hour = floor($decTime);
		$min = round(60 * ($decTime - $hour));
		return array(
			'short' => $hour . vtranslate('LBL_H') . ' ' . $min . vtranslate('LBL_M'),
			'full' => $hour . vtranslate('LBL_HOURS') . ' ' . $min . vtranslate('LBL_MINUTES'),
		);
	}

	static function getArrayFromValue($values)
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

	public static function throwNewException($message, $die = true)
	{
		$request = new Vtiger_Request($_REQUEST);
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setError($message);
			$response->emit();
		} else {
			$viewer = new Vtiger_Viewer();
			$viewer->assign('MESSAGE', $message);
			$viewer->view('OperationNotPermitted.tpl', 'Vtiger');
		}
		if ($die) {
			exit();
		}
	}

	public static function throwNoPermittedException($message, $die = true)
	{
		$request = new Vtiger_Request($_REQUEST);
		$dbLog = PearDatabase::getInstance('log');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dbLog->insert('l_yf_access_to_record', [
			'username' => $currentUser->getDisplayName(),
			'date' => date('Y-m-d H:i:s'),
			'ip' => self::getRemoteIP(),
			'record' => $request->get('record'),
			'module' => $request->get('module'),
			'url' => Vtiger_Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
		]);
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setError($message);
			$response->emit();
		} else {
			$viewer = new Vtiger_Viewer();
			$viewer->assign('MESSAGE', $message);
			$viewer->view('NoPermissionsForRecord.tpl', 'Vtiger');
		}
		if ($die) {
			exit();
		}
	}

	static function removeHtmlTags(array $tags, $html)
	{
		$crmUrl = vglobal($key);

		$doc = new DOMDocument('1.0', 'UTF-8');
		$previousValue = libxml_use_internal_errors(TRUE);
		$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
		libxml_clear_errors();
		libxml_use_internal_errors($previousValue);

		foreach ($tags as $tag) {
			$xPath = new DOMXPath($doc);
			$nodes = $xPath->query('//' . $tag);
			for ($i = 0; $i < $nodes->length; $i++) {
				if ('img' === $tag) {
					$htmlNode = $nodes->item($i)->ownerDocument->saveHTML($nodes->item($i));
					$imgDom = new DOMDocument();
					$imgDom->loadHTML($htmlNode);
					$xpath = new DOMXPath($imgDom);
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

	static function getHtmlOrPlainText($content)
	{
		if (substr($content, 0, 1) == '<') {
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
	static function get_group_options()
	{
		global $adb, $noof_group_rows;
		$sql = "select groupname,groupid from vtiger_groups";
		$result = $adb->pquery($sql, array());
		$noof_group_rows = $adb->num_rows($result);
		return $result;
	}

	public function recurseDelete($src)
	{
		$rootDir = vglobal('root_directory');
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
		$rootDir = vglobal('root_directory');
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

			$browser = new stdClass;
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

		if (!empty($remote_ip) && $onlyIP != false) {
			$address .= '(' . implode(',', $remote_ip) . ')';
		}
		return $address;
	}

	function parseBytes($str)
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

	public function getMaxUploadSize()
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
				$return = SysDeveloper::get('MINIMIZE_JS');
				break;
			case 'css':
				$return = SysDeveloper::get('MINIMIZE_CSS');
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

	public function getBacktrace($ignore = 2)
	{
		$trace = '';
		foreach (debug_backtrace() as $k => $v) {
			if ($k < $ignore) {
				continue;
			}
			$trace .= '#' . ($k - $ignore) . ' ' . (isset($v['class']) ? $v['class'] . '->' : '') . $v['function'] . '() in ' . $v['file'] . '(' . $v['line'] . '): ' . PHP_EOL;
		}

		return $trace;
	}

	public function getDiskSpace($dir = '')
	{
		if ($dir == '') {
			$dir = vglobal('root_directory');
		}
		$total = disk_total_space($dir);
		$free = disk_free_space($dir);
		$used = $total - $free;
		return ['total' => $total, 'free' => $free, 'used' => $used];
	}

	public static function textLength($text, $length = false, $addDots = true)
	{
		if (!$length) {
			$length = vglobal('listview_max_textlength');
		}
		$newText = preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $text);
		if (function_exists('mb_strlen')) {
			if (mb_strlen(html_entity_decode($newText)) > $length) {
				$newText = mb_substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $text), 0, $length, vglobal('default_charset'));
				if ($addDots) {
					$newText .= '...';
				}
			}
		} elseif (strlen(html_entity_decode($text)) > $length) {
			$newText = substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $text), 0, $length);
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
		$currencyUpdateModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
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
			$info['value'] = round($value, 5);
			$info['conversion'] = round(1 / $value, 5);
		}

		return $info;
	}

	static public function getMimeContentType($filename)
	{
		require 'config/mimetypes.php';
		$ext = strtolower(array_pop(explode('.', $filename)));
		if (array_key_exists($ext, $mimeTypes)) {
			$fileMimeContentType = $mimeTypes[$ext];
		} elseif (function_exists('mime_content_type')) {
			$fileMimeContentType = mime_content_type($filename);
		} elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$fileMimeContentType = finfo_file($finfo, $filename);
			finfo_close($finfo);
		} else {
			$fileMimeContentType = 'application/octet-stream';
		}
		return $fileMimeContentType;
	}
}
