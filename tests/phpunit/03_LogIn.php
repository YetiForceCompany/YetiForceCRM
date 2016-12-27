<?php
/**
 * LogIn test class
 * @package YetiForce.Tests
 * @license licenses/License.html
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
		ob_start();
		(new Vtiger_WebUI())->process(AppRequest::init());
		file_put_contents('tests/LoginPage.txt', ob_get_contents());
		ob_end_clean();
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
		}
	}
}
