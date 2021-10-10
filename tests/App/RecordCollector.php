<?php
/**
 * RecordCollector test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * RecordCollector test class.
 */
class RecordCollector extends \Tests\Base
{
	/**
	 *  Vies record collector test.
	 */
	public function testVies()
	{
		$request = new \App\Request([], false);
		$request->set('vatNumber', '1180002425');
		$request->set('countryCode', 'PL');

		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\Vies', 'Accounts');
		$recordCollector->setRequest($request);
		$response = $recordCollector->search();
		$this->assertArrayHasKey('fields', $response, 'Data from VIES not downloaded, ' . ( $response['error'] ?? '-'));
		$this->assertEquals('PL1180002425', $response['fields']['Vat ID'] ?? 'No value');
	}
}
