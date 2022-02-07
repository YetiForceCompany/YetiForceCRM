<?php
/**
 * Mail message file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail;

/**
 * Mail message class.
 */
class Message
{
	/**
	 * Get instance by scanner engine.
	 *
	 * @param string $engineName
	 *
	 * @return \App\Mail\ScannerEngine\Base
	 */
	public static function getScannerByEngine(string $engineName): ScannerEngine\Base
	{
		$class = "App\\Mail\\ScannerEngine\\{$engineName}";
		if (!class_exists($class)) {
			throw new \App\Exceptions\NotAllowedMethod('ERR_PARAMETER_DOES_NOT_EXIST|$engineName|' . $engineName, 406);
		}
		return new $class();
	}

	/**
	 * Find by crm unique id.
	 *
	 * @param string $cid
	 *
	 * @return int|bool
	 */
	public static function findByCid(string $cid)
	{
		if (\App\Cache::staticHas('App\Mail\Message::findByCid', $cid)) {
			return \App\Cache::staticGet('App\Mail\Message::findByCid', $cid);
		}
		$mailCrmId = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['cid' => $cid])->scalar();
		\App\Cache::staticSave('App\Mail\Message::findByCid', $cid, $mailCrmId);
		return $mailCrmId;
	}

	/**
	 * Find by message id and rc user id.
	 *
	 * @param string $cid
	 * @param string $messageId
	 * @param int    $rcUser
	 *
	 * @return int|bool
	 */
	public static function findByMessageId(string $messageId, int $rcUser)
	{
		$key = "$messageId|$rcUser";
		if (\App\Cache::staticHas('App\Mail\Message::findByMessageId', $key)) {
			return \App\Cache::staticGet('App\Mail\Message::findByMessageId', $key);
		}
		$mailCrmId = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['uid' => $messageId, 'rc_user' => $rcUser])->scalar();
		\App\Cache::staticSave('App\Mail\Message::findByMessageId', $key, $mailCrmId);
		return $mailCrmId;
	}
}
