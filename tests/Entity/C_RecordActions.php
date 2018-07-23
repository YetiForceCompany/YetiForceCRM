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
		}
		$record = \Vtiger_Record_Model::getCleanInstance('Accounts');
		$record->set('accountname', 'YetiForce Sp. z o.o.');
		$record->set('legal_form', 'PLL_GENERAL_PARTNERSHIP');
		$record->save();
		static::$record = $record;
		return $record;
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
		$this->assertTrue(static::$record->isEditable());
		$this->assertTrue(static::$record->isCreateable());
		$this->assertTrue(static::$record->isViewable());
		$this->assertFalse(static::$record->privilegeToActivate());
		$this->assertTrue(static::$record->privilegeToArchive());
		$this->assertTrue(static::$record->privilegeToMoveToTrash());
		$this->assertTrue(static::$record->privilegeToDelete());
	}

	/**
	 * Testing the edit block feature.
	 */
	public function testCheckLockFields()
	{
		$this->assertTrue(static::$record->checkLockFields());
	}

	/**
	 * Testing record editing.
	 */
	public function testEditRecord()
	{
		static::$record->set('accounttype', 'Customer');
		static::$record->save();
		$this->assertTrue((new \App\Db\Query())->from('vtiger_account')->where(['account_type' => 'Customer'])->exists());
	}

	/**
	 * Testing the record label.
	 */
	public function testGetDisplayName()
	{
		$this->assertTrue(static::$record->getDisplayName() === 'YetiForce Sp. z o.o.');
	}

	/**
	 * Testing the change record state.
	 */
	public function testStateRecord()
	{
		static::$record->changeState('Trash');
		$this->assertSame(1, (new \App\Db\Query())->select(['deleted'])->from('vtiger_crmentity')->where(['crmid' => static::$record->getId()])->scalar());
		static::$record->changeState('Active');
		$this->assertSame(0, (new \App\Db\Query())->select(['deleted'])->from('vtiger_crmentity')->where(['crmid' => static::$record->getId()])->scalar());
		static::$record->changeState('Archived');
		$this->assertSame(2, (new \App\Db\Query())->select(['deleted'])->from('vtiger_crmentity')->where(['crmid' => static::$record->getId()])->scalar());
	}
}
