<?php

/**
 * Init install test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Init;

/**
 * Init install test class.
 *
 * @internal
 * @coversNothing
 */
final class InstallTest extends \Tests\Base
{
	/**
	 * Testing database installation from SQL file.
	 */
	public function testInstall()
	{
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();

		static::assertNotNull($schema->getTableSchema('a_yf_adv_permission'));
		static::assertNotNull($schema->getTableSchema('yetiforce_updates'));
		static::assertTrue(((new \App\Db\Query())->from('vtiger_ws_fieldtype')->count()) > 0);
	}

	/**
	 * Testing library downloads.
	 */
	public function testDownloadLibrary()
	{
		\Settings_ModuleManager_Library_Model::downloadAll();
		foreach (\Settings_ModuleManager_Library_Model::$libraries as $name => $lib) {
			static::assertFileExists($lib['dir'] . 'version.php');
		}
	}
}
