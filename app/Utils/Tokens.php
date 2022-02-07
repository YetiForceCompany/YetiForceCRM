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
	/**
	 * Execute token method.
	 *
	 * @param string     $uid
	 * @param array|null $tokenData
	 *
	 * @return bool
	 */
	public static function execute(string $uid, array $tokenData = null): bool
	{
		if (null === $tokenData) {
			$tokenData = self::get($uid);
		}
		if (null !== $tokenData) {
			return \call_user_func($tokenData['method'], $tokenData['params']);
		}
		return false;
	}

	/**
	 * Generate token.
	 *
	 * @param string      $method         Method name
	 * @param array       $params
	 * @param string|null $expirationDate Date and time until which the token is valid
	 *
	 * @return string
	 */
	public static function generate(string $method, array $params, string $expirationDate = null): string
	{
		if (!\is_callable($method) && !class_exists($method)) {
			throw new \App\Exceptions\AppException("The method `$method` does not exist");
		}
		$uid = self::generateUid();
		\App\Db::getInstance('admin')->createCommand()->insert('s_#__tokens', [
			'uid' => $uid,
			'method' => $method,
			'params' => \App\Json::encode($params),
			'created_by_user' => \App\User::getCurrentUserRealId(),
			'created_date' => date('Y-m-d H:i:s'),
			'expiration_date' => $expirationDate,
		])->execute();
		return $uid;
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
	public static function get(string $uid, bool $remove = true): ?array
	{
		$row = (new \App\Db\Query())->from('s_#__tokens')->where(['uid' => $uid])->one(\App\Db::getInstance('admin')) ?: null;
		if (!empty($row['expiration_date']) && strtotime($row['expiration_date']) < time()) {
			self::delete($uid);
			$row = null;
		}
		if ($row) {
			$row['params'] = \App\Json::decode($row['params']);
		}
		if ($remove) {
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
		\App\Db::getInstance('admin')->createCommand()->delete('s_#__tokens', ['uid' => $uid])->execute();
	}
}
