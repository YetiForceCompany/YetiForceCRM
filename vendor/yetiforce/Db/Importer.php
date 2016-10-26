<?php
namespace App\Db;

use App\Db\Importers\Base;

/**
 * Class that imports structure and data to database
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Importer
{

	public $path = 'install/install_schema';
	private $importers = [];

	/**
	 * Load all files for import
	 */
	public function loadFiles()
	{
		$dir = new \DirectoryIterator($this->path);
		foreach ($dir as $fileinfo) {
			if ($fileinfo->getType() !== 'dir' && $fileinfo->getExtension() === 'php') {
				require $fileinfo->getPath() . DIRECTORY_SEPARATOR . $fileinfo->getFilename();
				$className = 'Importers\\' . $fileinfo->getBasename('.php');
				$instance = new $className();
				$instance->scheme();
				$instance->data();
				$this->importers[] = $instance;
			}
		}
	}

	/**
	 * Import database structure
	 */
	public function importScheme()
	{
		foreach ($this->importers as &$importer) {
			$this->addTables($importer);
		}
		foreach ($this->importers as &$importer) {
			$this->addForeignKey($importer);
		}
	}

	/**
	 * Import database rows
	 */
	public function importData()
	{
		foreach ($this->importers as &$importer) {
			$this->addData($importer);
		}
	}

	/**
	 * Creating tables
	 * @param Base $importer
	 */
	public function addTables(Base $importer)
	{
		foreach ($importer->tables as $tableName => $table) {
			$importer->db->createCommand()->createTable(
				$tableName, $table['columns'], $this->getOptions($importer->db->type, $table)
			)->execute();
			if (isset($table['index'])) {
				foreach ($table['index'] as $index) {
					$importer->db->createCommand()->createIndex($index[0], $tableName, $index[1], (isset($index[2]) && $index[2]) ? true : false )->execute();
				}
			}
			if (isset($table['primaryKeys'])) {
				foreach ($table['primaryKeys'] as $primaryKey) {
					$importer->db->createCommand()->addPrimaryKey($primaryKey[0], $tableName, $primaryKey[1])->execute();
				}
			}
		}
	}

	/**
	 * Get additional SQL fragment that will be appended to the generated SQL.
	 * @param string $type
	 * @param array $table
	 * @return string
	 */
	public function getOptions($type, $table)
	{
		$options = null;
		switch ($type) {
			case 'mysql':
				$options = "ENGINE={$table['engine']} DEFAULT CHARSET={$table['charset']}";
				break;
		}
		return $options;
	}

	/**
	 * Creates a SQL command for adding a foreign key constraint to an existing table.
	 * @param Base $importer
	 */
	public function addForeignKey(Base $importer)
	{
		if (!isset($importer->foreignKey)) {
			return;
		}
		foreach ($importer->foreignKey as $key) {
			$importer->db->createCommand()->addForeignKey(
				$key[0], $key[1], $key[2], $key[3], $key[4], $key[5]
			)->execute();
		}
	}

	/**
	 * Creating rows
	 * @param Base $importer
	 */
	public function addData(Base $importer)
	{
		if (!isset($importer->data)) {
			return;
		}
		foreach ($importer->data as $tableName => $table) {
			$keys = $table['columns'];
			foreach ($table['values'] as $values) {
				$importer->db->createCommand()->insert($tableName, array_combine($keys, $values))->execute();
			}
		}
	}
}
