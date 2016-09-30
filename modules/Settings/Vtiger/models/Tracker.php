<?php

/**
 * Main class to save modification in settings
 * @package YetiForce.Model
 * @license licenses/License.html
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
		if ($type == 'view' && Vtiger_Request::isAjax()) {
			self::lockTracking();
		}
		if (self::$id != false || self::$lockTrack) {
			return true;
		}
		$db = PearDatabase::getInstance('log');
		$currentUser = Users_Privileges_Model::getCurrentUserModel();

		$params = [
			'user_id' => $currentUser->getId(),
			'type' => self::$types[$type],
			'module_name' => AppRequest::get('module'),
			'record_id' => self::$recordId,
			'date' => date('Y-m-d H:i:s'),
			'action' => _PROCESS_NAME
		];
		$insertedInfo = $db->insert('l_yf_settings_tracker_basic', $params);
		if ($insertedInfo['rowCount'] == 1) {
			self::$id = $insertedInfo['id'];
		}
	}

	static function changeType($type)
	{
		$db = PearDatabase::getInstance('log');
		$db->update('l_yf_settings_tracker_basic', ['type' => self::$types[$type]], ' id = ?', [self::$id]);
	}

	static function addDetail($prev, $post)
	{
		if (self::$lockTrack) {
			return true;
		}
		if (self::$id != false) {
			self::addBasic('save');
		}
		$db = PearDatabase::getInstance('log');
		foreach ($post as $key => $value) {
			if ($value == $prev[$key]) {
				continue;
			}
			$paramsToSave = [
				'id' => self::$id,
				'prev_value' => isset($prev[$key]) ? $prev[$key] : '',
				'post_value' => is_null($value) ? '' : $value,
				'field' => $key,
			];
			$db->insert('l_yf_settings_tracker_detail', $paramsToSave);
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
