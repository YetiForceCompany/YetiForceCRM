<?php

/**
 * TestData Sample data Class.
 *
 * @license licenses/License.html
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TestData_Types_Helper
{
	//date range
	protected static $dateStart = '-1 month'; // http://php.net/manual/en/datetime.formats.php
	protected static $dateEnd = '+1 month'; // http://php.net/manual/en/datetime.formats.php

	public static function setConfig($type, $value)
	{
		self::$type = $value;
	}

	public static function getString($field)
	{
		$value = md5(uniqid('', true));
		$max = $field->get('maximumlength');
		if ($max) {
			return mb_substr($value, 0, $max);
		}
		return $value;
	}

	protected static $valuesFromPicklist = [];
	protected static $valuesFromPicklistCount = [];

	public static function getPicklist($field)
	{
		$fieldId = $field->getId();
		if (!isset(self::$valuesFromPicklist[$fieldId])) {
			self::$valuesFromPicklist[$fieldId] = array_keys($field->getPicklistValues());
			self::$valuesFromPicklistCount[$fieldId] = \count(self::$valuesFromPicklist[$fieldId]) - 1;
		}
		if (empty(self::$valuesFromPicklist[$fieldId])) {
			return '';
		}
		return self::$valuesFromPicklist[$fieldId][mt_rand(0, self::$valuesFromPicklistCount[$fieldId])];
	}

	public static function getMultipicklist($field)
	{
		return self::getPicklist($field);
	}

	protected static $valuesFromReference = [];
	protected static $referenceRows = [];
	protected static $referenceRowsCount = [];

	public static function getReference($field)
	{
		if (52 == $field->get('uitype')) {
			return self::getOwner($field);
		}
		$fieldId = $field->getId();
		if (!isset(self::$valuesFromReference[$fieldId])) {
			self::$valuesFromReference[$fieldId] = $field->getReferenceList();
		}
		if (!isset(self::$referenceRows[$fieldId])) {
			$result = (new \App\Db\Query())
				->select(['crmid'])
				->from(['vtiger_crmentity'])
				->where([
					'and',
					['deleted' => 0],
					['in', 'setype', self::$valuesFromReference[$fieldId]],
				])->limit(100)
				->column();
			$rows = [];
			if (!empty($result)) {
				$rows = $result;
			}

			self::$referenceRows[$fieldId] = $rows;
			if (!empty($rows)) {
				self::$referenceRowsCount[$fieldId] = \count($rows) - 1;
			}
		}
		if (isset(self::$referenceRowsCount[$fieldId])) {
			return self::$referenceRows[$fieldId][mt_rand(0, self::$referenceRowsCount[$fieldId])];
		}
		return false;
	}

	public static function getReferenceLink($field)
	{
		return self::getReference($field);
	}

	public static function getReferenceProcess($field)
	{
		return self::getReference($field);
	}

	public static function getReferenceSubProcess($field)
	{
		return self::getReference($field);
	}

	public static function getReferenceExtend($field)
	{
		return self::getReference($field);
	}

	protected static $valuesFromOwner = false;
	protected static $valuesFromOwnerCount = false;

	public static function getOwner($field)
	{
		if (false === self::$valuesFromOwner) {
			$result = (new \App\Db\Query())
				->select(['id'])
				->from(['vtiger_users'])
				->where(['status' => 'Active'])
				->column();
			self::$valuesFromOwner = $result;
			self::$valuesFromOwnerCount = \count(self::$valuesFromOwner) - 1;
		}
		return self::$valuesFromOwner[mt_rand(0, self::$valuesFromOwnerCount)];
	}

	public static function getBoolean($field)
	{
		return mt_rand(0, 1);
	}

	public static function getCurrency($field)
	{
		return \App\Fields\Currency::formatToDb(self::getDouble($field));
	}

	public static function getPercentage($field)
	{
		return random_int(0, 99) . '.' . random_int(0, 9) . random_int(0, 9);
	}

	public static function getInteger($field)
	{
		$maximumLength = $field->get('maximumlength');
		if ($maximumLength) {
			$max = explode(',', $maximumLength);
			$max = $max[1] ?? $max[0];
		} else {
			$max = 99999;
		}
		if ($max > 9999) {
			$max = 10000;
		}
		return random_int(0, $max);
	}

	public static function getDouble($field)
	{
		$max = explode(',', $field->get('maximumlength'));
		$maxValue = (int) ($max[1] ?? $max[0]);
		if (!is_numeric($maxValue) || $maxValue > 10000) {
			$maxValue = 9999;
		}
		return (float) (random_int(0, $maxValue - 1) . '.' . random_int(0, 9) . random_int(0, 9));
	}

	public static function getDatetime($field)
	{
		$start = date('Y-m-d', strtotime(self::$dateStart, strtotime('now')));
		$end = date('Y-m-d', strtotime(self::$dateEnd, strtotime('now')));
		return self::getRandomdateBetween($start, $end, true);
	}

	public static function getRandomdateBetween($start, $stop, $time = false)
	{
		$start = strtotime($start);
		$stop = strtotime($stop);
		$timeStamp = mt_rand($start, $stop);

		$date = date('Y-m-d', $timeStamp);

		if ($time) {
			$hour = str_pad(mt_rand(0, 23), 2, '0', STR_PAD_LEFT);
			$minute = str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT);
			$date .= " $hour:$minute:00";
		}
		return $date;
	}

	public static function getDate($field)
	{
		$start = strtotime(self::$dateStart, strtotime('now'));
		$stop = strtotime(self::$dateEnd, strtotime('now'));
		$timeStamp = mt_rand($start, $stop);
		return date('Y-m-d', $timeStamp);
	}

	public static function getPhone($field)
	{
		$phoneNumber = '';
		for ($i = 0; $i < 9; ++$i) {
			$phoneNumber .= mt_rand(0, 9);
		}
		return '+48' . $phoneNumber;
	}

	protected static $valuesFromFiles = [];
	protected static $valuesFromFilesCount = [];
	protected static $checkedFiles = [];

	public static function getFromFile($fileName)
	{
		if (self::$checkedFiles[$fileName] ?? is_file($fileName)) {
			self::$checkedFiles[$fileName] = true;
			if (!isset(self::$valuesFromFiles[$fileName])) {
				self::$valuesFromFiles[$fileName] = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				self::$valuesFromFilesCount[$fileName] = \count(self::$valuesFromFiles[$fileName]) - 1;
			}
			return self::$valuesFromFiles[$fileName][mt_rand(0, self::$valuesFromFilesCount[$fileName])];
		}
		self::$checkedFiles[$fileName] = false;

		return false;
	}

	protected static $randomFiles = [];

	public static function getRandomFile($dir)
	{
		if (empty(self::$randomFiles[$dir])) {
			$files = [];
			foreach (new \DirectoryIterator($dir) as $fileinfo) {
				if (!$fileinfo->isDot()) {
					$files[$fileinfo->getPathname()] = \App\Utils\Completions::decode(\App\Purifier::purifyHtml(file_get_contents($fileinfo->getPathname())));
				}
			}
			self::$randomFiles[$dir] = $files;
		}
		return self::$randomFiles[$dir][array_rand(self::$randomFiles[$dir])];
	}

	protected static $valuesFromLanguages = false;

	public static function getLanguages($field)
	{
		if (false === self::$valuesFromLanguages) {
			self::$valuesFromLanguages = \App\Language::getAll();
		}
		return array_rand(self::$valuesFromLanguages);
	}

	protected static $valuesFromTree = [];
	protected static $valuesFromTreeCount = [];

	public static function getTree($field)
	{
		$template = $field->getFieldParams();
		if (!isset(self::$valuesFromTree[$template])) {
			$result = (new \App\Db\Query())
				->select(['tree'])
				->from(['vtiger_trees_templates_data'])
				->where(['templateid' => $template])
				->column();
			self::$valuesFromTree[$template] = $result;
			self::$valuesFromTreeCount[$template] = \count(self::$valuesFromTree[$template]) - 1;
		}
		return self::$valuesFromTree[$template][mt_rand(0, self::$valuesFromTreeCount[$template])];
	}

	public static function getPassword($field)
	{
		return 'TestDataPassword';
	}

	public static function getTime($field)
	{
		return self::timeBetween();
	}

	public static function timeBetween($start = 0, $stop = 23, $minstart = 0, $minend = 59)
	{
		$hour = str_pad(mt_rand($start, $stop), 2, '0', STR_PAD_LEFT);
		$minute = str_pad(mt_rand($minstart, $minend), 2, '0', STR_PAD_LEFT);
		return "$hour:$minute:00";
	}

	protected static $valuesFromRole = false;
	protected static $valuesFromRoleCount = false;

	public static function getUserRole($field)
	{
		if (false === self::$valuesFromRole) {
			self::$valuesFromRole = (new \App\Db\Query())
				->select(['roleid'])
				->from(['vtiger_role'])
				->where(['<>', 'roleid', 'H1'])
				->column();
			self::$valuesFromRoleCount = \count(self::$valuesFromRole) - 1;
		}

		return self::$valuesFromRole[mt_rand(0, self::$valuesFromRoleCount)];
	}

	public static function getCurrencyList($field)
	{
		return array_rand(\App\Fields\Currency::getAll(), 1);
	}

	protected static $themeCount = 0;

	public static function getTheme($field)
	{
		$skins = array_keys(Vtiger_Theme::getAllSkins());
		if (!self::$themeCount) {
			self::$themeCount = \count($skins) - 1;
		}
		return $skins[mt_rand(0, self::$themeCount)];
	}

	protected static $taxCount = 0;

	public static function getTaxes($field)
	{
		$taxs = array_keys(Vtiger_Inventory_Model::getGlobalTaxes());
		if (!self::$taxCount) {
			self::$taxCount = \count($taxs) - 1;
		}
		return $taxs[mt_rand(0, self::$taxCount)];
	}

	public static function getMultiCurrency($field)
	{
		$currencyDefault = \App\Fields\Currency::getDefault();
		return \App\Json::encode(['currencies' => [$currencyDefault['id'] => ['price' => mt_rand(1, 999)]], 'currencyId' => $currencyDefault['id']]);
	}

	public static function getBarcode($field)
	{
		return random_int(10000, 99999999);
	}
}
