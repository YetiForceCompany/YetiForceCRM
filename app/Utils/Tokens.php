<?php
/**
 * Tokens utils file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Utils;

/**
 * Tokens utils class.
 */
class Tokens
{
	/** @var string Token table name. */
	const TABLE_NAME = 's_#__tokens';

	/** @var string Last generated token. */
	private static $lastToken;

	/**
	 * Generate token.
	 *
	 * @param string      $method         Method name
	 * @param array       $params
	 * @param string|null $expirationDate Date and time until which the token is valid
	 * @param bool        $oneTime
	 *
	 * @return string
	 */
	public static function generate(string $method, array $params, string $expirationDate = null, bool $oneTime = true): string
	{
		if (!\is_callable($method) && !class_exists($method)) {
			throw new \App\Exceptions\AppException("The method `$method` does not exist");
		}
		$uid = self::generateUid();
		\App\Db::getInstance('admin')->createCommand()->insert(self::TABLE_NAME, [
			'uid' => $uid,
			'method' => $method,
			'params' => \App\Json::encode($params),
			'created_by_user' => \App\User::getCurrentUserRealId(),
			'created_date' => date('Y-m-d H:i:s'),
			'expiration_date' => $expirationDate,
			'one_time_use' => (int) $oneTime,
		])->execute();
		return self::$lastToken = $uid;
	}

	/**
	 * Generate uid function.
	 *
	 * @return string
	 */
	private static function generateUid(): string
	{
		$uid = \App\Encryption::generatePassword(64);
		if (null !== self::get($uid)) {
			return self::generateUid();
		}
		return $uid;
	}

	/**
	 * Get token detail.
	 *
	 * @param string $uid
	 * @param bool   $remove
	 *
	 * @return array|null
	 */
	public static function get(string $uid, bool $remove = false): ?array
	{
		$row = (new \App\Db\Query())->from(self::TABLE_NAME)->where(['uid' => $uid])->one(\App\Db::getInstance('admin')) ?: null;
		if (!empty($row['expiration_date']) && strtotime($row['expiration_date']) < time()) {
			self::delete($uid);
			$row = null;
		}
		if ($row) {
			$row['params'] = \App\Json::decode($row['params']);
		}
		if ($remove || ($row && (bool) $row['one_time_use'])) {
			self::delete($uid);
		}
		return $row;
	}

	/**
	 * Delete token.
	 *
	 * @param string $uid
	 *
	 * @return void
	 */
	public static function delete(string $uid): void
	{
		\App\Db::getInstance('admin')->createCommand()->delete(self::TABLE_NAME, ['uid' => $uid])->execute();
	}

	/**
	 * Generate URL form token.
	 *
	 * @param string|null $token
	 * @param int|null    $serverId
	 *
	 * @return string
	 */
	public static function generateLink(?string $token = null, ?int $serverId = null): string
	{
		if (null === $token) {
			$token = self::$lastToken;
		}
		$url = \App\Config::main('site_URL');
		if (0 === $serverId) {
			$row = (new \App\Db\Query())->from('w_#__servers')->where(['type' => 'Token'])->one(\App\Db::getInstance('webservice')) ?: [];
			if ($row && $row['url']) {
				$url = $row['url'];
				if ('/' !== substr($url, -1)) {
					$url .= '/';
				}
			}
		} elseif ($serverId && ($data = \App\Fields\ServerAccess::get($serverId)) && $data['url']) {
			$url = $data['url'];
			if ('/' !== substr($url, -1)) {
				$url .= '/';
			}
		}
		return $url . 'webservice/Token/' . $token;
	}
}
