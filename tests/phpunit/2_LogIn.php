<?php
/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers WebUI::<public>
 */
class LogIn extends TestCase
{

	public function loginPage()
	{
		ob_start();

		$webUI = new Vtiger_WebUI();
		$webUI->process(AppRequest::init());

		file_put_contents('tests/LoginPage.txt', ob_get_contents());
		ob_end_clean();
	}

	public function loginInToCrm()
	{
		$userName = 'demo';
		$user = CRMEntity::getInstance('Users');
		$user->column_fields['user_name'] = $userName;
		if ($user->doLogin($userName)) {
			Vtiger_Session::set('AUTHUSERID', TESTS_USER_ID);
			Vtiger_Session::set('authenticated_user_id', TESTS_USER_ID);
			Vtiger_Session::set('app_unique_key', AppConfig::main('application_unique_key'));
			Vtiger_Session::set('authenticated_user_language', AppConfig::main('default_language'));
			Vtiger_Session::set('user_name', $userName);
			Vtiger_Session::set('full_user_name', \includes\fields\Owner::getUserLabel(TESTS_USER_ID));
		}
	}
}
