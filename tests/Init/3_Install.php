<?php

/**
 * Install test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Install extends \Tests\Base
{
	/**
	 * Testing database installation from SQL file.
	 */
	public function testInstall()
	{
		require_once 'install/models/InitSchema.php';

		$initSchema = new Install_InitSchema_Model();
		$initSchema->initialize();

		$db = \App\Db::getInstance();
		$schema = $db->getSchema();

		$this->assertNotNull($schema->getTableSchema('a_yf_adv_permission'));
		$this->assertNotNull($schema->getTableSchema('yetiforce_updates'));
		$this->assertTrue(((new \App\Db\Query())->from('vtiger_ws_fieldtype')->count()) > 0);
	}

	/**
	 * Testing library downloads.
	 */
	public function testDownloadLibrary()
	{
		Settings_ModuleManager_Library_Model::downloadAll();
		foreach (Settings_ModuleManager_Library_Model::$libraries as $name => $lib) {
			$this->assertTrue(file_exists($lib['dir'] . 'version.php'));
		}
	}
}
