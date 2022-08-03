<?php
/**
 * RecordCollector test file.
 *
 * @see App\RecordCollectors\Vies
 * @see App\RecordCollectors\DkCvr
 * @see App\RecordCollectors\FrEnterpriseGouv
 * @see App\RecordCollectors\NoBrregEnhetsregisteret
 * @see App\RecordCollectors\PlNcr
 * @see App\RecordCollectors\UsaEdgarRegistryFromSec
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
	 * Vies record collector test.
	 *
	 * @return void
	 */
	public function testVies(): void
	{
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\Vies', 'Accounts');
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'vatNumber' => '1180002425',
			'countryCode' => 'PL',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('LBL_REQUEST_ID', $response['fields']);
			$this->assertEquals('PL1180002425', $response['fields']['Vat ID'] ?? 'No value');
		} else {
			$this->markTestSkipped($response['error']);
		}
	}

	/**
	 * PlNcr record collector test.
	 *
	 * @return void
	 */
	public function testPlNcr(): void
	{
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\PlNcr', 'Accounts');
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'ncr' => '0000940956',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('links', $response);
			$this->assertArrayHasKey('additional', $response);
			$this->assertEquals('1180002425', $response['fields']['vat_id']['data'][0]['raw']);
			$this->assertEquals('00816349200000', $response['fields']['registration_number_2']['data'][0]['raw']);
		} else {
			$this->markTestSkipped($response['error']);
		}
	}

	/**
	 * DkCvr record collector test.
	 *
	 * @return void
	 */
	public function testDkCvr(): void
	{
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\DkCvr', 'Accounts');
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'country' => 'no',
			'vatNumber' => '213305295',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('links', $response);
		} else {
			$this->markTestSkipped($response['error']);
		}
	}

	/**
	 * FrEnterpriseGouv record collector test.
	 *
	 * @return void
	 */
	public function testFrEnterpriseGouv(): void
	{
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\FrEnterpriseGouv', 'Accounts');
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'vatNumber' => '213305295',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('links', $response);
			$this->assertArrayHasKey('additional', $response);
		} else {
			$this->markTestSkipped($response['error']);
		}
	}

	/**
	 * NoBrregEnhetsregisteret record collector test.
	 *
	 * @return void
	 */
	public function testNoBrregEnhetsregisteret(): void
	{
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\NoBrregEnhetsregisteret', 'Accounts');
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'vatNumber' => '923486178',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('links', $response);
			$this->assertArrayHasKey('additional', $response);
		} else {
			$this->markTestSkipped($response['error']);
		}
	}

	/**
	 * UsaEdgarRegistryFromSec record collector test.
	 *
	 * @return void
	 */
	public function testUsaEdgarRegistryFromSec(): void
	{
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\UsaEdgarRegistryFromSec', 'Accounts');
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'cik' => '0001823466',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('links', $response);
			$this->assertArrayHasKey('additional', $response);
		} else {
			$this->markTestSkipped($response['error']);
		}
	}
}
