<?php

/**
 * API Authorization class
 * @package YetiForce.WebserviceSession
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class APISession extends Vtiger_Base_Model
{

	public static function regenerateId()
	{
		return md5(time() . rand());
	}

	public static function init($userDetail, $params = [])
	{
		$sessionId = self::regenerateId();
		$sessionData = [
			'id' => $sessionId,
			'user_id' => $userDetail['id'],
			'created' => date('Y-m-d H:i:s'),
			'changed' => date('Y-m-d H:i:s')
		];
		if(isset($params['ip'])){
			$sessionData['ip'] = $params['ip'];
		}
		if(isset($params['language'])){
			$sessionData['language'] = $params['language'];
		}
		$db = PearDatabase::getInstance();
		$db->insert('w_yf_sessions', $sessionData);
		return $sessionData;
	}

	public static function checkSession($sessionId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM w_yf_sessions WHERE id = ? LIMIT 1', [$sessionId]);
		if ($session = $db->getRow($result)) {
			$sessionModel = self::getInstance();
			$sessionModel->setData($session);
			return $sessionModel;
		} else {
			return false;
		}
	}

	public static function getInstance()
	{
		return new self();
	}
}
