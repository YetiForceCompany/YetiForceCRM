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
	 * Detect if is possible to install sample data.
	 *
	 * @codeCoverageIgnore
	 */
	protected function setUp()
	{
		if (\file_exists(static::$testDataPath)) {
			$this->fileUrl = static::$testDataPath;
		} elseif (!empty($_SERVER['YETI_KEY']) && \App\RequestUtil::isNetConnection() && \strpos(\get_headers(static::$testDataUrl . $_SERVER['YETI_KEY'])[0], '200') !== false) {
			$this->fileUrl = static::$testDataUrl . $_SERVER['YETI_KEY'];
		} else {
			$this->markTestSkipped('TestData package not available, no sample data to install.');
		}
	}

	/**
	 * Testing the installation of the sample data module.
	 */
	public function testInstallSampleData()
	{
		try {
			\copy($this->fileUrl, static::$testModuleFile);
			(new \vtlib\Package())->import(static::$testModuleFile);
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
