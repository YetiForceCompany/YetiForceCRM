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
	 * The name of the tables in the database.
	 */
	private const TABLE_NAME = 'u_#__social_media_config';
	/**
	 * The name of the cache.
	 */
	private const CACHE_NAME = 'SocialMediaConfig';

	/**
	 * Configuration type.
	 *
	 * @var string
	 */
	protected $type;
	/**
	 * Array with information which fields have changed.
	 *
	 * @var array
	 */
	protected $changes = [];
	/**
	 * Array with information which fields are new.
	 *
	 * @var array
	 */
	protected $newRecords = [];
	/**
	 * Array with information on which fields to delete.
	 *
	 * @var array
	 */
	protected $removeRecords = [];

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
		if (\App\Cache::has(static::CACHE_NAME, $type)) {
			$this->value = \App\Cache::get(static::CACHE_NAME, $type);
			return $this->value;
		}
		$this->type = $type;
		$this->value = [];
		$dataReader = (new \App\Db\Query())
			->select(['name', 'value'])
			->from(static::TABLE_NAME)
			->where(['type' => $type])
			->createCommand()
			->query();
		while ($row = $dataReader->read()) {
			$this->value[$row['name']] = \App\Json::decode($row['value']);
		}
		$dataReader->close();
		\App\Cache::save(static::CACHE_NAME, $type, $this->value, \App\Cache::LONG);
		return $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value)
	{
		if (!in_array($key, $this->changes)) {
			$this->changes[] = $key;
		}
		if (!array_key_exists($key, $this->value)) {
			$this->newRecords[] = $key;
		}
		return parent::set($key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		if (array_key_exists($key, $this->value)) {
			$this->removeRecords[] = $key;
		}
		parent::remove($key);
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
			foreach ($this->changes as $key) {
				$val = $this->value[$key];
				if (in_array($key, $this->newRecords)) {
					$db->createCommand()->insert(static::TABLE_NAME, [
						'name' => $key,
						'value' => \App\Json::encode($val),
						'type' => $this->type
					])->execute();
				} else {
					$db->createCommand()->update(static::TABLE_NAME,
						['value' => \App\Json::encode($val)],
						['type' => $this->type, 'name' => $key]
					)->execute();
				}
			}
			//Remove records
			foreach ($this->removeRecords as $key) {
				$db->createCommand()->delete(static::TABLE_NAME, ['type' => $this->type, 'name' => $key])->execute();
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
		\App\Cache::delete(static::CACHE_NAME, $this->type);
	}
}
