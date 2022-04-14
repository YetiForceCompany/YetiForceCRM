<?php

/**
 * MultiCompany test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

namespace Tests\Base;

use Vtiger_Record_Model;

class J_MultiCompany extends \Tests\Base
{
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
	 * Creating MultiCompany module record for tests.
	 *
	 * @var bool
	 *
	 * @param mixed $cache
	 *
	 * @return \Vtiger_Record_Model
	 */
	public static function createMultiCompanyRecord($cache = true): \Vtiger_Record_Model
	{
		if (self::$recordMultiCompany && $cache) {
			return self::$recordMultiCompany;
		}
		$recordModel = \Vtiger_Record_Model::getCleanInstance('MultiCompany');
		$recordModel->set('company_name', 'TestMulti sp. z o.o.');
		$recordModel->set('companyid1', '23123214141412');
		$recordModel->set('email1', 'mail@testowy.pl');
		$recordModel->set('assigned_user_id', \App\User::getCurrentUserId());
		$recordModel->save();
		if ($cache) {
			self::$recordMultiCompany = $recordModel;
		}
		return $recordModel;
	}

	/**
	 * Creating new role for test.
	 *
	 * @return \Settings_Roles_Record_Model
	 */
	public static function createRole($cache = true): \Settings_Roles_Record_Model
	{
		$recordModel = new \Settings_Roles_Record_Model();
		$parentRoleId = 'H1';

		$parentRole = \Settings_Roles_Record_Model::getInstanceById($parentRoleId);
		$recordModel->set('changeowner', 1)
			->set('searchunpriv', null)
			->set('listrelatedrecord', 0)
			->set('previewrelatedrecord',0)
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

		if ($cache) {
			self::$role = $recordModel;
		}

		return $recordModel;
	}

	/**
	 * Testing creating MultiCompany record.
	 *
	 * @return void
	 */
	public function testCreateMultiCompanyRecords(): void
	{
		$multiCompany = self::createMultiCompanyRecord(true);
		$this->assertIsNumeric($multiCompany->getId());
		$this->assertInstanceOf(\Vtiger_Record_Model::class, $multiCompany);
	}

	/**
	 * Testing privilege to delete.
	 *
	 * @return void
	 */
	public function testPrivilegeToDelete(): void
	{
		$this->assertTrue(\Vtiger_Record_Model::getInstanceById(self::$recordMultiCompany->getId())->privilegeToDelete());
	}

	/**
	 * Testing creating role.
	 *
	 * @return void
	 */
	public function testCreateRole(): void
	{
		$role = self::createRole();
		$this->assertIsString($role->getId());
		$this->assertInstanceOf(\Settings_Roles_Record_Model::class, $role);
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

		$this->assertFalse(\Vtiger_Record_Model::getInstanceById(self::$recordMultiCompany->getId())->privilegeToDelete());
	}

	/**
	 * Cleaning after tests
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void
	{
		self::$recordMultiCompany->delete();
		self::$role->delete(\Settings_Roles_Record_Model::getInstanceById('H1'));
	}
}
