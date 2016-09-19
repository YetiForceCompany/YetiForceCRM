<?php
/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class TestModule extends TestCase
{

	public function test()
	{

		$testModule = 'TestModule.zip';
		try {
			file_put_contents($testModule, file_get_contents('https://tests.yetiforce.com/' . $_SERVER['YETI_KEY']));
		} catch (Exception $exc) {
			
		}
		if (file_exists($testModule)) {
			$package = new vtlib\Package();
			$package->import($testModule);
		}
	}
}
