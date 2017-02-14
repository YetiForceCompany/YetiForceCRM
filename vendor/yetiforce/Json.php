<?php
namespace App;

/**
 * Json class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	 * encoded in the JSON format
	 *
	 * Uses ext/json's json_decode if available.
	 *
	 * @param string $encodedValue Encoded in JSON format
	 * @param int $objectDecodeType Optional; When TRUE, returned objects will be converted into associative arrays.
	 * @link https://secure.php.net/manual/en/function.json-decode.php
	 * @return mixed
	 */
	public static function decode($encodedValue, $objectDecodeType = self::TYPE_ARRAY)
	{
		if (function_exists('json_decode')) {
			return json_decode($encodedValue, $objectDecodeType);
		}
		throw new \Exception\AppException('ERR_NO_JSON_DECODE');
	}

	/**
	 * Encode the mixed $valueToEncode into the JSON format
	 *
	 * Encodes using ext/json's json_encode() if available.
	 *
	 * NOTE: Object should not contain cycles; the JSON format
	 * does not allow object reference.
	 *
	 * NOTE: Only public variables will be encoded
	 *
	 * @param mixed $valueToEncode
	 * @param boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
	 * @return string JSON encoded object
	 */
	public static function encode($valueToEncode, $cycleCheck = false)
	{
		if (function_exists('json_encode')) {
			return json_encode($valueToEncode);
		}
		throw new \Exception\AppException('ERR_NO_JSON_ENCODE');
	}
}
