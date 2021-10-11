<?php
/**
 * RecordSearch test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$recordSearch = new \App\RecordSearch('YetiForce', 'Accounts', 10);
		$this->logs = $rows = $recordSearch->search();
		$this->assertNotEmpty($rows);
		$row = current($rows);
		$this->logs = $row;
		$this->assertEquals('YetiForce Sp. z o.o.', $row['searchlabel']);

		$recordSearch->operator = 'FulltextWord';
		$this->logs = $rows = $recordSearch->search();
		$this->assertNotEmpty($rows);
		$row = current($rows);
		$this->logs = $row;
		$this->assertEquals('YetiForce Sp. z o.o.', $row['searchlabel']);
		$this->assertArrayHasKey('matcher', $row);

		$recordSearch->setMode(\App\RecordSearch::SEARCH_MODE);
		$recordSearch->operator = 'FulltextBegin';
		$this->logs = $rows = $recordSearch->search();
		$this->assertNotEmpty($rows);
		$row = current($rows);
		$this->logs = $row;
		$this->assertEquals('YetiForce Sp. z o.o.', $row['searchlabel']);
	}
}
