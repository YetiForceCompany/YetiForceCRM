<?php
/**
 * SocialMediaRecord test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Entity;

class SocialMediaRecord extends \Tests\Base
{
	/**
	 * @var \yii\db\Transaction
	 */
	private static $transaction;

	/**
	 * Record id.
	 */
	private static $id;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		static::$transaction = \App\Db::getInstance()->beginTransaction();
	}

	/**
	 * Test user for 2FA.
	 */
	public function testInsert()
	{
		$createAt = date('Y-m-d H:i:s');
		$recordModel = new \SocialMedia_Record_Model();
		$recordModel->set('twitter_login', 'spacex');
		$recordModel->set('id_twitter', '4543645654456654');
		$recordModel->set('message', 'gdshgjkfdjfdgfds');
		$recordModel->set('created_at', $createAt);
		$recordModel->set('data_json', \App\Json::encode(['test' => 'data']));
		$recordModel->set('created_time', $createAt);
		$recordModel->save();
		static::$id = $recordModel->getId();

		$row = (new \App\Db\Query())->from('u_#__social_media_twitter')->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertSame('spacex', $row['twitter_login']);
		$this->assertSame('gdshgjkfdjfdgfds', $row['message']);
		$this->assertSame(\App\Json::encode(['test' => 'data']), $row['data_json']);
		$this->assertSame($createAt, $row['created_at']);
		$this->assertSame($createAt, $row['created_time']);
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		static::$transaction->rollBack();
		\App\Cache::clear();
	}
}
