<?php

/**
 * Abstract Social media class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App\SocialMedia;

/**
 * Abstract Social media class.
 */
abstract class Base
{
	/**
	 * Allowed type of log.
	 */
	public const ALLOWED_TYPE_OF_LOG = ['info', 'warning', 'error'];

	/**
	 * @var string
	 */
	protected static $socialMediaType;

	/**
	 * Base constructor.
	 *
	 * @param string $userName
	 */
	abstract public function __construct(string $userName);

	/**
	 * Is configured.
	 *
	 * @return bool
	 */
	abstract public static function isActive();

	/**
	 * Remove mass social media records from DB.
	 *
	 * @param string[] $logins
	 *
	 * @return mixed
	 */
	abstract public static function removeMass(array $logins);

	/**
	 * Retrieve data from Api.
	 */
	abstract public function retrieveDataFromApi();

	/**
	 * Remove social media account from database.
	 */
	abstract public function remove();

	/**
	 * Check if the current user exists.
	 *
	 * @return  bool
	 */
	abstract public function isExists();

	/**
	 * Log info.
	 *
	 * @param string $message
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \yii\db\Exception
	 */
	public static function logInfo(string $message)
	{
		static::log('info', $message);
	}

	/**
	 * Log error.
	 *
	 * @param string $message
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \yii\db\Exception
	 */
	public static function logError(string $message)
	{
		static::log('error', $message);
	}

	/**
	 * Log warning.
	 *
	 * @param string $message
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \yii\db\Exception
	 */
	public static function logWarning(string $message)
	{
		static::log('warning', $message);
	}

	/**
	 * Log to db.
	 *
	 * @param string $typeOfLog
	 * @param string $message
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \yii\db\Exception
	 */
	public static function log(string $typeOfLog, string $message)
	{
		if (!\in_array($typeOfLog, static::ALLOWED_TYPE_OF_LOG)) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE');
		}
		\App\Db::getInstance()
			->createCommand()
			->insert('l_#__social_media_logs', [
				'date' => date('Y-m-d H:i:s'),
				'type' => $typeOfLog,
				'name' => static::$socialMediaType,
				'message' => $message,
			])->execute();
	}
}
