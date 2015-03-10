<?php
/*********************************************************************************
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
 ********************************************************************************/
require_once 'include/logging.php';
include_once 'libraries/adodb/adodb.inc.php';
require_once 'libraries/adodb/adodb-xmlschema.inc.php';

$log =& LoggerManager::getLogger('VT');
$logsqltm =& LoggerManager::getLogger('SQLTIME');

// Callback class useful to convert PreparedStatement Question Marks to SQL value
// See function convertPS2Sql in PearDatabase below
class PreparedQMark2SqlValue {
	// Constructor
	function PreparedQMark2SqlValue($vals){
        $this->ctr = 0;
        $this->vals = $vals;
    }
    function call($matches){
            /**
             * If ? is found as expected in regex used in function convert2sql
             * /('[^']*')|(\"[^\"]*\")|([?])/
             *
             */
            if($matches[3]=='?'){
                    $this->ctr++;
                    return $this->vals[$this->ctr-1];
            }else{
                    return $matches[0];
            }
    }
}

class PearDatabase{
    var $database = null;
    var $dieOnError = false;
    var $dbType = null;
    var $dbHostName = null;
    var $dbName = null;
    var $dbOptions = null;
    var $userName=null;
    var $userPassword=null;
    var $query_time = 0;
    var $log = null;
    var $lastmysqlrow = -1;
    var $enableSQLlog = false;
    var $continueInstallOnError = true;

    // If you want to avoid executing PreparedStatement, set this to true
    // PreparedStatement will be converted to normal SQL statement for execution
	var $avoidPreparedSql = false;

	/**
	 * Performance tunning parameters (can be configured through performance.prefs.php)
	 * See the constructor for initialization
	 */
	var $isdb_default_utf8_charset = false;

	/**
	 * Manage instance usage of this class
	 */
	static function &getInstance() {
		global $adb, $log;

		if(!isset($adb)) {
			$adb = new self();
		}
		return $adb;
	}
	// END

    function isMySQL() { return (stripos($this->dbType ,'mysql') === 0);}
    function isOracle() { return $this->dbType=='oci8'; }
    function isPostgres() { return $this->dbType=='pgsql'; }

    function println($msg)
    {
		require_once('include/logging.php');
		$log1 = LoggerManager::getLogger('VT');
		if(is_array($msg)) {
		    $log1->info("PearDatabse ->".print_r($msg,true));
		} else {
		    $log1->info("PearDatabase ->".$msg);
		}
		return $msg;
    }

    function setDieOnError($value){	 $this->dieOnError = $value; }
    function setDatabaseType($type){ $this->dbType = $type; }
    function setUserName($name){ $this->userName = $name; }

    function setOption($name, $value){
		if(isset($this->dbOptions)) $this->dbOptions[$name] = $value;
		if(isset($this->database)) $this->database->setOption($name, $value);
    }

    function setUserPassword($pass){ $this->userPassword = $pass; }
    function setDatabaseName($db){ $this->dbName = $db;	}
    function setDatabaseHost($host){ $this->dbHostName = $host;	}

    function getDataSourceName(){
		return 	$this->dbType. "://".$this->userName.":".$this->userPassword."@". $this->dbHostName . "/". $this->dbName;
    }

    function startTransaction() {
	    if($this->isPostgres()) return;
		$this->checkConnection();
		$this->println("TRANS Started");
		$this->database->StartTrans();
    }

    function completeTransaction() {
	    if($this->isPostgres()) return;
		if($this->database->HasFailedTrans()) $this->println("TRANS  Rolled Back");
		else $this->println("TRANS  Commited");

		$this->database->CompleteTrans();
		$this->println("TRANS  Completed");
    }

    function hasFailedTransaction(){ return $this->database->HasFailedTrans();   }

    function checkError($msg='', $dieOnError=false) {
		if($this->dieOnError || $dieOnError) {
			$bt = debug_backtrace();
			$ut = array();
			foreach ($bt as $t) {
				$ut[] = array('file'=>$t['file'],'line'=>$t['line'],'function'=>$t['function']);
			}
			echo '<pre>';
			var_export($ut);
			echo '</pre>';
		    $this->println("ADODB error ".$msg."->[".$this->database->ErrorNo()."]".$this->database->ErrorMsg());
		    die ($msg."ADODB error ".$msg."->".$this->database->ErrorMsg());
		} else {
		    $this->println("ADODB error ".$msg."->[".$this->database->ErrorNo()."]".$this->database->ErrorMsg());
		}
		return false;
    }

    function change_key_case($arr) {
		return is_array($arr)?array_change_key_case($arr):$arr;
    }

    var $req_flist;
    function checkConnection(){
		global $log;

		if(!isset($this->database)) {
		    $this->println("TRANS creating new connection");
		    $this->connect(false);
		} else {
		    //$this->println("checkconnect using old connection");
		}
    }

	/* SQLTime logging */
	protected $logSqlTimingID = false;
	function logSqlTiming($startat, $endat, $sql, $params=false) {
        if(!PerformancePrefs::getBoolean('SQL_LOG_INCLUDE_CALLER', false)) {
        	return;
        }

		$today  = date('Y-m-d H:i:s'); $logtable = 'vtiger_sqltimelog';
		$logsql = 'INSERT INTO '.$logtable.'(id, type, started, ended, data, loggedon) VALUES (?,?,?,?,?,?)';

		if ($this->logSqlTimingID === false) {
			$this->logSqlTimingID = $this->getUniqueID($logtable);

			$type = (php_sapi_name() == 'cli') ? 'CLI' : 'REQ';
			$data = '';
			if (isset($_SERVER['REQUEST_METHOD'])) {
				$uri  = $_SERVER['REQUEST_URI'];
				$qmarkIndex = strpos($_SERVER['REQUEST_URI'], '?');
				if ($qmarkIndex !== false) $uri = substr($uri, 0, $qmarkIndex);
				$data = $uri . '?'. http_build_query($_SERVER['REQUEST_METHOD'] == 'GET'? $_GET:$_POST);
			} else if ($argv) {
				$data = implode(' ', $argv);
			}

			$this->database->Execute($logsql, array($this->logSqlTimingID, $type, NULL, NULL, $data, $today));
		}

		$type = 'SQL';
		$data = trim($sql);
		if (is_array($params) && !empty($params)) {
			$data .= "\n[" . implode(",", $params) . "]";
		}
		$this->database->Execute($logsql, array($this->logSqlTimingID, $type, $startat, $endat, $data, $today));

		$type = 'CALLERS';
		$data = array();
		$callers = debug_backtrace();
		for ($calleridx = 0, $callerscount = count($callers); $calleridx < $callerscount; ++$calleridx) {
			if ($calleridx == 0) {
				continue;
			}
			if ($calleridx < $callerscount) {
				$callerfunc = $callers[$calleridx+1]['function'];
				if (!empty($callerfunc)) $callerfunc = " ($callerfunc) ";
			}
			$data[] = "CALLER: (" . $callers[$calleridx]['line'] . ') ' . $callers[$calleridx]['file'] . $callerfunc;
		}
		$this->database->Execute($logsql, array($this->logSqlTimingID, $type, NULL, NULL, implode("\n", $data), $today));
	}

	/**
	 * Execute SET NAMES UTF-8 on the connection based on configuration.
	 */
	function executeSetNamesUTF8SQL($force = false) {
		global $default_charset;
		static $DEFAULTCHARSET = null;
		if ($DEFAULTCHARSET === null) $DEFAULTCHARSET = strtoupper($default_charset);
		
		// Performance Tuning: If database default charset is UTF-8, we don't need this
		if($DEFAULTCHARSET == 'UTF-8' && ($force || !$this->isdb_default_utf8_charset)) {

			$sql_start_time = microtime(true);

			$setnameSql = "SET NAMES utf8";
			$this->database->Execute($setnameSql);
			$this->logSqlTiming($sql_start_time, microtime(true), $setnameSql);
		}
	}

	/**
	 * Execute query in a batch.
	 *
	 * For example:
	 * INSERT INTO TABLE1 VALUES (a,b);
	 * INSERT INTO TABLE1 VALUES (c,d);
	 *
	 * like: INSERT INTO TABLE1 VALUES (a,b), (c,d)
	 */
	function query_batch($prefixsql, $valuearray) {
		if(PerformancePrefs::getBoolean('ALLOW_SQL_QUERY_BATCH')) {
			$sql = $prefixsql;
			$suffixsql = $valuearray;
			if(!is_array($valuearray)) $suffixsql = implode(',', $valuearray);
			$this->query($prefixsql . $suffixsql);
		} else {
			if(is_array($valuearray) && !empty($valuearray)) {
				foreach($valuearray as $suffixsql) {
					$this->query($prefixsql . $suffixsql);
				}
			}
		}
	}

    function query($sql, $dieOnError=false, $msg='')
    {
	global $log, $default_charset;

	$log->debug('query being executed : '.$sql);
	$this->checkConnection();

	$this->executeSetNamesUTF8SQL();

	$sql_start_time = microtime(true);
	$result = & $this->database->Execute($sql);
	$this->logSqlTiming($sql_start_time, microtime(true), $sql);

	$this->lastmysqlrow = -1;
	if(!$result)$this->checkError($msg.' Query Failed:' . $sql . '::', $dieOnError);

	return $result;
    }


	/**
	 * Convert PreparedStatement to SQL statement
	 */
	function convert2Sql($ps, $vals) {
		if(empty($vals)) { return $ps; }
		// TODO: Checks need to be added array out of bounds situations
		for($index = 0; $index < count($vals); $index++) {
            // Package import pushes data after XML parsing, so type-cast it
            if(is_a($vals[$index], 'SimpleXMLElement')) {
                $vals[$index] = (string) $vals[$index];
            }
			if(is_string($vals[$index])) {
				if($vals[$index] == '') {
					$vals[$index] = $this->database->Quote($vals[$index]);
				}
				else {
					$vals[$index] = "'".$this->sql_escape_string($vals[$index]). "'";
				}
			}
			if($vals[$index] === null) {
				$vals[$index] = "NULL";
			}
		}
		$sql = preg_replace_callback("/('[^']*')|(\"[^\"]*\")|([?])/", array(new PreparedQMark2SqlValue($vals),"call"), $ps);
		return $sql;
	}

  	/* ADODB prepared statement Execution
   	* @param $sql -- Prepared sql statement
   	* @param $params -- Parameters for the prepared statement
   	* @param $dieOnError -- Set to true, when query execution fails
   	* @param $msg -- Error message on query execution failure
   	*/
	function pquery($sql, $params=array(), $dieOnError=false, $msg='') {
		global $log, $default_charset;
		$log->debug('Prepared sql query being executed : '.$sql);
		$this->checkConnection();

		$this->executeSetNamesUTF8SQL();

		$sql_start_time = microtime(true);
		$params = $this->flatten_array($params);
		if (count($params) > 0) {
			$log->debug('Prepared sql query parameters : [' . implode(",", $params) . ']');
		}

		if($this->avoidPreparedSql || empty($params)) {
			$sql = $this->convert2Sql($sql, $params);
			$result = $this->database->Execute($sql);
		} else {
			$result = $this->database->Execute($sql, $params);
		}
		$sql_end_time = microtime(true);
		$this->logSqlTiming($sql_start_time, $sql_end_time, $sql, $params);

		$this->lastmysqlrow = -1;
		if(!$result)$this->checkError($msg.' Query Failed:' . $sql . '::', $dieOnError);

		return $result;
	}

	/**
	 * Flatten the composite array into single value.
	 * Example:
	 * $input = array(10, 20, array(30, 40), array('key1' => '50', 'key2'=>array(60), 70));
	 * returns array(10, 20, 30, 40, 50, 60, 70);
	 */
	function flatten_array($input, $output=null) {
		if($input == null) return null;
		if($output == null) $output = array();
		foreach($input as $value) {
			if(is_array($value)) {
				$output = $this->flatten_array($value, $output);
			} else {
				array_push($output, $value);
			}
		}
		return $output;
	}

    function getEmptyBlob($is_string=true)
    {
	//if(dbType=="oci8") return 'empty_blob()';
	//else return 'null';
	if (is_string) return 'null';
	return null;
    }

    function updateBlob($tablename, $colname, $id, $data)
    {
	$this->println("updateBlob t=".$tablename." c=".$colname." id=".$id);
	$this->checkConnection();
	$this->executeSetNamesUTF8SQL();

	$sql_start_time = microtime(true);
	$result = $this->database->UpdateBlob($tablename, $colname, $data, $id);
	$this->logSqlTiming($sql_start_time, microtime(true), "Update Blob $tablename, $colname, $id");

	$this->println("updateBlob t=".$tablename." c=".$colname." id=".$id." status=".$result);
	return $result;
    }

    function updateBlobFile($tablename, $colname, $id, $filename)
    {
	$this->println("updateBlobFile t=".$tablename." c=".$colname." id=".$id." f=".$filename);
	$this->checkConnection();
	$this->executeSetNamesUTF8SQL();

	$sql_start_time = microtime(true);
	$result = $this->database->UpdateBlobFile($tablename, $colname, $filename, $id);
	$this->logSqlTiming($sql_start_time, microtime(true), "Update Blob $tablename, $colname, $id");

	$this->println("updateBlobFile t=".$tablename." c=".$colname." id=".$id." f=".$filename." status=".$result);
	return $result;
    }

    function limitQuery($sql,$start,$count, $dieOnError=false, $msg='')
    {
	global $log;
	//$this->println("ADODB limitQuery sql=".$sql." st=".$start." co=".$count);
	$log->debug(' limitQuery sql = '.$sql .' st = '.$start .' co = '.$count);
	$this->checkConnection();

	$this->executeSetNamesUTF8SQL();

	$sql_start_time = microtime(true);
	$result =& $this->database->SelectLimit($sql,$count,$start);
	$this->logSqlTiming($sql_start_time, microtime(true), "$sql LIMIT $count, $start");

	if(!$result) $this->checkError($msg.' Limit Query Failed:' . $sql . '::', $dieOnError);
	return $result;
    }

    function getOne($sql, $dieOnError=false, $msg='')
    {
	$this->println("ADODB getOne sql=".$sql);
	$this->checkConnection();

	$this->executeSetNamesUTF8SQL();

	$sql_start_time = microtime(true);
	$result =& $this->database->GetOne($sql);
	$this->logSqlTiming($sql_start_time, microtime(true), "$sql GetONE");

	if(!$result) $this->checkError($msg.' Get one Query Failed:' . $sql . '::', $dieOnError);
	return $result;
    }

    function getFieldsDefinition(&$result)
    {
	//$this->println("ADODB getFieldsArray");
	$field_array = array();
	if(! isset($result) || empty($result))
	{
		return 0;
	}

	$i = 0;
	$n = $result->FieldCount();
	while ($i < $n)
	{
		$meta = $result->FetchField($i);
		if (!$meta)
		{
			return 0;
		}
		array_push($field_array,$meta);
		$i++;
	}

	//$this->println($field_array);
	return $field_array;
    }

    function getFieldsArray(&$result)
    {
	//$this->println("ADODB getFieldsArray");
	$field_array = array();
	if(! isset($result) || empty($result))
	{
	    return 0;
	}

	$i = 0;
	$n = $result->FieldCount();
	while ($i < $n)
	{
	    $meta = $result->FetchField($i);
	    if (!$meta)
	    {
		return 0;
	    }
	    array_push($field_array,$meta->name);
	    $i++;
	}

	//$this->println($field_array);
	return $field_array;
    }

    function getRowCount(&$result){
		global $log;
		if(isset($result) && !empty($result))
		    $rows= $result->RecordCount();
		return $rows;
    }

    /* ADODB newly added. replacement for mysql_num_rows */
    function num_rows(&$result) {
		return $this->getRowCount($result);
    }

    /* ADODB newly added. replacement form mysql_num_fields */
    function num_fields(&$result) {
		return $result->FieldCount();
    }

    /* ADODB newly added. replacement for mysql_fetch_array() */
    function fetch_array(&$result) {
		if($result->EOF) {
		    //$this->println("ADODB fetch_array return null");
		    return NULL;
		}
		$arr = $result->FetchRow();
        if(is_array($arr))
			$arr = array_map('to_html', $arr);
        return $this->change_key_case($arr);
    }

    ## adds new functions to the PearDatabase class to come around the whole
    ## broken query_result() idea
    ## Code-Contribution given by weigelt@metux.de - Starts
    function run_query_record_html($query) {
	    if (!is_array($rec = $this->run_query_record($query)))
	    	return $rec;
	    foreach ($rec as $walk => $cur)
	    	$r[$walk] = to_html($cur);
	    return $r;
    }

    function sql_quote($data) {
		if (is_array($data)) {
			switch($data{'type'}) {
			case 'text':
			case 'numeric':
			case 'integer':
			case 'oid':
				return $this->quote($data{'value'});
				break;
			case 'timestamp':
				return $this->formatDate($data{'value'});
				break;
			default:
				throw new Exception("unhandled type: ".serialize($cur));
			}
		} else
			return $this->quote($data);
    }

    function sql_insert_data($table, $data) {
		if (!$table)
			throw new Exception("missing table name");
		if (!is_array($data))
			throw new Exception("data must be an array");
		if (!count($table))
	    	throw new Exception("no data given");

		$sql_fields = '';
		$sql_data = '';
		foreach($data as $walk => $cur) {
			$sql_fields .= ($sql_fields?',':'').$walk;
			$sql_data   .= ($sql_data?',':'').$this->sql_quote($cur);
		}
		return 'INSERT INTO '.$table.' ('.$sql_fields.') VALUES ('.$sql_data.')';
    }

    function run_insert_data($table,$data) {
	    $query = $this->sql_insert_data($table,$data);
	    $res = $this->query($query);
	    $this->query("commit;");
    }

    function run_query_record($query) {
	    $result = $this->query($query);
	    if (!$result)
	    	return;
	    if (!is_object($result))
	    	throw new Exception("query \"$query\" failed: ".serialize($result));
	    $res = $result->FetchRow();
	    $rowdata = $this->change_key_case($res);
	    return $rowdata;
    }

    function run_query_allrecords($query) {
	    $result = $this->query($query);
	    $records = array();
	    $sz = $this->num_rows($result);
	    for ($i=0; $i<$sz; $i++)
			$records[$i] = $this->change_key_case($result->FetchRow());
	    return $records;
    }

    function run_query_field($query,$field='') {
	    $rowdata = $this->run_query_record($query);
	    if(isset($field) && $field != '')
	    	return $rowdata{$field};
	    else
	    	return array_shift($rowdata);
    }

    function run_query_list($query,$field){
	    $records = $this->run_query_allrecords($query);
	    foreach($records as $walk => $cur)
			$list[] = $cur{$field};
    }

    function run_query_field_html($query,$field){
	    return to_html($this->run_query_field($query,$field));
    }

    function result_get_next_record($result){
	    return $this->change_key_case($result->FetchRow());
    }

    // create an IN expression from an array/list
    function sql_expr_datalist($a) {
	    if (!is_array($a))
	    	throw new Exception("not an array");
	    if (!count($a))
	    	throw new Exception("empty arrays not allowed");

	    foreach($a as $walk => $cur)
	    	$l .= ($l?',':'').$this->quote($cur);
	    return ' ( '.$l.' ) ';
    }

    // create an IN expression from an record list, take $field within each record
    function sql_expr_datalist_from_records($a,$field) {
	    if (!is_array($a))
	    	throw new Exception("not an array");
	    if (!$field)
	    	throw new Exception("missing field");
	    if (!count($a))
	    	throw new Exception("empty arrays not allowed");

	    foreach($a as $walk => $cur)
	    	$l .= ($l?',':'').$this->quote($cur{$field});

	    return ' ( '.$l.' ) ';
    }

    function sql_concat($list) {
	    switch ($this->dbType) {
		    case 'mysql':
			    return 'concat('.implode(',',$list).')';
			case 'mysqli':
				return 'concat('.implode(',',$list).')';
		    case 'pgsql':
			    return '('.implode('||',$list).')';
		    default:
			    throw new Exception("unsupported dbtype \"".$this->dbType."\"");
	    }
    }
    ## Code-Contribution given by weigelt@metux.de - Ends

    /* ADODB newly added. replacement for mysql_result() */
    function query_result(&$result, $row, $col=0) {
		if (!is_object($result))
	                throw new Exception("result is not an object");
		$result->Move($row);
		$rowdata = $this->change_key_case($result->FetchRow());
		//$this->println($rowdata);
		//Commented strip_selected_tags and added to_html function for HTML tags vulnerability
		if($col == 'fieldlabel') $coldata = $rowdata[$col];
		else $coldata = to_html($rowdata[$col]);
		return $coldata;
    }
	
    function query_result_raw(&$result, $row, $col=0) {
		if (!is_object($result))
			throw new Exception("result is not an object");
		$result->Move($row);
		$rowdata = $this->change_key_case($result->FetchRow());
		$coldata = $rowdata[$col];
		return $coldata;
    }
	// Function to get particular row from the query result
	function query_result_rowdata(&$result, $row=0) {
		if (!is_object($result))
                throw new Exception("result is not an object");
		$result->Move($row);
		$rowdata = $this->change_key_case($result->FetchRow());

		foreach($rowdata as $col => $coldata) {
			if($col != 'fieldlabel')
				$rowdata[$col] = to_html($coldata);
		}
		return $rowdata;
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
	function raw_query_result_rowdata(&$result, $row=0) {
		if (!is_object($result))
                throw new Exception("result is not an object");
		$result->Move($row);
		$rowdata = $this->change_key_case($result->FetchRow());
		return $rowdata;
	}



    function getAffectedRowCount(&$result){
		global $log;
		$log->debug('getAffectedRowCount');
		$rows =$this->database->Affected_Rows();
		$log->debug('getAffectedRowCount rows = '.$rows);
		return $rows;
    }

    function requireSingleResult($sql, $dieOnError=false,$msg='', $encode=true) {
		$result = $this->query($sql, $dieOnError, $msg);

		if($this->getRowCount($result ) == 1)
	    	return $result;
		$this->log->error('Rows Returned:'. $this->getRowCount($result) .' More than 1 row returned for '. $sql);
		return '';
    }
	/* function which extends requireSingleResult api to execute prepared statment
	 */

    function requirePsSingleResult($sql, $params, $dieOnError=false,$msg='', $encode=true) {
		$result = $this->pquery($sql, $params, $dieOnError, $msg);

		if($this->getRowCount($result ) == 1)
	    	return $result;
		$this->log->error('Rows Returned:'. $this->getRowCount($result) .' More than 1 row returned for '. $sql);
		return '';
    }

    function fetchByAssoc(&$result, $rowNum = -1, $encode=true) {
		if($result->EOF) {
		    $this->println("ADODB fetchByAssoc return null");
		    return NULL;
		}
		if(isset($result) && $rowNum < 0) {
		    $row = $this->change_key_case($result->GetRowAssoc(false));
		    $result->MoveNext();
		    if($encode&& is_array($row))
				return array_map('to_html', $row);
		    return $row;
		}

		if($this->getRowCount($result) > $rowNum) {
		    $result->Move($rowNum);
		}
		$this->lastmysqlrow = $rowNum;
		$row = $this->change_key_case($result->GetRowAssoc(false));
		$result->MoveNext();
		$this->println($row);

		if($encode&& is_array($row))
			return array_map('to_html', $row);
		return $row;
    }

    function getNextRow(&$result, $encode=true){
		global $log;
		$log->info('getNextRow');
		if(isset($result)){
	    	$row = $this->change_key_case($result->FetchRow());
		    if($row && $encode&& is_array($row))
				return array_map('to_html', $row);
	    	return $row;
		}
		return null;
    }

    function fetch_row(&$result, $encode=true) {
		return $this->getNextRow($result);
    }

    function field_name(&$result, $col) {
		return $result->FetchField($col);
    }

    function getQueryTime(){
		return $this->query_time;
    }

    function connect($dieOnError = false) {
		global $dbconfigoption,$dbconfig;
		if(!isset($this->dbType)) {
		    $this->println("ADODB Connect : DBType not specified");
		    return;
		}
		$this->database = ADONewConnection($this->dbType);

		$result = $this->database->PConnect($this->dbHostName, $this->userName, $this->userPassword, $this->dbName);
		if ($result) {
			$this->database->LogSQL($this->enableSQLlog);

			// 'SET NAMES UTF8' needs to be executed even if database has default CHARSET UTF8
			// as mysql server might be running with different charset!
			// We will notice problem reading UTF8 characters otherwise.
			if($this->isdb_default_utf8_charset) {
				$this->executeSetNamesUTF8SQL(true);
			}
		}
	}

	/**
	 * Constructor
	 */
    function PearDatabase($dbtype = '', $host = '', $dbname = '', $username = '', $passwd = '') {
		global $currentModule;
		$this->log = LoggerManager::getLogger('PearDatabase_' . $currentModule);
		$this->resetSettings($dbtype, $host, $dbname, $username, $passwd);

		// Initialize performance parameters
		$this->isdb_default_utf8_charset = PerformancePrefs::getBoolean('DB_DEFAULT_CHARSET_UTF8');
		// END

		if (!isset($this->dbType)) {
			$this->println("ADODB Connect : DBType not specified");
			return;
		}
		$this->setDieOnError(SysDebug::get('SQL_DIE_ON_ERROR'));
	}

	function resetSettings($dbtype,$host,$dbname,$username,$passwd){
		global $dbconfig, $dbconfigoption;

		if($host == '') {
		    $this->disconnect();
		    $this->setDatabaseType($dbconfig['db_type']);
	    	$this->setUserName($dbconfig['db_username']);
		    $this->setUserPassword($dbconfig['db_password']);
		    $this->setDatabaseHost( $dbconfig['db_hostname']);
	    	$this->setDatabaseName($dbconfig['db_name']);
		    $this->dbOptions = $dbconfigoption;
		    if($dbconfig['log_sql'])
	    		$this->enableSQLlog = ($dbconfig['log_sql'] == true);
		} else {
		    $this->disconnect();
		    $this->setDatabaseType($dbtype);
	    	$this->setDatabaseName($dbname);
		    $this->setUserName($username);
		    $this->setUserPassword($passwd);
	    	$this->setDatabaseHost( $host);
		}
    }

    function quote($string){
		return $this->database->qstr($string);
    }

	function disconnect() {
		$this->println("ADODB disconnect");
		if(isset($this->database)){
			if($this->dbType == "mysql"){
				mysql_close($this->database->_connectionID);
			}else if($this->dbType=="mysqli"){
                mysqli_close($this->database->_connectionID);
			} 
			else {
				$this->database->disconnect();
			}
			unset($this->database);
		}
	}

    function setDebug($value) {
		$this->database->debug = $value;
    }

    // ADODB newly added methods
    function createTables($schemaFile, $dbHostName=false, $userName=false, $userPassword=false, $dbName=false, $dbType=false) {
		$this->println("ADODB createTables ".$schemaFile);
		if($dbHostName!=false) $this->dbHostName=$dbHostName;
		if($userName!=false) $this->userName=$userPassword;
		if($userPassword!=false) $this->userPassword=$userPassword;
		if($dbName!=false) $this->dbName=$dbName;
		if($dbType!=false) $this->dbType=$dbType;

		$this->checkConnection();
		$db = $this->database;
		$schema = new adoSchema( $db );
		//Debug Adodb XML Schema
		$schema->XMLS_DEBUG = TRUE;
		//Debug Adodb
		$schema->debug = true;
		$sql = $schema->ParseSchema( $schemaFile );

		$this->println("--------------Starting the table creation------------------");
		$result = $schema->ExecuteSchema( $sql, $this->continueInstallOnError );
		if($result) print $db->errorMsg();
		// needs to return in a decent way
		$this->println("ADODB createTables ".$schemaFile." status=".$result);
		return $result;
    }

    function createTable($tablename, $flds) {
		$this->println("ADODB createTable table=".$tablename." flds=".$flds);
		$this->checkConnection();
		$dict = NewDataDictionary($this->database);
		$sqlarray = $dict->CreateTableSQL($tablename, $flds);
		$result = $dict->ExecuteSQLArray($sqlarray);
		$this->println("ADODB createTable table=".$tablename." flds=".$flds." status=".$result);
		return $result;
    }

    function alterTable($tablename, $flds, $oper) {
		$this->println("ADODB alterTableTable table=".$tablename." flds=".$flds." oper=".$oper);
		$this->checkConnection();
		$dict = NewDataDictionary($this->database);

		if($oper == 'Add_Column') {
		    $sqlarray = $dict->AddColumnSQL($tablename, $flds);
		} else if($oper == 'Delete_Column') {
		    $sqlarray = $dict->DropColumnSQL($tablename, $flds);
		}
		$this->println("sqlarray");
		$this->println($sqlarray);

		$result = $dict->ExecuteSQLArray($sqlarray);

		$this->println("ADODB alterTableTable table=".$tablename." flds=".$flds." oper=".$oper." status=".$result);
		return $result;
    }

    function getColumnNames($tablename) {
		$this->println("ADODB getColumnNames table=".$tablename);
		$this->checkConnection();
		$adoflds = $this->database->MetaColumns($tablename);
		$i=0;
		foreach($adoflds as $fld) {
		    $colNames[$i] = $fld->name;
		    $i++;
		}
		return $colNames;
    }

    function formatString($tablename,$fldname, $str) {
		$this->checkConnection();
		$adoflds = $this->database->MetaColumns($tablename);

		foreach ( $adoflds as $fld ) {
		    if(strcasecmp($fld->name,$fldname)==0) {
				$fldtype =strtoupper($fld->type);
				if(strcmp($fldtype,'CHAR')==0 || strcmp($fldtype,'VARCHAR') == 0 || strcmp($fldtype,'VARCHAR2') == 0 || strcmp($fldtype,'LONGTEXT')==0 || strcmp($fldtype,'TEXT')==0) {
				    return $this->database->Quote($str);
				} else if(strcmp($fldtype,'DATE') ==0 || strcmp($fldtype,'TIMESTAMP')==0) {
				    return $this->formatDate($str);
				} else {
				    return $str;
				}
		    }
		}
		$this->println("format String Illegal field name ".$fldname);
		return $str;
    }

    function formatDate($datetime, $strip_quotes=false) {
		$this->checkConnection();
		$db = &$this->database;
		$date = $db->DBTimeStamp($datetime);
		/* remove single quotes to use the date as parameter for Prepared statement */
		if($strip_quotes == true) {
			return trim($date, "'");
		}
		return $date;
    }

    function getDBDateString($datecolname) {
		$this->checkConnection();
		$db = &$this->database;
		$datestr = $db->SQLDate("Y-m-d, H:i:s" ,$datecolname);
		return $datestr;
    }

    function getUniqueID($seqname) {
		$this->checkConnection();
		return $this->database->GenID($seqname."_seq",1);
	}

    function get_tables() {
		$this->checkConnection();
		$result = & $this->database->MetaTables('TABLES');
		$this->println($result);
		return $result;
    }

	//To get a function name with respect to the database type which escapes strings in given text
	function sql_escape_string($str)
	{
		if($this->isMySql()){
			$result_data = ($this->dbType=='mysqli')?mysqli_real_escape_string($this->database->_connectionID,$str):mysql_real_escape_string($str);
		}
		elseif($this->isPostgres())
			$result_data = pg_escape_string($str);
		return $result_data;
	}

	// Function to get the last insert id based on the type of database
	function getLastInsertID($seqname = '') {
		if($this->isPostgres()) {
			$result = pg_query("SELECT currval('".$seqname."_seq')");
			if($result)
			{
				$row = pg_fetch_row($result);
				$last_insert_id = $row[0];
			}
		} else {
			$last_insert_id = $this->database->Insert_ID();
		}
		return $last_insert_id;
	}

	// Function to escape the special characters in database name based on database type.
	function escapeDbName($dbName='') {
		if ($dbName == '')  $dbName = $this->dbName;
		if($this->isMySql()) {
			$dbName = "`{$dbName}`";
		}
		return $dbName;
	}

	function check_db_utf8_support() {
		global $db_type;
		if($db_type == 'pgsql')
			return true;
		$dbvarRS = $this->database->Execute("show variables like '%_database' ");
		$db_character_set = null;
		$db_collation_type = null;
		while(!$dbvarRS->EOF) {
			$arr = $dbvarRS->FetchRow();
			$arr = array_change_key_case($arr);
			switch($arr['variable_name']) {
				case 'character_set_database' : $db_character_set = $arr['value']; break;
				case 'collation_database'     : $db_collation_type = $arr['value']; break;
			}
			// If we have all the required information break the loop.
			if($db_character_set != null && $db_collation_type != null) break;
		}
		return (stristr($db_character_set, 'utf8') && stristr($db_collation_type, 'utf8'));
	}

	function get_db_charset() {
		global $db_type;
		if($db_type == 'pgsql')
			return 'UTF8';
		$dbvarRS = $this->database->query("show variables like '%_database' ");
		$db_character_set = null;
		while(!$dbvarRS->EOF) {
			$arr = $dbvarRS->FetchRow();
			$arr = array_change_key_case($arr);
			if($arr['variable_name'] == 'character_set_database') {
				$db_character_set = $arr['value'];
				break;
			}
		}
		return $db_character_set;
	}
} /* End of class */

if(empty($adb)) {
	$adb = new PearDatabase();
	$adb->connect();
	if (SysDebug::get('DISPLAY_SQL_QUERY')) {
		$adb->setDebug(true);
	}
}
//$adb->database->setFetchMode(ADODB_FETCH_BOTH);