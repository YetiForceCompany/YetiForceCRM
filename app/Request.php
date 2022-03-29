<?php
/**
 * Request basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Request basic class.
 */
class Request
{
	/**
	 * Raw request data.
	 *
	 * @var array
	 */
	protected $rawValues = [];

	/**
	 * Headers request.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * Self instance.
	 *
	 * @var Request
	 */
	protected static $request;

	/**
	 * Purified request values for get.
	 *
	 * @var array
	 */
	protected $purifiedValuesByGet = [];

	/**
	 * Purified request values for type.
	 *
	 * @var array
	 */
	protected $purifiedValuesByType = [];

	/**
	 * Purified request values for integer.
	 *
	 * @var array
	 */
	protected $purifiedValuesByInteger = [];

	/**
	 * Purified request values for array.
	 *
	 * @var array
	 */
	protected $purifiedValuesByArray = [];

	/**
	 * Purified request values for exploded.
	 *
	 * @var array
	 */
	protected $purifiedValuesByExploded = [];

	/**
	 * Purified request values for multi dimension array.
	 *
	 * @var array
	 */
	protected $purifiedValuesByMultiDimension = [];

	/**
	 * Purified request values for date range.
	 *
	 * @var array
	 */
	protected $purifiedValuesByDateRange = [];

	/**
	 * Purified request values for date html.
	 *
	 * @var array
	 */
	protected $purifiedValuesByHtml = [];
	/**
	 * List of headings and sanitization methods.
	 *
	 * @var array
	 */
	public $headersPurifierMap = [
	];

	/**
	 * Constructor.
	 *
	 * @param array $rawValues
	 * @param bool  $overwrite
	 */
	public function __construct($rawValues, $overwrite = true)
	{
		$this->rawValues = $rawValues;
		if ($overwrite) {
			static::$request = $this;
		}
	}

	/**
	 * Function to get the value for a given key.
	 *
	 * @param string $key
	 * @param mixed  $value Default value
	 *
	 * @return mixed
	 */
	public function get($key, $value = '')
	{
		if (isset($this->purifiedValuesByGet[$key])) {
			return $this->purifiedValuesByGet[$key];
		}
		if (isset($this->rawValues[$key])) {
			$value = $this->rawValues[$key];
		} else {
			return $value;
		}
		if (\is_string($value) && (0 === strpos($value, '[') || 0 === strpos($value, '{'))) {
			$decodeValue = Json::decode($value);
			if (isset($decodeValue)) {
				$value = $decodeValue;
			}
		}
		if ($value) {
			$value = Purifier::purify($value);
		}

		return $this->purifiedValuesByGet[$key] = $value;
	}

	/**
	 * Purify by data type.
	 *
	 * Type list:
	 * Standard - only words
	 * 1 - only words
	 * Alnum - word and int
	 * 2 - word and int
	 *
	 * @param string     $key     Key name
	 * @param int|string $type    Data type that is only acceptable, default only words 'Standard'
	 * @param mixed      $convert
	 *
	 * @return bool|mixed
	 */
	public function getByType($key, $type = 'Standard', $convert = false)
	{
		if (isset($this->purifiedValuesByType[$key][$type])) {
			return $this->purifiedValuesByType[$key][$type];
		}
		if (isset($this->rawValues[$key])) {
			return $this->purifiedValuesByType[$key][$type] = Purifier::purifyByType($this->rawValues[$key], $type, $convert);
		}
		return false;
	}

	/**
	 * Function to get the boolean value for a given key.
	 *
	 * @param string $key
	 * @param bool   $defaultValue Default value
	 *
	 * @return bool
	 */
	public function getBoolean(string $key, bool $defaultValue = null)
	{
		$value = $this->get($key, $defaultValue);
		if (\is_bool($value)) {
			return $value;
		}
		return 0 === strcasecmp('true', (string) $value) || '1' === (string) $value;
	}

	/**
	 * Function to get the integer value for a given key.
	 *
	 * @param string $key
	 * @param int    $value
	 *
	 * @return int
	 */
	public function getInteger($key, $value = 0)
	{
		if (isset($this->purifiedValuesByInteger[$key])) {
			return $this->purifiedValuesByInteger[$key];
		}
		if (!isset($this->rawValues[$key])) {
			return $value;
		}
		if (false !== ($value = filter_var($this->rawValues[$key], FILTER_VALIDATE_INT))) {
			return $this->purifiedValuesByInteger[$key] = $value;
		}

		throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$key||{$this->rawValues[$key]}", 406);
	}

	/**
	 * Function to get the array values for a given key.
	 *
	 * @param string      $key
	 * @param mixed       $type
	 * @param array       $value
	 * @param string|null $keyType
	 *
	 * @return array
	 */
	public function getArray($key, $type = false, $value = [], ?string $keyType = null)
	{
		if (isset($this->purifiedValuesByArray[$key])) {
			return $this->purifiedValuesByArray[$key];
		}
		if (isset($this->rawValues[$key])) {
			$value = $this->rawValues[$key];
			if (!$value) {
				return [];
			}
			if (\is_string($value) && (0 === strpos($value, '[') || 0 === strpos($value, '{'))) {
				$decodeValue = Json::decode($value);
				if (isset($decodeValue)) {
					$value = $decodeValue;
				} else {
					\App\Log::warning('Invalid data format, problem encountered while decoding JSON. Data should be in JSON format. Data: ' . $value);
				}
			}
			if ($value) {
				if (\is_array($value)) {
					$input = [];
					foreach ($value as $k => $v) {
						if (!\is_int($k)) {
							$k = $keyType ? Purifier::purifyByType($k, $keyType) : Purifier::purify($k);
						}
						$input[$k] = $type ? Purifier::purifyByType($v, $type) : Purifier::purify($v);
					}
					$value = $input;
				} else {
					$value = $type ? Purifier::purifyByType($value, $type) : Purifier::purify($value);
				}
			}

			return $this->purifiedValuesByArray[$key] = (array) $value;
		}
		return $value;
	}

	/**
	 * Function to get the exploded values for a given key.
	 *
	 * @param string      $key
	 * @param string      $delimiter
	 * @param bool|string $type
	 *
	 * @return array
	 */
	public function getExploded($key, $delimiter = ',', $type = false)
	{
		if (isset($this->purifiedValuesByExploded[$key])) {
			return $this->purifiedValuesByExploded[$key];
		}
		$value = [];
		if (isset($this->rawValues[$key])) {
			if ('' === $this->rawValues[$key]) {
				return $value;
			}
			$value = explode($delimiter, $this->rawValues[$key]);
			if ($value) {
				$value = $type ? Purifier::purifyByType($value, $type) : Purifier::purify($value);
			}

			return $this->purifiedValuesByExploded[$key] = $value;
		}

		return $value;
	}

	/**
	 * Purify multi dimension array.
	 *
	 * @param mixed        $values
	 * @param array|string $template
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return mixed
	 */
	private function purifyMultiDimensionArray($values, $template)
	{
		if (\is_array($template)) {
			foreach ($values as $firstKey => $value) {
				if (\is_array($value)) {
					if (1 === \count($template)) {
						$template = current($template);
					}
					foreach ($value as $secondKey => $val) {
						$tempTemplate = $template;
						if (isset($template[$firstKey])) {
							$tempTemplate = $template[$firstKey];
						}
						if (1 === \count($tempTemplate)) {
							$tempTemplate = current($tempTemplate);
						} elseif (!isset($tempTemplate[$secondKey])) {
							throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$secondKey}", 406);
						} else {
							$tempTemplate = $tempTemplate[$secondKey];
						}
						$values[$firstKey][$secondKey] = $this->purifyMultiDimensionArray($val, $tempTemplate);
					}
				} else {
					if (\is_array($template) && 1 === \count($template)) {
						$values[$firstKey] = $this->purifyMultiDimensionArray($value, current($template));
					} elseif (isset($template[$firstKey])) {
						$values[$firstKey] = $this->purifyMultiDimensionArray($value, $template[$firstKey]);
					} else {
						throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$firstKey}||" . print_r($template, true), 406);
					}
				}
			}
		} else {
			$values = empty($values) ? $values : ($template ? Purifier::purifyByType($values, $template) : Purifier::purify($values));
		}
		return $values;
	}

	/**
	 * Function to get multi dimension array.
	 *
	 * @param string $key
	 * @param array  $template
	 *
	 * @return array
	 */
	public function getMultiDimensionArray(string $key, array $template): array
	{
		$return = [];
		if (isset($this->purifiedValuesByMultiDimension[$key])) {
			$return = $this->purifiedValuesByMultiDimension[$key];
		} elseif (isset($this->rawValues[$key]) && ($value = $this->rawValues[$key])) {
			if (\is_string($value) && (0 === strpos($value, '[') || 0 === strpos($value, '{'))) {
				$decodeValue = Json::decode($value);
				if (null !== $decodeValue) {
					$value = $decodeValue;
				} else {
					Log::warning('Invalid data format, problem encountered while decoding JSON. Data should be in JSON format. Data: ' . $value);
				}
			}
			$value = (array) $this->purifyMultiDimensionArray($value, $template);
			$return = $this->purifiedValuesByMultiDimension[$key] = $value;
		}

		return $return;
	}

	/**
	 * Function to get the date range values for a given key.
	 *
	 * @param string $key request param like 'createdtime'
	 *
	 * @return array
	 */
	public function getDateRange($key)
	{
		return $this->getByType($key, 'DateRangeUserFormat');
	}

	/**
	 * Function to get html the value for a given key.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public function getForHtml($key, $value = '')
	{
		if (isset($this->purifiedValuesByHtml[$key])) {
			return $this->purifiedValuesByHtml[$key];
		}
		if (isset($this->rawValues[$key])) {
			$value = $this->rawValues[$key];
		}
		if ($value) {
			$value = \App\Purifier::purifyHtml($value);
		}

		return $this->purifiedValuesByHtml[$key] = $value;
	}

	/**
	 * Function to get the value if its safe to use for SQL Query (column).
	 *
	 * @param string $key
	 * @param bool   $skipEmtpy
	 *
	 * @return string
	 */
	public function getForSql($key, $skipEmtpy = true)
	{
		return Purifier::purifySql($this->get($key), $skipEmtpy);
	}

	/**
	 * Function to get the request mode.
	 *
	 * @return string
	 */
	public function getMode()
	{
		return '' !== $this->getRaw('mode') ? $this->getByType('mode', 'Alnum') : '';
	}

	/**
	 * Get all data.
	 *
	 * @return array
	 */
	public function getAll()
	{
		foreach ($this->rawValues as $key => $value) {
			$this->get($key);
		}

		return $this->purifiedValuesByGet;
	}

	/**
	 * Get all raw data.
	 *
	 * @return array
	 */
	public function getAllRaw()
	{
		return $this->rawValues;
	}

	/**
	 * Get raw value.
	 *
	 * @param string $key
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getRaw($key, $defaultValue = '')
	{
		if (isset($this->rawValues[$key])) {
			return $this->rawValues[$key];
		}

		return $defaultValue;
	}

	/**
	 * Get all headers.
	 *
	 * @return string[]
	 */
	public function getHeaders()
	{
		if (isset($this->headers)) {
			return $this->headers;
		}
		$data = array_change_key_case(getallheaders(), CASE_LOWER);
		foreach ($data as $key => &$value) {
			if ('' !== $value) {
				$value = isset($this->headersPurifierMap[$key]) ? Purifier::purifyByType($value, $this->headersPurifierMap[$key]) : Purifier::purify($value);
			}
		}
		return $this->headers = $data;
	}

	/**
	 * Get header for a given key.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getHeader($key)
	{
		if (!isset($this->headers)) {
			$this->getHeaders();
		}
		return $this->headers[$key] ?? null;
	}

	/**
	 * Get request method.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public static function getRequestMethod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if ('POST' === $method && isset($_SERVER['HTTP_X_HTTP_METHOD'])) {
			if ('DELETE' === $_SERVER['HTTP_X_HTTP_METHOD']) {
				$method = 'DELETE';
			} elseif ('PUT' === $_SERVER['HTTP_X_HTTP_METHOD']) {
				$method = 'PUT';
			} else {
				throw new \App\Exceptions\AppException('Unexpected Header');
			}
		}
		return strtoupper($method);
	}

	/**
	 * Get server and execution environment information.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return bool
	 */
	public function getServer($key, $default = false)
	{
		if (!isset($_SERVER[$key])) {
			return $default;
		}
		return Purifier::purifyByType($_SERVER[$key], 'Text');
	}

	/**
	 * Get module name.
	 *
	 * @param bool $raw
	 *
	 * @return string
	 */
	public function getModule($raw = true)
	{
		$moduleName = $this->getByType('module', \App\Purifier::ALNUM);
		if (!$raw && !$this->isEmpty('parent', true) && 'Settings' === ($parentModule = $this->getByType('parent', \App\Purifier::ALNUM))) {
			$moduleName = "$parentModule:$moduleName";
		}
		return $moduleName;
	}

	/**
	 * Check for existence of key.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return isset($this->rawValues[$key]);
	}

	/**
	 * Function to check if the key is empty.
	 *
	 * @param string $key
	 * @param bool   $emptyFunction
	 *
	 * @return bool
	 */
	public function isEmpty($key, $emptyFunction = false)
	{
		if ($emptyFunction) {
			return empty($this->rawValues[$key]);
		}
		return !isset($this->rawValues[$key]) || '' === $this->rawValues[$key];
	}

	/**
	 * Function to set the value for a given key.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param bool   $onlyRaw
	 *
	 * @return $this
	 */
	public function set($key, $value, bool $onlyRaw = false): self
	{
		if ($onlyRaw) {
			$this->rawValues[$key] = $value;
		} else {
			$this->rawValues[$key] = $this->purifiedValuesByGet[$key] = $this->purifiedValuesByInteger[$key] = $this->purifiedValuesByHtml[$key] = $value;
			$this->purifiedValuesByType[$key] = [];
		}
		return $this;
	}

	/**
	 * Function to remove the value for a given key.
	 *
	 * @param string $key
	 */
	public function delete($key)
	{
		if (isset($this->purifiedValuesByGet[$key])) {
			unset($this->purifiedValuesByGet[$key]);
		}
		if (isset($this->purifiedValuesByInteger[$key])) {
			unset($this->purifiedValuesByInteger[$key]);
		}
		if (isset($this->purifiedValuesByType[$key])) {
			unset($this->purifiedValuesByType[$key]);
		}
		if (isset($this->purifiedValuesByHtml[$key])) {
			unset($this->purifiedValuesByHtml[$key]);
		}
		if (isset($this->purifiedValuesByArray[$key])) {
			unset($this->purifiedValuesByArray[$key]);
		}
		if (isset($this->purifiedValuesByDateRange[$key])) {
			unset($this->purifiedValuesByDateRange[$key]);
		}
		if (isset($this->purifiedValuesByExploded[$key])) {
			unset($this->purifiedValuesByExploded[$key]);
		}
		if (isset($this->purifiedValuesByMultiDimension[$key])) {
			unset($this->purifiedValuesByMultiDimension[$key]);
		}
		if (isset($this->rawValues[$key])) {
			unset($this->rawValues[$key]);
		}
	}

	/**
	 * Get all request keys.
	 *
	 * @return array
	 */
	public function getKeys()
	{
		return array_keys($this->rawValues);
	}

	/**
	 * Function to check if the ajax request.
	 *
	 * @return bool
	 */
	public function isAjax()
	{
		if (!empty($_SERVER['HTTP_X_PJAX']) && true === $_SERVER['HTTP_X_PJAX']) {
			return true;
		}
		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			return true;
		}
		return false;
	}

	/**
	 * Is json.
	 *
	 * @return bool
	 */
	public function isJSON()
	{
		return false !== strpos($this->getHeader('accept'), 'application/json');
	}

	/**
	 * Validating read access request.
	 *
	 * @throws \App\Exceptions\Csrf
	 */
	public function validateReadAccess()
	{
		// Referer check if present - to over come && Check for user post authentication.
		if (\Config\Security::$verifyRefererHeader && isset($_SERVER['HTTP_REFERER']) && \App\User::getCurrentUserId() && 'Install' !== $this->get('module')) {
			$allowed = array_merge(\Config\Security::$allowedFrameDomains, \Config\Security::$allowedFormDomains);
			$allowed[] = \App\Config::main('site_URL');
			$throw = true;
			foreach ($allowed as $value) {
				if (0 === stripos($_SERVER['HTTP_REFERER'], $value)) {
					$throw = false;
				}
			}
			if ($throw) {
				throw new \App\Exceptions\Csrf('Illegal request');
			}
		}
	}

	/**
	 * Validating write access request.
	 *
	 * @param bool $skipRequestTypeCheck
	 *
	 * @throws \App\Exceptions\Csrf
	 */
	public function validateWriteAccess($skipRequestTypeCheck = false)
	{
		if (!$skipRequestTypeCheck && 'POST' !== $_SERVER['REQUEST_METHOD']) {
			throw new \App\Exceptions\Csrf('Invalid request - validate Write Access', 403);
		}
		$this->validateReadAccess();
		if (\App\Config::security('csrfActive')) {
			\CsrfMagic\Csrf::check();
		}
	}

	/**
	 * Static instance initialization.
	 *
	 * @param array|bool $request
	 *
	 * @return Request
	 */
	public static function init($request = false)
	{
		if (!static::$request) {
			static::$request = new self($request ?: $_REQUEST);
		}
		return static::$request;
	}

	/**
	 * Support static methods, all functions must start with "_".
	 *
	 * @param string     $name
	 * @param array|null $arguments
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments = null)
	{
		if (!static::$request) {
			static::init();
		}
		$function = ltrim($name, '_');
		if (!method_exists(static::$request, $function)) {
			throw new \App\Exceptions\AppException('Method not found');
		}
		if (empty($arguments)) {
			return static::$request->{$function}();
		}
		$first = array_shift($arguments);
		if (empty($arguments)) {
			return static::$request->{$function}($first);
		}
		return static::$request->{$function}($first, $arguments[0]);
	}
}
