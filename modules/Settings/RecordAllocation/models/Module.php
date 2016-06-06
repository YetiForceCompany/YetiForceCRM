<?php

/**
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_Module_Model extends Settings_Vtiger_Module_Model
{

	private static $types = [
		'owner' => 'user_privileges/module_record_allocation.php',
		'multiOwner' => 'user_privileges/MultiOwner.php',
	];

	public function saveRecordAllocation($data)
	{
		$newData = [];
		$file = self::$types[$this->get('type')];
		require($file);
		$toLowerModule = strtolower($data['module']);
		$userId = $data['userid'];
		$userData = isset($data['ids']) ? $data['ids'] : [];
		if (isset($$toLowerModule)) {
			$dataFromFile = $$toLowerModule;
			if (empty($userData)) {
				unset($dataFromFile[$userId]);
			} else {
				$dataFromFile[$userId] = $userData;
			}
			$newData = $dataFromFile;
			$content = $this->removeDataInFile($toLowerModule);
			$this->putData($toLowerModule, $newData, $content);
		} elseif (!empty($userData)) {
			$newData[$userId] = $userData;
			$content = file_get_contents($file) . PHP_EOL;
			$this->putData($toLowerModule, $newData, $content);
		}
	}

	public function removeDataInFile($toLowerModule)
	{
		$file = self::$types[$this->get('type')];
		if (file_exists($file)) {
			$configContent = file($file);
			$removeLine = false;
			foreach ($configContent as $key => $line) {
				if (strpos($line, $toLowerModule) !== false) {
					unset($configContent[$key]);
					$removeLine = true;
				} elseif ($removeLine && strpos($line, '$') === false) {
					unset($configContent[$key]);
				} elseif ($removeLine) {
					break;
				}
			}
			return implode('', $configContent);
		}
	}

	public function putData($toLowerModule, $newData, $content)
	{
		$file = self::$types[$this->get('type')];
		if ($newData) {
			$newContent = '$' . $toLowerModule . ' = [';
			foreach ($newData as $userId => $userData) {
				$newContent .= "'" . $userId . "'=>[";
				foreach ($userData as $type => $ids) {
					$newContent .= "'" . $type . "'=>['" . implode("','", $ids) . "'],";
				}
				$newContent .= '],';
			}
			$newContent .= '];';
			$content = $content . $newContent;
		}
		file_put_contents($file, $content);
	}

	public static function getRecordAllocationByModule($type, $moduleName)
	{
		$file = self::$types[$type];
		require($file);
		$toLowerModule = strtolower($moduleName);
		if (isset($$toLowerModule)) {
			return $$toLowerModule;
		}
		return false;
	}
}
