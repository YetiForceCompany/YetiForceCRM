<?php
/**
 * Validator basic class.
 *
 * @package   App
 *
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @copyright YetiForce Sp. z o.o
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Class Validator.
 */
class Validator
{
	public static function isBool($input): bool
	{
		return \is_bool($input);
	}

	public static function standard($input): bool
	{
		return (bool)preg_match('/^[\-_a-zA-Z]+$/', $input);
	}
}
