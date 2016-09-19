<?php
/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class RecordActions extends TestCase
{

	public function test()
	{
		$record = Vtiger_Record_Model::getCleanInstance('Accounts');
		$record->set('accountname', 'YetiForce Sp. z o.o.');
		$record->set('assigned_user_id', TESTS_USER_ID);
		$record->set('legal_form', 'PLL_GENERAL_PARTNERSHIP');
		$record->save();
		$record->isEditable();
		$record->isWatchingRecord();
		$record->set('accounttype', 'Customer');
		$record->set('mode', 'edit');
		$record->save();
		define('ACCOUNT_ID', $record->getId());
	}
}
