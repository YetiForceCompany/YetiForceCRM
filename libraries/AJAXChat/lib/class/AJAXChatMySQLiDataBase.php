<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Class to initialize the MySQL DataBase connection:
class AJAXChatDataBaseMySQLi {

	var $_connectionID;
	var $_errno = 0;
	var $_error = '';
	var $_dbName;

	function AJAXChatDataBaseMySQLi(&$dbConnectionConfig) {
		$this->_connectionID = $dbConnectionConfig['link'];
		$this->_dbName = $dbConnectionConfig['name'];
	}
	
	// Method to connect to the DataBase server:
	function connect(&$dbConnectionConfig) {
		@$this->_connectionID = new mysqli(
			$dbConnectionConfig['host'],
			$dbConnectionConfig['user'],
			$dbConnectionConfig['pass'],
			"",
			$dbConnectionConfig['port']
		);
		if($this->_connectionID->connect_errno) {
			$this->_errno = mysqli_connect_errno();
			$this->_error = mysqli_connect_error();
			return false;
		}
		return true;
	}
	
	// Method to select the DataBase:
	function select($dbName) {
		if(!$this->_connectionID->select_db($dbName)) {
			$this->_errno = $this->_connectionID->errno;
			$this->_error = $this->_connectionID->error;
			return false;
		}
		$this->_dbName = $dbName;
		return true;	
	}
	
	// Method to determine if an error has occured:
	function error() {
		return (bool)$this->_error;
	}
	
	// Method to return the error report:
	function getError() {
		if($this->error()) {
			$str = 'Error-Report: '	.$this->_error."\n";
			$str .= 'Error-Code: '.$this->_errno."\n";
		} else {
			$str = 'No errors.'."\n";
		}
		return $str;		
	}
	
	// Method to return the connection identifier:
	function &getConnectionID() {
		return $this->_connectionID;
	}
	
	// Method to prevent SQL injections:
	function makeSafe($value) {
		return "'".$this->_connectionID->escape_string($value)."'";
	}

	// Method to perform SQL queries:
	function sqlQuery($sql) {
		return new AJAXChatMySQLiQuery($sql, $this->_connectionID);
	}

	// Method to retrieve the current DataBase name:
	function getName() {
		return $this->_dbName;
	}

	// Method to retrieve the last inserted ID:
	function getLastInsertedID() {
		return $this->_connectionID->insert_id;
	}

}
?>