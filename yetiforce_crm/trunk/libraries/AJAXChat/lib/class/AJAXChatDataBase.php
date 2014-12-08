<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Class to initialize the DataBase connection:
class AJAXChatDataBase {

	var $_db;

	function AJAXChatDataBase(&$dbConnectionConfig) {
		switch($dbConnectionConfig['type']) {
			case 'mysqli':
				$this->_db = new AJAXChatDatabaseMySQLi($dbConnectionConfig);
				break;
			case 'mysql':
				$this->_db = new AJAXChatDatabaseMySQL($dbConnectionConfig);
				break;
			default:
				// Use MySQLi if available, else MySQL (and check the type of a given database connection object):
				if(function_exists('mysqli_connect') && (!$dbConnectionConfig['link'] || is_object($dbConnectionConfig['link']))) {
					$this->_db = new AJAXChatDatabaseMySQLi($dbConnectionConfig);
				} else {
					$this->_db = new AJAXChatDatabaseMySQL($dbConnectionConfig);	
				}
		}
	}
	
	// Method to connect to the DataBase server:
	function connect(&$dbConnectionConfig) {
		return $this->_db->connect($dbConnectionConfig);
	}
	
	// Method to select the DataBase:
	function select($dbName) {
		return $this->_db->select($dbName);
	}
	
	// Method to determine if an error has occured:
	function error() {
		return $this->_db->error();
	}
	
	// Method to return the error report:
	function getError() {
		return $this->_db->getError();
	}
	
	// Method to return the connection identifier:
	function &getConnectionID() {
		return $this->_db->getConnectionID();
	}
	
	// Method to prevent SQL injections:
	function makeSafe($value) {
		return $this->_db->makeSafe($value);
	}

	// Method to perform SQL queries:
	function sqlQuery($sql) {
		return $this->_db->sqlQuery($sql);
	}
	
	// Method to retrieve the current DataBase name:
	function getName() {
		return $this->_db->getName(); 
		//If your database has hyphens ( - ) in it, try using this instead:
		//return '`'.$this->_db->getName().'`'; 
	}

	// Method to retrieve the last inserted ID:
	function getLastInsertedID() {
		return $this->_db->getLastInsertedID();
	}

}
?>