<?php
/**
 * RecordSearch test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * RecordSearch test class.
 */
class RecordSearch extends \Tests\Base
{
	/**
	 * Record search test.
	 */
	public function testSearch()
	{
		$record = \Tests\Base\C_RecordActions::createAccountRecord();
		\App\PrivilegeUpdater::update($record->getId(), $record->getModuleName());

		$recordSearch = new \App\RecordSearch('YetiForce', 'Accounts', 10);
		// $this->logs = $rows = $recordSearch->search();
		// $this->assertNotEmpty($rows);
		// $this->assertArrayHasKey($record->getId(), $rows, 'Record id not found');
		// $row = reset($rows);
		// $this->logs = $row;
		// $this->assertEquals('YetiForce S.A.', $row['searchlabel']);

		// $recordSearch->operator = 'FulltextWord';
		// $this->logs = $rows = $recordSearch->search();
		// $this->assertNotEmpty($rows);
		// $this->assertArrayHasKey($record->getId(), $rows, 'Record id not found');
		// $row = reset($rows);
		// $this->logs = $row;
		// $this->assertEquals('YetiForce S.A.', $row['searchlabel']);
		// $this->assertArrayHasKey('matcher', $row);

		$recordSearch->setMode(\App\RecordSearch::LABEL_MODE);
		$recordSearch->operator = 'FulltextBegin';
		$this->logs = $rows = $recordSearch->search();
		$this->assertNotEmpty($rows);
		$key = array_search($record->getId(), array_column($rows, 'crmid'));
		$this->assertNotFalse($key);
		$this->assertEquals('YetiForce S.A.', $rows[$key]['label'], "Not found '$key' ({$record->getId()}) in" . print_r($rows, true));
	}
}
