<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

class PearDatabase
{
	protected $database;
	protected $stmt = false;
	public $dieOnError = false;
	private static $dbCache = false;
	protected $dbType;
	protected $dbHostName;
	protected $dbName;
	protected $userName;
	protected $userPassword;
	protected $port;
	// If you want to avoid executing PreparedStatement, set this to true
	// PreparedStatement will be converted to normal SQL statement for execution
	protected $avoidPreparedSql = false;

	/**
	 * Performance tunning parameters (can be configured through performance.prefs.php)
	 * See the constructor for initialization.
	 */
	protected $isdb_default_utf8_charset = false;
	protected $hasActiveTransaction = false;
	protected $hasFailedTransaction = false;
	protected $transCnt = 0;
	protected $autoCommit = false;

	const DEFAULT_QUOTE = '`';

	protected $types = [
		PDO::PARAM_BOOL => 'bool',
		PDO::PARAM_NULL => 'null',
		PDO::PARAM_INT => 'int',
		PDO::PARAM_LOB => 'blob',
		PDO::PARAM_STR => 'string',
		PDO::PARAM_STMT => 'statement',
	];

	/**
	 * Constructor.
	 */
	public function __construct($dbtype = '', $host = '', $dbname = '', $username = '', $passwd = '', $port = 3306)
	{
		$this->loadDBConfig($dbtype, $host, $dbname, $username, $passwd, $port);
		$this->isdb_default_utf8_charset = AppConfig::performance('DB_DEFAULT_CHARSET_UTF8');
		$this->setDieOnError(AppConfig::debug('SQL_DIE_ON_ERROR'));
		$this->connect();
	}

	/**
	 * Manage instance usage of this class.
	 */
	public static function &getInstance()
	{
		if (self::$dbCache !== false) {
			return self::$dbCache;
		}
		$db = new self(\Config\Db::$db_type, \Config\Db::$db_server, \Config\Db::$db_name, \Config\Db::$db_username, \Config\Db::$db_password, \Config\Db::$db_port);
		if ($db->database === null) {
			\App\Log::error('Database getInstance: Error connecting to the database', 'error');
			$db->checkError('Error connecting to the database');

			return false;
		} else {
			self::$dbCache = $db;
		}
		return $db;
	}

	public function connect()
	{
		// Set DSN
		$dsn = $this->dbType . ':host=' . $this->dbHostName . ';dbname=' . $this->dbName . ';port=' . $this->port;

		// Set options
		$options = [
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_TIMEOUT => 5,
		];
		// Create a new PDO instanace
		try {
			$this->database = new PDO($dsn, $this->userName, $this->userPassword, $options);
			$this->database->exec('SET NAMES ' . $this->database->quote('utf8'));
		} catch (\App\Exceptions\AppException $e) {
			// Catch any errors
			\App\Log::error('Database connect : ' . $e->getMessage());
			$this->checkError($e->getMessage());
		}
	}

	protected function loadDBConfig($dbtype, $host, $dbname, $username, $passwd, $port)
	{
		if ($host == '_SERVER_') {
			\App\Log::error('No configuration for the database connection');
		}
		$this->dbType = $dbtype;
		$this->dbHostName = $host;
		$this->dbName = $dbname;
		$this->userName = $username;
		$this->userPassword = $passwd;
		$this->port = $port;
	}

	public function setDBCache()
	{
		self::$dbCache = $this;
	}

	public function getDatabaseName()
	{
		return $this->dbName;
	}

	public function println($msg)
	{
		return $msg;
	}

	public function checkError($message, $dieOnError = false, $query = false, $params = false)
	{
		if ($this->hasActiveTransaction) {
			$this->rollbackTransaction();
		}
		if ($this->dieOnError || $dieOnError) {
			$backtrace = false;
			if (AppConfig::debug('DISPLAY_EXCEPTION_BACKTRACE')) {
				$backtrace = \App\Debuger::getBacktrace();
			}
			$message = [
				'message' => $message,
				'trace' => $backtrace,
				'query' => $query,
				'params' => $params,
			];
			vtlib\Functions::throwNewException($message, true, 'LBL_SQL_ERROR');
		}
	}

	public function errorMsg()
	{
		$error = $this->database->errorInfo();

		return $error[2];
	}

	public function isMySQL()
	{
		return stripos($this->dbType, 'mysql') === 0;
	}

	public function isOracle()
	{
		return $this->dbType == 'oci8';
	}

	public function isPostgres()
	{
		return $this->dbType == 'pgsql';
	}

	public function setDieOnError($value)
	{
		$this->dieOnError = $value;
	}

	public function setAttribute($attribute, $value)
	{
		$this->database->setAttribute($attribute, $value);
	}

	public function startTransaction()
	{
		$this->transCnt += 1;

		if ($this->hasActiveTransaction) {
			return $this->hasActiveTransaction;
		} else {
			$this->autoCommit = false;
			$this->hasActiveTransaction = $this->database->beginTransaction();

			return $this->hasActiveTransaction;
		}
	}

	public function completeTransaction()
	{
		$this->transCnt -= 1;

		if ($this->transCnt == 0) {
			$this->database->commit();
			$this->autoCommit = false;
			$this->hasActiveTransaction = false;
		}
	}

	public function hasFailedTransaction()
	{
		return $this->hasFailedTransaction;
	}

	public function rollbackTransaction()
	{
		if ($this->hasActiveTransaction) {
			$this->hasFailedTransaction = true;
			$result = $this->database->rollback();
			$this->autoCommit = false;
			$this->transCnt -= 1;

			return $result;
		}
		return false;
	}

	public function getRowCount(PDOStatement $result)
	{
		if (method_exists($result, 'rowCount')) {
			return $result->rowCount();
		}
		App\Log::warning('No rowCount function');

		return 0;
	}

	public function numRows(PDOStatement $result)
	{
		return $result->rowCount();
	}

	public function getFieldsCount(PDOStatement $result)
	{
		return $result->columnCount();
	}

	public function fetchArray(PDOStatement $result)
	{
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	public function getSingleValue(PDOStatement $result)
	{
		return $result->fetchColumn();
	}

	public function getRow(PDOStatement $result)
	{
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	public function getColumnByGroup(PDOStatement $result)
	{
		return $result->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
	}

	public function getArray(PDOStatement $result)
	{
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getArrayColumn(PDOStatement $result, $column = 0)
	{
		return $result->fetchAll(PDO::FETCH_COLUMN, $column);
	}

	public function disconnect()
	{
		if (isset($this->database)) {
			unset($this->database);
		}
	}

	public function query($query, $dieOnError = false, $msg = '')
	{
		$this->stmt = false;
		$sqlStartTime = microtime(true);

		try {
			\App\Log::beginProfile($query, __METHOD__);
			$this->stmt = $this->database->query($query);
			\App\Log::endProfile($query, __METHOD__);

			$this->logSqlTime($sqlStartTime, microtime(true), $query);
		} catch (PDOException $e) {
			$error = $this->database->errorInfo();
			\App\Log::error($msg . 'Query Failed: ' . $query . ' | ' . $error[2] . ' | ' . $e->getMessage());
			$this->checkError($e->getMessage(), $dieOnError, $query);
		}
		return $this->stmt;
	}

	/* Prepared statement Execution
	 * @param $sql -- Prepared sql statement
	 * @param $params -- Parameters for the prepared statement
	 * @param $dieOnError -- Set to true, when query execution fails
	 * @param $msg -- Error message on query execution failure
	 */

	public function pquery($query, $params = [], $dieOnError = false, $msg = '')
	{
		$this->stmt = false;
		$sqlStartTime = microtime(true);
		$params = $this->flattenArray($params);
		if (empty($params)) {
			return $this->query($query, $dieOnError, $msg);
		}
		try {
			$this->stmt = $this->database->prepare($query);

			\App\Log::beginProfile($query, __METHOD__);
			$this->stmt->execute($params);
			\App\Log::endProfile($query, __METHOD__);

			$this->logSqlTime($sqlStartTime, microtime(true), $query, $params);
		} catch (PDOException $e) {
			$error = $this->database->errorInfo();
			\App\Log::error($msg . 'Query Failed: ' . $query . ' | ' . $error[2] . ' | ' . $e->getMessage());
			$this->checkError($e->getMessage(), $dieOnError, $query, $params);
		}
		return $this->stmt;
	}

	public function prepare($query)
	{
		$this->stmt = $this->database->prepare($query);

		return $this->stmt;
	}

	public function bind($param, $value, $type = null)
	{
		if (is_null($type)) {
			switch (true) {
				case is_int($value):
					$type = PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = PDO::PARAM_NULL;
					break;
				default:
					$type = PDO::PARAM_STR;
			}
		}
		$this->stmt->bindValue($param, $value, $type);
	}

	public function execute()
	{
		try {
			$this->stmt->execute($params);
			$this->logSqlTime($sqlStartTime, microtime(true), $query, $params);
		} catch (\App\Exceptions\AppException $e) {
			$error = $this->database->errorInfo();
			\App\Log::error('Query Failed: ' . $query . ' | ' . $error[2] . ' | ' . $e->getMessage());
			$this->checkError($e->getMessage());
		}
		return $this->stmt;
	}

	/**
	 * A function to insert data into the database.
	 *
	 * @param string $table Table name
	 * @param array  $data  Query data
	 *
	 * @return array Row count and last insert id
	 */
	public function insert($table, array $data)
	{
		if (!$table) {
			\App\Log::error('Missing table name');
			$this->checkError('Missing table name');

			return false;
		} elseif (!is_array($data)) {
			\App\Log::error('Missing data, data must be an array');
			$this->checkError('Missing table name');

			return false;
		}
		$columns = '';
		foreach ($data as $column => $cur) {
			$columns .= ($columns ? ',' : '') . $this->quote($column, false);
		}
		$insert = 'INSERT INTO ' . $this->quote($table, false) . ' (' . $columns . ') VALUES (' . $this->generateQuestionMarks($data) . ')';
		$this->pquery($insert, $data);

		return ['rowCount' => $this->stmt->rowCount(), 'id' => $this->database->lastInsertId()];
	}

	/**
	 * A function to remove data from the database.
	 *
	 * @param string $table  Table name
	 * @param string $where  Conditions
	 * @param array  $params Query data
	 *
	 * @return int Number of deleted records
	 */
	public function delete($table, $where = '', array $params = [])
	{
		if (!$table) {
			\App\Log::error('Missing table name');
			$this->checkError('Missing table name');

			return false;
		}
		if ($where != '') {
			$where = sprintf('WHERE %s', $where);
		}
		if (count($params) === 0) {
			$this->query(sprintf('DELETE FROM %s %s', $table, $where));
		} else {
			$this->pquery(sprintf('DELETE FROM %s %s', $table, $where), $params);
		}
		return $this->stmt->rowCount();
	}

	/**
	 * A function to update data in the database.
	 *
	 * @param string $table   Table name
	 * @param array  $columns Columns to update
	 * @param string $where   Conditions
	 * @param array  $params  Query data
	 *
	 * @return int Number of updated records
	 */
	public function update($table, array $columns, $where = false, array $params = [])
	{
		$query = "UPDATE $table SET ";
		foreach ($columns as $column => $value) {
			$query .= $this->quote($column, false) . ' = ?,';
			$values[] = $value;
		}
		$query = trim($query, ',');
		if ($where !== false) {
			$query .= sprintf(' WHERE %s', $where);
		}
		$this->pquery(trim($query, ','), [array_merge($values, $params)]);

		return $this->stmt->rowCount();
	}

	public function queryResult(&$result, $row, $col = 0)
	{
		return $this->queryResultRaw($result, $row, $col);
	}

	public function queryResultRaw(&$result, $row, $col = 0)
	{
		if (!is_object($result)) {
			\App\Log::error('Result is not an object');
			$this->checkError('Result is not an object');
		}

		if (!isset($result->tmp)) {
			$result->tmp = $result->fetchAll(PDO::FETCH_ASSOC);
		}
		if (!isset($result->tmp[$row]) || !isset($result->tmp[$row][$col])) {
			return null;
		}
		return $result->tmp[$row][$col];
	}

	// Function to get particular row from the query result
	public function queryResultRowData(&$result, $row = 0)
	{
		return $this->rawQueryResultRowData($result, $row);
	}

	/**
	 * Get an array representing a row in the result set
	 * Unlike it's non raw siblings this method will not escape
	 * html entities in return strings.
	 *
	 * The case of all the field names is converted to lower case.
	 * as with the other methods.
	 *
	 * @param &$result The query result to fetch from
	 * @param $row     The row number to fetch. It's default value is 0
	 */
	public function rawQueryResultRowData(&$result, $row = 0)
	{
		if (!is_object($result)) {
			\App\Log::error('Result is not an object');
			$this->checkError('Result is not an object');
		}
		if (!isset($result->tmp)) {
			$result->tmp = $result->fetchAll(PDO::FETCH_ASSOC);
		}
		return $result->tmp[$row];
	}

	/**
	 * Flatten the composite array into single value.
	 * Example:
	 * $input = array(10, 20, array(30, 40), array('key1' => '50', 'key2'=>array(60), 70));
	 * returns array(10, 20, 30, 40, 50, 60, 70);.
	 */
	public function flattenArray($input, $output = null)
	{
		if (empty($input)) {
			return null;
		}
		if (empty($output)) {
			$output = [];
		}
		foreach ($input as $value) {
			if (is_array($value)) {
				$output = $this->flattenArray($value, $output);
			} else {
				array_push($output, $value);
			}
		}
		return $output;
	}

	public function getColumnNames($tablename)
	{
		$query = $this->database->query('SHOW COLUMNS FROM ' . $tablename, PDO::FETCH_OBJ);
		$columns = [];
		foreach ($query as $col) {
			$columns[] = $col->Field;
		}
		return $columns;
	}

	public function getColumnsMeta($tablename)
	{
		$query = $this->database->query('SHOW COLUMNS FROM ' . $tablename, PDO::FETCH_OBJ);
		$columns = [];
		foreach ($query as $col) {
			if (strpos($col->Type, '(') !== false) {
				$showType = explode('(', $col->Type); //PREG_SPLIT IS BETTER
			}
			$type = $showType[0];
			$vals = explode(')', $showType[1]);
			if (is_int((int) $vals[0])) {
				$maxLength = $vals[0];
			} elseif (strpos($vals[0], ',') !== false) {
				$vs = explode(',', $vals[0]);
				$vs = array_map('str_replace', $vs, ['\'', '', $vs[0]]);
				$maxLength = [];
				foreach ($vs as $v) {
					$maxLength[] = $v;
				}
			}
			$column = new stdClass();
			$column->name = $col->Field;
			$column->notNull = ($col->null == 'NO');
			$column->primaryKey = ($col->Key == 'PRI');
			$column->uniqueKey = ($col->Key == 'UNI');
			$column->hasDefault = !($col->Default === null);
			if ($column->hasDefault) {
				$column->default = $col->Default;
			}
			$column->maxLength = $maxLength;
			$column->type = $type;
			$columns[strtoupper($column->name)] = $column;
		}
		return $columns;
	}

	public function updateBlob($table, $column, $val, $where)
	{
		return $this->pquery("UPDATE $table SET $column=? WHERE $where", [$val]);
	}

	public function getEmptyBlob()
	{
		return 'null';
	}

	public function fetchByAssoc(&$result, $rowNum = -1)
	{
		if (isset($result) && $rowNum < 0) {
			return $this->getRow($result);
		}
		if ($this->getRowCount($result) > $rowNum) {
			$row = $this->rawQueryResultRowData($result, $rowNum);
		}
		return $row;
	}

	//To get a function name with respect to the database type which escapes strings in given text
	public function sqlEscapeString($str, $type = false)
	{
		if ($type) {
			$search = ['\\', "\0", "\n", "\r", "\x1a", "'", '"'];
			$replace = ['\\\\', '\\0', '\\n', '\\r', "\Z", "\'", '\"'];

			return str_replace($search, $replace, $str);
		} else {
			return $this->database->quote($str);
		}
	}

	public function getUniqueID($seqname)
	{
		$tableName = $seqname . '_seq';
		if ($this->checkExistTable($tableName)) {
			$result = $this->query(sprintf('SELECT id FROM %s', $tableName));
			$id = ((int) $this->getSingleValue($result)) + 1;
			$this->database->query("update $tableName set id = $id");
		} else {
			$result = $this->query('SHOW COLUMNS FROM ' . $this->quote($seqname, false));
			$column = $this->getSingleValue($result);
			$result = $this->query("SELECT MAX($column ) AS max FROM " . $this->quote($seqname, false));
			$id = ((int) $this->getSingleValue($result)) + 1;
		}
		return $id;
	}

	public function checkExistTable($tableName, $cache = true)
	{
		$tablePresent = Vtiger_Cache::get('checkExistTable', $tableName);
		if ($tablePresent !== false && $cache) {
			return $tablePresent;
		}

		$tmpDieOnError = $this->dieOnError;
		$this->dieOnError = false;

		$tablename = $this->sqlEscapeString($tableName);
		$tableCheck = $this->query("SHOW TABLES LIKE $tablename");
		$tablePresent = 1;
		if (empty($tableCheck) || $this->getRowCount($tableCheck) === 0) {
			$tablePresent = 0;
		}
		$this->dieOnError = $tmpDieOnError;
		Vtiger_Cache::set('checkExistTable', $tableName, $tablePresent);

		return $tablePresent;
	}

	// Function to get the last insert id based on the type of database
	public function getLastInsertID()
	{
		return $this->database->lastInsertId();
	}

	public function formatDate($datetime, $strip_quotes = false)
	{
		// remove single quotes to use the date as parameter for Prepared statement
		if ($strip_quotes === true) {
			return trim($datetime, "'");
		}
		return $datetime;
	}

	public function getOne($sql, $dieOnError = false, $msg = '')
	{
		$result = $this->query($sql, $dieOnError, $msg);
		return $this->getSingleValue($result);
	}

	public function getFieldsDefinition(PDOStatement $result)
	{
		$fieldArray = [];
		if (!isset($result) || empty($result)) {
			return 0;
		}
		foreach (range(0, $result->columnCount() - 1) as $columnIndex) {
			$meta = $result->getColumnMeta($columnIndex);
			$column = new stdClass();
			$column->name = $meta['name'];
			$column->type = $this->types[$meta['pdo_type']];
			$column->max_length = $meta['len'];
			array_push($fieldArray, $column);
		}
		return $fieldArray;
	}

	public function getFieldsArray(PDOStatement $result)
	{
		$fieldArray = [];
		if (!isset($result) || empty($result)) {
			return 0;
		}
		foreach (range(0, $result->columnCount() - 1) as $columnIndex) {
			$meta = $result->getColumnMeta($columnIndex);
			array_push($fieldArray, $meta['name']);
		}
		return $fieldArray;
	}

	/**
	 * Function to generate question marks for a given list of items.
	 */
	public function generateQuestionMarks($items)
	{
		// array_map will call the function specified in the first parameter for every element of the list in second parameter
		return implode(',', array_map(function ($a) {
			return '?';
		}, is_array($items) ? $items : explode(',', $items)));
	}

	public function concat($columns, $space = '" "')
	{
		$concat = 'CONCAT(';
		foreach ($columns as $key => $column) {
			if ($key != 0 && $space) {
				$concat .= $space . ',';
			}
			$concat .= $column . ',';
		}
		return rtrim($concat, ',') . ')';
	}

	// create an IN expression from an array/list
	public function sqlExprDatalist($array)
	{
		if (!is_array($array)) {
			\App\Log::error('sqlExprDatalist: not an array');
			$this->checkError('sqlExprDatalist: not an array');
		}
		if (!count($array)) {
			\App\Log::error('sqlExprDatalist: empty arrays not allowed');
			$this->checkError('sqlExprDatalist: empty arrays not allowed');
		}
		$l = '';
		foreach ($array as $val) {
			$l .= ($l ? ',' : '') . $this->quote($val);
		}
		return ' ( ' . $l . ' ) ';
	}

	public function getAffectedRowCount(PDOStatement $result)
	{
		return $result->rowCount();
	}

	public function requirePsSingleResult($sql, $params, $dieOnError = false, $msg = '')
	{
		$result = $this->pquery($sql, $params, $dieOnError, $msg);

		if ($this->getRowCount($result) == 1) {
			return $result;
		}
		\App\Log::error('Rows Returned:' . $this->getRowCount($result) . ' More than 1 row returned for ' . $sql);
		$this->checkError('Rows Returned:' . $this->getRowCount($result) . ' More than 1 row returned for ' . $sql, $dieOnError);

		return '';
	}

	public function columnMeta(PDOStatement $result, $col)
	{
		$meta = $result->getColumnMeta($col);
		$column = new stdClass();
		$column->name = $meta['name'];
		$column->type = $this->types[$meta['pdo_type']];
		$column->max_length = $meta['len'];

		return $column;
	}

	public function quote($input, $quote = true, $type = null)
	{
		// handle int directly for better performance
		if ($type == 'integer' || $type == 'int') {
			return (int) $input;
		}

		if (is_null($input)) {
			return 'NULL';
		}

		$map = [
			'bool' => PDO::PARAM_BOOL,
			'integer' => PDO::PARAM_INT,
		];

		$type = $map[$type] ?? PDO::PARAM_STR;
		if ($quote) {
			return strtr($this->database->quote($input, $type), [self::DEFAULT_QUOTE => self::DEFAULT_QUOTE . self::DEFAULT_QUOTE]);
		} else {
			return self::DEFAULT_QUOTE . $input . self::DEFAULT_QUOTE;
		}
	}

	// SQLTime logging

	protected $logSqlTimeID = false;
	protected $logSqlTimeGroup = 1;

	public function logSqlTime($startat, $endat, $sql, $params = false)
	{
		if (!AppConfig::performance('SQL_LOG_INCLUDE_CALLER')) {
			return;
		}
		$db = self::getInstance('log');
		$now = date('Y-m-d H:i:s');
		$group = $this->logSqlTimeGroup;
		$logTable = 'l_yf_sqltime';
		$logQuery = 'INSERT INTO ' . $logTable . '(`id`, `type`, `qtime`, `content`, `date`, `group`) VALUES (?,?,?,?,?,?)';

		if ($this->logSqlTimeID === false) {
			$query = $db->database->query(sprintf('SELECT MAX(id) FROM %s', $logTable));
			$this->logSqlTimeID = (int) $this->getSingleValue($query) + 1;

			$type = PHP_SAPI;
			$data = '';
			if (isset($_SERVER['REQUEST_METHOD'])) {
				$uri = $_SERVER['REQUEST_URI'];
				$qmarkIndex = strpos($_SERVER['REQUEST_URI'], '?');
				if ($qmarkIndex !== false) {
					$uri = substr($uri, 0, $qmarkIndex);
				}
				$data = $uri . '?' . http_build_query($_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET : $_POST);
			}
			$query = $db->database->prepare($logQuery);
			$query->execute([$this->logSqlTimeID, $type, null, $data, $now, $group]);
		}

		$type = 'SQL';
		$data = trim($sql);
		if (is_array($params) && !empty($params)) {
			$data .= '[' . implode(',', $params) . ']';
		}
		$qtime = round(($endat - $startat) * 1000) / 1000;
		$query = $db->database->prepare($logQuery);
		$query->execute([$this->logSqlTimeID, $type, $qtime, $data, $now, $group]);

		$type = 'CALLERS';
		$data = [];
		$callers = debug_backtrace();
		for ($calleridx = 0, $callerscount = count($callers); $calleridx < $callerscount; ++$calleridx) {
			if ($calleridx == 0) {
				continue;
			}
			if ($calleridx < $callerscount) {
				$callerfunc = $callers[$calleridx + 1]['function'];
				if (isset($callers[$calleridx + 1]['args'])) {
					$args = '';
					foreach ($callers[$calleridx + 1]['args'] as &$arg) {
						if (!is_array($arg) && !is_object($arg) && !is_resource($arg)) {
							$args .= "'$arg'";
						}
						$args .= ',';
					}
					$args = rtrim($args, ',');
				}
				if (!empty($callerfunc)) {
					$callerfunc = " ($callerfunc)";
				}
				if (!empty($args)) {
					$callerfunc .= "[$args] ";
				}
			}
			$data[] = 'CALLER: (' . $callers[$calleridx]['line'] . ') ' . $callers[$calleridx]['file'] . $callerfunc;
		}
		$query = $db->database->prepare($logQuery);
		$query->execute([$this->logSqlTimeID, $type, null, implode(PHP_EOL, $data), $now, $group]);
		++$this->logSqlTimeGroup;
	}

	public static function whereEquals($val)
	{
		if (is_array($val)) {
			if (count($val) > 1) {
				return 'IN (\'' . implode("','", $val) . '\')';
			} else {
				$val = array_shift($val);
			}
		}
		return '=\'' . $val . '\'';
	}
}
