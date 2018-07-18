<?php

/**
 * Record Actions test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Entity;

class C_RecordActions extends \Tests\Base
{
	/**
	 * Temporary record object.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected static $record;

	/**
	 * Creating account module record for tests.
	 */
	public static function createAccountRecord()
	{
		if (static::$record) {
			return static::$record;
		} else {
			$record = \Vtiger_Record_Model::getCleanInstance('Accounts');
			$record->set('accountname', 'YetiForce Sp. z o.o.');
			$record->set('legal_form', 'PLL_GENERAL_PARTNERSHIP');
			$record->save();
			static::$record = $record;
			return $record;
		}
	}

	/**
	 * Testing the record creation.
	 */
	public static function testCreateRecord()
	{
		static::assertInternalType('int', static::createAccountRecord()->getId());
	}

	/**
	 * Testing editing permissions.
	 */
	public function testPermission()
	{
		$this->assertTrue(static::createAccountRecord()->isEditable());
		$this->assertTrue(static::createAccountRecord()->isCreateable());
		$this->assertTrue(static::createAccountRecord()->isViewable());
		$this->assertFalse(static::createAccountRecord()->privilegeToActivate());
		$this->assertTrue(static::createAccountRecord()->privilegeToArchive());
		$this->assertTrue(static::createAccountRecord()->privilegeToMoveToTrash());
		$this->assertTrue(static::createAccountRecord()->privilegeToDelete());
	}

	/**
	 * Testing the edit block feature.
	 */
	public function testCheckLockFields()
	{
		$this->assertTrue(static::createAccountRecord()->checkLockFields());
	}

	/**
	 * Testing record editing.
	 */
	public function testEditRecord()
	{
		static::createAccountRecord()->set('accounttype', 'Customer');
		static::createAccountRecord()->save();
		$this->assertTrue((new \App\Db\Query())->from('vtiger_account')->where(['account_type' => 'Customer'])->exists());
	}

	/**
	 * Testing the record label.
	 */
	public function testGetDisplayName()
	{
		$this->assertTrue(static::createAccountRecord()->getDisplayName() === 'YetiForce Sp. z o.o.');
	}

	/**
	 * Testing the change record state.
	 */
	public function testStateRecord()
	{
		static::$record->changeState('Trash');
		$this->assertSame(1, (new \App\Db\Query())->select(['deleted'])->from('vtiger_crmentity')->where(['crmid' => static::createAccountRecord()->getId()])->scalar());
		static::$record->changeState('Active');
		$this->assertSame(0, (new \App\Db\Query())->select(['deleted'])->from('vtiger_crmentity')->where(['crmid' => static::createAccountRecord()->getId()])->scalar());
		static::$record->changeState('Archived');
		$this->assertSame(2, (new \App\Db\Query())->select(['deleted'])->from('vtiger_crmentity')->where(['crmid' => static::createAccountRecord()->getId()])->scalar());
	}
}
