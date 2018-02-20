<?php

namespace App;

/**
 * Utils class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Utils
{
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
	 * Import data from previosly exported string from varExport.
	 *
	 * @param  string $str [description]
	 *
	 * @return [type]      [description]
	 */
	public static function varImport(string $str)
	{
		return unserialize($str);
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
	 * Get text length.
	 *
	 * @param string $text
	 *
	 * @return int
	 */
	public static function getTextLength($text)
	{
		if (function_exists('mb_strlen')) {
			return mb_strlen($text);
		} else {
			return strlen($text);
		}
	}
}
