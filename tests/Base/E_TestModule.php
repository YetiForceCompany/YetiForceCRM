<?php
/**
 * Cron test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Base;

/**
 * @codeCoverageIgnore
 */
class E_TestModule extends \Tests\Base
{
	/**
	 * TestData package url prefix.
	 *
	 * @var string
	 */
	public static $testDataUrl = 'https://tests.yetiforce.com/';

	/**
	 * Detect if is possible to install sample data.
	 */
	protected function setUp()
	{
		if (!(\file_exists('./public_html/_private/TestData.zip') || (!empty($_SERVER['YETI_KEY']) && \App\RequestUtil::isNetConnection() && \strpos(\get_headers(static::$testDataUrl . $_SERVER['YETI_KEY'])[0], '200') !== false))) {
			$this->markTestSkipped('TestData package not available, no sample data to install.');
		}
	}

	/**
	 * Testing the installation of the sample data module.
	 */
	public function testInstallSampleData()
	{
		$testModule = 'TestModule.zip';
		try {
			if (\file_exists('./public_html/_private/TestData.zip')) {
				$urlFile = './public_html/_private/TestData.zip';
			} elseif (!empty($_SERVER['YETI_KEY']) && \App\RequestUtil::isNetConnection() && \strpos(\get_headers(static::$testDataUrl . $_SERVER['YETI_KEY'])[0], '200') !== false) {
				$urlFile = static::$testDataUrl . $_SERVER['YETI_KEY'];
			} else {
				return;
			}
			\copy($urlFile, $testModule);
			(new \vtlib\Package())->import($testModule);
			$this->assertTrue((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'TestData'])->exists());
			$db = \App\Db::getInstance();
			$db->createCommand()
				->update('vtiger_cron_task', [
					'sequence' => 0,
				], ['name' => 'TestData'])
				->execute();
		} catch (\Exception $exc) {
		}
	}
}
