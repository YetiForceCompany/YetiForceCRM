<?php
/**
 * YetiForce register config file.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce register class.
 */
final class Config
{
	/** @var string URL */
	public const TABLE_NAME = 's_#__reg_data';

	private const CACHE_NAME = 'YetiForce:config';

	/** @var array Data */
	private array $data = [];

	/** Constructor */
	public function __construct()
	{
		if (!\App\Cache::has(self::CACHE_NAME, '')) {
			$db = \App\Db::getInstance('admin');
			$this->data = (new \App\Db\Query())->from(self::TABLE_NAME)->one($db) ?: [];
			if (!$this->data) {
				$data = (new Encryption())->getDefaultData();
				$result = $db->createCommand()->insert(self::TABLE_NAME, $data)->execute();
				$this->data = $result ? $data : [];
				\App\Cache::save(self::CACHE_NAME, '', $this->data, \App\Cache::LONG);
			}
		} else {
			$this->data = \App\Cache::get(self::CACHE_NAME, '');
		}
	}

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * Get token.
	 *
	 * @return string
	 */
	public function getToken(): string
	{
		return $this->data['key'] ?? '';
	}

	/**
	 * Set Token.
	 *
	 * @param string $token
	 *
	 * @return bool
	 */
	public function setToken(string $token): bool
	{
		\App\Purifier::purifyByType($token, \App\Purifier::ALNUM2);
		$result = \App\Db::getInstance('admin')->createCommand()->update(self::TABLE_NAME, ['key' => $token])->execute();
		if ($result) {
			\App\Cache::delete(self::CACHE_NAME, '');
			$this->data['key'] = $token;
		}
		return (bool) $result;
	}
}
