<?php

/**
 * Main class to save modification in settings
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Vtiger_Tracker_Model
{

	static private $recordId = '';
	static private $lockTrack = false;
	static private $id = false;
	static private $types = [
		'view' => 1,
		'save' => 2,
		'delete' => 3,
	];

	static function addBasic($type)
	{
		$db = App\Db::getInstance('log');
		if ($type == 'view' && App\Request::_isAjax()) {
			self::lockTracking();
		}
		if (self::$id !== false || self::$lockTrack) {
			return true;
		}
		$insertedInfo = $db->createCommand()->insert('l_#__settings_tracker_basic', [
				'user_id' => Users_Privileges_Model::getCurrentUserModel()->getId(),
				'type' => self::$types[$type],
				'module_name' => \App\Request::_get('module'),
				'record_id' => self::$recordId ? self::$recordId : 0,
				'date' => date('Y-m-d H:i:s'),
				'action' => \App\Config::$processType . ':' . \App\Config::$processName
			])->execute();
		if ($insertedInfo === 1) {
			self::$id = $db->getLastInsertID('l_#__settings_tracker_basic_id_seq');
		}
	}

	static function changeType($type)
	{
		App\Db::getInstance('log')->createCommand()
			->update('l_#__settings_tracker_basic', ['type' => self::$types[$type]], ['id' => [self::$id]])
			->execute();
	}

	static function addDetail($prev, $post)
	{
		if (self::$lockTrack) {
			return true;
		}
		if (self::$id !== false) {
			self::addBasic('save');
		}
		$db = App\Db::getInstance('log');
		foreach ($post as $key => $value) {
			if (isset($prev[$key]) && $value == $prev[$key]) {
				continue;
			}
			$db->createCommand()->insert('l_#__settings_tracker_detail', [
				'id' => self::$id,
				'prev_value' => isset($prev[$key]) ? $prev[$key] : '',
				'post_value' => is_null($value) ? '' : $value,
				'field' => $key,
			])->execute();
		}
	}

	static function lockTracking($lock = true)
	{
		self::$lockTrack = $lock;
	}

	static function setRecordId($record)
	{
		if (empty(self::$recordId)) {
			self::$recordId = $record;
		}
	}
}
