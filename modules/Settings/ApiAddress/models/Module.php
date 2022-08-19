<?php

/**
 * Settings ApiAddress module model class.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_ApiAddress_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $baseTable = 's_#__address_finder_config';

	/**
	 * Get configuration.
	 *
	 * @param string $type
	 *
	 * @return void
	 */
	public function getConfig(string $type = null): array
	{
		$rawData = [];
		$query = (new \App\Db\Query())->from($this->baseTable);
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

	/**
	 * Save Configuration.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function setConfig(array $data): bool
	{
		$db = \App\Db::getInstance();
		$result = false;
		foreach ($data as $value) {
			['name' => $key, 'type' => $type, 'val' => $val] = $value;
			if ((new \App\Db\Query())->select('type', 'name')
				->from($this->baseTable)
				->where(['name' => $key, 'type' => $type])
				->exists()
			) {
				$result = $db->createCommand()->update($this->baseTable, ['val' => $val], ['type' => $type, 'name' => $key])->execute();
			} else {
				$result = $db->createCommand()->insert($this->baseTable, ['val' => $val, 'type' => $type, 'name' => $key])->execute();
			}
		}

		return (bool) $result;
	}
}
