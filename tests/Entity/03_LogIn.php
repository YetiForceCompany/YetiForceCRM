<?php

/**
 * LogIn test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class LogIn extends \Tests\Base
{
	/**
	 * Testing login page display.
	 */
	public function testLoginPage()
	{
		if (!IS_WINDOWS) {
			ob_start();
			(new Vtiger_WebUI())->process(App\Request::init());
			$content = ob_get_contents();
			$this->assertTrue(strpos($content, 'input name="username"') !== false);
			$this->assertTrue(strpos($content, 'input name="password"') !== false);
			file_put_contents('tests/LoginPage.txt', $content);
			ob_end_clean();
		}
	}

	/**
	 * Test logging into the system.
	 */
	public function testLoginInToCrm()
	{
		$userName = 'demo';
		$userRecordModel = Users_Record_Model::getCleanInstance('Users')->set('user_name', $userName);
		if ($userRecordModel->doLogin($userName)) {
			App\Session::set('authenticated_user_id', TESTS_USER_ID);
			App\Session::set('app_unique_key', AppConfig::main('application_unique_key'));
			App\Session::set('user_name', $userName);
			App\Session::set('full_user_name', \App\Fields\Owner::getUserLabel(TESTS_USER_ID));
			$this->assertInternalType('int', TESTS_USER_ID);
		}
	}

	/**
	 * Test Case-sensitive Username.
	 */
	public function testCaseSensitiveUsername()
	{
		$userName = 'Demo';
		$userRecordModel = \Users_Record_Model::getCleanInstance('Users')->set('user_name', $userName);
		$this->assertTrue($userRecordModel->doLogin('demo'));
		$this->assertTrue(App\User::getUserModel($userRecordModel->getId())->getDetail('user_name') !== $userName);
	}

	/**
	 * Testing user data verification.
	 */
	public function testUserVerifyData()
	{
		$this->assertTrue(Users_Module_Model::checkMailExist('demo@yetiforce.com'));
		$this->assertFalse(Users_Module_Model::checkMailExist('demo@yetiforce.com', TESTS_USER_ID));
		$this->assertFalse(Users_Module_Model::checkMailExist('xxx@yetiforce.com'));
		$this->assertSame(Users_Module_Model::checkUserName('demo'), \App\Language::translate('LBL_USER_NAME_EXISTS', 'Users'));
		$this->assertSame(Users_Module_Model::checkUserName('demo', TESTS_USER_ID), \App\Language::translate('LBL_USER_NAME_HAS_ALREADY_BEEN_USED', 'Users'));
		$this->assertSame(Users_Module_Model::checkUserName('test', 1), \App\Language::translate('LBL_FORBIDDEN_USERNAMES', 'Users'));
	}

	/**
	 * Testing the Brute Force mechanism.
	 */
	public function testBruteForce()
	{
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		$this->assertFalse($bfInstance->isBlockedIp());
		$bfInstance->updateBlockedIp();
	}
}
