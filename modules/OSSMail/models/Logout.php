<?php
/**
 * OSSMail logout model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * OSSMail logout model class.
 */
class OSSMail_Logout_Model
{
	/**
	 * Logout current user.
	 *
	 * @throws \yii\db\Exception
	 */
	public static function logoutCurrentUser()
	{
		if ($sessionId = $_COOKIE['roundcube_sessid'] ?? null) {
			$cookie = session_get_cookie_params();
			$secure = $cookie['secure'] || \App\RequestUtil::isHttps();
			$exp = time() - 3600;
			setcookie('roundcube_sessid', '', $exp, $cookie['path'], $cookie['domain'], $secure, true);
			setcookie('roundcube_sessauth', '', $exp, $cookie['path'], $cookie['domain'], $secure, true);
			\App\Db::getInstance()->createCommand()
				->delete('roundcube_session', ['sess_id' => $sessionId])
				->execute();
		}
	}

	/**
	 * Get a list of sessions by Crm user ID.
	 *
	 * @param int $userId Crm user ID
	 *
	 * @return string[]
	 */
	public static function getSessId(int $userId)
	{
		$roundCubeUsers = (new \App\Db\Query())->select(['user_id'])
			->from('roundcube_users')->where(['crm_user_id' => $userId])->column();
		$arraySess = [];
		$dataReader = (new \App\Db\Query())->from('roundcube_session')->createCommand()->query();
		while ($row = $dataReader->read()) {
			$sessData = \App\Session\File::unserialize(base64_decode($row['vars']));
			if (isset($sessData['user_id']) && \in_array((int) $sessData['user_id'], $roundCubeUsers)) {
				$arraySess[] = $row['sess_id'];
			}
		}
		$dataReader->close();
		return $arraySess;
	}

	/**
	 * Log out user by ID.
	 *
	 * @param int $userId
	 *
	 * @throws \yii\db\Exception
	 */
	public static function logutUserById(int $userId)
	{
		$sessId = static::getSessId($userId);
		if ($sessId) {
			\App\Db::getInstance()->createCommand()
				->delete('roundcube_session', ['sess_id' => $sessId])
				->execute();
		}
	}
}
