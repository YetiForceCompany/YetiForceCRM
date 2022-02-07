<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_Module_Model extends Settings_Vtiger_Module_Model
{
	private static $data = [];
	private static $types = [
		'owner' => 'app_data/module_record_allocation.php',
		'sharedOwner' => 'app_data/sharedOwner.php',
	];

	public function save($data)
	{
		$moduleName = $data['module'];
		$userId = $data['userid'];
		$userData = $data['ids'] ?? [];
		$this->putToFile($moduleName, $userId, $userData);
	}

	public function remove($moduleName)
	{
		$fileData = self::loadFile($this->get('type'));
		unset($fileData[$moduleName]);
		$content = '$map=' . var_export($fileData, true) . ';';
		\App\Utils::saveToFile(self::$types[$this->get('type')], $content);
	}

	public function putToFile($moduleName, $userId, $userData)
	{
		$fileData = self::loadFile($this->get('type'));
		if (!isset($fileData[$moduleName])) {
			$fileData[$moduleName] = [];
		}
		$fileData[$moduleName][$userId] = $userData;
		$content = '$map=' . var_export($fileData, true) . ';';
		\App\Utils::saveToFile(self::$types[$this->get('type')], $content);
	}

	public static function getRecordAllocationByModule($type, $moduleName)
	{
		$fileData = self::loadFile($type);

		return $fileData[$moduleName] ?? [];
	}

	public static function loadFile($type)
	{
		if (empty(self::$types[$type])) {
			return [];
		}
		if (!isset(self::$data[$type])) {
			$file = self::$types[$type];
			require $file;
			self::$data[$type] = $map ?? [];
		}
		return self::$data[$type];
	}

	public static function resetDataVariable()
	{
		self::$data = [];
	}
}
