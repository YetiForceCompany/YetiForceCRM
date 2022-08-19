<?php
/**
 * Config main class.
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
 * Config main class.
 */
class Config
{
	/**
	 * Js environment variables.
	 *
	 * @var array
	 */
	private static $jsEnv = [];

	/**
	 * Get all js configuration in json.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public static function getJsEnv()
	{
		return Json::encode(self::$jsEnv);
	}

	/**
	 * Get js configuration by key.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function getJsEnvByKey(string $key)
	{
		return self::$jsEnv[$key] ?? null;
	}

	/**
	 * Set js environment variables.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function setJsEnv($key, $value)
	{
		self::$jsEnv[$key] = $value;
	}

	/**
	 * Gets main configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function main(?string $arg = null, $default = null)
	{
		if ($arg && isset($GLOBALS[$arg])) {
			return $GLOBALS[$arg];
		}
		$class = '\\Config\\Main';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets module configuration.
	 *
	 * @param string      $moduleName
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function module(string $moduleName, ?string $arg = null, $default = null)
	{
		$class = "\\Config\\Modules\\$moduleName";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets component configuration.
	 *
	 * @param string      $component
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function component(string $component, ?string $arg = null, $default = null)
	{
		$class = "\\Config\\Components\\$component";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets performance configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function performance(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Performance';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets api configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function api(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Api';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets debug configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function debug(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Debug';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets developer configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function developer(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Developer';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets layout configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function layout(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Layout';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets security configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function security(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Security';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets search configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function search(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Search';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets sounds configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function sounds(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Sounds';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets relation configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function relation(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Relation';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets security keys configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function securityKeys(?string $arg = null, $default = null)
	{
		$class = '\\Config\\SecurityKeys';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets database configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function db(?string $arg = null, $default = null)
	{
		$class = '\\Config\\Db';
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets configuration for class.
	 *
	 * @param string      $class
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function get(string $class, ?string $arg = null, $default = null)
	{
		$value = $default;
		if (\class_exists($class)) {
			if (null === $arg) {
				$object = (new \ReflectionClass($class));
				$value = $object->getStaticProperties();
				foreach ($object->getMethods() as $method) {
					$value[$method->getName()] = \call_user_func("{$class}::{$method->getName()}");
				}
			} elseif (isset($class::${$arg})) {
				$value = $class::${$arg};
			} elseif (\method_exists($class, $arg)) {
				$value = \call_user_func("{$class}::{$arg}");
			}
		}
		return $value;
	}

	/**
	 * Set config value.
	 *
	 * @return bool
	 */
	public static function set(): bool
	{
		if (4 === \func_num_args()) {
			[$component, $type, $key, $value] = \func_get_args();
			$component = ucfirst($component) . 's\\';
		} else {
			[$type, $key, $value] = \func_get_args();
			$type = ucfirst($type);
		}
		$class = '\Config\\' . ($component ?? '') . $type;
		if ($result = (class_exists($class) && isset($class::${$key}))) {
			$class::${$key} = $value;
		}
		return $result;
	}

	/**
	 * Get the maximum size of an uploaded file to the server taking into account CRM configuration and server settings.
	 *
	 * @param bool $checkMain
	 * @param bool $returnInMb
	 *
	 * @return int
	 */
	public static function getMaxUploadSize(bool $checkMain = true, bool $returnInMb = false): int
	{
		$size = \vtlib\Functions::parseBytes(ini_get('upload_max_filesize'));
		$maxPostSize = \vtlib\Functions::parseBytes(ini_get('post_max_size'));
		if ($maxPostSize < $size) {
			$size = $maxPostSize;
		}
		if ($checkMain && \Config\Main::$upload_maxsize < $size) {
			$size = (int) \Config\Main::$upload_maxsize;
		}
		if ($returnInMb) {
			$size = floor($size / 1048576);
		}
		return $size;
	}
}
