<?php
/**
 * Base file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Base class.
 */
class Base
{
	/** @var array Values */
	protected $value;

	/**
	 * Constructor.
	 *
	 * @param array $values
	 */
	public function __construct($values = [])
	{
		$this->value = $values;
	}

	/**
	 * Function to get the value if its safe to use for SQL Query (column).
	 *
	 * @param string $key
	 * @param bool   $skipEmtpy Skip the check if string is empty
	 *
	 * @return mixed Value for the given key
	 */
	public function getForSql($key, $skipEmtpy = true)
	{
		return Purifier::purifySql($this->get($key), $skipEmtpy);
	}

	/**
	 * Function to get the value for a given key.
	 *
	 * @param string $key
	 *
	 * @return mixed Value for the given key
	 */
	public function get($key)
	{
		return $this->value[$key] ?? null;
	}

	/**
	 * Function to get the html encoded value for a given key.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getForHtml($key)
	{
		return Purifier::encodeHtml($this->get($key));
	}

	/**
	 * Function to get the array values for a given key.
	 *
	 * @param string $key
	 * @param array  $value
	 *
	 * @return array
	 */
	public function getArray($key, $value = [])
	{
		if (!isset($this->value[$key])) {
			return $value;
		}
		if (\is_string($this->value[$key]) && (0 === strpos($this->value[$key], '[') || 0 === strpos($this->value[$key], '{'))) {
			$value = Json::decode($this->value[$key]);
		} else {
			$value = (array) $this->value[$key];
		}
		return $value;
	}

	/**
	 * Function to set the value for a given key.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->value[$key] = $value;
		return $this;
	}

	/**
	 * Function to set all the values.
	 *
	 * @param mixed $values
	 *
	 * @return $this
	 */
	public function setData($values)
	{
		$this->value = $values;

		return $this;
	}

	/**
	 * Function to get all the values of the Object.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->value;
	}

	/**
	 * Function to check if the key exists.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return isset($this->value[$key]);
	}

	/**
	 * Function to check if the key is empty.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function isEmpty($key)
	{
		return empty($this->value[$key]);
	}

	/**
	 * Function to remove the value.
	 *
	 * @param string $key
	 */
	public function remove($key)
	{
		unset($this->value[$key]);
	}

	/**
	 * Function to get keys.
	 *
	 * @return string[]
	 */
	public function getKeys()
	{
		return array_keys($this->value);
	}
}
