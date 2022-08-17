<?php
/**
 * File that imports structure and data to database.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Db;

use App\Db\Importers\Base;

/**
 * Class that imports structure and data to database.
 */
class Importer
{
	/**
	 * End of line character.
	 *
	 * @var string
	 */
	public $logs;
	/**
	 * Start time.
	 *
	 * @var string|float
	 */
	private $startTime;
	/**
	 * Path to the directory with files to import.
	 *
	 * @var string
	 */
	public $path = 'install/install_schema';

	/**
	 * Stop import if an error occurs.
	 *
	 * @var bool
	 */
	public $dieOnError = false;

	/**
	 * Check redundant tables.
	 *
	 * @var bool
	 */
	public $redundantTables = false;

	/**
	 * Array with objects to import.
	 *
	 * @var Base[]
	 */
	private $importers = [];

	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->logs = '-------------   ' . date('Y-m-d H:i:s') . "   -------------\n";
		$this->startTime = microtime(true);
	}

	/**
	 * Load all files for import.
	 *
	 * @param bool|string $path
	 */
	public function loadFiles($path = false)
	{
		$dir = new \DirectoryIterator($path ?: $this->path);
		foreach ($dir as $fileinfo) {
			if ('dir' !== $fileinfo->getType() && 'php' === $fileinfo->getExtension()) {
				require $fileinfo->getPath() . \DIRECTORY_SEPARATOR . $fileinfo->getFilename();
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
	 * Refresh db schema.
	 */
	public function refreshSchema()
	{
		\App\Db::getInstance()->getSchema()->getTableSchemas('', true);
	}

	/**
	 * Show or save logs.
	 *
	 * @param bool $show
	 */
	public function logs($show = true)
	{
		$time = round((microtime(true) - $this->startTime) / 60, 2);
		if ($show) {
			echo $this->logs . '---------  ' . date('Y-m-d H:i:s') . "  ($time min)  -------------\n";
		} else {
			file_put_contents('cache/logs/Importer.log', $this->logs . '-------------  ' . date('Y-m-d H:i:s') . " ($time min)   -------------\n", LOCK_EX);
		}
	}

	/**
	 * Import database structure.
	 */
	public function importScheme()
	{
		foreach ($this->importers as &$importer) {
			$this->addTables($importer);
		}
	}

	/**
	 * Import database rows.
	 */
	public function importData()
	{
		foreach ($this->importers as &$importer) {
			$this->addData($importer);
		}
	}

	/**
	 * Post Process action.
	 */
	public function postImport()
	{
		foreach ($this->importers as &$importer) {
			$this->addForeignKey($importer);
		}
	}

	/**
	 * Update db scheme.
	 */
	public function updateScheme()
	{
		foreach ($this->importers as &$importer) {
			$this->updateTables($importer);
			$this->drop($importer);
		}
	}

	/**
	 * Post Process action.
	 */
	public function postUpdate()
	{
		foreach ($this->importers as &$importer) {
			$this->updateForeignKey($importer);
		}
	}

	/**
	 * Creating tables.
	 *
	 * @param Base $importer
	 */
	public function addTables(Base $importer)
	{
		$this->logs .= "> start add tables ({$importer->dbType})\n";
		$startMain = microtime(true);
		foreach ($importer->tables as $tableName => $table) {
			$this->logs .= "  > add table: $tableName ... ";
			$start = microtime(true);
			try {
				$importer->db->createCommand()->createTable($tableName, $this->getColumns($importer, $table), $this->getOptions($importer, $table))->execute();
				$time = round((microtime(true) - $start), 1);
				$this->logs .= "done    ({$time}s)\n";
			} catch (\Throwable $e) {
				$time = round((microtime(true) - $start), 1);
				$this->logs .= "    ({$time}s) | Error(1) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
				if ($this->dieOnError) {
					throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
				}
			}
			if ($indexes = $this->getIndexes($importer, $table)) {
				foreach ($indexes as $index) {
					$this->logs .= "  > create index: {$index[0]} ... ";
					$start = microtime(true);
					try {
						$importer->db->createCommand()->createIndex($index[0], $tableName, $index[1], (isset($index[2]) && $index[2]) ? true : false)->execute();
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "done    ({$time}s)\n";
					} catch (\Throwable $e) {
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "    ({$time}s) | Error(2) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
						if ($this->dieOnError) {
							throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
						}
					}
				}
			}
			if (isset($table['primaryKeys'])) {
				foreach ($table['primaryKeys'] as $primaryKey) {
					$this->logs .= "  > add primary key: {$primaryKey[0]} ... ";
					$start = microtime(true);
					try {
						$importer->db->createCommand()->addPrimaryKey($primaryKey[0], $tableName, $primaryKey[1])->execute();
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "done    ({$time}s)\n";
					} catch (\Throwable $e) {
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "    ({$time}s) | Error(3) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
						if ($this->dieOnError) {
							throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
						}
					}
				}
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end add tables ($time min)\n";
	}

	/**
	 * Get additional SQL fragment that will be appended to the generated SQL.
	 *
	 * @param Base  $importer
	 * @param array $table
	 *
	 * @return string
	 */
	public function getOptions(Base $importer, $table)
	{
		$options = null;
		if ('mysql' === $importer->db->getDriverName()) {
			$options = "ENGINE={$table['engine']} DEFAULT CHARSET={$table['charset']}";
			if (isset($table['collate'])) {
				$options .= " COLLATE={$table['collate']}";
			}
		}
		return $options;
	}

	/**
	 * Get columns to create.
	 *
	 * @param Base  $importer
	 * @param array $table
	 *
	 * @return array
	 */
	public function getColumns(Base $importer, $table)
	{
		if (empty($table['columns'])) {
			return [];
		}
		$type = $importer->db->getDriverName();
		$columns = $table['columns'];
		if (isset($table['columns_' . $type])) {
			foreach ($table['columns_' . $type] as $column => $customType) {
				$columns[$column] = $customType;
			}
		}
		return $columns;
	}

	/**
	 * Get index to create.
	 *
	 * @param Base  $importer
	 * @param array $table
	 *
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
			foreach ($table['index_' . $type] as $customIndex) {
				foreach ($indexes as $key => $index) {
					if ($customIndex[0] === $index[0]) {
						$this->logs .= "    > custom index, driver: $type, type: {$customIndex['0']} \n";
						$indexes[$key] = $customIndex;
					}
				}
			}
		}
		return $indexes;
	}

	/**
	 * Creates a SQL command for adding a foreign key constraint to an existing table.
	 *
	 * @param Base $importer
	 */
	public function addForeignKey(Base $importer)
	{
		if (!isset($importer->foreignKey)) {
			return;
		}
		$this->logs .= "> start add foreign key ({$importer->dbType})\n";
		$startMain = microtime(true);
		foreach ($importer->foreignKey as $key) {
			$this->logs .= "  > add: {$key[0]}, {$key[1]} ... ";
			$start = microtime(true);
			try {
				$importer->db->createCommand()->addForeignKey($key[0], $key[1], $key[2], $key[3], $key[4], $key[5] ?? null, $key[6] ?? null)->execute();
				$time = round((microtime(true) - $start), 1);
				$this->logs .= "done    ({$time}s)\n";
			} catch (\Throwable $e) {
				$time = round((microtime(true) - $start), 1);
				$this->logs .= "    ({$time}s) | Error(4) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
				if ($this->dieOnError) {
					throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
				}
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end add foreign key ($time min)\n";
	}

	/**
	 * Creating rows.
	 *
	 * @param Base $importer
	 */
	public function addData(Base $importer)
	{
		if (!isset($importer->data)) {
			return;
		}
		$this->logs .= "> start add data rows ({$importer->dbType})\n";
		$startMain = microtime(true);
		foreach ($importer->data as $tableName => $table) {
			$this->logs .= "  > add data to table: $tableName ... ";
			try {
				$keys = $table['columns'];
				if (\is_array($table['values']) && isset($table['values'][0])) {
					if ((new \App\Db\Query())->from($tableName)->where(array_combine($keys, $table['values'][0]))->exists($importer->db)) {
						$this->logs .= "| Info: skipped because it exist first row\n";
					} else {
						$start = microtime(true);
						foreach ($table['values'] as $values) {
							$importer->db->createCommand()->insert($tableName, array_combine($keys, $values))->execute();
						}
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "done    ({$time}s)\n";
					}
				} else {
					$this->logs .= "| Error: No values\n";
				}
			} catch (\Throwable $e) {
				$this->logs .= " | Error(5) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
				if ($this->dieOnError) {
					throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
				}
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end add data rows ($time min)\n";
		$this->logs .= "> start reset sequence\n";
		$startMain = microtime(true);
		foreach ($importer->data as $tableName => $table) {
			$tableSchema = $importer->db->getTableSchema($tableName);
			$isAutoIncrement = false;
			foreach ($tableSchema->columns as $column) {
				if ($column->autoIncrement) {
					$isAutoIncrement = true;
					break;
				}
			}
			if ($isAutoIncrement) {
				$this->logs .= "  > reset sequence: $tableName ... ";
				$start = microtime(true);
				try {
					$importer->db->createCommand()->resetSequence($tableName)->execute();
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "done    ({$time}s)\n";
				} catch (\Throwable $e) {
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "    ({$time}s) | Error(6) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
					if ($this->dieOnError) {
						throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
					}
				}
				if ($this->redundantTables && isset($importer->data[$tableName . '_seq'])) {
					$this->logs .= "   > Error: redundant table {$tableName}_seq !!!\n";
					if ($this->dieOnError) {
						throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
					}
				}
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end reset sequence ($time min)\n";
	}

	/**
	 * Rename tables.
	 *
	 * $tables = [
	 *        ['oldName', 'newName']
	 *        ['u_#__mail_address_boock', 'u_#__mail_address_book']
	 * ];
	 *
	 * @param array $tables
	 */
	public function renameTables($tables)
	{
		$this->logs .= "> start rename tables\n";
		$startMain = microtime(true);
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($tables as $table) {
			$this->logs .= "  > rename table, {$table[0]} ... ";
			if ($db->isTableExists($table[0])) {
				$start = microtime(true);
				try {
					$dbCommand->renameTable($table[0], $table[1])->execute();
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "done    ({$time}s)\n";
				} catch (\Throwable $e) {
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "    ({$time}s) | Error(11) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
				}
			} elseif ($db->isTableExists($table[1])) {
				$this->logs .= " | Info - table {$table[1]} is exists\n";
			} else {
				$this->logs .= " | Error - table does not exist\n";
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end rename tables ($time min)\n";
	}

	/**
	 * Drop table.
	 *
	 * @param array|string $tables
	 */
	public function dropTable($tables)
	{
		$this->logs .= "> start drop tables\n";
		$startMain = microtime(true);
		$db = \App\Db::getInstance();
		if (\is_string($tables)) {
			$tables = [$tables];
		}
		foreach ($tables as $tableName) {
			$this->logs .= "  > drop table, {$tableName} ... ";
			if ($db->isTableExists($tableName)) {
				$start = microtime(true);
				try {
					$db->createCommand()->dropTable($tableName)->execute();
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "done    ({$time}s)\n";
				} catch (\Throwable $e) {
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "    ({$time}s) | Error(12) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
				}
			} else {
				$this->logs .= " | Info - table does not exist\n";
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end drop tables ($time min)\n";
	}

	/**
	 * Drop indexes.
	 *
	 * @param array $tables [$table=>[$index,...],...]
	 */
	public function dropIndexes(array $tables)
	{
		$this->logs .= "> start drop indexes\n";
		$startMain = microtime(true);
		$db = \App\Db::getInstance();
		foreach ($tables as $tableName => $indexes) {
			$dbIndexes = $db->getTableKeys($tableName);
			foreach ($indexes as $index) {
				$this->logs .= "  > drop index, {$tableName}:{$index} ... ";
				if (isset($dbIndexes[$index])) {
					$start = microtime(true);
					try {
						$db->createCommand()->dropIndex($index, $tableName)->execute();
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "done    ({$time}s)\n";
					} catch (\Throwable $e) {
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "    ({$time}s) | Error(12) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
					}
				} else {
					$this->logs .= " | Info - index not exists\n";
				}
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end drop indexes ($time min)\n";
	}

	/**
	 * Drop foreign keys.
	 *
	 * @param array $foreignKeys [$foreignKey=>table,...]
	 */
	public function dropForeignKeys(array $foreignKeys)
	{
		$this->logs .= "> start drop foreign keys\n";
		$startMain = microtime(true);
		$db = \App\Db::getInstance();
		foreach ($foreignKeys as $keyName => $tableName) {
			$this->logs .= "  > drop foreign key, {$tableName}:{$keyName} ... ";
			$tableSchema = $db->getTableSchema($tableName, true);
			if ($tableSchema) {
				$keyName = str_replace('#__', $db->tablePrefix, $keyName);
				if (isset($tableSchema->foreignKeys[$keyName])) {
					$start = microtime(true);
					try {
						$db->createCommand()->dropForeignKey($keyName, $tableName)->execute();
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "done    ({$time}s)\n";
					} catch (\Throwable $e) {
						$time = round((microtime(true) - $start), 1);
						$this->logs .= "    ({$time}s) | Error [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
					}
				} else {
					$this->logs .= " | Info - foreign key not exists\n";
				}
			} else {
				$this->logs .= " | Error - table does not exists\n";
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end drop foreign keys ($time min)\n";
	}

	/**
	 * Rename columns.
	 *
	 * $columns = [
	 *        ['TableName', 'oldName', 'newName'],
	 *        ['vtiger_smsnotifier', 'status', 'smsnotifier_status'],
	 * ];
	 *
	 * @param array $columns
	 */
	public function renameColumns($columns)
	{
		$this->logs .= "> start rename columns\n";
		$startMain = microtime(true);
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$schema = $db->getSchema();
		foreach ($columns as $column) {
			$tableSchema = $schema->getTableSchema($column[0]);
			$this->logs .= "  > rename column: {$column[0]}:{$column[1]} ... ";
			if ($tableSchema && isset($tableSchema->columns[$column[1]]) && !isset($tableSchema->columns[$column[2]])) {
				$start = microtime(true);
				try {
					$dbCommand->renameColumn($column[0], $column[1], $column[2])->execute();
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "done    ({$time}s)\n";
				} catch (\Throwable $e) {
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "    ({$time}s) | Error(13) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
				}
			} else {
				$this->logs .= " | Warning - table or column does not exists\n";
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end rename columns ($time min)\n";
	}

	/**
	 * Drop tables and columns.
	 *
	 * @param Base $importer
	 */
	public function drop(Base $importer)
	{
		if (isset($importer->dropTables)) {
			$this->dropTable($importer->dropTables);
		}
		if (isset($importer->dropColumns)) {
			$this->dropColumns($importer->dropColumns);
		}
		if (isset($importer->dropIndexes)) {
			$this->dropIndexes($importer->dropIndexes);
		}
	}

	/**
	 * Drop columns.
	 *
	 * $columns = [
	 *        ['TableName', 'columnName'],
	 *        ['vtiger_smsnotifier', 'status'],
	 * ];
	 *
	 * @param array $columns
	 */
	public function dropColumns($columns)
	{
		$this->logs .= "> start drop columns\n";
		$startMain = microtime(true);
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$schema = $db->getSchema();
		foreach ($columns as $column) {
			$tableSchema = $schema->getTableSchema($column[0]);
			$this->logs .= "  > drop column: {$column[0]}:{$column[1]} ... ";
			if ($tableSchema && isset($tableSchema->columns[$column[1]])) {
				$start = microtime(true);
				try {
					$dbCommand->dropColumn($column[0], $column[1])->execute();
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "done    ({$time}s)\n";
				} catch (\Throwable $e) {
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "    ({$time}s) | Error(14) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
				}
			} else {
				$this->logs .= " | Info - table or column does not exist\n";
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end drop columns ($time min)\n";
	}

	/**
	 * Update tables structure.
	 *
	 * @param Base $importer
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function updateTables(Base $importer)
	{
		$this->logs .= "> start update tables ({$importer->dbType})\n";
		$startMain = microtime(true);
		$schema = $importer->db->getSchema();
		$queryBuilder = $schema->getQueryBuilder();
		$dbCommand = $importer->db->createCommand();
		foreach ($importer->tables as $tableName => $table) {
			try {
				if (!$importer->db->isTableExists($tableName)) {
					$this->logs .= "  > add table: $tableName ... ";
					$start = microtime(true);
					$dbCommand->createTable($tableName, $this->getColumns($importer, $table), $this->getOptions($importer, $table))->execute();
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "done    ({$time}s)\n";
				} else {
					$tableSchema = $schema->getTableSchema($tableName);
					foreach ($this->getColumns($importer, $table) as $columnName => $column) {
						$renameFrom = $mode = null;
						if (\is_array($column)) {
							$renameFrom = $column['renameFrom'] ?? '';
							$mode = $column['mode'] ?? $mode; // 0,null - create/update, 1 - update only
							$column = $column['type'] ?? '';
						}
						$columnExists = isset($tableSchema->columns[$columnName]);
						if ($renameFrom && !$columnExists && isset($tableSchema->columns[$renameFrom])) {
							$this->logs .= "  > rename column: {$tableName}:{$renameFrom} -> {$columnName}... ";
							$start = microtime(true);
							$dbCommand->renameColumn($tableName, $renameFrom, $columnName)->execute();
							$time = round((microtime(true) - $start), 1);
							$this->logs .= "done    ({$time}s)\n";
							$tableSchema = $schema->getTableSchema($tableName, true);
							$columnExists = isset($tableSchema->columns[$columnName]);
						}elseif (!$columnExists && 1 !== $mode) {
							$this->logs .= "  > add column: $tableName:$columnName ... ";
							$start = microtime(true);
							$dbCommand->addColumn($tableName, $columnName, $column)->execute();
							$time = round((microtime(true) - $start), 1);
							$this->logs .= "done    ({$time}s)\n";
						}
						if ($columnExists && $column instanceof \yii\db\ColumnSchemaBuilder && $this->compareColumns($queryBuilder, $tableSchema->columns[$columnName], $column)) {
							$primaryKey = false;
							if ($column instanceof \yii\db\ColumnSchemaBuilder && (\in_array($column->get('type'), ['upk', 'pk', 'ubigpk', 'bigpk']))) {
								$primaryKey = true;
								$column->set('type', \in_array($column->get('type'), ['ubigpk', 'bigpk']) ? \yii\db\Schema::TYPE_BIGINT : \yii\db\Schema::TYPE_INTEGER);
							}
							if ($tableSchema->foreignKeys) {
								foreach ($tableSchema->foreignKeys as $keyName => $value) {
									if (isset($value[$columnName])) {
										$this->logs .= "  > foreign key must be removed and added in postUpdate: $tableName:$columnName <> {$value[0]}:{$value[$columnName]} FK:{$keyName}\n";
										$importer->foreignKey[] = [$keyName, $tableName, $columnName, $value[0], $value[$columnName], 'CASCADE', null];
										$dbCommand->dropForeignKey($keyName, $tableName)->execute();
									}
								}
							}
							foreach ($schema->findForeignKeyToColumn($tableName, $columnName) as $sourceTableName => $fks) {
								foreach ($fks as $keyName => $fk) {
									$this->logs .= "  > foreign key must be removed and added in postUpdate: $tableName:$columnName <> $sourceTableName:{$fk['sourceColumn']} FK:{$keyName}\n";
									$importer->foreignKey[] = [$keyName, $sourceTableName, $fk['sourceColumn'], $tableName, $columnName, 'CASCADE', null];
									$dbCommand->dropForeignKey($keyName, $sourceTableName)->execute();
								}
							}
							$this->logs .= "  > alter column: $tableName:$columnName ... ";
							$start = microtime(true);
							$dbCommand->alterColumn($tableName, $columnName, $column)->execute();
							$time = round((microtime(true) - $start), 1);
							$this->logs .= "done    ({$time}s)\n";
							if ($primaryKey) {
								if (!isset($table['primaryKeys'])) {
									$table['primaryKeys'] = [];
								}
								$table['primaryKeys'][] = [$tableSchema->fullName . '_pk', [$columnName]];
							}
						} elseif (!($column instanceof \yii\db\ColumnSchemaBuilder)) {
							$this->logs .= "  > Warning: column ({$tableName}:{$columnName}) is not verified\n";
						}
					}
				}
			} catch (\Throwable $e) {
				$this->logs .= " | Error(7) {$tableName} [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
				if ($this->dieOnError) {
					throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
				}
			}
			if ($indexes = $this->getIndexes($importer, $table)) {
				$dbIndexes = $importer->db->getTableKeys($tableName);
				foreach ($indexes as $index) {
					try {
						if (isset($dbIndexes[$index[0]])) {
							$update = false;
							if (\is_string($index[1]) ? !isset($dbIndexes[$index[0]][$index[1]]) : array_diff($index[1], array_keys($dbIndexes[$index[0]]))) {
								$update = true;
							} else {
								foreach ($dbIndexes[$index[0]] as $dbIndex) {
									if (empty($index[2]) !== empty($dbIndex['unique'])) {
										$update = true;
									}
								}
							}
							if ($update) {
								$this->logs .= "  > update index: {$index[0]} ... ";
								$start = microtime(true);
								$dbCommand->dropIndex($index[0], $tableName)->execute();
								$dbCommand->createIndex($index[0], $tableName, $index[1], !empty($index[2]))->execute();
								$time = round((microtime(true) - $start), 1);
								$this->logs .= "done    ({$time}s)\n";
							}
						} else {
							$this->logs .= "  > create index: {$index[0]} ... ";
							$start = microtime(true);
							$dbCommand->createIndex($index[0], $tableName, $index[1], !empty($index[2]))->execute();
							$time = round((microtime(true) - $start), 1);
							$this->logs .= "done    ({$time}s)\n";
						}
					} catch (\Throwable $e) {
						$this->logs .= " | Error(8) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
						if ($this->dieOnError) {
							throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
						}
					}
				}
			}
			if (isset($table['primaryKeys'])) {
				$dbPrimaryKeys = $importer->db->getPrimaryKey($tableName);
				foreach ($table['primaryKeys'] as $primaryKey) {
					$status = true;
					foreach ($dbPrimaryKeys as $dbPrimaryKey) {
						if (\is_string($primaryKey[1]) ? !(1 !== \count($dbPrimaryKey) && $primaryKey[1] !== $dbPrimaryKey[0]) : !array_diff($primaryKey[1], $dbPrimaryKey)) {
							$status = false;
						}
					}
					if ($status) {
						$this->logs .= "  > update primary key: {$primaryKey[0]} , table: $tableName , column: " . (\is_array($primaryKey[1]) ? implode(',', $primaryKey[1]) : $primaryKey[1]) . ' ... ';
						$start = microtime(true);
						try {
							if (isset($dbPrimaryKeys[$primaryKey[0]])) {
								$dbCommand->dropPrimaryKey($primaryKey[0], $tableName)->execute();
							} elseif ($dbPrimaryKeys) {
								$dbCommand->dropPrimaryKey(key($dbPrimaryKeys), $tableName)->execute();
							}
							$dbCommand->addPrimaryKey($primaryKey[0], $tableName, $primaryKey[1])->execute();
							$time = round((microtime(true) - $start), 1);
							$this->logs .= "done    ({$time}s)\n";
						} catch (\Throwable $e) {
							$time = round((microtime(true) - $start), 1);
							$this->logs .= "    ({$time}s) | Error(10) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
							if ($this->dieOnError) {
								throw new \App\Exceptions\AppException('Importer error: ' . $e->getMessage(), (int) $e->getCode(), $e);
							}
						}
					}
				}
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end update tables    ({$time}s)\n";
	}

	/**
	 * Compare two columns if they are identical.
	 *
	 * @param \yii\db\QueryBuilder        $queryBuilder
	 * @param \yii\db\ColumnSchema        $baseColumn
	 * @param \yii\db\ColumnSchemaBuilder $targetColumn
	 *
	 * @return bool
	 */
	protected function compareColumns(\yii\db\QueryBuilder $queryBuilder, \yii\db\ColumnSchema $baseColumn, \yii\db\ColumnSchemaBuilder $targetColumn)
	{
		return strtok($baseColumn->dbType, ' ') !== strtok($queryBuilder->getColumnType($targetColumn), ' ')
		|| ($baseColumn->allowNull !== (null === $targetColumn->isNotNull))
		|| ($baseColumn->defaultValue !== $targetColumn->default)
		|| ($baseColumn->unsigned !== $targetColumn->isUnsigned)
		|| ($baseColumn->autoIncrement !== $targetColumn->autoIncrement);
	}

	/**
	 * Update a foreign key constraint to an existing table.
	 *
	 * @param Base $importer
	 */
	public function updateForeignKey(Base $importer)
	{
		if (!isset($importer->foreignKey)) {
			return;
		}
		$this->logs .= "> start update foreign key ({$importer->dbType})\n";
		$startMain = microtime(true);
		$dbCommand = $importer->db->createCommand();
		$schema = $importer->db->getSchema();
		foreach ($importer->foreignKey as $key) {
			$add = true;
			$keyName = $importer->db->quoteSql($key[0]);
			$sourceTableName = $importer->db->quoteSql($key[1]);
			$destTableName = $importer->db->quoteSql($key[3]);
			$tableSchema = $schema->getTableSchema($sourceTableName);
			foreach ($tableSchema->foreignKeys as $dbForeignKey) {
				if ($destTableName === $dbForeignKey[0] && isset($dbForeignKey[$key[2]]) && $key[4] === $dbForeignKey[$key[2]]) {
					$add = false;
				}
			}
			if ($add) {
				$this->logs .= "  > add: $keyName, $sourceTableName ... ";
				$start = microtime(true);
				try {
					$dbCommand->addForeignKey($keyName, $sourceTableName, $key[2], $destTableName, $key[4], $key[5], $key[6])->execute();
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "done    ({$time}s)\n";
				} catch (\Throwable $e) {
					$time = round((microtime(true) - $start), 1);
					$this->logs .= "     ({$time}s) | Error(10) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
				}
			}
		}
		$time = round((microtime(true) - $startMain) / 60, 2);
		$this->logs .= "# end update foreign key    ({$time}s)\n";
	}

	/**
	 * Builds a SQL command for enabling or disabling integrity check.
	 *
	 * @param bool $check whether to turn on or off the integrity check.
	 *
	 * @return void
	 */
	public function checkIntegrity($check)
	{
		foreach ($this->importers as &$importer) {
			$importer->db->createCommand()->checkIntegrity($check)->execute();
		}
	}
}
