<?php

/**
 * Main class to save modification in settings.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Vtiger_Tracker_Model
{
	private static $recordId = '';
	private static $lockTrack = false;
	private static $id = false;
	private static $types = [
		'view' => 1,
		'save' => 2,
		'delete' => 3,
	];

	/**
	 * Adding basic information about tracking.
	 *
	 * @param string $type
	 *
	 * @return void
	 */
	public static function addBasic(string $type): void
	{
		$db = App\Db::getInstance('log');
		if ('view' == $type && App\Request::_isAjax()) {
			self::lockTracking();
		}
		if (false !== self::$id || self::$lockTrack) {
			return;
		}
		$action = \App\Process::$processType . ':' . \App\Process::$processName;
		if ($mode = \App\Request::_getMode()) {
			$action .= ':' . $mode;
		}
		$insertedInfo = $db->createCommand()->insert('l_#__settings_tracker_basic', [
			'user_id' => \App\User::getCurrentUserId(),
			'type' => self::$types[$type],
			'module_name' => \App\Request::_get('module'),
			'record_id' => self::$recordId ? self::$recordId : 0,
			'date' => date('Y-m-d H:i:s'),
			'action' => $action,
		])->execute();
		if (1 === $insertedInfo) {
			self::$id = $db->getLastInsertID('l_#__settings_tracker_basic_id_seq');
		}
	}

	public static function changeType($type)
	{
		App\Db::getInstance('log')->createCommand()
			->update('l_#__settings_tracker_basic', ['type' => self::$types[$type]], ['id' => [self::$id]])
			->execute();
	}

	public static function addDetail($prev, $post)
	{
		if (self::$lockTrack) {
			return true;
		}
		if (false !== self::$id) {
			self::addBasic('save');
		}
		$db = App\Db::getInstance('log');
		foreach ($post as $key => $value) {
			if (isset($prev[$key]) && $value == $prev[$key]) {
				continue;
			}
			$db->createCommand()->insert('l_#__settings_tracker_detail', [
				'id' => self::$id,
				'prev_value' => $prev[$key] ?? '',
				'post_value' => null === $value ? '' : $value,
				'field' => $key,
			])->execute();
		}
	}

	public static function lockTracking($lock = true)
	{
		self::$lockTrack = $lock;
	}

	public static function setRecordId($record)
	{
		if (empty(self::$recordId)) {
			self::$recordId = $record;
		}
	}
}
