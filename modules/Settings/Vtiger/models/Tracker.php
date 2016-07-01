<?php

/**
 * Main class to save modification in settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Vtiger_Tracker_Model
{

	static $id = false;
	static $types = [
		'view' => 1,
		'save' => 2,
		'delete' => 3,
	];

	static function addBasic($type)
	{
		if (self::$id != false) {
			return true;
		}
		$db = PearDatabase::getInstance('log');
		$currentUser = Users_Privileges_Model::getCurrentUserModel();

		$params = [
			'user_id' => $currentUser->getId(),
			'type' => self::$types[$type],
			'module_name' => AppRequest::get('module'),
			'date' => date('Y-m-d H:i:s'),
			'action' => _PROCESS_NAME
		];
		$insertedInfo = $db->insert('l_yf_settings_tracker_basic', $params);
		if ($insertedInfo['rowCount'] == 1) {
			self::$id = $insertedInfo['id'];
		}
	}

	static function addDetail($prev, $post, $field = false)
	{
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
				'post_value' => $value,
				'field' => $field == false && isset($field[$key]) ? $field[$key] : '',
			];
			$db->insert('l_yf_settings_tracker_detail', $paramsToSave);
		}
	}
}
