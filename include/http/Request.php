<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_Request
{

	// Datastore
	private $valuemap;
	private $rawvaluemap;
	private $defaultmap = array();

	/**
	 * Default constructor
	 */
	function __construct($values, $rawvalues = array(), $stripifgpc = true)
	{
		$this->valuemap = $values;
		$this->rawvaluemap = $rawvalues;
		if ($stripifgpc && !empty($this->valuemap) && get_magic_quotes_gpc()) {
			$this->valuemap = $this->stripslashes_recursive($this->valuemap);
			$this->rawvaluemap = $this->stripslashes_recursive($this->rawvaluemap);
		}
	}

	/**
	 * Strip the slashes recursively on the values.
	 */
	function stripslashes_recursive($value)
	{
		$value = is_array($value) ? array_map(array($this, 'stripslashes_recursive'), $value) : stripslashes($value);
		return $value;
	}

	/**
	 * Get key value (otherwise default value)
	 */
	function get($key, $defvalue = '')
	{
		$value = $defvalue;
		if (isset($this->valuemap[$key])) {
			$value = $this->valuemap[$key];
		}
		if ($value === '' && isset($this->defaultmap[$key])) {
			$value = $this->defaultmap[$key];
		}

		$isJSON = false;
		if (is_string($value)) {
			// NOTE: Zend_Json or json_decode gets confused with big-integers (when passed as string)
			// and convert them to ugly exponential format - to overcome this we are performin a pre-check
			if (strpos($value, "[") === 0 || strpos($value, "{") === 0) {
				$isJSON = true;
			}
		}
		if ($isJSON) {
			$oldValue = Zend_Json::$useBuiltinEncoderDecoder;
			Zend_Json::$useBuiltinEncoderDecoder = false;
			$decodeValue = Zend_Json::decode($value);
			if (isset($decodeValue)) {
				$value = $decodeValue;
			}
			Zend_Json::$useBuiltinEncoderDecoder = $oldValue;
		}

		//Handled for null because vtlib_purify returns empty string
		if (!empty($value)) {
			$value = vtlib_purify($value);
		}
		return $value;
	}

	/**
	 * Get value for key as boolean
	 */
	function getBoolean($key, $defvalue = '')
	{
		return strcasecmp('true', $this->get($key, $defvalue) . '') === 0;
	}

	/**
	 * Function to get the value if its safe to use for SQL Query (column).
	 * @param <String> $key
	 * @param <Boolean> $skipEmpty - Skip the check if string is empty
	 * @return Value for the given key
	 */
	public function getForSql($key, $skipEmtpy = true)
	{
		return Vtiger_Util_Helper::validateStringForSql($this->get($key), $skipEmtpy);
	}

	function getForHtml($key, $defvalue = '')
	{
		$value = $defvalue;
		if (isset($this->valuemap[$key])) {
			$value = $this->valuemap[$key];
		}
		if ($value === '' && isset($this->defaultmap[$key])) {
			$value = $this->defaultmap[$key];
		}

		$isJSON = false;
		if (is_string($value)) {
			// NOTE: Zend_Json or json_decode gets confused with big-integers (when passed as string)
			// and convert them to ugly exponential format - to overcome this we are performin a pre-check
			if (strpos($value, "[") === 0 || strpos($value, "{") === 0) {
				$isJSON = true;
			}
		}
		if ($isJSON) {
			$oldValue = Zend_Json::$useBuiltinEncoderDecoder;
			Zend_Json::$useBuiltinEncoderDecoder = false;
			$decodeValue = Zend_Json::decode($value);
			if (isset($decodeValue)) {
				$value = $decodeValue;
			}
			Zend_Json::$useBuiltinEncoderDecoder = $oldValue;
		}

		//Handled for null because vtlib_purifyForHtml returns empty string
		if (!empty($value)) {
			$value = vtlib_purifyForHtml($value);
		}
		return $value;
	}
	
	/**
	 * Get data map
	 */
	function getAll()
	{
		return $this->valuemap;
	}

	/**
	 * Check for existence of key
	 */
	function has($key)
	{
		return isset($this->valuemap[$key]);
	}

	/**
	 * Is the value (linked to key) empty?
	 */
	function isEmpty($key)
	{
		$value = $this->get($key);
		return empty($value);
	}

	/**
	 * Get the raw value (if present) ignoring primary value.
	 */
	function getRaw($key, $defvalue = '')
	{
		if (isset($this->rawvaluemap[$key])) {
			return $this->rawvaluemap[$key];
		}
		return $this->get($key, $defvalue);
	}

	/**
	 * Set the value for key
	 */
	function set($key, $newvalue)
	{
		$this->valuemap[$key] = $newvalue;
	}

	/**
	 * Set the value for key, both in the object as well as global $_REQUEST variable
	 */
	function setGlobal($key, $newvalue)
	{
		$this->set($key, $newvalue);
		// TODO - This needs to be cleaned up once core apis are made independent of REQUEST variable.
		// This is added just for backward compatibility
		$_REQUEST[$key] = $newvalue;
	}

	/**
	 * Set default value for key
	 */
	function setDefault($key, $defvalue)
	{
		$this->defaultmap[$key] = $defvalue;
	}

	/**
	 * Shorthand function to get value for (key=_operation|operation)
	 */
	function getOperation()
	{
		return $this->get('_operation', $this->get('operation'));
	}

	/**
	 * Shorthand function to get value for (key=_session)
	 */
	function getSession()
	{
		return $this->get('_session', $this->get('session'));
	}

	/**
	 * Shorthand function to get value for (key=mode)
	 */
	function getMode()
	{
		return $this->get('mode');
	}

	function getModule($raw = true)
	{
		$moduleName = $this->get('module');
		if (!$raw) {
			$parentModule = $this->get('parent');
			if (!empty($parentModule) && $parentModule == 'Settings') {
				$moduleName = $parentModule . ':' . $moduleName;
			}
		}
		return $moduleName;
	}

	function isAjax()
	{
		if (!empty($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] == true) {
			return true;
		} elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			return true;
		}
		return false;
	}

	/**
	 * Validating incoming request.
	 */
	function validateReadAccess()
	{
		$this->validateReferer();
		// TODO validateIP restriction?
		return true;
	}

	function validateWriteAccess($skipRequestTypeCheck = false)
	{
		if (!$skipRequestTypeCheck) {
			if ($_SERVER['REQUEST_METHOD'] != 'POST')
				throw new CsrfException('Invalid request - validate Write Access');
		}
		$this->validateReadAccess();
		$this->validateCSRF();
		return true;
	}

	protected function validateReferer()
	{
		$user = vglobal('current_user');
		// Referer check if present - to over come 
		if (isset($_SERVER['HTTP_REFERER']) && $user) {//Check for user post authentication.
			if ((stripos($_SERVER['HTTP_REFERER'], AppConfig::main('site_URL')) !== 0) && ($this->get('module') != 'Install')) {
				throw new CsrfException('Illegal request');
			}
		}
		return true;
	}

	protected function validateCSRF()
	{
		if (!csrf_check(false)) {
			throw new CsrfException('Unsupported request');
		}
	}
}
