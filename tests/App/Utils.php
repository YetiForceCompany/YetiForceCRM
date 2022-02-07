<?php
/**
 * The file contains: Utils test class.
 *
 * @package Tests
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * Utils test class.
 */
class Utils extends \Tests\Base
{
	/**
	 * Testing Benchmarks file.
	 *
	 * @return void
	 */
	public function testBenchmarks(): void
	{
		$benchmarks = \App\Utils\Benchmarks::all();
		$this->assertArrayHasKey('cpu', $benchmarks);
		$this->assertArrayHasKey('ram', $benchmarks);
		$this->assertArrayHasKey('hardDrive', $benchmarks);
		$this->assertArrayHasKey('db', $benchmarks);
	}
}
