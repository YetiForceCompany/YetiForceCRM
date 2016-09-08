<?php

/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class RecordActions extends TestCase {

    public function test() {
	$rekord = Vtiger_Record_Model::getCleanInstance('Accounts');
	$rekord->set('accountname', 'YetiForce Sp. z o.o.');
	$rekord->set('assigned_user_id', TESTS_USER_ID);
	$rekord->set('legal_form', 'PLL_GENERAL_PARTNERSHIP');
	$rekord->save();
	$rekord->isEditable();
	$rekord->isWatchingRecord();
	$rekord->set('accounttype', 'Customer');
	$rekord->set('mode', 'edit');
	$rekord->save();
	$rekord->delete();
    }

}
