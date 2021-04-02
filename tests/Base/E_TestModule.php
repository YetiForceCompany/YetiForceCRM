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
	 * TestData package at yetiforce.com url prefix.
	 *
	 * @var string
	 */
	public static $testDataUrl = 'https://tests.yetiforce.com/';

	/**
	 * @var string TestData package path in _private directory
	 */
	public static $testDataPath = './public_html/_private/TestData.zip';

	/**
	 * @var string Test module package file name
	 */
	public static $testModuleFile = 'TestModule.zip';

	/**
	 * @var string File url
	 */
	public $fileUrl = '';

	/**
	 * @var bool|string Skip test flag and message
	 */
	public static $skipTest = false;

	/**
	 * Detect if is possible to install sample data.
	 *
	 * @codeCoverageIgnore
	 */
	protected function setUp(): void
	{
		if (\file_exists(static::$testDataPath)) {
			$this->fileUrl = static::$testDataPath;
		} elseif (!empty($_SERVER['YETI_TEST_MODULE_KEY'])) {
			if (\App\RequestUtil::isNetConnection()) {
				$path = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . static::$testModuleFile;
				try {
					(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', static::$testDataUrl . $_SERVER['YETI_TEST_MODULE_KEY'], ['sink' => $path]);
				} catch (\Exception $e) {
				}
				if (file_exists($path)) {
					$this->fileUrl = $path;
				} else {
					static::$skipTest = 'TestData package not available - bad response from remote server, no sample data to install.';
				}
			} else {
				static::$skipTest = 'TestData package not available - no internet connection, no sample data to install.';
			}
		} else {
			static::$skipTest = 'TestData package not available, no sample data to install.';
		}
	}

	/**
	 * Testing the installation of the sample data module.
	 */
	public function testInstallSampleData(): void
	{
		if (static::$skipTest) {
			$this->markTestSkipped(static::$skipTest);
			return;
		}
		try {
			\copy($this->fileUrl, static::$testModuleFile);
			(new \vtlib\Package())->import(static::$testModuleFile);
			$this->assertTrue((new \App\Db\Query())->from('vtiger_tab')->where(['name' => 'TestData'])->exists(), 'TestData instalation failed.');
			$db = \App\Db::getInstance();
			$db->createCommand()
				->update('vtiger_cron_task', [
					'sequence' => 0,
				], ['name' => 'TestData'])
				->execute();
		} catch (\Exception $exc) {
			echo 'TestData instalation failed' . PHP_EOL . $exc->__toString();
		}
	}
}
