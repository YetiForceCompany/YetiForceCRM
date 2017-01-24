<?php
namespace App\Db;

use App\Db\Importers\Base;
use App\Exceptions;

/**
 * Class that imports structure and data to database
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Importer
{

	public $logs = "\n";
	public $path = 'install/install_schema';
	public $dieOnError = false;
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
				if (method_exists($instance, 'scheme')) {
					$instance->scheme();
				}
				if (method_exists($instance, 'data')) {
					$instance->data();
				}
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
	 * Post Process action
	 */
	public function postProcess()
	{
		foreach ($this->importers as &$importer) {
			$this->addForeignKey($importer);
		}
	}

	/**
	 * Creating tables
	 * @param Base $importer
	 */
	public function addTables(Base $importer)
	{
		$this->logs .= "> start add tables\n";
		foreach ($importer->tables as $tableName => $table) {
			$this->logs .= "  > add table: $tableName ... ";
			try {
				$importer->db->createCommand()->createTable(
					$tableName, $this->getColumns($importer, $table), $this->getOptions($importer, $table)
				)->execute();
				$this->logs .= "done\n";
			} catch (\Exception $e) {
				$this->logs .= "error ({$e->getMessage()}) !!!\n";
				if ($this->dieOnError) {
					throw new Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
				}
			}
			if ($indexes = $this->getIndexes($importer, $table)) {
				foreach ($indexes as $index) {
					$this->logs .= "  > create index: {$index[0]} ... ";
					try {
						$importer->db->createCommand()->createIndex($index[0], $tableName, $index[1], (isset($index[2]) && $index[2]) ? true : false )->execute();
						$this->logs .= "done\n";
					} catch (\Exception $e) {
						$this->logs .= "error ({$e->getMessage()}) !!!\n";
						if ($this->dieOnError) {
							throw new Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
						}
					}
				}
			}
			if (isset($table['primaryKeys'])) {
				foreach ($table['primaryKeys'] as $primaryKey) {
					$this->logs .= "  > add primary key: {$primaryKey[0]} ... ";
					try {
						$importer->db->createCommand()->addPrimaryKey($primaryKey[0], $tableName, $primaryKey[1])->execute();
						$this->logs .= "done\n";
					} catch (\Exception $e) {
						$this->logs .= "error ({$e->getMessage()}) !!!\n";
						if ($this->dieOnError) {
							throw new Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
						}
					}
				}
			}
		}
		$this->logs .= "# end add tables\n";
	}

	/**
	 * Get additional SQL fragment that will be appended to the generated SQL.
	 * @param string $type
	 * @param array $table
	 * @return string
	 */
	public function getOptions(Base $importer, $table)
	{
		$options = null;
		switch ($importer->db->getDriverName()) {
			case 'mysql':
				$options = "ENGINE={$table['engine']} DEFAULT CHARSET={$table['charset']}";
				break;
		}
		return $options;
	}

	/**
	 * Get columns to create
	 * @param Base $importer
	 * @param array $table
	 * @return array
	 */
	public function getColumns(Base $importer, $table)
	{
		$type = $importer->db->getDriverName();
		$columns = $table['columns'];
		if (isset($table['columns_' . $type])) {
			foreach ($table['columns_' . $type] as $column => &$customType) {
				$this->logs .= "\n    > custom column type,  driver: $type, type: $customType";
				$columns[$column] = $customType;
			}
		}
		return $columns;
	}

	/**
	 * Get index to create
	 * @param Base $importer
	 * @param array $table
	 * @return array
	 */
	public function getIndexes(Base $importer, $table)
	{
		if (!isset($table['index'])) {
			return false;
		}
		$type = $importer->db->getDriverName();
		$indexes = $table['index'];
		if (isset($table['index_' . $type])) {
			foreach ($table['index_' . $type] as &$customIndex) {
				foreach ($indexes as $key => &$index) {
					if ($customIndex[0] === $index[0]) {
						$this->logs .= "\n    > custom index,  driver: $type, type: {$customIndex['0']}";
						$indexes[$key] = $customIndex;
					}
				}
			}
		}
		return $indexes;
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
		$this->logs .= "> start add foreign key\n";
		foreach ($importer->foreignKey as &$key) {
			$this->logs .= "  > add: {$key[0]}, {$key[1]} ... ";
			try {
				$importer->db->createCommand()->addForeignKey(
					$key[0], $key[1], $key[2], $key[3], $key[4], $key[5], $key[6]
				)->execute();
				$this->logs .= "done\n";
			} catch (\Exception $e) {
				$this->logs .= "error ({$e->getMessage()}) !!!\n";
				if ($this->dieOnError) {
					throw new Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
				}
			}
		}
		$this->logs .= "# end add foreign key\n";
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
		$this->logs .= "> start add data rows\n";
		foreach ($importer->data as $tableName => &$table) {
			$this->logs .= "  > add data to table: $tableName ... ";
			try {
				$keys = $table['columns'];
				foreach ($table['values'] as $values) {
					$importer->db->createCommand()->insert($tableName, array_combine($keys, $values))->execute();
				}
				$this->logs .= "done\n";
			} catch (\Exception $e) {
				$this->logs .= "error ({$e->getMessage()}) !!!\n";
				if ($this->dieOnError) {
					throw new Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
				}
			}
		}
		$this->logs .= "# end add data rows\n";
		$this->logs .= "> start reset sequence\n";
		foreach ($importer->data as $tableName => &$table) {
			$tableSchema = $importer->db->getTableSchema($tableName);
			$isAutoIncrement = false;
			foreach ($tableSchema->columns as &$column) {
				if ($column->autoIncrement) {
					$isAutoIncrement = true;
					break;
				}
			}
			if ($isAutoIncrement) {
				$this->logs .= "  > reset sequence: $tableName ... ";
				try {
					$importer->db->createCommand()->resetSequence($tableName)->execute();
					$this->logs .= "done\n";
				} catch (\Exception $e) {
					$this->logs .= "error ({$e->getMessage()}) !!!\n";
					if ($this->dieOnError) {
						throw new Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
					}
				}
				if (false && isset($importer->data[$tableName . '_seq'])) {
					$this->logs .= "   > error: redundant table {$tableName}_seq !!!\n";
					if ($this->dieOnError) {
						throw new Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
					}
				}
			}
		}
		$this->logs .= "# end reset sequence\n";
	}

	public function logs($show = true)
	{
		if ($show) {
			echo $this->logs;
		} else {
			file_put_contents('cache/logs/Importer.log', $this->logs);
		}
	}
}
