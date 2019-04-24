<?php

/**
 * LogIn test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Base;

class B_LogIn extends \Tests\Base
{
	/**
	 * Test logging into the system.
	 */
	public function testLoginInToCrm()
	{
		$userName = 'demo';
		$userRecordModel = \Users_Record_Model::getCleanInstance('Users')->set('user_name', $userName);
		if ($userRecordModel->doLogin(A_User::$defaultPassrowd)) {
			\App\Session::set('authenticated_user_id', \Tests\Base\A_User::createUsersRecord()->getId());
			\App\Session::set('app_unique_key', \App\Config::main('application_unique_key'));
			\App\Session::set('user_name', $userName);
			\App\Session::set('full_user_name', \App\Fields\Owner::getUserLabel(\Tests\Base\A_User::createUsersRecord()->getId()));
			$this->assertInternalType('int', \Tests\Base\A_User::createUsersRecord()->getId());
		}
	}

	/**
	 * Test Case-sensitive Username.
	 */
	public function testCaseSensitiveUsername()
	{
		$userName = 'Demo';
		$userRecordModel = \Users_Record_Model::getCleanInstance('Users')->set('user_name', $userName);
		$this->assertTrue($userRecordModel->doLogin(A_User::$defaultPassrowd));
		$this->assertTrue(\App\User::getUserModel($userRecordModel->getId())->getDetail('user_name') !== $userName);
	}

	/**
	 * Testing user data verification.
	 */
	public function testUserVerifyData()
	{
		$this->assertTrue(\Users_Module_Model::checkMailExist('demo@yetiforce.com'));
		$this->assertFalse(\Users_Module_Model::checkMailExist('demo@yetiforce.com', \Tests\Base\A_User::createUsersRecord()->getId()));
		$this->assertFalse(\Users_Module_Model::checkMailExist('xxx@yetiforce.com'));
		$this->assertSame(\Users_Module_Model::checkUserName('demo'), \App\Language::translate('LBL_USER_NAME_EXISTS', 'Users'));
		$this->assertSame(\Users_Module_Model::checkUserName('demo', \Tests\Base\A_User::createUsersRecord()->getId()), \App\Language::translate('LBL_USER_NAME_HAS_ALREADY_BEEN_USED', 'Users'));
		$this->assertSame(\Users_Module_Model::checkUserName('test', 1), \App\Language::translate('LBL_FORBIDDEN_USERNAMES', 'Users'));
	}

	/**
	 * Testing the Brute Force mechanism.
	 */
	public function testBruteForce()
	{
		$bfInstance = \Settings_BruteForce_Module_Model::getCleanInstance();
		$this->assertFalse($bfInstance->isBlockedIp());
		$bfInstance->updateBlockedIp();
	}
}
