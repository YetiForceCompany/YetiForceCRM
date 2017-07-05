<?php
/**
 * Library test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
		foreach ($libs as $name => $lib) {
			$appVersion = \App\Version::get($lib['name']);
			$this->assertTrue(file_exists($lib['dir'] . 'version.php'), 'File does not exist: ' . $lib['dir'] . 'version.php');

			$libVersions = require $lib['dir'] . 'version.php';
			$libVersion = $libVersions['version'];
			$this->assertTrue($appVersion == $libVersion, "Wrong library version: $name, library version: $libVersion, config version: $appVersion");
		}
	}
}
