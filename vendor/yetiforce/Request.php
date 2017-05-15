<?php
/**
 * Request basic class
 * @package YetiForce.App
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @copyright YetiForce Sp. z o.o.
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
namespace App;

/**
 * Request basic class
 */
class Request
{

	/**
	 * Cleared request data
	 * @var array 
	 */
	protected $parseValues = [];

	/**
	 * Raw request data
	 * @var array 
	 */
	protected $rawValues = [];

	/**
	 * Headers request
	 * @var array 
	 */
	protected $headers;

	/**
	 * Self instance
	 * @var Request 
	 */
	protected static $request;

	/**
	 * Constructor
	 * @param array $rawValues
	 * @param array $parseValues
	 */
	public function __construct($rawValues, $parseValues = [])
	{
		$this->rawValues = $rawValues;
		if ($parseValues) {
			$this->parseValues = $parseValues;
		}
		static::$request = $this;
	}

	/**
	 * Function to get the value for a given key
	 * @param string $key
	 * @param mixed $value Default value
	 * @return mixed
	 */
	public function get($key, $value = '')
	{
		if (isset($this->parseValues[$key])) {
			return $this->parseValues[$key];
		}
		if (isset($this->rawValues[$key])) {
			$value = $this->rawValues[$key];
		}
		if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
			if ($decodeValue = Json::decode($value)) {
				$value = $decodeValue;
			}
		}
		if ($value) {
			$value = Purifier::purify($value);
		}
		return $this->parseValues[$key] = $value;
	}

	/**
	 * Function to get the boolean value for a given key
	 * @param string $key
	 * @param mixed $defaultValue Default value
	 * @return boolean
	 */
	public function getBoolean($key, $defaultValue = '')
	{
		$value = $this->get($key, $defaultValue);
		if (is_bool($value)) {
			return $value;
		}
		return strcasecmp('true', (string) $value) === 0;
	}

	/**
	 * Function to get the integer value for a given key
	 * @param string $key
	 * @param integer $value
	 * @return integer
	 */
	public function getInteger($key, $value = 0)
	{
		if (isset($this->parseValues[$key])) {
			return $this->parseValues[$key];
		}
		if (isset($this->rawValues[$key])) {
			$value = $this->rawValues[$key];
		}
		settype($value, 'integer');
		return $this->parseValues[$key] = $value;
	}

	/**
	 * Function to get the array values for a given key
	 * @param string $key
	 * @param array $value
	 * @return array
	 */
	public function getArray($key, $value = [])
	{
		if (isset($this->parseValues[$key])) {
			return $this->parseValues[$key];
		}
		if (isset($this->rawValues[$key])) {
			$value = $this->rawValues[$key];
			if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
				if ($decodeValue = Json::decode($value)) {
					$value = $decodeValue;
				}
			}
			settype($value, 'array');
			if ($value) {
				$value = Purifier::purify($value);
			}
			return $this->parseValues[$key] = $value;
		}
		return $value;
	}

	/**
	 * Function to get html the value for a given key
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	public function getForHtml($key, $value = '')
	{
		if (isset($this->parseValues["html_$key"])) {
			return $this->parseValues["html_$key"];
		}
		if (isset($this->rawValues[$key])) {
			$value = $this->rawValues[$key];
		}
		if ($value) {
			$value = \App\Purifier::purifyHtml($value);
		}
		return $this->parseValues["html_$key"] = $value;
	}

	/**
	 * Function to get the value if its safe to use for SQL Query (column).
	 * @param string $key
	 * @param boolean $skipEmtpy
	 * @return string
	 */
	public function getForSql($key, $skipEmtpy = true)
	{
		return Purifier::purifySql($this->get($key), $skipEmtpy);
	}

	/**
	 * Function to get the request mode 
	 * @return string
	 */
	public function getMode()
	{
		return $this->get('mode');
	}

	/**
	 * Get all data
	 * @return array
	 */
	public function getAll()
	{
		foreach ($this->rawValues as $key => $value) {
			$this->get($key);
		}
		return $this->parseValues;
	}

	/**
	 * Get all raw data
	 * @return array
	 */
	public function getAllRaw()
	{
		return $this->rawValues;
	}

	/**
	 * Get raw value
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getRaw($key, $defaultValue = '')
	{
		if (isset($this->rawValues[$key])) {
			return $this->rawValues[$key];
		}
		if (isset($this->parseValues[$key])) {
			return $this->parseValues[$key];
		}
		return $defaultValue;
	}

	/**
	 * Get all headers
	 * @return string[]
	 */
	public function getHeaders()
	{
		if (isset($this->headers)) {
			return $this->headers;
		}
		$headers = [];
		if (!function_exists('apache_request_headers')) {
			foreach ($_SERVER as $key => $value) {
				if (substr($key, 0, 5) === 'HTTP_') {
					$key = str_replace(' ', '-', strtoupper(str_replace('_', ' ', substr($key, 5))));
					$headers[$key] = $value;
				} else {
					$headers[$key] = $value;
				}
			}
		} else {
			$headers = array_change_key_case(apache_request_headers(), CASE_UPPER);
		}
		return $this->headers = $headers;
	}

	/**
	 * Get header for a given key
	 * @param string $key
	 * @return string
	 */
	public function getHeader($key)
	{
		if (!isset($this->headers)) {
			$this->getHeaders();
		}
		return isset($this->headers[$key]) ? $this->headers[$key] : null;
	}

	/**
	 * Get request method
	 * @return string
	 * @throws Exceptions\AppException
	 */
	public function getRequestMethod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD'])) {
			if ($_SERVER['HTTP_X_HTTP_METHOD'] === 'DELETE') {
				$method = 'DELETE';
			} else if ($_SERVER['HTTP_X_HTTP_METHOD'] === 'PUT') {
				$method = 'PUT';
			} else {
				throw new Exceptions\AppException('Unexpected Header');
			}
		}
		return $method;
	}

	/**
	 * Get module name
	 * @param boolean $raw
	 * @return string
	 */
	public function getModule($raw = true)
	{
		$moduleName = $this->get('module');
		if (!$raw) {
			$parentModule = $this->get('parent');
			if ($parentModule === 'Settings') {
				$moduleName = "$parentModule:$moduleName";
			}
		}
		return $moduleName;
	}

	/**
	 * Check for existence of key
	 * @param string $key
	 * @return boolean
	 */
	public function has($key)
	{
		return isset($this->rawValues[$key]) || isset($this->parseValues[$key]);
	}

	/**
	 * Function to check if the key is empty.
	 * @param string $key
	 * @return boolean
	 */
	public function isEmpty($key)
	{
		if (isset($this->parseValues[$key])) {
			return $this->parseValues[$key] === '';
		}
		return $this->get($key, '') === '';
	}

	/**
	 * Function to set the value for a given key
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->parseValues[$key] = $value;
		return $this;
	}

	/**
	 * Function to remove the value for a given key 
	 * @param string $key
	 */
	public function delete($key)
	{
		if (isset($this->parseValues[$key])) {
			unset($this->parseValues[$key]);
		}
		if (isset($this->rawValues[$key])) {
			unset($this->rawValues[$key]);
		}
	}

	/**
	 * Function to check if the ajax request.
	 * @return boolean
	 */
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
	 * Validating read access request
	 * @throws \Exception\Csrf
	 */
	public function validateReadAccess()
	{
		$user = vglobal('current_user');
		// Referer check if present - to over come 
		if (isset($_SERVER['HTTP_REFERER']) && $user) {//Check for user post authentication.
			if ((stripos($_SERVER['HTTP_REFERER'], \AppConfig::main('site_URL')) !== 0) && ($this->get('module') != 'Install')) {
				throw new \Exception\Csrf('Illegal request');
			}
		}
	}

	/**
	 * Validating write access request
	 * @param boolean $skipRequestTypeCheck
	 * @throws \Exception\Csrf
	 */
	public function validateWriteAccess($skipRequestTypeCheck = false)
	{
		if (!$skipRequestTypeCheck) {
			if ($_SERVER['REQUEST_METHOD'] !== 'POST')
				throw new \Exception\Csrf('Invalid request - validate Write Access');
		}
		$this->validateReadAccess();
		if (!\CSRF::check(false)) {
			throw new \Exception\Csrf('Unsupported request');
		}
	}

	/**
	 * Static instance initialization
	 * @param boolean|array $request
	 * @return Request
	 */
	public static function init($request = false)
	{
		if (!static::$request) {
			static::$request = new self($request ? $request : $_REQUEST);
		}
		return static::$request;
	}

	/**
	 * Support static methods, all functions must start with "_"
	 * @param string $name
	 * @param null|array $arguments
	 * @return mied
	 * @throws Exceptions\AppException
	 */
	public static function __callStatic($name, $arguments = null)
	{
		if (!static::$request) {
			self::init();
		}
		$function = ltrim($name, '_');
		if (!method_exists(static::$request, $function)) {
			throw new Exceptions\AppException('Method not found');
		}
		if (empty($arguments)) {
			return static::$request->$function();
		} else {
			$first = array_shift($arguments);
			if (empty($arguments)) {
				return static::$request->$function($first);
			}
			return static::$request->$function($first, $arguments[0]);
		}
	}
}
