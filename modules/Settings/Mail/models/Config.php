<?php

/**
 * Settings mail config model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Mail_Config_Model
{
	public static function updateConfig($name, $val, $type)
	{
		\App\Db::getInstance()->createCommand()->update('yetiforce_mail_config', ['value' => $val], [
			'type' => $type,
			'name' => $name,
		])->execute();
	}

	public static function getConfig($type)
	{
		$config = [];
		$dataReader = (new \App\Db\Query())->select(['name', 'value'])
			->from('yetiforce_mail_config')
			->where(['type' => $type])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$config[$row['name']] = $row['value'];
		}
		$dataReader->close();

		return $config;
	}

	public static function acceptanceRecord($id)
	{
		\App\Db::getInstance('admin')->createCommand()->update('s_#__mail_queue', ['status' => 1], [
			'id' => $id,
		])->execute();
	}

	/**
	 * Function to get instance.
	 *
	 * @param bool true/false
	 *
	 * @return Settings_Mail_Config_Model
	 */
	public static function getInstance()
	{
		return new self();
	}
}
