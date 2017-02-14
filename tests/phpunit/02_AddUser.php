<?php
/**
 * AddUser test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class AddUser extends TestCase
{

	public function test()
	{
		$user = Vtiger_Record_Model::getCleanInstance('Users');
		$user->set('user_name', 'demo');
		$user->set('email1', 'demo@yetiforce.com');
		$user->set('first_name', 'Demo');
		$user->set('last_name', 'YetiForce');
		$user->set('user_password', 'demo');
		$user->set('confirm_password', 'demo');
		$user->set('roleid', 'H2');
		$user->save();
		define('TESTS_USER_ID', $user->getId());
		$user = CRMEntity::getInstance('Users');
		$currentUser = $user->retrieveCurrentUserInfoFromFile(TESTS_USER_ID);
		vglobal('current_user', $currentUser);
		App\User::setCurrentUserId(TESTS_USER_ID);
	}
}
