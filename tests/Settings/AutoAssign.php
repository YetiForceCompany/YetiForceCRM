<?php
/**
 * Auto Assign test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Tests\Settings;

/**
 * Auto Assign test class.
 */
class AutoAssign extends \Tests\Base
{
	/**
	 * List of users.
	 *
	 * @var \Vtiger_Record_Model[]
	 */
	private static $users = [];
	/**
	 * List of users who can be assigned.
	 *
	 * @var int[]
	 */
	private static $autoAssignUsers = [];
	/**
	 * Default user model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	private static $defaultUser;
	/**
	 * Group ID.
	 *
	 * @var int
	 */
	private static $groupId;
	/**
	 * Tickets created.
	 *
	 * @var \Vtiger_Record_Model[]
	 */
	private static $tickets = [];
	/**
	 * Auto Assign record model ID.
	 *
	 * @var int
	 */
	private static $autoAssign;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass(): void
	{
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		self::$users['assignUserNo'] = self::createUserRecord('assignUserNo');
		self::$users['assignUserYes'] = self::createUserRecord('assignUserYes', ['available' => '1', 'auto_assign' => '1']);
		self::$users['assignUserYes2'] = self::createUserRecord('assignUserYes2', ['available' => '1', 'auto_assign' => '1']);
		self::$defaultUser = self::createUserRecord('assignUserDefault');

		self::$autoAssignUsers = array_filter(array_map(fn ($userModel) => $userModel->get('auto_assign') ? $userModel->getId() : null, self::$users));

		$members = array_map(fn ($userModel) => \App\PrivilegeUtil::MEMBER_TYPE_USERS . ':' . $userModel->getId(), self::$users);

		$recordModel = \Settings_Groups_Record_Model::getCleanInstance();
		$recordModel->set('groupname', 'Support groups');
		$recordModel->set('description', 'Test description');
		$recordModel->set('members', $recordModel->getFieldInstanceByName('members')->getDBValue($members));
		$recordModel->set('modules', $recordModel->getFieldInstanceByName('modules')->getDBValue([\App\Module::getModuleId('HelpDesk')]));
		$recordModel->save();
		self::$groupId = $recordModel->getId();
	}

	/**
	 * Cleaning after tests.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void
	{
		foreach (self::$tickets as $recordModel) {
			$recordModel->delete();
		}
		foreach (self::$users as $recordModel) {
			\Users_Record_Model::deleteUserPermanently($recordModel->getId(), \App\User::getCurrentUserId());
		}
		\Users_Record_Model::deleteUserPermanently(self::$defaultUser->getId(), \App\User::getCurrentUserId());
	}

	/**
	 * Create user.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $login
	 * @param array  $data
	 *
	 * @return \Users_Record_Model
	 */
	public static function createUserRecord($login = 'demo', $data = []): \Vtiger_Record_Model
	{
		$pwd = \App\Encryption::generatePassword(10);
		$user = \Vtiger_Record_Model::getCleanInstance('Users');
		$userData = array_merge([
			'user_name' => $login,
			'email1' => "{$login}@yetiforce.com",
			'first_name' => "{$login}Name",
			'last_name' => "{$login}Surname",
			'user_password', $pwd,
			'confirm_password', $pwd,
			'roleid' => 'H2',
			'is_admin' => 'off',
		], $data);
		foreach ($userData as $key => $values) {
			$user->set($key, $values);
		}
		$user->save();
		return $user;
	}

	/**
	 * Undocumented function.
	 *
	 * @param int $owner
	 *
	 * @return \Vtiger_Record_Model
	 */
	public static function createTicket(int $owner): \Vtiger_Record_Model
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('HelpDesk');
		$recordModel->set('ticket_title', 'Ticket' . random_int(0, 10));
		$recordModel->set('ticketstatus', 'Open');
		$recordModel->set('parent_id', \Tests\Base\C_RecordActions::createAccountRecord()->getId());
		$recordModel->set('assigned_user_id', $owner);
		$recordModel->save();
		self::$tickets[$recordModel->getId()] = $recordModel;
		return $recordModel;
	}

	/**
	 * Testing create auto assign record.
	 */
	public function testCreate()
	{
		$request = new \App\Request([
			'tabid' => \App\Module::getModuleId('HelpDesk'),
			'subject' => 'Auto Assign',
			'state' => \App\AutoAssign::STATUS_ACTIVE,
			'workflow' => 1,
			'handler' => 1,
			'gui' => 1,
			'conditions' => '{"condition":"AND","rules":[{"fieldname":"assigned_user_id:HelpDesk","operator":"e","value":"' . self::$groupId . '"}]}',
			'members' => [\App\PrivilegeUtil::MEMBER_TYPE_GROUPS . ':' . self::$groupId],
			'method' => \App\AutoAssign::METHOD_LOAD_BALANCE,
			'default_assign' => self::$defaultUser->getId(),
			'record_limit' => 0,
			'record_limit_conditions' => '',
		]);
		$recordModel = \Settings_AutomaticAssignment_Record_Model::getCleanInstance();
		$recordModel->setDataFromRequest($request);
		$recordModel->save();
		self::$autoAssign = $recordModel->getId();
		$data = $recordModel->getData();

		$autoAssignInstance = \Settings_AutomaticAssignment_Record_Model::getInstanceById($recordModel->getId());
		$newData = $autoAssignInstance->getData();
		$this->assertSame(ksort($data), ksort($newData), 'Data mismatch');

		$handlers = (new \App\EventHandler())->setModuleName('HelpDesk')->getHandlers('EntityBeforeSave');
		$this->assertArrayHasKey('Vtiger_AutoAssign_Handler', $handlers, 'The handler should be set up');
	}

	/**
	 * Testing handler.
	 */
	public function testHandler()
	{
		if (!\App\YetiForce\Shop::check('YetiForceAutoAssignment')) {
			$this->markTestSkipped('No required access to test this functionality');
			return;
		}
		$recordModel = self::createTicket(self::$groupId);
		$owner = $recordModel->get('assigned_user_id');
		$possibleOwners = self::$autoAssignUsers;
		$this->assertContains($owner, $possibleOwners, 'Wrong ticket owner assign:' . print_r($possibleOwners, true));

		$recordModel = self::createTicket(self::$groupId);
		$possibleOwners = array_diff($possibleOwners, [$owner]);
		$owner = $recordModel->get('assigned_user_id');
		$this->assertContains($owner, $possibleOwners, 'Wrong ticket owner assign.');

		$user = self::$users['assignUserNo'];
		$recordModel = self::createTicket($user->getId());
		$this->assertSame($user->getId(), $recordModel->get('assigned_user_id'), 'Wrong ticket owner');
	}

	/**
	 * Testing assign record.
	 */
	public function testUpdate()
	{
		$autoAssignInstance = \Settings_AutomaticAssignment_Record_Model::getInstanceById(self::$autoAssign);
		$autoAssignInstance->set('method', \App\AutoAssign::METHOD_ROUND_ROBIN);
		$autoAssignInstance->save();

		$isChange = (new \App\Db\Query())
			->from($autoAssignInstance->getTable())
			->where([$autoAssignInstance->getTableIndex() => $autoAssignInstance->getId(), 'method' => \App\AutoAssign::METHOD_ROUND_ROBIN])
			->exists(\App\Db::getInstance('admin'));
		$this->assertTrue($isChange, 'Auto assign method should change');
	}

	/**
	 * Testing round robin method.
	 *
	 * @param int $expectedCurrentOwner
	 * @param int $expectedNextOwner
	 */
	public function testRoundRobin()
	{
		if (!\App\YetiForce\Shop::check('YetiForceAutoAssignment')) {
			$this->markTestSkipped('No required access to test this functionality');
			return;
		}
		$assigned = [];
		for ($i = 0; $i < \count(self::$autoAssignUsers); ++$i) {
			$recordModel = self::createTicket(self::$groupId);
			$assigned[$i] = $recordModel->get('assigned_user_id');
		}
		$autoAssignModel = \App\AutoAssign::getInstanceById(self::$autoAssign);
		foreach ($assigned as $key => $ownerId) {
			$expectedNextOwner = $assigned[$key + 1] ?? $assigned[0];
			$recordModel = self::createTicket(self::$groupId);
			$owner = $recordModel->get('assigned_user_id');
			$this->assertContains($owner, self::$autoAssignUsers, 'Wrong ticket owner assign');
			$this->assertSame($owner, $ownerId, 'Wrong ticket owner assign');
			$this->assertSame($expectedNextOwner, $autoAssignModel->getQueryByRoundRobin()->scalar(), 'Incorrect user selection');
		}
	}

	/**
	 * Removal test.
	 */
	public function testDelete()
	{
		$autoAssignInstance = \Settings_AutomaticAssignment_Record_Model::getInstanceById(self::$autoAssign);
		$this->assertTrue($autoAssignInstance->delete(), 'The delete function should return true');

		$autoAssignInstance = \Settings_AutomaticAssignment_Record_Model::getInstanceById(self::$autoAssign);
		$this->assertNull($autoAssignInstance->getId(), 'Assign record should not exists');

		$handlers = (new \App\EventHandler())->setModuleName('HelpDesk')->getHandlers('EntityBeforeSave');
		$this->assertFalse(isset($handlers['Vtiger_AutoAssign_Handler']), 'The handler should not be active');
	}
}
