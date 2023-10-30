<?php

/**
 * MultiCompany test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace Tests\Base;

/**
 * Class testing creating and setting roles and privileges for MultiCompany records.
 */
class J_MultiCompany extends \Tests\Base
{
	/**
	 * Temporary User record object.
	 *
	 * @var \Vtiger_Record_Model
	 */
	protected static $user;

	/**
	 * Temporary MultiCompany record object.
	 *
	 * @var \Vtiger_Record_Model
	 */
	protected static $recordMultiCompany;

	/**
	 * Temporary Roles Settings record object.
	 *
	 * @var \Settings_Roles_Record_Model
	 */
	protected static $role;

	/**
	 * Creating User module for tests.
	 *
	 * @return void
	 */
	public static function createUser(): void
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Users');
		$recordModel->set('user_name', 'TestMultiCompany');
		$recordModel->set('email1', 'testuser@yetiforce.com');
		$recordModel->set('first_name', 'Test');
		$recordModel->set('last_name', 'MultiCompany');
		$recordModel->set('user_password', 'Demo12345678T');
		$recordModel->set('confirm_password', 'Demo12345678T');
		$recordModel->set('roleid', self::$role->getId());
		$recordModel->set('is_admin', 'on');
		$recordModel->save();

		self::$user = $recordModel;
	}

	/**
	 * Creating MultiCompany module record for tests.
	 *
	 * @return \void
	 */
	public static function createMultiCompanyRecord(): void
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('MultiCompany');
		$recordModel->set('company_name', 'TestMulti sp. z o.o.');
		$recordModel->set('companyid1', '23123214141412');
		$recordModel->set('email1', 'mail@testowy.pl');
		$recordModel->set('assigned_user_id', \App\User::getCurrentUserId());
		$recordModel->save();

		self::$recordMultiCompany = $recordModel;
	}

	/**
	 * Creating new role for test.
	 *
	 * @return \void
	 */
	public static function createRole(): void
	{
		$recordModel = new \Settings_Roles_Record_Model();
		$parentRoleId = 'H1';

		$parentRole = \Settings_Roles_Record_Model::getInstanceById($parentRoleId);
		$recordModel->set('changeowner', 1)
			->set('searchunpriv', null)
			->set('listrelatedrecord', 0)
			->set('previewrelatedrecord', 0)
			->set('editrelatedrecord', 0)
			->set('permissionsrelatedfield', 0)
			->set('globalsearchadv', 1)
			->set('assignedmultiowner', 1)
			->set('clendarallorecords', 3)
			->set('company', 1)
			->set('auto_assign', 0)
			->set('allowassignedrecordsto', 1);

		$recordModel->set('rolename', 'TestMultiSelect');
		$parentRole->addChildRole($recordModel);
		$recordModel->save();

		self::$role = $recordModel;
	}

	/**
	 * Setup data to tests.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void
	{
		self::createMultiCompanyRecord();
		self::createRole();
		self::createUser();
	}

	/**
	 * Testing privilege to delete.
	 *
	 * @return void
	 */
	public function testPrivilegeToDelete(): void
	{
		$this->assertTrue(self::$recordMultiCompany->privilegeToDelete());
	}

	/**
	 * Testing not privilege to delete.
	 *
	 * @return void
	 */
	public function testNotPrivilegeToDelete(): void
	{
		self::$role->set('company', self::$recordMultiCompany->getId());
		self::$role->save();

		self::$recordMultiCompany->clearPrivilegesCache();

		$this->assertFalse(self::$recordMultiCompany->privilegeToDelete());
	}

	/**
	 * Testing Reloaded by MultiCompany by image loading.
	 *
	 * @return void
	 */
	public function testReloadByMultiCompany(): void
	{
		$filePath = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'tests' . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . 'MultiImage' . \DIRECTORY_SEPARATOR . '0.jpg';
		$filePathDestination = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'storage' . \DIRECTORY_SEPARATOR . '0.jpg';
		\copy($filePath, $filePathDestination);
		$recordModel = self::$recordMultiCompany;
		$fileObj = \App\Fields\File::loadFromPath($filePathDestination);
		$hash = $fileObj->generateHash(true, $filePathDestination);
		$attach[] = [
			'name' => '0.jpg',
			'size' => $fileObj->getSize(),
			'key' => $hash,
			'path' => $fileObj->getPath(),
			'type' => $fileObj->getMimeType(),
		];

		$recordModel->set('logo', \App\Json::encode($attach));
		$recordModel->save();

		$userModel = \App\User::getUserModel(self::$user->getId());
		$multiCompanyLogo = $userModel->get('multiCompanyLogo');

		$fieldModel = $recordModel->getField('logo');
		$data = \App\Json::decode(\App\Purifier::decodeHtml($fieldModel->getUITypeModel()->getDisplayValueEncoded($recordModel->get('logo'), $recordModel->getId(), $fieldModel->getFieldInfo())));

		$this->assertSame('0.jpg', $data[0]['name']);
		$this->assertSame($fileObj->getSize(), $data[0]['size']);
		$this->assertSame($hash, $data[0]['key']);

		$this->assertSame($multiCompanyLogo['name'], $data[0]['name']);
		$this->assertSame($multiCompanyLogo['key'], $data[0]['key']);
	}

	/**
	 * Cleaning after tests.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void
	{
		self::$recordMultiCompany->delete();
		self::$role->delete(\Settings_Roles_Record_Model::getInstanceById('H1'));
		\Users_Record_Model::deleteUserPermanently(self::$user->getId(), \Users_Record_Model::getCurrentUserModel()->getId());
	}
}
