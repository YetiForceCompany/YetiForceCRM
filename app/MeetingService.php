<?php

/**
 * Meeting Service.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	private const TABLE_NAME = 's_#__meeting_services';

	/**
	 * @var int Status active
	 */
	private const STATUS_ACTIVE = 1;

	/**
	 * Return object instance.
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		$instance = new self();
		$instance->setData($instance->getService(true));
		return $instance;
	}

	/**
	 * Undocumented function.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return self::STATUS_ACTIVE === $this->get('status');
	}

	/**
	 * Gets service data.
	 *
	 * @param bool $active
	 *
	 * @return array
	 */
	public function getService(bool $active = true): array
	{
		$cacheName = 'MeetingService::getService';
		if (!Cache::has($cacheName, $active)) {
			$query = (new \App\Db\Query())->from(self::TABLE_NAME);
			if ($active) {
				$query->where(['status' => self::STATUS_ACTIVE]);
			}
			$result = $query->one();
			Cache::save($cacheName, $active, $result ? $result : [], Cache::LONG);
		}

		return Cache::get($cacheName, $active);
	}

	/**
	 * Gets URL address.
	 *
	 * @param string    $roomName
	 * @param int|null  $userId
	 * @param bool|null $moderator
	 * @param array     $data
	 *
	 * @return string
	 */
	public function getUrl(array $data, ?int $userId = null, ?bool $moderator = false): string
	{
		if ($userId) {
			$userModel = \App\User::getUserModel($userId);
			$data['context']['user'] = [
				'avatar' => '',
				'name' => \App\Fields\Owner::getLabel($userId),
				'email' => $userModel->getDetail('email1'),
				'id' => $userId
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
		return hash('sha1', $prefix . microtime(true));
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
		return  \substr($path, 1);
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
