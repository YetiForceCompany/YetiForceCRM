<?php

/**
 * Meeting Service.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Class MeetingService.
 */
class MeetingService extends Base
{
	/**
	 * Table name.
	 */
	public const TABLE_NAME = 's_#__meeting_services';

	/**
	 * @var int Status active
	 */
	private const STATUS_ACTIVE = 1;

	/**
	 * @var int Default service ID
	 */
	public const DEFAULT_SERVICE = 0;

	/**
	 * Return object instance.
	 *
	 * @param int $serviceId
	 *
	 * @return self
	 */
	public static function getInstance(int $serviceId = self::DEFAULT_SERVICE): self
	{
		$instance = new self();
		$instance->setData(self::getService($serviceId));
		return $instance;
	}

	/**
	 * Checks if service is active.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return self::STATUS_ACTIVE === $this->get('status');
	}

	/**
	 * Gets services data.
	 *
	 * @return array
	 */
	public static function getServices(): array
	{
		$cacheName = 'MeetingService::getServices';
		if (!Cache::has($cacheName, '')) {
			$result = (new \App\Db\Query())->from(self::TABLE_NAME)->orderBy(['status' => SORT_DESC])->indexBy('id')->all();
			Cache::save($cacheName, '', $result, Cache::LONG);
		}
		return Cache::get($cacheName, '');
	}

	/**
	 * Gets service data.
	 *
	 * @param int $serviceId
	 *
	 * @return array
	 */
	public static function getService(int $serviceId): array
	{
		if (self::DEFAULT_SERVICE === $serviceId) {
			return self::getDefaultService();
		}
		return self::getServices()[$serviceId] ?? [];
	}

	/**
	 * Gets default service data.
	 *
	 * @return array
	 */
	public static function getDefaultService(): array
	{
		return current(self::getServices()) ?: [];
	}

	/**
	 * Gets URL address.
	 *
	 * @param array     $data
	 * @param int|null  $userId
	 * @param bool|null $moderator
	 *
	 * @return string
	 */
	public function getUrl(array $data, ?int $userId = null, ?bool $moderator = false): string
	{
		if ($userId) {
			$userModel = \App\User::getUserModel($userId);
			$data['context']['user'] = [
				'avatar' => '',
				'name' => $userModel->getName(),
				'email' => $userModel->getDetail('email1'),
				'id' => $userId,
			];
		}
		$data['room'] = $data['room'] ?? $this->generateRoomName();
		$data['moderator'] = $moderator;

		return $this->get('url') . $data['room'] . '?jwt=' . $this->getToken($data);
	}

	/**
	 * Generate room name.
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function generateRoomName(string $prefix = ''): string
	{
		$prefix = preg_replace_callback_array([
			'/[^a-z0-9 ]/' => function () {
				return '';
			},
			'/\b[a-z]/' => function ($matches) {
				return mb_strtoupper($matches[0]);
			},
			'/[\s]/' => function () {
				return '';
			},
		], strtolower(\App\Utils::sanitizeSpecialChars($prefix, ' ')));
		[$msec, $sec] = explode(' ', microtime());
		return $prefix . 'ID' . str_replace('.', '', $sec . $msec) . random_int(0, 1000);
	}

	/**
	 * Gets room name from URL.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function getRoomFromUrl(string $url): string
	{
		$path = parse_url($url, PHP_URL_PATH);
		return \substr($path, 1);
	}

	/**
	 * Check URL.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function validateUrl(string $url): bool
	{
		$result = false;
		$query = parse_url($url, PHP_URL_QUERY);
		parse_str($query, $output);
		if (!empty($output['jwt']) && 0 === strpos($url, $this->get('url'))) {
			try {
				$jwt = new \Ahc\Jwt\JWT(\App\Encryption::getInstance()->decrypt($this->get('secret')));
				$jwt->decode($output['jwt']);
				$result = true;
			} catch (\Ahc\Jwt\JWTException $e) {
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Gets data from URL.
	 *
	 * @param string $url
	 *
	 * @return array
	 */
	public function getDataFromUrl(string $url): array
	{
		$query = parse_url($url, PHP_URL_QUERY);
		parse_str($query, $output);
		$jwt = new \Ahc\Jwt\JWT(\App\Encryption::getInstance()->decrypt($this->get('secret')));
		return $jwt->decode($output['jwt']);
	}

	/**
	 * Gets token.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	private function getToken(array $data): string
	{
		$data['aud'] = $this->get('key');
		$data['iss'] = $this->get('key');
		$data['sub'] = $this->get('url');
		$data['exp'] = $data['exp'] ?? strtotime("+{$this->get('duration')} minutes");
		$jwt = new \Ahc\Jwt\JWT(\App\Encryption::getInstance()->decrypt($this->get('secret')));

		return $jwt->encode($data);
	}
}
