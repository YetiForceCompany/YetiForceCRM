<?php
/**
 * YetiForce watchdog class.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce status class.
 */
class Watchdog
{
	/**
	 * Allowed flags array.
	 *
	 * @var string[]
	 */
	public static $variables = [
		'watchdogUrl' => 'string',
		'domain' => 'bool',
		'phpVersion' => 'bool',
		'crmVersion' => 'bool',
		'dbVersion' => 'bool',
		'osVersion' => 'bool',
		'sapiVersion' => 'bool',
		'lastCronTime' => 'bool',
		'spaceRoot' => 'bool',
		'spaceStorage' => 'bool',
		'spaceTemp' => 'bool',
		'updates' => 'bool',
		//ConfReport
		'security' => 'bool',
		'stability' => 'bool',
		'libraries' => 'bool',
		'performance' => 'bool',
		'publicDirectoryAccess' => 'bool',
		'environment' => 'bool',
		'writableFilesAndFolders' => 'bool',
		'database' => 'bool',
		'pathVerification' => 'bool',
	];
	/**
	 * Cache.
	 *
	 * @var array
	 */
	public $cache = [];

	/**
	 * Send status informations.
	 */
	public static function send()
	{
		$config = \App\Config::component('YetiForce');
		if (empty($config['watchdogUrl'])) {
			return;
		}
		$url = $config['watchdogUrl'];
		unset($config['watchdogUrl']);
		$status = new self();
		$info = [
			'insKey' => \App\YetiForce\Register::getInstanceKey(),
		];
		foreach ($config as $name => $state) {
			if ($state) {
				$info[$name] = \call_user_func([$status, 'get' . ucfirst($name)]);
			}
		}
		try {
			\App\Log::beginProfile("POST|Watchdog::send|{$url}", __NAMESPACE__);
			(new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->post($url, [
				'headers' => [
					'App-Id' => $info['insKey'],
				],
				'allow_redirects' => false,
				'timeout' => 5,
				'json' => $info
			]);
			\App\Log::endProfile("POST|Watchdog::send|{$url}", __NAMESPACE__);
		} catch (\Throwable $e) {
			\App\Log::warning('Not possible to connect to the server status' . PHP_EOL . $e->getMessage(), 'YetiForceStatus');
		}
	}

	/**
	 * Returns array of all flags with current config.
	 *
	 * @return array
	 */
	public static function getAll()
	{
		$result = [];
		$config = \App\Config::component('YetiForce');
		foreach (static::$variables as $flag => $type) {
			$result[$flag] = ['name' => $flag, 'label' => 'LBL_' . \strtoupper($flag), 'type' => $type, 'value' => $config[$flag] ?? '-'];
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
		$value = [];
		if (isset($this->cache['stability']['phpVersion']['www'])) {
			$value['www'] = $this->cache['stability']['phpVersion']['www'];
		}
		if (isset($this->cache['stability']['phpVersion']['cron'])) {
			$value['cron'] = $this->cache['stability']['phpVersion']['cron'];
		}
		return $value;
	}

	/**
	 * Get system version param.
	 *
	 * @return array
	 */
	public function getCrmVersion()
	{
		if (empty($this->cache['environment'])) {
			$this->cache['environment'] = \App\Utils\ConfReport::get('environment');
		}
		return $this->cache['environment']['crmVersion']['www'] ?? '';
	}

	/**
	 * Get database version param.
	 *
	 * @return array
	 */
	public function getDbVersion()
	{
		if (empty($this->cache['database'])) {
			$this->cache['database'] = \App\Utils\ConfReport::get('database');
		}
		return [
			'version' => $this->cache['database']['serverVersion']['www'] ?? '',
			'comment' => $this->cache['database']['version_comment']['www'] ?? '',
		];
	}

	/**
	 * Get os version param.
	 *
	 * @return array
	 */
	public function getOsVersion()
	{
		if (empty($this->cache['environment'])) {
			$this->cache['environment'] = \App\Utils\ConfReport::get('environment');
		}
		return $this->cache['environment']['operatingSystem']['www'] ?? '';
	}

	/**
	 * Get last cron time param.
	 *
	 * @return array
	 */
	public function getLastCronTime()
	{
		$cron = \App\Utils\ConfReport::getCronVariables('last_start');
		$value = '-';
		if ($cron) {
			$value = $cron;
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
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status']];
			if (isset($values['cron'])) {
				$value['cron'] = $values['cron'];
			}
			if (isset($values['recommended'])) {
				$value['recommended'] = $values['recommended'];
			}
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get stability param.
	 *
	 * @return array
	 */
	public function getStability()
	{
		if (empty($this->cache['stability'])) {
			$this->cache['stability'] = \App\Utils\ConfReport::get('stability');
		}
		$param = [];
		foreach ($this->cache['stability'] as $name => $values) {
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status']];
			if (isset($values['cron'])) {
				$value['cron'] = $values['cron'];
			}
			if (isset($values['recommended'])) {
				$value['recommended'] = $values['recommended'];
			}
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get libraries param.
	 *
	 * @return array
	 */
	public function getLibraries()
	{
		if (empty($this->cache['libraries'])) {
			$this->cache['libraries'] = \App\Utils\ConfReport::get('libraries');
		}
		$param = [];
		foreach ($this->cache['libraries'] as $name => $values) {
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status'], 'mandatory' => ($values['mandatory'] ?? false)];
			if (isset($values['cron'])) {
				$value['cron'] = $values['cron'];
			}
			if (isset($values['mode'])) {
				$value['mode'] = $values['mode'];
			}
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get performance param.
	 *
	 * @return array
	 */
	public function getPerformance()
	{
		if (empty($this->cache['performance'])) {
			$this->cache['performance'] = \App\Utils\ConfReport::get('performance');
		}
		$param = [];
		foreach ($this->cache['performance'] as $name => $values) {
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status']];
			if (isset($values['cron'])) {
				$value['cron'] = $values['cron'];
			}
			if (isset($values['recommended'])) {
				$value['recommended'] = $values['recommended'];
			}
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get security param.
	 *
	 * @return array
	 */
	public function getPublicDirectoryAccess()
	{
		if (empty($this->cache['publicDirectoryAccess'])) {
			$this->cache['publicDirectoryAccess'] = \App\Utils\ConfReport::get('publicDirectoryAccess');
		}
		$param = [];
		foreach ($this->cache['publicDirectoryAccess'] as $name => $values) {
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status']];
			if (isset($values['cron'])) {
				$value['cron'] = $values['cron'];
			}
			if (isset($values['recommended'])) {
				$value['recommended'] = $values['recommended'];
			}
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get environment param.
	 *
	 * @return array
	 */
	public function getEnvironment()
	{
		if (empty($this->cache['environment'])) {
			$this->cache['environment'] = \App\Utils\ConfReport::get('environment');
		}
		$param = [];
		foreach ($this->cache['environment'] as $name => $values) {
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status']];
			if (isset($values['cron'])) {
				$value['cron'] = $values['cron'];
			}
			if (isset($values['recommended'])) {
				$value['recommended'] = $values['recommended'];
			}
			if (isset($values['mode'])) {
				$value['mode'] = $values['mode'];
			}
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get writable files and folders param.
	 *
	 * @return array
	 */
	public function getWritableFilesAndFolders()
	{
		if (empty($this->cache['writableFilesAndFolders'])) {
			$this->cache['writableFilesAndFolders'] = \App\Utils\ConfReport::get('writableFilesAndFolders');
		}
		$param = [];
		foreach ($this->cache['writableFilesAndFolders'] as $name => $values) {
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status']];
			if (isset($values['cron'])) {
				$value['cron'] = $values['cron'];
			}
			if (isset($values['recommended'])) {
				$value['recommended'] = $values['recommended'];
			}
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get database param.
	 *
	 * @return array
	 */
	public function getDatabase()
	{
		if (empty($this->cache['database'])) {
			$this->cache['database'] = \App\Utils\ConfReport::get('database');
		}
		$param = [];
		foreach ($this->cache['database'] as $name => $values) {
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status']];
			if (isset($values['cron'])) {
				$value['cron'] = $values['cron'];
			}
			if (isset($values['recommended'])) {
				$value['recommended'] = $values['recommended'];
			}
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get database param.
	 *
	 * @return array
	 */
	public function getPathVerification()
	{
		if (empty($this->cache['pathVerification'])) {
			$this->cache['pathVerification'] = \App\Utils\ConfReport::get('pathVerification');
		}
		$param = [];
		foreach ($this->cache['pathVerification'] as $name => $values) {
			$value = ['www' => $values['www'] ?? '', 'status' => $values['status']];
			$param[$name] = $value;
		}
		return $param;
	}

	/**
	 * Get web server name and version.
	 */
	public function getSapiVersion()
	{
		return [];
	}

	/**
	 * Get CRM root directory space.
	 *
	 * @return array
	 */
	public function getSpaceRoot()
	{
		if (empty($this->cache['environment'])) {
			$this->cache['environment'] = \App\Utils\ConfReport::get('environment');
		}
		return $this->cache['environment']['spaceRoot']['spaceFree'] ?? 0;
	}

	/**
	 * Get storage directory space.
	 *
	 * @return array
	 */
	public function getSpaceStorage()
	{
		if (empty($this->cache['environment'])) {
			$this->cache['environment'] = \App\Utils\ConfReport::get('environment');
		}
		return $this->cache['environment']['spaceStorage']['spaceFree'] ?? 0;
	}

	/**
	 * Get temporary directory space.
	 *
	 * @return array
	 */
	public function getSpaceTemp()
	{
		if (empty($this->cache['environment'])) {
			$this->cache['environment'] = \App\Utils\ConfReport::get('environment');
		}
		return $this->cache['environment']['spaceTemp']['spaceFree'] ?? 0;
	}

	/**
	 * Get backup directory space.
	 *
	 * @return array
	 */
	public function getSpaceBackup()
	{
		if (empty($this->cache['environment'])) {
			$this->cache['environment'] = \App\Utils\ConfReport::get('environment');
		}
		return $this->cache['environment']['spaceBackup']['spaceFree'] ?? 0;
	}

	/**
	 * Get domain.
	 *
	 * @return array
	 */
	public function getDomain()
	{
		return \App\Config::main('site_URL');
	}

	/**
	 * Get updates.
	 *
	 * @return array
	 */
	public function getUpdates()
	{
		$rows = [];
		foreach (\Settings_Updates_Module_Model::getUpdates() as $row) {
			$rows[] = [$row['name'], $row['from_version'], $row['to_version'], $row['result']];
		}
		return $rows;
	}
}
