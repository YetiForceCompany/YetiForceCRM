<?php

/**
 * Class SocialMedia Record Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class SocialMedia_Record_Model extends \App\Base
{
	/**
	 * The name of the table in the database for Twitter.
	 */
	public const TABLE_TWITTER = 'u_#__social_media_twitter';
	/**
	 * Table name in the database for the twitter archive.
	 */
	public const TABLE_TWITTER_BACKUP = 'b_#__social_media_twitter';
	/**
	 * Allowed fields as they are in the table.
	 */
	private const ALLOWED_FIELDS = ['id', 'twitter_login', 'id_twitter', 'message', 'created_at', 'data_json', 'created_time'];

	/**
	 * Function to get the clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Function to get the instance.
	 *
	 * @param $id
	 *
	 * @return \SocialMedia_Record_Model
	 */
	public static function getInstanceById($id)
	{
		$cacheName = get_class();
		if (\App\Cache::staticHas($cacheName, $id)) {
			return \App\Cache::staticGet($cacheName, $id);
		}
		$instance = self::getCleanInstance();
		$data = (new \App\Db\Query())
			->from(static::TABLE_TWITTER)
			->where(['id' => $id])
			->one();
		$instance->setData($data);
		\App\Cache::staticSave($cacheName, $id, $instance);
		return $instance;
	}

	/**
	 * Function to get the id of the record.
	 *
	 * @return int - Record Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record.
	 *
	 * @param int $value - id value
	 */
	public function setId($value)
	{
		return $this->set('id', (int) $value);
	}

	/**
	 * Is new record.
	 *
	 * @return bool
	 */
	public function isNew()
	{
		return empty($this->get('id'));
	}

	/**
	 * Save to the database.
	 */
	public function save()
	{
		$db = \App\Db::getInstance();
		$data = [];
		foreach (static::ALLOWED_FIELDS as $fieldName) {
			$data[$fieldName] = $this->get($fieldName);
		}
		if ($this->isNew()) {
			$db->createCommand()->insert(static::TABLE_TWITTER, $data)->execute();
			$this->setId((int) $db->getLastInsertID(static::TABLE_TWITTER . '_seq'));
		} else {
			unset($data['id']);
			$db->createCommand()->update(static::TABLE_TWITTER, $data, ['id' => $this->getId()])->execute();
		}
	}

	/**
	 * Removal from the database.
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function delete()
	{
		$cacheName = get_class();
		if (\App\Cache::staticHas($cacheName, $this->getId())) {
			\App\Cache::staticDelete($cacheName, $this->getId());
		}
		return \App\Db::getInstance()->createCommand()->delete(static::TABLE_TWITTER, ['id' => $this->getId()])
			->execute();
	}
}
