<?php

namespace App\Db\Drivers;

/**
 * Command represents a SQL statement to be executed against a database.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
trait SchemaTrait
{
	/**
	 * @var array list of ALL table names in the database
	 */
	private $_tableNames = [];

	/**
	 * Refreshes the schema.
	 * This method cleans up all cached table schemas so that they can be re-created later
	 * to reflect the database schema change.
	 */
	public function refresh()
	{
		$this->_tableNames = [];
		\App\Cache::clear();
	}

	/**
	 * Refreshes the particular table schema.
	 * This method cleans up cached table schema so that it can be re-created later
	 * to reflect the database schema change.
	 *
	 * @param string $name table name.
	 *
	 * @since 2.0.6
	 */
	public function refreshTableSchema($name)
	{
		$rawName = $this->getRawTableName($name);
		\App\Cache::delete('tableSchema', $rawName);
		$this->_tableNames = [];
	}

	/**
	 * Creates a new savepoint.
	 *
	 * @param string $name the savepoint name
	 */
	public function createSavepoint($name)
	{
		$this->db->pdo->exec("SAVEPOINT $name");
	}

	/**
	 * Releases an existing savepoint.
	 *
	 * @param string $name the savepoint name
	 */
	public function releaseSavepoint($name)
	{
		$this->db->pdo->exec("RELEASE SAVEPOINT $name");
	}

	/**
	 * Rolls back to a previously created savepoint.
	 *
	 * @param string $name the savepoint name
	 */
	public function rollBackSavepoint($name)
	{
		$this->db->pdo->exec("ROLLBACK TO SAVEPOINT $name");
	}

	/**
	 * Sets the isolation level of the current transaction.
	 *
	 * @param string $level The transaction isolation level to use for this transaction.
	 *                      This can be one of [[Transaction::READ_UNCOMMITTED]], [[Transaction::READ_COMMITTED]], [[Transaction::REPEATABLE_READ]]
	 *                      and [[Transaction::SERIALIZABLE]] but also a string containing DBMS specific syntax to be used
	 *                      after `SET TRANSACTION ISOLATION LEVEL`.
	 *
	 * @see https://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
	 */
	public function setTransactionIsolationLevel($level)
	{
		$this->db->pdo->exec("SET TRANSACTION ISOLATION LEVEL $level");
	}

	/**
	 * Returns the actual name of a given table name.
	 * This method will strip off curly brackets from the given table name
	 * and replace the percentage character '%' with [[Connection::tablePrefix]].
	 *
	 * @param string $name the table name to be converted
	 *
	 * @return string the real name of the given table name
	 */
	public function getRawTableName($name)
	{
		if (false !== strpos($name, '{{')) {
			$name = preg_replace('/\\{\\{(.*?)\\}\\}/', '\1', $name);
			return str_replace('%', $this->db->tablePrefix, $name);
		}
		return str_replace('#__', $this->db->tablePrefix, $name);
	}

	/**
	 * Returns all table names in the database.
	 *
	 * @param string $schema  the schema of the tables. Defaults to empty string, meaning the current or default schema name.
	 *                        If not empty, the returned table names will be prefixed with the schema name.
	 * @param bool   $refresh whether to fetch the latest available table names. If this is false,
	 *                        table names fetched previously (if available) will be returned.
	 *
	 * @return string[] all table names in the database.
	 */
	public function getTableNames($schema = '', $refresh = false)
	{
		if (!isset($this->_tableNames[$schema]) || $refresh) {
			$this->_tableNames[$schema] = $this->findTableNames($schema);
		}
		return $this->_tableNames[$schema];
	}

	/**
	 * Returns the metadata of the given type for the given table.
	 * If there's no metadata in the cache, this method will call
	 * a `'loadTable' . ucfirst($type)` named method with the table name to obtain the metadata.
	 *
	 * @param string $name    table name. The table name may contain schema name if any. Do not quote the table name.
	 * @param string $type    metadata type.
	 * @param bool   $refresh whether to reload the table metadata even if it is found in the cache.
	 *
	 * @return mixed metadata.
	 *
	 * @since 2.0.13
	 */
	protected function getTableMetadata($name, $type, $refresh)
	{
		$rawName = $this->getRawTableName($name);
		$tableSchema = [];
		if (!$refresh && \App\Cache::has('tableSchema', $rawName)) {
			$tableSchema = \App\Cache::get('tableSchema', $rawName);
			if (isset($tableSchema[$type])) {
				return $tableSchema[$type];
			}
		}
		if ($refresh || !isset($tableSchema[$type])) {
			$tableSchema[$type] = $this->{'loadTable' . ucfirst($type)}($rawName);
			\App\Cache::save('tableSchema', $rawName, $tableSchema, \App\Cache::LONG);
		}
		return $tableSchema[$type];
	}

	/**
	 * Sets the metadata of the given type for the given table.
	 *
	 * @param string $name table name.
	 * @param string $type metadata type.
	 * @param mixed  $data metadata.
	 *
	 * @since 2.0.13
	 */
	protected function setTableMetadata($name, $type, $data)
	{
		$rawName = $this->getRawTableName($name);
		$tableSchema = [];
		if (\App\Cache::has('tableSchema', $rawName)) {
			$tableSchema = \App\Cache::get('tableSchema', $rawName);
		}
		$tableSchema[$type] = $data;
		\App\Cache::save('tableSchema', $rawName, $tableSchema, \App\Cache::LONG);
	}

	/**
	 * Lists indexes for table.
	 *
	 * @param string $name
	 * @param bool   $refresh
	 *
	 * @return \yii\db\IndexConstraint[]
	 */
	public function getTableIndexes($name, $refresh = false)
	{
		return $this->getTableMetadata($name, 'indexes', $refresh);
	}

	/**
	 * Find foreign keys to column.
	 *
	 * @param string $findTableName
	 * @param string $findColumnName
	 *
	 * @return array
	 */
	public function findForeignKeyToColumn(string $findTableName, string $findColumnName)
	{
		$foreignKeys = [];
		foreach ($this->getTableNames() as $tableName) {
			$tableSchema = $this->getTableSchema($tableName);
			if ($tableSchema->foreignKeys) {
				foreach ($tableSchema->foreignKeys as $name => $value) {
					if ($findTableName === $value[0] && ($key = array_search($findColumnName, $value))) {
						$foreignKeys[$tableName][$name] = ['sourceColumn' => $key];
					}
				}
			}
		}
		return $foreignKeys;
	}
}
