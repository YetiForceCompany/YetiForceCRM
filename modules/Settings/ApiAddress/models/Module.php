<?php

/**
 * Settings ApiAddress module model class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_ApiAddress_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getConfig($type = NULL)
	{
		$rawData = [];
		$query = (new \App\Db\Query())->from('vtiger_apiaddress');
		if ($type) {
			$query->where(['type' => $type]);
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$rawData[$row['type']][$row['name']] = $row['val'];
		}
		return $rawData;
	}

	public function setConfig(array $elements)
	{
		\App\Log::trace('Entering set api address config');
		$apiName = $elements['api_name'];
		unset($elements['api_name']);
		$result = 0;
		if (count($elements)) {
			$db = \App\Db::getInstance();
			foreach ($elements as $key => $value) {
				$result = $db->createCommand()
					->update('vtiger_apiaddress', [
						'val' => $value
						], ['type' => $apiName, 'name' => $key])
					->execute();
			}
		}
		\App\Log::trace('Exiting set api address config');
		return $result;
	}
	/*
	 * Function that checks if keys for chosen adress api are entered, hence if this api is active
	 * @return boolean - true if active, false otherwise
	 */

	public static function isActive()
	{
		return (new \App\Db\Query())
				->from('vtiger_apiaddress')
				->where(['name' => 'nominatim'])
				->andWhere(['>', 'val', 0])
				->count(1);
	}
}
