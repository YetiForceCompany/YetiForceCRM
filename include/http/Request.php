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
	protected $valueMap = [];
	protected $rawValueMap = [];
	protected $defaultmap = [];
	protected $headers = [];

	/**
	 * Default constructor
	 */
	public function __construct($values, $rawvalues = [], $stripifgpc = true)
	{
		$this->rawValueMap = $values;
		if ($stripifgpc && !empty($this->rawValueMap) && get_magic_quotes_gpc()) {
			$this->rawValueMap = $this->stripslashes_recursive($this->rawValueMap);
		}
	}

	/**
	 * Strip the slashes recursively on the values.
	 */
	public function stripslashes_recursive($value)
	{
		$value = is_array($value) ? array_map(array($this, 'stripslashes_recursive'), $value) : stripslashes($value);
		return $value;
	}

	/**
	 * Get key value (otherwise default value)
	 */
	public function get($key, $defvalue = '')
	{
		$value = $defvalue;
		if (isset($this->valueMap[$key])) {
			return $this->valueMap[$key];
		}
		if (isset($this->rawValueMap[$key])) {
			$value = $this->rawValueMap[$key];
		}
		if ($value === '' && isset($this->defaultmap[$key])) {
			$value = $this->defaultmap[$key];
		}

		$isJSON = false;
		if (is_string($value)) {
			// NOTE: Zend_Json or json_decode gets confused with big-integers (when passed as string)
			// and convert them to ugly exponential format - to overcome this we are performin a pre-check
			if (strpos($value, '[') === 0 || strpos($value, '{') === 0) {
				$isJSON = true;
			}
		}
		if ($isJSON) {
			$decodeValue = \App\Json::decode($value);
			if (isset($decodeValue)) {
				$value = $decodeValue;
			}
		}

		//Handled for null because App\Purifier::purify returns empty string
		if (!empty($value)) {
			$value = App\Purifier::purify($value);
		}
		$this->valueMap[$key] = $value;
		return $value;
	}

	/**
	 * Get value for key as boolean
	 */
	public function getBoolean($key, $defvalue = '')
	{
		return strcasecmp('true', $this->get($key, $defvalue) . '') === 0;
	}

	/**
	 * Function to get the value if its safe to use for SQL Query (column).
	 * @param string $key
	 * @param boolean $skipEmpty - Skip the check if string is empty
	 * @return Value for the given key
	 */
	public function getForSql($key, $skipEmtpy = true)
	{
		return \App\Purifier::purifySql($this->get($key), $skipEmtpy);
	}

	public function getForHtml($key, $defvalue = '')
	{
		$value = $defvalue;
		if (isset($this->rawValueMap[$key])) {
			$value = $this->rawValueMap[$key];
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
			$decodeValue = \App\Json::decode($value);
			if (isset($decodeValue)) {
				$value = $decodeValue;
			}
		}

		//Handled for null because \App\Purifier::purifyHtml returns empty string
		if (!empty($value)) {
			$value = \App\Purifier::purifyHtml($value);
		}
		return $value;
	}

	/**
	 * Get data map
	 */
	public function getAllRaw()
	{
		return $this->rawValueMap;
	}

	/**
	 * Get data map
	 */
	public function getAll()
	{
		foreach ($this->rawValueMap as $key => $value) {
			$this->get($key);
		}
		return $this->valueMap;
	}

	/**
	 * Check for existence of key
	 */
	public function has($key)
	{
		return isset($this->rawValueMap[$key]) || isset($this->valueMap[$key]);
	}

	/**
	 * Is the value (linked to key) empty?
	 */
	public function isEmpty($key)
	{
		if (isset($this->rawValueMap[$key])) {
			return empty($this->rawValueMap[$key]);
		}
		return true;
	}

	/**
	 * Get the raw value (if present) ignoring primary value.
	 */
	public function getRaw($key, $defvalue = '')
	{
		if (isset($this->rawValueMap[$key])) {
			return $this->rawValueMap[$key];
		}
		return $this->get($key, $defvalue);
	}

	/**
	 * Set the value for key
	 */
	public function set($key, $newvalue)
	{
		$this->valueMap[$key] = $newvalue;
	}

	/**
	 * Set the value for key
	 */
	public function delete($key)
	{
		unset($this->valueMap[$key]);
		unset($this->rawValueMap[$key]);
	}

	/**
	 * Set the value for key, both in the object as well as global $_REQUEST variable
	 */
	public function setGlobal($key, $newvalue)
	{
		$this->set($key, $newvalue);
	}

	/**
	 * Set default value for key
	 */
	public function setDefault($key, $defvalue)
	{
		$this->defaultmap[$key] = $defvalue;
	}

	/**
	 * Shorthand function to get value for (key=_operation|operation)
	 */
	public function getOperation()
	{
		return $this->get('_operation', $this->get('operation'));
	}

	/**
	 * Shorthand function to get value for (key=_session)
	 */
	public function getSession()
	{
		return $this->get('_session', $this->get('session'));
	}

	/**
	 * Shorthand function to get value for (key=mode)
	 */
	public function getMode()
	{
		return $this->get('mode');
	}

	public function getHeaders()
	{
		if (!empty($this->headers)) {
			return $this->headers;
		}
		if (!function_exists('apache_request_headers')) {
			foreach ($_SERVER as $key => $value) {
				if (substr($key, 0, 5) == 'HTTP_') {
					$key = str_replace(' ', '-', strtoupper(str_replace('_', ' ', substr($key, 5))));
					$out[$key] = $value;
				} else {
					$out[$key] = $value;
				}
			}
			$headers = $out;
		} else {
			$headers = array_change_key_case(apache_request_headers(), CASE_UPPER);
		}
		$this->headers = $headers;
		return $headers;
	}

	public function getHeader($key)
	{
		if (empty($this->headers)) {
			$this->getHeaders();
		}
		return isset($this->headers[$key]) ? $this->headers[$key] : null;
	}

	public function getRequestMetod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$method = 'DELETE';
			} else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
				$method = 'PUT';
			} else {
				throw new APIException('Unexpected Header');
			}
		}
		return $method;
	}

	public function getModule($raw = true)
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

	public function isAjax()
	{
		if (!empty($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] === true) {
			return true;
		} elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			return true;
		}
		return false;
	}

	/**
	 * Validating incoming request.
	 */
	public function validateReadAccess()
	{
		$this->validateReferer();
		return true;
	}

	public function validateWriteAccess($skipRequestTypeCheck = false)
	{
		if (!$skipRequestTypeCheck) {
			if ($_SERVER['REQUEST_METHOD'] != 'POST')
				throw new \Exception\Csrf('Invalid request - validate Write Access');
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
				throw new \Exception\Csrf('Illegal request');
			}
		}
		return true;
	}

	protected function validateCSRF()
	{
		if (!CSRF::check(false)) {
			throw new \Exception\Csrf('Unsupported request');
		}
	}
}

class AppRequest
{

	private static $request = false;

	public static function init()
	{
		if (!static::$request) {
			static::$request = new Vtiger_Request($_REQUEST, $_REQUEST);
		}
		return static::$request;
	}

	public static function get($key, $defvalue = '')
	{
		if (!static::$request) {
			static::init();
		}
		return static::$request->get($key, $defvalue);
	}

	public static function has($key)
	{
		if (!static::$request) {
			static::init();
		}
		return static::$request->has($key);
	}

	public static function getForSql($key, $skipEmtpy = true)
	{
		if (!static::$request) {
			static::init();
		}
		return static::$request->getForSql($key, $skipEmtpy);
	}

	public static function set($key, $value)
	{
		if (!static::$request) {
			static::init();
		}
		return static::$request->set($key, $value);
	}

	public static function isEmpty($key)
	{
		if (!static::$request) {
			static::init();
		}
		return static::$request->isEmpty($key);
	}

	public static function isAjax()
	{
		if (!empty($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] === true) {
			return true;
		} elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			return true;
		}
		return false;
	}
}
