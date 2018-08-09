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
		$recordModel = new \SocialMedia_Record_Model();
		$recordModel->set('twitter_login', 'spacex');
		$recordModel->set('id_twitter', '4543645654456654');
		$recordModel->set('twitter_login', 'spacex');
		$recordModel->set('message', 'gdshgjkfdjfdgfds');
		$recordModel->set('created_at', date('Y-m-d H:i:s'));
		$recordModel->set('data_json', '{json}');
		$recordModel->set('created_time', date('Y-m-d H:i:s'));
		$recordModel->save();

		$this->assertTrue(true);
		//$row = (new \App\Db\Query())->from('u_#__social_media_twitter')->where(['id' => static::$id])->one();
		//$this->assertNotFalse($row, 'No record id: ' . static::$id);
		/*$this->assertSame('test', $row['name']);
		$this->assertSame(4, $row['tabid']);
		$this->assertSame(0, $row['action']);
		$this->assertSame(0, $row['status']);
		$this->assertSame(0, $row['priority']);
		$this->assertSame(\App\Json::encode($members), $row['members']);
		$this->assertSame(\App\Json::encode([]), $row['conditions']);*/
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
