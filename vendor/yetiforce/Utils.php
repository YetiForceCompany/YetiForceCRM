<?php
namespace App;

/**
 * Utils class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Utils
{

	/**
	 * Outputs or returns a parsable string representation of a variable
	 * @link http://php.net/manual/en/function.var-export.php
	 * @param mixed $variable
	 * @return mixed the variable representation when the <i>return</i>
	 */
	public static function varExport($variable)
	{
		if (is_array($variable)) {
			$toImplode = [];
			foreach ($variable as $key => $value) {
				$toImplode[] = var_export($key, true) . '=>' . static::varExport($value);
			}
			return '[' . implode(',', $toImplode) . ']';
		} else {
			return var_export($variable, true);
		}
	}
}
