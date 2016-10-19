<?php
/**
 * Record Actions test class
 * @package YetiForce.Tests
 * @license licenses/License.html
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
		self::$record = $record;
		define('ACCOUNT_ID', $record->getId());
	}

	public function testIsEditable()
	{
		self::$record->isEditable();
	}

	public function testIsWatchingRecord()
	{
		self::$record->isWatchingRecord();
	}

	public function testIsViewable()
	{
		self::$record->isViewable();
	}

	public function testIsCreateable()
	{
		self::$record->isCreateable();
	}

	public function testCheckLockFields()
	{
		self::$record->checkLockFields();
	}

	public function testQuickEdit()
	{
		$record = self::$record;
		$record->set('accounttype', 'Customer');
		$record->set('mode', 'edit');
		$record->save();
	}

	public function testGetDisplayName()
	{
		self::$record->getDisplayName();
	}
}
