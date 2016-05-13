<?php

/**
 * API Authorization class
 * @package YetiForce.WebserviceSession
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class APISession
{

	public static function regenerateId()
	{
		return md5(time() . rand());
	}

	public static function init($userDetail)
	{
		$sessionId = self::regenerateId();
		$sessionData = [
			'id' => $sessionId,
			'user_id' => $userDetail['id'],
			'created' => date('Y-m-d H:i:s'),
			'changed' => date('Y-m-d H:i:s'),
			'ip' => '',
		];
		$dbPortal = PearDatabase::getInstance();
		$dbPortal->insert('w_yf_sessions', $sessionData);
		return $sessionData;
	}
}
