<?php

namespace App;

/**
 * Json class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Json
{
	/**
	 * How objects should be encoded -- arrays or as StdClass. TYPE_ARRAY is 1
	 * so that it is a boolean true value, allowing it to be used with
	 * ext/json's functions.
	 */
	const TYPE_ARRAY = 1;
	const TYPE_OBJECT = 0;

	/**
	 * Decodes the given $encodedValue string which is
	 * encoded in the JSON format.
	 *
	 * Uses ext/json's json_decode if available.
	 *
	 * @param string $encodedValue     Encoded in JSON format
	 * @param int    $objectDecodeType Optional; When TRUE, returned objects will be converted into associative arrays
	 *
	 * @see https://secure.php.net/manual/en/function.json-decode.php
	 *
	 * @return mixed
	 */
	public static function decode($encodedValue, $objectDecodeType = self::TYPE_ARRAY)
	{
		if (null === $encodedValue) {
			return '';
		}
		if (\function_exists('json_decode')) {
			return json_decode($encodedValue, $objectDecodeType);
		}
		throw new \App\Exceptions\AppException('ERR_NO_JSON_DECODE');
	}

	/**
	 * Encode the mixed $valueToEncode into the JSON format.
	 *
	 * Encodes using ext/json's json_encode() if available.
	 *
	 * NOTE: Object should not contain cycles; the JSON format
	 * does not allow object reference.
	 *
	 * NOTE: Only public variables will be encoded
	 *
	 * @param mixed $valueToEncode
	 * @param int   $options       Optional; whether or not to check for object recursion; off by default
	 *
	 * @return string JSON encoded object
	 */
	public static function encode($valueToEncode, $options = 0)
	{
		if (\function_exists('json_encode')) {
			return json_encode($valueToEncode, $options | JSON_UNESCAPED_UNICODE);
		}
		throw new \App\Exceptions\AppException('ERR_NO_JSON_ENCODE');
	}

	/**
	 * Determine whether a variable is empty.
	 *
	 * @param string|null $value
	 *
	 * @return bool
	 */
	public static function isEmpty(?string $value)
	{
		return empty($value) || '[]' === $value || '""' === $value;
	}

	/**
	 * Check that a string is a valid JSON string.
	 *
	 * @param string|null $value
	 *
	 * @return bool
	 */
	public static function isJson(?string $value): bool
	{
		return !(null === $value || '' === $value || null === self::decode($value) || JSON_ERROR_NONE !== \json_last_error());
	}

	/**
	 * Read json file to array.
	 *
	 * @param string $path
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function read(string $path)
	{
		return static::decode(file_get_contents($path), true) ?? [];
	}

	/**
	 * Save json file from array.
	 *
	 * @param string $path
	 * @param array  $data
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool|int
	 */
	public static function save(string $path, array $data)
	{
		return \file_put_contents($path, static::encode($data, JSON_PRETTY_PRINT), LOCK_EX);
	}
}
