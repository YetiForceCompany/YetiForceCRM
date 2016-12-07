<?php
/**
 * Library test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers InstalNewDb::<public>
 */
class InstalNewDb extends TestCase
{

	public function testInstalDb()
	{
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$db->createCommand()->checkIntegrity(false)->execute();
		try {
			foreach ($schema->getTableNames() as $tableName) {
				$db->createCommand()->dropTable($tableName)->execute();
			}
		} catch (\Exception $e) {
			var_dump($e->__toString());
		}
		$schema->refresh();
		try {
			foreach ($schema->getTableNames() as $tableName) {
				$db->createCommand()->dropTable($tableName)->execute();
			}
		} catch (\Exception $e) {
			var_dump($e->__toString());
		}
		$db->createCommand()->checkIntegrity(true)->execute();

		$importer = new \App\Db\Importer();
		$importer->dieOnError = true;
		$importer->loadFiles();
		$importer->importScheme();
		$importer->importData();
		$importer->postProcess();

		ob_start();
		$importer->logs();
		file_put_contents('tests/TestInstalDb.txt', ob_get_contents());
		ob_end_clean();
	}
}
