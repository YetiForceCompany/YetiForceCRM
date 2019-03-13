<?php
/**
 * YetiForce register class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * @var
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
	private const REGISTRATION_FILE = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'registration.php';
	/**
	 * Status messages.
	 *
	 * @var string[]
	 */
	public const STATUS_MESSAGES = [
		0 => 'LBL_NOT_REGISTERED',
		1 => 'LBL_WAITING_FOR_ACCEPTANCE',
		2 => 'LBL_INCORRECT_DATA',
		3 => 'LBL_INCOMPLETE_DATA',
		4 => 'LBL_OFFLINE_SIGNED',
		5 => 'LBL_OFFLINE_SERIAL_NOT_FOUND',
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
	private static function getInstanceKey(): string
	{
		return sha1(\App\Config::main('application_unique_key') . \App\Config::main('site_URL') . ($_SERVER['SERVER_ADDR'] ?? $_SERVER['COMPUTERNAME'] ?? null));
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
		if (!\App\RequestUtil::isNetConnection() || 'yetiforce.com' === gethostbyname('yetiforce.com')) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			$this->error = 'ERR_NO_INTERNET_CONNECTION';

			return false;
		}
		$result = false;

		try {
			$response = (new \GuzzleHttp\Client())
				->post(static::$registrationUrl . 'add',
					\App\RequestHttp::getOptions() + [
						'form_params' => $this->getData()
					]);
			$body = $response->getBody();
			if (!\App\Json::isEmpty($body)) {
				$body = \App\Json::decode($body);
				if ('OK' === $body['text']) {
					static::updateMetaData([
						'register_time' => date('Y-m-d H:i:s'),
						'status' => $body['status'],
						'text' => $body['text'],
						'serialKey' => $body['serialKey'] ?? '',
						'last_check_time' => ''
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
	 * @return bool
	 */
	public static function check($force = false)
	{
		if (!\App\RequestUtil::isNetConnection() || 'yetiforce.com' === gethostbyname('yetiforce.com')) {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION', __METHOD__);
			static::updateMetaData(['lastError' => 'ERR_NO_INTERNET_CONNECTION']);
			return false;
		}
		$conf = static::getConf();
		if (!$force && (!empty($conf['last_check_time']) && (($conf['status'] < 6 && strtotime('+6 hours', strtotime($conf['last_check_time'])) > time()) || ($conf['status'] > 6 && strtotime('+7 day', strtotime($conf['last_check_time'])) > time())))) {
			return false;
		}
		$params = [
			'version' => \App\Version::get(),
			'crmKey' => static::getCrmKey(),
			'insKey' => static::getInstanceKey(),
			'serialKey' => $conf['serialKey'] ?? '',
			'status' => $conf['status'] ?? 0,
		];

		try {
			$data = ['last_check_time' => date('Y-m-d H:i:s')];
			$response = (new \GuzzleHttp\Client())
				->post(static::$registrationUrl . 'check', \App\RequestHttp::getOptions() + ['form_params' => $params]);
			$body = $response->getBody();
			if (!\App\Json::isEmpty($body)) {
				$body = \App\Json::decode($body);
				if ('OK' === $body['text']) {
					static::updateCompanies($body['companies']);
					$data = [
						'status' => $body['status'],
						'text' => $body['text'],
						'serialKey' => $body['serialKey'],
						'last_check_time' => date('Y-m-d H:i:s')
					];
					$status = true;
				}
			} else {
				\App\Log::warning('ERR_BODY_IS_EMPTY', __METHOD__);
				static::updateMetaData(['lastError' => 'ERR_BODY_IS_EMPTY']);
			}

			static::updateMetaData($data);
		} catch (\Throwable $e) {
			\App\Log::warning($e->getMessage(), __METHOD__);
			static::updateMetaData(['lastError' => $e->getMessage()]);
		}
		return $status ?? false;
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
		$conf = static::getConf();
		if (!$conf) {
			return false;
		}
		$status = $conf['status'] > 6;
		if (!empty($conf['serialKey']) && $status && static::verifySerial($conf['serialKey'])) {
			return true;
		}
		if ($timer && !empty($conf['register_time']) && strtotime('+14 days', strtotime($conf['register_time'])) > time()) {
			$status = true;
		}
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
			'register_time' => $data['register_time'] ?? $conf['register_time'] ?? '',
			'last_check_time' => $data['last_check_time'] ?? '',
			'status' => $data['status'] ?? $conf['status'] ?? 0,
			'text' => $data['text'] ?? $conf['text'] ?? '',
			'serialKey' => $data['serialKey'] ?? $conf['serialKey'] ?? '',
			'lastError' => $data['lastError'] ?? '',
		];
		file_put_contents(static::REGISTRATION_FILE, "<?php //Modifying this file will breach the licence terms. \n return " . \var_export(static::$config, true) . ';');
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
			'status' => 4,
			'text' => 'OK',
			'insKey' => static::getInstanceKey(),
			'serialKey' => $serial
		]);
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
		$key = substr($serial, 0, 20) . substr(crc32(substr($serial, 0, 20)), 2, 5);
		return 0 === strcmp($serial, $key . substr(sha1($key), 5, 15));
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
		$conf = static::getConf();
		return $conf['last_check_time'] ?? false;
	}

	/**
	 * Get last check error.
	 *
	 * @return mixed
	 */
	public static function getLastCheckError()
	{
		$conf = static::getConf();
		return $conf['lastError'] ?? false;
	}

	/**
	 * Get registration status.
	 *
	 * @return int
	 */
	public static function getStatus(): int
	{
		$conf = static::getConf();
		return (int) ($conf['status'] ?? 0);
	}

	/**
	 * Update company status.
	 *
	 * @param array $companies
	 *
	 * @throws \yii\db\Exception
	 */
	private static function updateCompanies(array $companies)
	{
		foreach ($companies as $row) {
			if (!empty($row['name'])) {
				\App\Company::statusUpdate($row['status'], $row['name']);
			}
		}
	}
}
