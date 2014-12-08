<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class ConfigReader {

	protected $properties = array();
	protected $name;

	static $propertiesCache = array();

	//TODO - Instead of path to file, we may have to support sending the array/map directly
	// which might be fetched from database or some other source. In that case, we will check
	// for the type of $source/$path and act accordingly.
	function  __construct($path, $name, $force = false) {
		$this->load($path, $name, $force);
	}

	function load($path, $name, $force = false) {
		$this->name = $path;
		if(!$force && self::$propertiesCache[$path]) {
			$this->properties = self::$propertiesCache[$path];
			return;
		}
		require $path;
		$this->properties = $$name;
		self::$propertiesCache[$path] = $this->properties;
	}

	function setConfig($key, $value) {
		if(empty($key)) return;
		$this->properties[$key] = $value;
		//not neccessary for php5.x versions
		self::$propertiesCache[$this->name] = $this->properties;
	}

	function getConfig($key) {
		if(empty($key)) return '';
		return $this->properties[$key];
	}
}

?>