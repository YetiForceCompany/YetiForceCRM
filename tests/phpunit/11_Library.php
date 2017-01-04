<?php
/**
 * Library test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Library::<public>
 */
class Library extends TestCase
{

	public function testLibraryVersion()
	{

		Settings_ModuleManager_Library_Model::downloadAll();
		$libs = \Settings_ModuleManager_Library_Model::getAll();
		foreach ($libs as $name => &$lib) {
			$appVersion = \App\Version::get($lib['name']);
			if (!file_exists($lib['dir'] . 'version.php')) {
				throw new \Exception('File does not exist: ' . $lib['dir'] . 'version.php');
			}
			$libVersions = require $lib['dir'] . 'version.php';
			$libVersion = $libVersions['version'];
			if ($appVersion !== $libVersion) {
				throw new \Exception("Wrong library version: $name, library version: $libVersion, config version: $appVersion");
			}
		}
	}
}
