<?php

/**
 * Settings ApiAddress module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_ApiAddress_Module_Model extends Settings_Vtiger_Module_Model
{
	public function getConfig($type = null)
	{
		$rawData = [];
		$query = (new \App\Db\Query())->from('s_yf_address_finder_config');
		if ($type) {
			$query->where(['type' => $type]);
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$rawData[$row['type']][$row['name']] = $row['val'];
		}
		$dataReader->close();

		return $rawData;
	}

	public function setConfig(array $elements)
	{
		\App\Log::trace('Entering set api address config');
		unset($elements['api_name']);
		$result = false;
		if (\count($elements)) {
			$db = \App\Db::getInstance();
			foreach ($elements as $type => $values) {
				foreach ($values as $key => $value) {
					if ((new \App\Db\Query())->select('type', 'name')
						->from(['C' => 's_yf_address_finder_config'])
						->where(['C.name' => $key, 'C.type' => $type])
						->exists()) {
						$db->createCommand()
							->update('s_yf_address_finder_config', [
								'val' => $value,
							], ['type' => $type, 'name' => $key])
							->execute();
					} else {
						$db->createCommand()
							->insert('s_yf_address_finder_config', [
								'val' => $value,
								'type' => $type,
								'name' => $key
							])
							->execute();
					}
				}
			}
			$result = true;
		}
		\App\Log::trace('Exiting set api address config');

		return $result;
	}
}
