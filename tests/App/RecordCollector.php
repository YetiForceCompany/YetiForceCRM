<?php
/**
 * RecordCollector test file.
 *
 * @see App\RecordCollectors\Vies
 * @see App\RecordCollectors\DkCvr
 * @see App\RecordCollectors\FrEnterpriseGouv
 * @see App\RecordCollectors\NoBrregEnhetsregisteret
 * @see App\RecordCollectors\PlKrs
 * @see App\RecordCollectors\UsaEdgarRegistryFromSec
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
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
			// @codeCoverageIgnoreStart
			$this->markTestSkipped($response['error']);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * PlKrs record collector test.
	 *
	 * @return void
	 */
	public function testPlKrs(): void
	{
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\PlKrs', 'Accounts');
		if (!$recordCollector->isActive()) {
			$this->markTestSkipped('No required access to test this functionality');
			return;
		}
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'ncr' => '0000940956',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('additional', $response);
			$this->assertEquals('1180002425', $response['fields']['vat_id']['data'][0]['raw']);
			$this->assertEquals('00816349200000', $response['fields']['registration_number_2']['data'][0]['raw']);
		} else {
			// @codeCoverageIgnoreStart
			$this->markTestSkipped($response['error']);
			// @codeCoverageIgnoreEnd
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
		if (!$recordCollector->isActive()) {
			$this->markTestSkipped('No required access to test this functionality');
			return;
		}
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'country' => 'no',
			'name' => 'test',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('links', $response);
		} else {
			// @codeCoverageIgnoreStart
			$this->markTestSkipped($response['error']);
			// @codeCoverageIgnoreEnd
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
		if (!$recordCollector->isActive()) {
			$this->markTestSkipped('No required access to test this functionality');
			return;
		}
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'vatNumber' => '213305295',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('additional', $response);
		} else {
			// @codeCoverageIgnoreStart
			$this->markTestSkipped($response['error']);
			// @codeCoverageIgnoreEnd
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
		if (!$recordCollector->isActive()) {
			$this->markTestSkipped('No required access to test this functionality');
			return;
		}
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
			// @codeCoverageIgnoreStart
			$this->markTestSkipped($response['error']);
			// @codeCoverageIgnoreEnd
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
		if (!$recordCollector->isActive()) {
			$this->markTestSkipped('No required access to test this functionality');
			return;
		}
		$recordCollector->setRequest(new \App\Request([
			'module' => 'Accounts',
			'cik' => '0001823466',
		], false));
		$response = $recordCollector->search();
		if (empty($response['error'])) {
			$this->assertArrayHasKey('fields', $response);
			$this->assertArrayHasKey('additional', $response);
		} else {
			// @codeCoverageIgnoreStart
			$this->markTestSkipped($response['error']);
			// @codeCoverageIgnoreEnd
		}
	}
}
