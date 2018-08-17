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
	public static $Items = [
		'FLAG_1' => 'bool',
		'FLAG_2' => 'bool',
		'FLAG_3' => 'bool',
		'FLAG_4' => 'bool',
		'FLAG_5' => 'bool',
		'FLAG_6' => 'bool'
	];

	public static function getCurrentState()
	{
		$result = [];
		foreach (static::$Items as $flag => $type) {
			$result[$flag] = ['name' => $flag, 'label' => 'LBL_' . $flag, 'type' => $type, 'value' => \AppConfig::module('YetiForce', $flag) ?? false];
		}
		return $result;
	}
}
