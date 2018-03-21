<?php

/**
 * Library test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class InstalNewDb extends \Tests\Base
{
	/**
	 * Testing database installation from PHP file.
	 */
	public function testInstalDb()
	{
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$db->createCommand()->checkIntegrity(false)->execute();

		foreach ($schema->getTableNames() as $tableName) {
			$db->createCommand()->dropTable($tableName)->execute();
		}

		$schema->refresh();

		$db->createCommand()->checkIntegrity(true)->execute();

		$importer = new \App\Db\Importer();
		$importer->dieOnError = true;
		$importer->loadFiles();
		$importer->importScheme();
		$importer->importData();
		$importer->postImport();
		$importer->logs(false);
		$this->assertNotNull($schema->getTableSchema('a_yf_adv_permission'));
		$this->assertNotNull($schema->getTableSchema('yetiforce_updates'));
		$this->assertTrue(((new \App\Db\Query())->from('vtiger_ws_fieldtype')->count()) > 0);
	}
}
