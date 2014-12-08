<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

// Import dependencies
include_once 'libraries/HTTP_Session/Session.php';

/**
 * Session class
 */
class Vtiger_Session {

	/**
	 * Constructor
	 * Avoid creation of instances.
	 */
	private function __construct() {
	}

	/**
	 * Destroy session
	 */
	static function destroy($sessionid = false) {
		HTTP_Session::destroy($sessionid);
	}

	/**
	 * Initialize session
	 */
	static function init($sessionid = false) {
		if(empty($sessionid)) {
			HTTP_Session::start(null, null);
			$sessionid = HTTP_Session::id();
		} else {
			HTTP_Session::start(null, $sessionid);
		}

		if(HTTP_Session::isIdle() || HTTP_Session::isExpired()) {
			return false;
		}
		return $sessionid;
	}

	/**
	 * Is key defined in session?
	 */
	static function has($key) {
		return HTTP_Session::is_set($key);
	}

	/**
	 * Get value for the key.
	 */
	static function get($key, $defvalue = '') {
		return HTTP_Session::get($key, $defvalue);
	}

	/**
	 * Set value for the key.
	 */
	static function set($key, $value) {
		HTTP_Session::set($key, $value);
	}

}