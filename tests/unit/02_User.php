<?php
/**
 * AddUser test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers User::<public>
 */
class User extends TestCase
{

	/**
	 * User id
	 */
	private static $id;

	/**
	 * Testing user creation
	 */
	public function testLoadBaseUser()
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
		$userForus = CRMEntity::getInstance('Users');
		$currentUser = $userForus->retrieveCurrentUserInfoFromFile(TESTS_USER_ID);
		vglobal('current_user', $currentUser);
		App\User::setCurrentUserId(TESTS_USER_ID);
		$this->assertInternalType('int', TESTS_USER_ID);
	}

	/**
	 * Testing user creation
	 */
	public function testAddUser()
	{
		$user = Vtiger_Record_Model::getCleanInstance('Users');
		$user->set('user_name', 'testuser');
		$user->set('email1', 'testuser@yetiforce.com');
		$user->set('first_name', 'Test');
		$user->set('last_name', 'YetiForce');
		$user->set('user_password', 'testuser');
		$user->set('confirm_password', 'testuser');
		$user->set('roleid', 'H2');
		$user->save();
		static::$id = $user->getId();
		$this->assertInternalType('int', static::$id);
		$row = (new \App\Db\Query())->from('vtiger_users')->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['user_name'], 'testuser');
		$this->assertEquals($row['email1'], 'testuser@yetiforce.com');
		$this->assertEquals($row['first_name'], 'Test');
		$this->assertEquals($row['last_name'], 'YetiForce');
		$this->assertEquals((new App\Db\Query())->select('roleid')->from('vtiger_user2role')->where(['userid' => static::$id])->scalar(), 'H2');
	}

	/**
	 * Testing user edition
	 */
	public function testEditUser()
	{
		$user = Vtiger_Record_Model::getInstanceById(static::$id, 'Users');
		$this->assertNotFalse($user, 'No user');
		$user->set('user_name', 'testuseredit');
		$user->set('first_name', 'Test edit');
		$user->set('last_name', 'YetiForce edit');
		$user->set('email1', 'testuser-edit@yetiforce.com');
		$user->set('roleid', 'H1');
		$user->save();
		$row = (new \App\Db\Query())->from('vtiger_users')->where(['id' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['user_name'], 'testuseredit');
		$this->assertEquals($row['email1'], 'testuser-edit@yetiforce.com');
		$this->assertEquals($row['first_name'], 'Test edit');
		$this->assertEquals($row['last_name'], 'YetiForce edit');
		$this->assertEquals((new App\Db\Query())->select('roleid')->from('vtiger_user2role')->where(['userid' => static::$id])->scalar(), 'H1');
	}

	/**
	 * Testing user deletion
	 */
	public function testDeleteUser()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$this->assertNotFalse($currentUserModel, 'No current user');
		Users_Record_Model::deleteUserPermanently(static::$id, $currentUserModel->getId());
		$this->assertFalse((new App\Db\Query())->from('vtiger_users')->where(['id' => static::$id])->exists(), 'The record was not removed from the database ID: ' . static::$id);
	}
}
