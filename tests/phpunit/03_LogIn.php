<?php
/**
 * LogIn test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers LogIn::<public>
 */
class LogIn extends TestCase
{

	public function testLoginPage()
	{
		if (IS_WINDOWS) {
			$this->assertTrue(true);
		} else {
			ob_start();
			(new Vtiger_WebUI())->process(App\Request::init());
			$content = ob_get_contents();
			$this->assertTrue(strpos($content, 'input name="username"') !== false);
			$this->assertTrue(strpos($content, 'input name="password"') !== false);
			file_put_contents('tests/LoginPage.txt', $content);
			ob_end_clean();
		}
	}

	public function testLoginInToCrm()
	{
		$userName = 'demo';
		$user = CRMEntity::getInstance('Users');
		$user->column_fields['user_name'] = $userName;
		if ($user->doLogin($userName)) {
			Vtiger_Session::set('authenticated_user_id', TESTS_USER_ID);
			Vtiger_Session::set('app_unique_key', AppConfig::main('application_unique_key'));
			Vtiger_Session::set('user_name', $userName);
			Vtiger_Session::set('full_user_name', \App\Fields\Owner::getUserLabel(TESTS_USER_ID));
			$this->assertInternalType('int', TESTS_USER_ID);
		} else {
			$this->assertTrue(false);
		}
	}

	public function testBruteForce()
	{
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		$this->assertFalse($bfInstance->isBlockedIp());
		$bfInstance->updateBlockedIp();
	}
}
