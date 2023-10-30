<?php
/**
 * The file contains: Utils test class.
 *
 * @package Tests
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * Utils test class.
 */
class Utils extends \Tests\Base
{
	/**
	 * Tests `\App\Utils\Benchmarks` methods.
	 *
	 * @see \App\Utils\Benchmarks
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

	/**
	 * Tests `\App\Utils\Completions` methods.
	 *
	 * @see \App\Utils\Completions
	 *
	 * @return void
	 */
	public function testCompletions(): void
	{
		$this->assertSame('<img src="file.php?module=Documents&action=DownloadFile&record=448&fileid=19&show=true" />', \App\Utils\Completions::decodeCustomTag('<yetiforce type="Documents" crm-id="448" attachment-id="19"></yetiforce>'));
	}
}
