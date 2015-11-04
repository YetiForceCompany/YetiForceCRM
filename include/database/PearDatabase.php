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
require_once('include/logging.php');
require_once('include/runtime/Globals.php');

class PearDatabase
{

	protected $database = null;
	protected $stmt = false;
	public $dieOnError = false;
	protected $log = null;
	static private $dbConfig = false;
	static private $dbCache = [];
	protected $dbType = null;
	protected $dbHostName = null;
	protected $dbName = null;
	protected $userName = null;
	protected $userPassword = null;
	protected $port = null;
	// If you want to avoid executing PreparedStatement, set this to true
	// PreparedStatement will be converted to normal SQL statement for execution
	protected $avoidPreparedSql = false;

	/**
	 * Performance tunning parameters (can be configured through performance.prefs.php)
	 * See the constructor for initialization
	 */
	protected $isdb_default_utf8_charset = false;
	protected $hasActiveTransaction = false;
	protected $hasFailedTransaction = false;

	const DEFAULT_QUOTE = '`';

	protected $types = [
		PDO::PARAM_BOOL => 'bool',
		PDO::PARAM_NULL => 'null',
		PDO::PARAM_INT => 'int',
		PDO::PARAM_STR => 'string',
		PDO::PARAM_LOB => 'blob',
		PDO::PARAM_STMT => 'statement',
	];

	/**
	 * Constructor
	 */
	public function __construct($dbtype = '', $host = '', $dbname = '', $username = '', $passwd = '', $port = 3306)
	{
		$this->log = LoggerManager::getLogger('DB');
		$this->loadDBConfig($dbtype, $host, $dbname, $username, $passwd, $port);
		$this->isdb_default_utf8_charset = PerformancePrefs::getBoolean('DB_DEFAULT_CHARSET_UTF8');
		$this->setDieOnError(SysDebug::get('SQL_DIE_ON_ERROR'));
		$this->connect();
	}

	/**
	 * Manage instance usage of this class
	 */
	public static function &getInstance($type = 'base')
	{
		if (key_exists($type, self::$dbCache)) {
			$db = self::$dbCache[$type];
			vglobal('adb', $db);
			return $db;
		} else if (key_exists('base', self::$dbCache)) {
			$db = self::$dbCache['base'];
			vglobal('adb', $db);
			return $db;
		}

		$config = self::getDBConfig($type);
		$db = new self($config['db_type'], $config['db_server'], $config['db_name'], $config['db_username'], $config['db_password'], $config['db_port']);

		if ($db->database == NULL) {
			$db->log('Database getInstance: Error connecting to the database', 'error');
			$db->checkError('Error connecting to the database');
			return false;
		} else {
			self::$dbCache[$type] = $db;
			vglobal('adb', $db);
		}
		return $db;
	}

	public function connect()
	{
		// Set DSN 
		$dsn = 'mysql:host=' . $this->dbHostName . ';dbname=' . $this->dbName . ';charset=utf8' . ';port=' . $this->port;

		// Set options
		$options = array(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		);

		if ($this->isdb_default_utf8_charset) {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
		}
		// Create a new PDO instanace
		try {
			$this->database = new PDO($dsn, $this->userName, $this->userPassword, $options);
		} catch (AppException $e) {
			// Catch any errors
			$this->log('Database connect : ' . $e->getMessage(), 'error');
			$this->checkError($e->getMessage());
		}
	}

	public static function getDBConfig($type)
	{
		if (!self::$dbConfig) {
			require('config/config.db.php');
			self::$dbConfig = $dbConfig;
		}
		if (self::$dbConfig[$type]['db_server'] != '_SERVER_') {
			return self::$dbConfig[$type];
		}
		return self::$dbConfig['base'];
	}

	protected function loadDBConfig($dbtype, $host, $dbname, $username, $passwd, $port)
	{
		if ($host == '_SERVER_') {
			$this->log('No configuration for the database connection', 'error');
		}
		$this->dbType = $dbtype;
		$this->dbHostName = $host;
		$this->dbName = $dbname;
		$this->userName = $username;
		$this->userPassword = $passwd;
		$this->port = $port;
	}

	public function getDatabaseName()
	{
		return $this->dbName;
	}

	public function log($message, $type = 'info')
	{
		if (is_array($message)) {
			$message = print_r($message, true);
		}
		if ($type == 'error') {
			$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			if (isset($debug[1])) {
				$line = $debug[1]['line'];
				$file = $debug[1]['file'];
				$message .= "($file : $line)";
			}
		}
		$this->log->$type($message);
		return $message;
	}

	public function println($msg)
	{
		$this->log($msg);
		return $msg;
	}

	public function checkError($message, $dieOnError = false, $query = false, $params = false)
	{
		if ($this->hasActiveTransaction) {
			$this->rollbackTransaction();
		}
		if ($this->dieOnError || $dieOnError) {
			if (SysDebug::get('DISPLAY_DEBUG_BACKTRACE')) {

				$queryInfo = '';
				if ($query !== false) {
					$queryInfo .= 'Query: ' . $query . PHP_EOL;
				}
				if ($params !== false && $params != NULL) {
					$queryInfo .= 'Params: ' . implode(',', $params) . PHP_EOL;
				}
				$backtrace = Vtiger_Functions::getBacktrace();
				$trace = '<pre>' . $queryInfo . $backtrace . '</pre>';
			}
			Vtiger_Functions::throwNewException('Database ERROR: ' . PHP_EOL . $message . PHP_EOL . $trace);
		}
	}

	public function ErrorMsg()
	{
		$error = $this->database->errorInfo();
		return $error[2];
	}

	public function isMySQL()
	{
		return (stripos($this->dbType, 'mysql') === 0);
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

	public function setAttribute()
	{
		$this->database->setAttribute(func_get_args());
	}

	public function startTransaction()
	{
		if ($this->hasActiveTransaction) {
			return false;
		} else {
			$this->hasActiveTransaction = $this->database->beginTransaction();
			return $this->hasActiveTransaction;
		}
	}

	public function completeTransaction()
	{
		$this->database->commit();
		$this->hasActiveTransaction = false;
	}

	public function hasFailedTransaction()
	{
		return $this->hasFailedTransaction;
	}

	public function rollbackTransaction()
	{
		if ($this->hasActiveTransaction) {
			$this->hasFailedTransaction = true;
			return $this->database->rollback();
		}
		return false;
	}

	public function getRowCount(&$result)
	{
		return $result->rowCount();
	}

	//TODO DEPRECATED recommended to use getRowCount
	public function num_rows(&$result)
	{
		return $result->rowCount();
	}

	public function getFieldsCount(&$result)
	{
		return $result->columnCount();
	}

	//TODO DEPRECATED recommended to use getRow
	public function fetch_array(&$result)
	{
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	public function getSingleValue(&$result)
	{
		return $result->fetchColumn();
	}

	public function getRow(&$result)
	{
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	public function getArray(&$result)
	{
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	public function disconnect()
	{
		$this->log('Database disconnect');
		if (isset($this->database)) {
			unset($this->database);
		}
	}

	public function query($query, $dieOnError = false, $msg = '')
	{
		$this->log('Query: ' . $query);
		$this->stmt = false;
		$sqlStartTime = microtime(true);

		try {
			$this->stmt = $this->database->query($query);
			$this->logSqlTime($sqlStartTime, microtime(true), $query);
		} catch (AppException $e) {
			$error = $this->database->errorInfo();
			$this->log($msg . 'Query Failed: ' . $query . ' | ' . $error[2] . ' | ' . $e->getMessage(), 'error');
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
		$this->log('Query: ' . $query);
		$this->stmt = false;
		$sqlStartTime = microtime(true);
		$params = $this->flatten_array($params);
		if (count($params) > 0) {
			$this->log('Query parameters: [' . implode(",", $params) . ']');
		}

		try {
			$this->stmt = $this->database->prepare($query);
			$success = $this->stmt->execute($params);
			$this->logSqlTime($sqlStartTime, microtime(true), $query, $params);
		} catch (AppException $e) {
			$error = $this->database->errorInfo();
			$this->log($msg . 'Query Failed: ' . $query . ' | ' . $error[2] . ' | ' . $e->getMessage(), 'error');
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
			$success = $this->stmt->execute($params);
			$this->logSqlTime($sqlStartTime, microtime(true), $query, $params);
		} catch (AppException $e) {
			$error = $this->database->errorInfo();
			$this->log($msg . 'Query Failed: ' . $query . ' | ' . $error[2] . ' | ' . $e->getMessage(), 'error');
			$this->checkError($e->getMessage());
		}
		return $this->stmt;
	}

	/**
	 * A function to insert data into the database
	 * @param string $table Table name
	 * @param array $data Query data
	 * @return array Row count and last insert id
	 */
	public function insert($table, array $data)
	{
		if (!$table) {
			$this->log('Missing table name', 'error');
			$this->checkError('Missing table name');
			return false;
		} else if (!is_array($data)) {
			$this->log('Missing data, data must be an array', 'error');
			$this->checkError('Missing table name');
			return false;
		}
		$columns = '';
		foreach ($data as $column => $cur) {
			$columns .= ($columns ? ',' : '') . $this->quote($column, false);
		}
		$insert = 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $this->generateQuestionMarks($data) . ')';
		$this->pquery($insert, $data);
		return ['rowCount' => $this->stmt->rowCount(), 'id' => $this->database->lastInsertId()];
	}

	/**
	 * A function to remove data from the database
	 * @param string $table Table name
	 * @param string $where Conditions
	 * @param array $params Query data
	 * @return int Number of deleted records
	 */
	public function delete($table, $where = '', array $params = [])
	{
		if (!$table) {
			$this->log('Missing table name', 'error');
			$this->checkError('Missing table name');
			return false;
		}
		if ($where != '')
			$where = 'WHERE ' . $where;
		$this->pquery("DELETE FROM $table $where", $params);
		return $this->stmt->rowCount();
	}

	/**
	 * A function to update data in the database
	 * @param string $table Table name
	 * @param array $columns Columns to update
	 * @param string $where Conditions
	 * @param array $params Query data
	 * @return int Number of updated records
	 */
	public function update($table, array $columns, $where = false, array $params = [])
	{
		$this->log('Update | table: ' . $table . ',columns:' . print_r($columns, true) . ',where:' . $where . ',params:' . print_r($params, true));
		$query = "UPDATE $table SET ";
		foreach ($columns as $column => $value) {
			$query .= $column . ' = ?,';
			$values[] = $value;
		}
		$query = trim($query, ',');
		if ($where !== false) {
			$query .= ' WHERE ' . $where;
		}
		$this->pquery(trim($query, ','), [array_merge($values, $params)]);
		return $this->stmt->rowCount();
	}

	//TODO DEPRECATED
	public function query_result(&$result, $row, $col = 0)
	{
		return to_html($this->query_result_raw($result, $row, $col));
	}

	//TODO DEPRECATED
	public function query_result_raw(&$result, $row, $col = 0)
	{
		if (!is_object($result)) {
			$this->log('Result is not an object', 'error');
			$this->checkError('Result is not an object');
		}

		if (!isset($result->tmp)) {
			$result->tmp = $result->fetchAll(PDO::FETCH_ASSOC);
		}
		return $result->tmp[$row][$col];
	}

	// Function to get particular row from the query result
	//TODO DEPRECATED
	public function query_result_rowdata(&$result, $row = 0)
	{
		return $this->raw_query_result_rowdata($result, $row);
	}

	/**
	 * Get an array representing a row in the result set
	 * Unlike it's non raw siblings this method will not escape
	 * html entities in return strings.
	 *
	 * The case of all the field names is converted to lower case.
	 * as with the other methods.
	 *
	 * @param &$result The query result to fetch from.
	 * @param $row The row number to fetch. It's default value is 0
	 *
	 */
	//TODO DEPRECATED
	public function raw_query_result_rowdata(&$result, $row = 0)
	{
		if (!is_object($result)) {
			$this->log('Result is not an object', 'error');
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
	 * returns array(10, 20, 30, 40, 50, 60, 70);
	 */
	//TODO DEPRECATED
	public function flatten_array($input, $output = null)
	{
		if ($input == null)
			return null;
		if ($output == null)
			$output = [];
		foreach ($input as $value) {
			if (is_array($value)) {
				$output = $this->flatten_array($value, $output);
			} else {
				array_push($output, $value);
			}
		}
		return $output;
	}

	public function getColumnNames($tablename)
	{
		$stmt = $this->database->query("SHOW COLUMNS FROM " . $tablename, PDO::FETCH_OBJ);
		$columns = [];
		foreach ($stmt as $col) {
			$columns[] = $col->Field;
		}
		return $columns;
	}

	public function getColumnsMeta($tablename)
	{
		$stmt = $this->database->query("SHOW COLUMNS FROM " . $tablename, PDO::FETCH_OBJ);
		$columns = [];
		foreach ($stmt as $col) {
			if (strpos($col->Type, '(') !== FALSE) {
				$showType = explode("(", $col->Type); //PREG_SPLIT IS BETTER
			}
			$type = $showType[0];
			$vals = explode(")", $showType[1]);
			if (is_integer((int) $vals[0])) {
				$maxLength = $vals[0];
			} elseif (strpos($vals[0], ',') !== FALSE) {
				$vs = explode(',', $vals[0]);
				$vs = array_map('str_replace', $vs, ['\'', '', $vs[0]]);
				$maxLength = [];
				foreach ($vs as $v) {
					$maxLength[] = $v;
				}
			}
			$column = new stdClass();
			$column->name = $col->Field;
			$column->notNull = $col->Null == 'NO' ? true : false;
			$column->primaryKey = $col->Key == 'PRI' ? true : false;
			$column->uniqueKey = $col->Key == 'UNI' ? true : false;
			$column->hasDefault = $col->Default === null ? false : true;
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
		$this->log("Update Blob: $table, $column, $val, $where, $blobtype");
		$success = $this->pquery("UPDATE $table SET $column=? WHERE $where", [$val]);
		return $success;
	}

	public function getEmptyBlob()
	{
		return 'null';
	}

	public function fetchByAssoc(&$result, $rowNum = -1, $encode = true)
	{
		if (isset($result) && $rowNum < 0) {
			$row = $this->getRow($result);
			if ($encode && is_array($row))
				return array_map('to_html', $row);
			return $row;
		}
		if ($this->getRowCount($result) > $rowNum) {
			$row = $this->raw_query_result_rowdata($result, $rowNum);
		}
		if ($encode && is_array($row))
			return array_map('to_html', $row);
		return $row;
	}

	//To get a function name with respect to the database type which escapes strings in given text
	public function sql_escape_string($str, $type = false)
	{
		if ($type) {
			$search = ["\\", "\0", "\n", "\r", "\x1a", "'", '"'];
			$replace = ["\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"'];
			return str_replace($search, $replace, $str);
		} else {
			return $this->database->quote($str);
		}
	}

	public function getUniqueID($seqname)
	{
		$table = $seqname . '_seq';
		$result = $this->query("SHOW TABLES LIKE '$table'");
		if ($result->rowCount() > 0) {
			$result = $this->query('SELECT id FROM ' . $table);
			$id = ((int) $this->getSingleValue($result)) + 1;
			$this->database->query("update $table set id = $id");
		} else {
			$result = $this->query('SHOW COLUMNS FROM ' . $this->quote($seqname, false));
			$column = $this->getSingleValue($result);
			$result = $this->query("SELECT MAX($column ) AS max FROM " . $this->quote($seqname, false));
			$id = ((int) $this->getSingleValue($result)) + 1;
		}
		return $id;
	}

	// Function to get the last insert id based on the type of database
	public function getLastInsertID($seqname = '')
	{
		$lastInsertID = $this->database->lastInsertId();
		return $lastInsertID;
	}

	public function formatDate($datetime, $strip_quotes = false)
	{
		/* remove single quotes to use the date as parameter for Prepared statement */
		if ($strip_quotes == true) {
			return trim($datetime, "'");
		}
		return $datetime;
	}

	public function getOne($sql, $dieOnError = false, $msg = '')
	{
		$this->log('getOne: ' . $sql);
		$result = $this->query($sql, $dieOnError, $msg);
		$val = $this->getSingleValue($result);
		return $val;
	}

	public function getFieldsDefinition(&$result)
	{
		$this->log('getFieldsDefinition');
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

	public function getFieldsArray(&$result)
	{
		$this->log('getFieldsArray');
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
	 * Function to generate question marks for a given list of items
	 */
	public function generateQuestionMarks($items)
	{
		// array_map will call the function specified in the first parameter for every element of the list in second parameter
		if (is_array($items)) {
			return implode(",", array_map("_questionify", $items));
		} else {
			return implode(",", array_map("_questionify", explode(",", $items)));
		}
	}

	public function concat($list)
	{
		return 'concat(' . implode(',', $list) . ')';
	}

	// create an IN expression from an array/list
	public function sqlExprDatalist($array)
	{
		if (!is_array($array)) {
			$this->log('sqlExprDatalist: not an array', 'error');
			$this->checkError('sqlExprDatalist: not an array');
		}
		if (!count($array)) {
			$this->log('sqlExprDatalist: empty arrays not allowed', 'error');
			$this->checkError('sqlExprDatalist: empty arrays not allowed');
		}
		foreach ($array as $key => $val)
			$l .= ($l ? ',' : '') . $this->quote($val);
		return ' ( ' . $l . ' ) ';
	}

	public function getAffectedRowCount(&$result)
	{
		$rows = $result->rowCount();
		$this->log('getAffectedRowCount: ' . $rows);
		return $rows;
	}

	public function requireSingleResult($sql, $dieOnError = false, $msg = '', $encode = true)
	{
		$result = $this->query($sql, $dieOnError, $msg);

		if ($this->getRowCount($result) == 1)
			return $result;
		$this->log('Rows Returned:' . $this->getRowCount($result) . ' More than 1 row returned for ' . $sql, 'error');
		$this->checkError('Rows Returned:' . $this->getRowCount($result) . ' More than 1 row returned for ' . $sql, $dieOnError);
		return '';
	}
	/* function which extends requireSingleResult api to execute prepared statment
	 */

	public function requirePsSingleResult($sql, $params, $dieOnError = false, $msg = '', $encode = true)
	{
		$result = $this->pquery($sql, $params, $dieOnError, $msg);

		if ($this->getRowCount($result) == 1)
			return $result;
		$this->log('Rows Returned:' . $this->getRowCount($result) . ' More than 1 row returned for ' . $sql, 'error');
		$this->checkError('Rows Returned:' . $this->getRowCount($result) . ' More than 1 row returned for ' . $sql, $dieOnError);
		return '';
	}

	public function columnMeta(&$result, $col)
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
			return intval($input);
		}

		if (is_null($input)) {
			return 'NULL';
		}

		$map = array(
			'bool' => PDO::PARAM_BOOL,
			'integer' => PDO::PARAM_INT,
		);

		$type = isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
		if ($quote) {
			return strtr($this->database->quote($input, $type), array(self::DEFAULT_QUOTE => self::DEFAULT_QUOTE . self::DEFAULT_QUOTE));
		} else {
			return self::DEFAULT_QUOTE . $input . self::DEFAULT_QUOTE;
		}
	}
	/* SQLTime logging */

	protected $logSqlTimeID = false;

	public function logSqlTime($startat, $endat, $sql, $params = false)
	{
		if (!PerformancePrefs::getBoolean('SQL_LOG_INCLUDE_CALLER', false)) {
			return;
		}
		$db = PearDatabase::getInstance('log');
		$now = date('Y-m-d H:i:s');
		$logTable = 'l_yf_sqltime';
		$logQuery = 'INSERT INTO ' . $logTable . '(id, type, qtime, data, date) VALUES (?,?,?,?,?)';

		if ($this->logSqlTimeID === false) {
			$stmt = $db->database->query('SELECT MAX(id) FROM ' . $logTable);
			$this->logSqlTimeID = (int) $this->getSingleValue($stmt) + 1;

			$type = PHP_SAPI;
			$data = '';
			if (isset($_SERVER['REQUEST_METHOD'])) {
				$uri = $_SERVER['REQUEST_URI'];
				$qmarkIndex = strpos($_SERVER['REQUEST_URI'], '?');
				if ($qmarkIndex !== false)
					$uri = substr($uri, 0, $qmarkIndex);
				$data = $uri . '?' . http_build_query($_SERVER['REQUEST_METHOD'] == 'GET' ? $_GET : $_POST);
			}
			$stmt = $db->database->prepare($logQuery);
			$stmt->execute([$this->logSqlTimeID, $type, NULL, $data, $now]);
		}

		$type = 'SQL';
		$data = trim($sql);
		if (is_array($params) && !empty($params)) {
			$data .= '[' . implode(',', $params) . ']';
		}
		$qtime = round(($endat - $startat) * 1000) / 1000;
		$stmt = $db->database->prepare($logQuery);
		$stmt->execute([$this->logSqlTimeID, $type, $qtime, $data, $now]);

		$type = 'CALLERS';
		$data = [];
		$callers = debug_backtrace();
		for ($calleridx = 0, $callerscount = count($callers); $calleridx < $callerscount; ++$calleridx) {
			if ($calleridx == 0) {
				continue;
			}
			if ($calleridx < $callerscount) {
				$callerfunc = $callers[$calleridx + 1]['function'];
				if (!empty($callerfunc))
					$callerfunc = " ($callerfunc) ";
			}
			$data[] = 'CALLER: (' . $callers[$calleridx]['line'] . ') ' . $callers[$calleridx]['file'] . $callerfunc;
		}
		$stmt = $db->database->prepare($logQuery);
		$stmt->execute([$this->logSqlTimeID, $type, NULL, implode('\n', $data), $now]);
	}
}
