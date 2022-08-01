<?php
/**
 * YetiForce register file.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce register class.
 */
class Register
{
	/**
	 * Registration config cache.
	 *
	 * @var array
	 */
	private static $config;
	/**
	 * Last error.
	 *
	 * @var string
	 */
	public $error;
	/**
	 * Registration url.
	 *
	 * @var string
	 */
	private static $registrationUrl = 'https://api.yetiforce.com/registration/';

	/**
	 * Registration file path.
	 *
	 * @var string
	 */
	private const REGISTRATION_FILE = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'app_data' . \DIRECTORY_SEPARATOR . 'registration.php';
	/**
	 * Status messages.
	 *
	 * @var string[]
	 */
	public const STATUS_MESSAGES = [
		-2 => 'ERR_NO_INTERNET_CONNECTION',
		-1 => 'ERR_IT_HAS_NO_BEEN_6_HOURS_SINCE_THE_LAST_CHECK',
		0 => 'LBL_NOT_REGISTERED',
		1 => 'LBL_WAITING_FOR_ACCEPTANCE',
		2 => 'LBL_INCORRECT_DATA',
		3 => 'LBL_INCOMPLETE_DATA',
		5 => 'LBL_OFFLINE_SERIAL_NOT_FOUND',
		6 => 'LBL_OFFLINE_SIGNED',
		7 => 'LBL_OFFLINE_SIGNED',
		8 => 'LBL_SPECIAL_REGISTRATION',
		9 => 'LBL_ACCEPTED',
	];

	/**
	 * Generate a unique key for the crm.
	 *
	 * @return string
	 */
	public static function getCrmKey(): string
	{
		return sha1(\App\Config::main('application_unique_key'));
	}

	/**
	 * Generate a unique key for the instance.
	 *
	 * @return string
	 */
	public static function getInstanceKey(): string
	{
		return sha1(\App\Config::main('application_unique_key') . \App\Config::main('site_URL') . gethostname());
	}

	/**
	 * Get registration data.
	 *
	 * @return string[]
	 */
	private function getData(): array
	{
		return [
			'version' => \App\Version::get(),
			'language' => \App\Language::getLanguage(),
			'timezone' => date_default_timezone_get(),
			'insKey' => static::getInstanceKey(),
			'crmKey' => static::getCrmKey(),
			'package' => \App\Company::getSize(),
			'provider' => static::getProvider(),
			'companies' => \App\Company::getAll(),
		];
	}

	/**
	 * Send registration data.
	 *
	 * @return bool
	 */
	public function register(): bool
	{
		if (!\App\RequestUtil::isNetConnection() || 'api.yetiforce.com' === gethostbyname('api.yetiforce.com')) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			$this->error = 'ERR_NO_INTERNET_CONNECTION';
			return false;
		}
		$result = false;
		try {
			$url = static::$registrationUrl . 'add';
			\App\Log::beginProfile("POST|Register::register|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client())->post($url, \App\Utils::merge(\App\RequestHttp::getOptions(), ['form_params' => $this->getData()]));
			\App\Log::endProfile("POST|Register::register|{$url}", __NAMESPACE__);
			$body = $response->getBody();
			if (!\App\Json::isEmpty($body)) {
				$body = \App\Json::decode($body);
				if ('OK' === $body['text']) {
					static::updateMetaData([
						'register_time' => date('Y-m-d H:i:s'),
						'status' => $body['status'],
						'text' => $body['text'],
						'serialKey' => $body['serialKey'] ?? '',
						'last_check_time' => '',
					]);
					$result = true;
				}
			}
		} catch (\Throwable $e) {
			$this->error = $e->getMessage();
			\App\Log::warning($e->getMessage(), __METHOD__);
		}
		\App\Company::statusUpdate(1);
		return $result;
	}

	/**
	 * Checking registration status.
	 *
	 * @param bool $force
	 *
	 * @return int
	 */
	public static function check($force = false): int
	{
		if (!\App\RequestUtil::isNetConnection() || 'api.yetiforce.com' === gethostbyname('api.yetiforce.com')) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			static::updateMetaData(['last_error' => 'ERR_NO_INTERNET_CONNECTION', 'last_error_date' => date('Y-m-d H:i:s')]);
			return -1;
		}
		$conf = static::getConf();
		if (!$force && (!empty($conf['last_check_time']) && (($conf['status'] < 6 && strtotime('+6 hours', strtotime($conf['last_check_time'])) > time()) || ($conf['status'] >= 6 && strtotime('+7 day', strtotime($conf['last_check_time'])) > time())))) {
			return -2;
		}
		$status = 0;
		try {
			$data = ['last_check_time' => date('Y-m-d H:i:s')];
			$url = static::$registrationUrl . 'check';
			\App\Log::beginProfile("POST|Register::check|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->post($url, [
				'form_params' => \App\Utils::merge($conf, [
					'version' => \App\Version::get(),
					'crmKey' => static::getCrmKey(),
					'insKey' => static::getInstanceKey(),
					'provider' => static::getProvider(),
					'package' => \App\Company::getSize(),
					'shop' => \App\Utils\ConfReport::validateShopProducts('check', [], 'shop')['shop'],
				]),
			]);
			\App\Log::endProfile("POST|Register::check|{$url}", __NAMESPACE__);
			$body = $response->getBody();
			if (!\App\Json::isEmpty($body)) {
				$body = \App\Json::decode($body);
				if ('OK' === $body['text'] && static::updateCompanies($body['companies'])) {
					$data = [
						'status' => $body['status'],
						'text' => $body['text'],
						'serialKey' => $body['serialKey'],
						'last_check_time' => date('Y-m-d H:i:s'),
						'products' => $body['activeProducts'],
					];
					$status = 1;
				} else {
					throw new \App\Exceptions\AppException($body['text'], 4);
				}
			} else {
				throw new \App\Exceptions\AppException('ERR_BODY_IS_EMPTY', 0);
			}
			static::updateMetaData($data);
		} catch (\Throwable $e) {
			\App\Log::warning($e->getMessage(), __METHOD__);
			//Company details vary, re-registration is required.
			static::updateMetaData([
				'last_error' => $e->getMessage(),
				'last_error_date' => date('Y-m-d H:i:s'),
				'last_check_time' => date('Y-m-d H:i:s'),
			]);
			$status = $e->getCode();
		}
		return $status;
	}

	/**
	 * Registration verification.
	 *
	 * @param bool $timer
	 *
	 * @return bool
	 */
	public static function verify($timer = false): bool
	{
		if (\App\Cache::staticHas('RegisterVerify', $timer)) {
			return \App\Cache::staticGet('RegisterVerify', $timer);
		}
		$conf = static::getConf();
		if (!$conf) {
			\App\Cache::staticSave('RegisterVerify', $timer, false);
			return false;
		}
		$status = $conf['status'] > 5;
		if (!empty($conf['serialKey']) && $status && static::verifySerial($conf['serialKey'])) {
			\App\Cache::staticSave('RegisterVerify', $timer, true);
			return true;
		}
		if ($timer && !empty($conf['register_time']) && strtotime('+14 days', strtotime($conf['register_time'])) > time()) {
			$status = true;
		}
		\App\Cache::staticSave('RegisterVerify', $timer, $status);
		return $status;
	}

	/**
	 * Update registration data.
	 *
	 * @param string[] $data
	 */
	private static function updateMetaData(array $data): void
	{
		$conf = static::getConf();
		static::$config = [
			'last_check_time' => $data['last_check_time'] ?? '',
			'register_time' => $data['register_time'] ?? $conf['register_time'] ?? '',
			'status' => $data['status'] ?? $conf['status'] ?? 0,
			'text' => $data['text'] ?? $conf['text'] ?? '',
			'serialKey' => $data['serialKey'] ?? $conf['serialKey'] ?? '',
			'products' => $data['products'] ?? $conf['products'] ?? [],
			'last_error' => $data['last_error'] ?? '',
			'last_error_date' => $data['last_error_date'] ?? '',
		];
		\App\Utils::saveToFile(static::REGISTRATION_FILE, static::$config, 'Modifying this file or functions that affect the footer appearance will violate the license terms!!!', 0, true);
		\App\YetiForce\Shop::generateCache();
	}

	/**
	 * Set offline serial.
	 *
	 * @param string $serial
	 *
	 * @return bool
	 */
	public static function setSerial($serial)
	{
		if (!static::verifySerial($serial)) {
			return false;
		}
		static::updateMetaData([
			'register_time' => date('Y-m-d H:i:s'),
			'status' => 6,
			'text' => 'OK',
			'insKey' => static::getInstanceKey(),
			'serialKey' => $serial,
		]);
		\App\Company::statusUpdate(6);
		return true;
	}

	/**
	 * Verification of the serial number.
	 *
	 * @param string $serial
	 *
	 * @return bool
	 */
	public static function verifySerial(string $serial): bool
	{
		return hash_equals($serial, hash('sha1', self::getInstanceKey() . self::getCrmKey()));
	}

	/**
	 * Get registration config.
	 *
	 * @return array
	 */
	private static function getConf(): array
	{
		if (isset(static::$config)) {
			return static::$config;
		}
		if (!\file_exists(static::REGISTRATION_FILE)) {
			return static::$config = [];
		}
		return static::$config = require static::REGISTRATION_FILE;
	}

	/**
	 * Get last check time.
	 *
	 * @return mixed
	 */
	public static function getLastCheckTime()
	{
		return static::getConf()['last_check_time'] ?? false;
	}

	/**
	 * Get last check error.
	 *
	 * @return bool|string
	 */
	public static function getLastCheckError()
	{
		return static::getConf()['last_error'] ?? false;
	}

	/**
	 * Get registration status.
	 *
	 * @return int
	 */
	public static function getStatus(): int
	{
		return (int) (static::getConf()['status'] ?? 0);
	}

	/**
	 * Is the system is properly registered.
	 *
	 * @return bool
	 */
	public static function isRegistered(): bool
	{
		return static::getStatus() >= 6;
	}

	/**
	 * Get registration products.
	 *
	 * @param mixed $name
	 *
	 * @return array
	 */
	public static function getProducts($name = ''): array
	{
		$rows = [];
		foreach (static::getConf()['products'] ?? [] as $row) {
			$rows[$row['product']] = $row;
		}
		if ($name) {
			return $rows[$name] ?? [];
		}
		return $rows;
	}

	/**
	 * Update company status.
	 *
	 * @param array $companies
	 *
	 * @return bool
	 */
	private static function updateCompanies(array $companies): bool
	{
		$status = false;
		$names = \array_column(\App\Company::getAll(), 'name', 'name');
		foreach ($companies as $row) {
			if (!empty($row['name']) && isset($names[$row['name']])) {
				\App\Company::statusUpdate($row['status'], $row['name']);
				$status = true;
			}
		}
		if (!$status) {
			throw new \App\Exceptions\AppException('ERR_COMPANY_DATA_IS_NOT_COMPATIBLE', 3);
		}
		return $status;
	}

	/**
	 * Get provider.
	 *
	 * @return string
	 */
	public static function getProvider(): string
	{
		$path = ROOT_DIRECTORY . '/app_data/installSource.txt';
		if (\file_exists($path)) {
			return trim(file_get_contents($path));
		}
		return getenv('PROVIDER') ?: getenv('provider') ?: '';
	}
}
