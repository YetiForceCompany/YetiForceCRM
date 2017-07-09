<?php

/**
 * Record Actions test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers RecordActions::<public>
 */
class RecordActions extends TestCase
{

	/**
	 * Temporary record object
	 * @var Vtiger_Record_Model
	 */
	static protected $record;

	/**
	 * Testing the record creation
	 */
	public function testCreateRecord()
	{
		$record = Vtiger_Record_Model::getCleanInstance('Accounts');
		$record->set('accountname', 'YetiForce Sp. z o.o.');
		$record->set('assigned_user_id', TESTS_USER_ID);
		$record->set('legal_form', 'PLL_GENERAL_PARTNERSHIP');
		$record->save();
		$this->assertInternalType('int', $record->getId());
		static::$record = $record;
		define('ACCOUNT_ID', $record->getId());
	}

	/**
	 * Testing editing permissions
	 */
	public function testIsEditable()
	{
		$this->assertTrue(static::$record->isEditable());
	}

	/**
	 * Testing watching record permissions
	 */
	public function testIsWatchingRecord()
	{
		$this->assertFalse(static::$record->isWatchingRecord());
	}

	/**
	 * Testing permission to preview
	 */
	public function testIsViewable()
	{
		$this->assertTrue(static::$record->isViewable());
	}

	/**
	 * Testing permissions for the creation view
	 */
	public function testIsCreateable()
	{
		$this->assertTrue(static::$record->isCreateable());
	}

	/**
	 * Testing the edit block feature
	 */
	public function testCheckLockFields()
	{
		$this->assertTrue(static::$record->checkLockFields());
	}

	/**
	 * Testing record editing
	 */
	public function testEditRecord()
	{
		$record = static::$record;
		$record->set('accounttype', 'Customer');
		$record->save();
		$this->assertTrue((new \App\Db\Query())->from('vtiger_account')->where(['account_type' => 'Customer'])->exists());
	}

	/**
	 * Testing the record label
	 */
	public function testGetDisplayName()
	{
		$this->assertTrue(static::$record->getDisplayName() === 'YetiForce Sp. z o.o.');
	}
}
