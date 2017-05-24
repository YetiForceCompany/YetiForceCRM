<?php
/**
 * Cron test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers TestModule::<public>
 */
class TestModule extends TestCase
{

	public function testInstall()
	{
		$testModule = 'TestModule.zip';
		try {
			file_put_contents($testModule, file_get_contents('https://tests.yetiforce.com/' . $_SERVER['YETI_KEY']));
		} catch (Exception $exc) {
			
		}
		if (file_exists($testModule)) {
			(new vtlib\Package())->import($testModule);
		}
	}

	public function testSetConfig()
	{
		$db = \App\Db::getInstance();
		$db->createCommand()
			->update('vtiger_cron_task', [
				'sequence' => 0,
				], ['name' => 'TestData'])
			->execute();
	}
}
