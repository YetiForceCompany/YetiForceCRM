<?php
/**
 * RecordCollector test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\Vies', 'Accounts');
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'vatNumber' => '1180002425',
			'countryCode' => 'PL',
		], false));
		$response = $recordCollector->search();

		$this->assertArrayHasKey('fields', $response, 'Data from VIES not downloaded, ' . ($response['error'] ?? '-'));
		$this->assertEquals('PL1180002425', $response['fields']['Vat ID'] ?? 'No value');
	}
}
