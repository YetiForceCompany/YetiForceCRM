<?php
/**
 * YetiForce status helper class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		'statusUrl' => 'string',
		'phpVersion' => 'bool',
		'systemVersion' => 'bool',
		'dbVersion' => 'bool',
		'osVersion' => 'bool',
		'sapiVersion' => 'bool',
		'lastCronTime' => 'bool',
		'spaceRoot' => 'bool',
		'spaceStorage' => 'bool',
		'spaceTemp' => 'bool',
		//ConfReport
		'security' => 'bool',
		'stability' => 'bool',
		'libraries' => 'bool',
		'performance' => 'bool',
		'publicDirectoryAccess' => 'bool',
		'environment' => 'bool',
		'writableFilesAndFolders' => 'bool',
		'database' => 'bool',
	];
	/**
	 * Cache.
	 *
	 * @var array
	 */
	public $cache = [];

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

	/**
	 * Get php version param.
	 *
	 * @return array
	 */
	public function getPhpVersion()
	{
		if (empty($this->cache['stability'])) {
			$this->cache['stability'] = \App\Utils\ConfReport::get('stability');
		}
		$value = [$this->cache['stability']['phpVersion']['www']];
		if (isset($this->cache['stability']['phpVersion']['cron'])) {
			$value[] = $this->cache['stability']['phpVersion']['cron'];
		}
		return $value;
	}

	/**
	 * Get security param.
	 *
	 * @return array
	 */
	public function getSecurity()
	{
		if (empty($this->cache['security'])) {
			$this->cache['security'] = \App\Utils\ConfReport::get('security');
		}
		$param = [];
		foreach ($this->cache['security'] as $name => $values) {
			$value = [$values['www']];
			if (isset($values['cron'])) {
				$value[] = $values['cron'];
			}
			$param[$name] = $value;
		}
		return $param;
	}
}
