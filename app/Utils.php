<?php

namespace App;

/**
 * Utils class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Utils
{
	/**
	 * Function to capture the initial letters of words.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getInitials(string $name): string
	{
		preg_match_all('#(?<=\s|\b)\pL|[()]#u', $name, $initial);
		return isset($initial[0]) ? implode('', $initial[0]) : '';
	}

	/**
	 * Outputs or returns a parsable string representation of a variable.
	 *
	 * @link http://php.net/manual/en/function.var-export.php
	 *
	 * @param mixed $variable
	 *
	 * @return mixed the variable representation when the <i>return</i>
	 */
	public static function varExport($variable)
	{
		if (is_array($variable)) {
			$toImplode = [];
			if (static::isAssoc($variable)) {
				foreach ($variable as $key => $value) {
					$toImplode[] = var_export($key, true) . '=>' . static::varExport($value);
				}
			} else {
				foreach ($variable as $value) {
					$toImplode[] = static::varExport($value);
				}
			}

			return '[' . implode(',', $toImplode) . ']';
		} else {
			return var_export($variable, true);
		}
	}

	/**
	 * Check if array is associative.
	 *
	 * @param array $arr
	 *
	 * @return bool
	 */
	public static function isAssoc(array $arr)
	{
		if (empty($arr)) {
			return false;
		}
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	/**
	 * Convert string from encoding to encoding.
	 *
	 * @param string $value
	 * @param string $fromCharset
	 * @param string $toCharset
	 *
	 * @return string
	 */
	public static function convertCharacterEncoding($value, $fromCharset, $toCharset)
	{
		if (function_exists('mb_convert_encoding') && function_exists('mb_list_encodings') && in_array($fromCharset, mb_list_encodings()) && in_array($toCharset, mb_list_encodings())) {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($fromCharset, $toCharset, $value);
		}
		return $value;
	}
}
