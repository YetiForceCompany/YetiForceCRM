<?php

/**
 * Social media config model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_SocialMedia_Config_Model extends \App\Base
{
	/**
	 * Configuration type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Settings_SocialMedia_Config_Model constructor.
	 *
	 * @param $type string
	 */
	public function __construct($type)
	{
		$this->getConfig($type);
	}

	/**
	 * @param $type string
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getConfig($type)
	{
		if (\App\Cache::has('SocialMediaConfig', $type)) {
			return $this->value = \App\Cache::get('SocialMediaConfig', $type);
		}
		$this->type = $type;
		$this->value = [];
		$dataReader = (new \App\Db\Query())
			->select(['name', 'value'])
			->from('u_#__social_media_config')
			->where(['type' => $type])
			->createCommand()
			->query();
		while ($row = $dataReader->read()) {
			$this->value[$row['name']] = \App\Json::decode($row['value']);
		}
		$dataReader->close();
		\App\Cache::save('SocialMediaConfig', $type, $this->value, \App\Cache::LONG);
		return $this->value;
	}

	/**
	 * Save changes to DB.
	 *
	 * @throws \yii\db\Exception
	 */
	public function save()
	{
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		$transaction->begin();
		try {
			foreach ($this->value as $key => $val) {
				$db->createCommand()->update('u_#__social_media_config',
					['value' => \App\Json::encode($val)],
					['type' => $this->type, 'name' => $key]
				)->execute();
			}
			$transaction->commit();
			$this->clearCache();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	/**
	 * Function clears cache.
	 */
	public function clearCache()
	{
		\App\Cache::delete('SocialMediaConfig', $this->type);
	}
}
