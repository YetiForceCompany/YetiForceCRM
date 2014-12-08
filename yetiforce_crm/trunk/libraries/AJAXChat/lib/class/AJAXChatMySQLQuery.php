<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Class to perform SQL (MySQL) queries:
class AJAXChatMySQLQuery {

	var $_connectionID;
	var $_sql = '';
	var $_result = 0;
	var $_errno = 0;
	var $_error = '';

	// Constructor:
	function AJAXChatMySQLQuery($sql, $connectionID = null) {
		$this->_sql = trim($sql);
		$this->_connectionID = $connectionID;
		if($this->_connectionID) {
			$this->_result = mysql_query($this->_sql, $this->_connectionID);
			if(!$this->_result) {
				$this->_errno = mysql_errno($this->_connectionID);
				$this->_error = mysql_error($this->_connectionID);
			}
		} else {
			$this->_result = mysql_query($this->_sql);
			if(!$this->_result) {
				$this->_errno = mysql_errno();
				$this->_error = mysql_error();
			}	
		}
	}

	// Returns true if an error occured:
	function error() {
		// Returns true if the Result-ID is valid:
		return !(bool)($this->_result);
	}

	// Returns an Error-String:
	function getError() {
		if($this->error()) {
			$str  = 'Query: '	 .$this->_sql  ."\n";
			$str .= 'Error-Report: '	.$this->_error."\n";
			$str .= 'Error-Code: '.$this->_errno;
		} else {
			$str = "No errors.";
		}
		return $str;
	}

	// Returns the content:
	function fetch() {
		if($this->error()) {
			return null;
		} else {
			return mysql_fetch_assoc($this->_result);
		}
	}

	// Returns the number of rows (SELECT or SHOW):
	function numRows() {
		if($this->error()) {
			return null;
		} else {
			return mysql_num_rows($this->_result);
		}
	}

	// Returns the number of affected rows (INSERT, UPDATE, REPLACE or DELETE):
	function affectedRows() {
		if($this->error()) {
			return null;
		} else {
			return mysql_affected_rows($this->_connectionID);
		}
	}

	// Frees the memory:
	function free() {
		@mysql_free_result($this->_result);
	}
	
}
?>