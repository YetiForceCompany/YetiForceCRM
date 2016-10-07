<?php
/**
 * Library test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Permissions::<public>
 */
class Library extends TestCase
{

	public function testVersion()
	{
		$libs = \Settings_ModuleManager_Library_Model::getAll();
		foreach ($libs as $name => &$lib) {
			$appVersion = \App\Version::get($lib['name']);
			$libVersions = require $lib['dir'] . 'version.php';
			$libVersion = $libVersions['version'];
			if ($appVersion !== $libVersion) {
				trigger_error('Wrong library version: ' . $name, E_USER_ERROR);
			}
		}
	}
}
