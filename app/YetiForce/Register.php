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
	 * Last error.
	 *
	 * @var
	 */
	public $error;
	/**
	 * Registration url.
	 *
	 * @var string
	 */
	private static $registrationUrl = 'https://api.yetiforce.com/registration/';
	/**
	 * Companies details.
	 *
	 * @var null|string[]
	 */
	public $companies;
	/**
	 * Registration file path.
	 *
	 * @var string
	 */
	private const REGISTRATION_FILE = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'user_privileges' . \DIRECTORY_SEPARATOR . 'registration.php';
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
		8 => 'LBL_SPECIAL_REGISTRATION',
		9 => 'LBL_ACCEPTED',
	];

	/**
	 * Generate a unique key for the crm instance.
	 *
	 * @return string
	 */
	private function getKey(): string
	{
		return sha1(\AppConfig::main('site_URL') . $_SERVER['SERVER_ADDR']);
	}

	/**
	 * Get registration data.
	 *
	 * @return string[]
	 */
	private function getData(): array
	{
		$companies = $this->companies ?? \App\Company::getAll();
		foreach ($companies as &$row) {
			if (\file_exists(\Settings_Companies_Record_Model::$logoPath . $row['id'])) {
				$row['logo'] = \App\Fields\File::getImageBaseData(\Settings_Companies_Record_Model::$logoPath . $row['id']);
			}
		}
		return [
			'version' => \App\Version::get(),
			'language' => \App\Language::getLanguage(),
			'timezone' => date_default_timezone_get(),
			'key' => $this->getKey(),
			'companies' => $companies,
		];
	}

	/**
	 * Send registration data.
	 *
	 * @return bool
	 */
	public function send(): bool
	{
		if (gethostbyname('yetiforce.com') === 'yetiforce.com') {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION');
			$this->error = 'ERR_NO_INTERNET_CONNECTION';
			return false;
		}
		$result = false;
		try {
			$data = $this->getData();
			$response = (new \GuzzleHttp\Client())
				->post(static::$registrationUrl . 'add',
					\App\RequestHttp::getOptions() + [
						'form_params' => $data
					]);
			$body = $response->getBody();
			if (!\App\Json::isEmpty($body)) {
				$body = \App\Json::decode($body);
				if ($body['text'] === 'OK') {
					$this->updateMetaData($body + $data);
					$result = true;
				}
			}
		} catch (\Throwable $e) {
			$this->error = $e->getMessage();
		}
		return $result;
	}

	/**
	 * Update registration data.
	 *
	 * @param string[] $data
	 */
	private function updateMetaData(array $data): void
	{
		file_put_contents(static::REGISTRATION_FILE, '<?php return ' . \var_export([
				'time' => date('Y-m-d H:i:s'),
				'status' => $data['status'],
				'text' => $data['text'],
				'crmKey' => $data['key'],
				'serialKey' => $data['serialKey'],
			], true) . ';');
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
		return strcmp($serial, $key . substr(sha1($key), 5, 15)) !== 0;
	}

	/**
	 * Registration verification.
	 *
	 * @param bool $timer
	 *
	 * @return array
	 */
	public static function verify($timer = false): array
	{
		if (!\file_exists(static::REGISTRATION_FILE)) {
			return [false, 0];
		}
		$reg = require static::REGISTRATION_FILE;
		$status = $reg['status'] > 7;
		if (!empty($reg['serialKey']) && $status && static::verifySerial($reg['serialKey'])) {
			return [true, 9];
		}
		if ($timer && strtotime('+14 days', strtotime($reg['time'])) > \strtotime('now')) {
			$status = true;
		}
		return [$status, $reg['status']];
	}
}
