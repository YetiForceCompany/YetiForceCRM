<?php
/**
 * Settings_SocialMedia_Config_Model test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Settings;

class SocialMediaConfig extends \Tests\Base
{
	/**
	 * @var \yii\db\Transaction
	 */
	private static $transaction;
	/**
	 * Configuration type.
	 *
	 * @var string
	 */
	private static $type = 'test_type';
	/**
	 * The key name in the configuration.
	 *
	 * @var string
	 */
	private static $name = 'test';

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		static::$transaction = \App\Db::getInstance()->beginTransaction();
	}

	/**
	 * Test for adding.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \yii\db\Exception
	 */
	public function testInsert()
	{
		$config = new \Settings_SocialMedia_Config_Model(static::$type);
		$config->set(static::$name, 123);
		$config->save();
		$row = (new \App\Db\Query())
			->from('u_#__social_media_config')
			->where(['type' => static::$type, 'name'=> static::$name])
			->one();
		$this->assertSame(\App\Json::decode($row['value']), 123);
	}

	/**
	 * Test for editing.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \yii\db\Exception
	 */
	public function testUpdate()
	{
		$config = new \Settings_SocialMedia_Config_Model(static::$type);
		$config->set(static::$name, ['a', 'b', 'c']);
		$config->save();
		$row = (new \App\Db\Query())
			->from('u_#__social_media_config')
			->where(['type' => static::$type, 'name' => static::$name])
			->one();
		$this->assertSame(\App\Json::decode($row['value']), ['a', 'b', 'c']);
	}

	/**
	 * Test for removal.
	 *
	 * @throws \yii\db\Exception
	 */
	public function testRemove()
	{
		$config = new \Settings_SocialMedia_Config_Model(static::$type);
		$config->remove(static::$name);
		$config->save();
		$this->assertFalse((new \App\Db\Query())->from('u_#__social_media_config')->where(['type' => static::$type, 'name' => static::$name])->exists(), 'Error while removing');
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		static::$transaction->rollBack();
	}
}
