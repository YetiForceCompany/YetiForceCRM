<?php

/**
 * API Authorization class for POS
 * @package YetiForce.WebserviceSession
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class APISessionPOS
{

	public static function checkSession($sessionId)
	{
		$dbPortal = PearDatabase::getInstance();
		$result = $dbPortal->pquery('SELECT * FROM w_yf_portal_sessions WHERE id = ? LIMIT 1', [$sessionId]);
		if($session = $dbPortal->getRow($result)){
			return $session;
		} else {
			throw new APIException('Invalid session Id ', 401);
		}
	}

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
		$dbPortal->insert('w_yf_portal_sessions', $sessionData);
		return $sessionData;
	}
}
