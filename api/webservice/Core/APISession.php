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

	public static function init($userDetail, $params)
	{
		$sessionId = self::regenerateId();
		$sessionData = [
			'id' => $sessionId,
			'user_id' => $userDetail['id'],
			'created' => date('Y-m-d H:i:s'),
			'changed' => date('Y-m-d H:i:s'),
			'ip' => $params['ip'],
		];
		if (key_exists('language', $params)) {
			$sessionData['language'] = $params['language'];
		} else {
			$sessionData['language'] = $userDetail['language'];
		}

		$dbPortal = PearDatabase::getInstance('portal');
		$dbPortal->insert('w_yf_sessions', $sessionData);
		return $sessionData;
	}
}
