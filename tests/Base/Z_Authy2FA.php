<?php
/**
 * 2FA test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Base;

class Z_Authy2FA extends \Tests\Base
{
	/**
	 * @var \yii\db\Transaction
	 */
	private static $transaction;
	/**
	 * @var string
	 */
	private static $userAuthyMode;
	/**
	 * @var int
	 */
	private static $userId;
	/**
	 * @var string
	 */
	private static $systemMode;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass(): void
	{
		static::$userAuthyMode = \App\Config::security('USER_AUTHY_MODE');
		static::$transaction = \App\Db::getInstance()->beginTransaction();
		static::$systemMode = \App\Config::main('systemMode');
	}

	/**
	 * Test method "verifyCode".
	 */
	public function testVerifyCode()
	{
		$auth = new \Google\Authenticator\GoogleAuthenticator();
		$secret = $auth->generateSecret();
		$this->assertFalse(\Users_Totp_Authmethod::verifyCode($secret, '123000'), 'The "verifyCode" method does not work');
		$this->assertTrue(\Users_Totp_Authmethod::verifyCode($secret, $auth->getCode($secret)), 'The "verifyCode" method does not work');
	}

	/**
	 * Test user for 2FA.
	 */
	public function testUser()
	{
		static::$userId = \App\User::getUserIdByName('demo');
		$this->assertInternalType('int', static::$userId, 'No user demo');
		\App\User::setCurrentUserId(static::$userId);
		$this->assertSame(\App\User::getCurrentUserId(), static::$userId);
		$userRecordModel = \Users_Record_Model::getInstanceById(static::$userId, 'Users');
		$userRecordModel->set('authy_secret_totp', '');
		$userRecordModel->set('authy_methods', '');
		$userRecordModel->save();
		$row = (new \App\Db\Query())
			->select(['authy_secret_totp', 'authy_methods'])
			->from('vtiger_users')
			->where(['id' => static::$userId])
			->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$userId);
		$this->assertEmpty($row['authy_secret_totp']);
		$this->assertEmpty($row['authy_methods']);
	}

	/**
	 * Test config for 2FA.
	 */
	public function testConfig()
	{
		\App\Config::set('security', 'USER_AUTHY_MODE', 'TOTP_OFF');
		$this->assertSame(\App\Config::security('USER_AUTHY_MODE'), 'TOTP_OFF', 'Problem with saving the configuration');
		$this->assertFalse(\Users_Totp_Authmethod::isActive());
		\App\Config::set('security', 'USER_AUTHY_MODE', 'TOTP_OBLIGATORY');
		\App\Config::set('main', 'systemMode', 'demo');
		$this->assertFalse(\Users_Totp_Authmethod::isActive());
		\App\Config::set('main', 'systemMode', 'prod');
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		static::$transaction->rollBack();
		\App\Config::set('security', 'USER_AUTHY_MODE', static::$userAuthyMode);
		\App\Config::set('main', 'systemMode', static::$systemMode);
		\App\Cache::clear();
	}
}
