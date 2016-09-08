<?php

/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class LogIn extends TestCase {

    public function test() {
	$userName = 'admin';
	$userId = 1;
	$user = CRMEntity::getInstance('Users');
	$user->column_fields['user_name'] = $userName;
	if ($user->doLogin($userName)) {
	    Vtiger_Session::set('AUTHUSERID', $userId);
	    Vtiger_Session::set('authenticated_user_id', $userId);
	    Vtiger_Session::set('app_unique_key', AppConfig::main('application_unique_key'));
	    Vtiger_Session::set('authenticated_user_language', AppConfig::main('default_language'));
	    Vtiger_Session::set('user_name', $userName);
	    Vtiger_Session::set('full_user_name', \includes\fields\Owner::getUserLabel($userId));
	}
    }

}
