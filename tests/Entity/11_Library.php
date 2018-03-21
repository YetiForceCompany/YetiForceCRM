<?php

/**
 * Library test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Library extends \Tests\Base
{
	/**
	 * Testing library versions.
	 */
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
