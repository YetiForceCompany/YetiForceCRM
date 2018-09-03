<?php
/**
 * YetiForce status helper class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace App\YetiForce;

class Status
{
	/**
	 * Allowed flags array.
	 *
	 * @var array
	 */
	public static $variables = [
		'yf_status_url' => 'string',
		'flag_1' => 'bool',
		'flag_2' => 'bool',
		'flag_3' => 'bool',
		'flag_4' => 'bool',
		'flag_5' => 'bool',
		'flag_6' => 'bool'
	];

	/**
	 * Returns array of all flags with current config.
	 *
	 * @return array
	 */
	public static function getAll()
	{
		$result = [];
		foreach (static::$variables as $flag => $type) {
			$result[$flag] = ['name' => $flag, 'label' => 'LBL_' . \strtoupper($flag), 'type' => $type, 'value' => \AppConfig::module('YetiForce', $flag) ?? false];
		}
		return $result;
	}
}
