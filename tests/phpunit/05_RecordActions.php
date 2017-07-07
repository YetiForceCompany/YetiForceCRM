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

	static protected $record;

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

	public function testIsEditable()
	{
		$this->assertTrue(static::$record->isEditable());
	}

	public function testIsWatchingRecord()
	{
		$this->assertFalse(static::$record->isWatchingRecord());
	}

	public function testIsViewable()
	{
		$this->assertTrue(static::$record->isViewable());
	}

	public function testIsCreateable()
	{
		$this->assertTrue(static::$record->isCreateable());
	}

	public function testCheckLockFields()
	{
		$this->assertTrue(static::$record->checkLockFields());
	}

	public function testQuickEdit()
	{
		$record = static::$record;
		$record->set('accounttype', 'Customer');
		$record->save();
		$this->assertTrue((new \App\Db\Query())->from('vtiger_account')->where(['account_type' => 'Customer'])->exists());
	}

	public function testGetDisplayName()
	{
		$this->assertTrue(static::$record->getDisplayName() === 'YetiForce Sp. z o.o.');
	}
}
