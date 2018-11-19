<?php
/**
 * OSSMail logout model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if (isset($_COOKIE['roundcube_sessid'])) {
			setcookie('roundcube_sessid', '', time() - 3600, '/');
			setcookie('roundcube_sessauth', '', time() - 3600, '/');
			\App\Db::getInstance()->createCommand()
				->delete('roundcube_session', ['sess_id' => $_COOKIE['roundcube_sessid']])
				->execute();
		}
	}

	/**
	 * Get a list of sessions by Crm user ID.
	 *
	 * @param int $userId Crm user ID
	 *
	 * @return array
	 */
	public static function getSessId(int $userId)
	{
		$roundCubeUsers = (new \App\Db\Query())->select(['user_id'])
			->from('roundcube_users')->where(['crm_user_id' => $userId])->column();
		$arraySess = [];
		$dataReader = (new \App\Db\Query())->from('roundcube_session')->createCommand()->query();
		while ($row = $dataReader->read()) {
			$sessData = explode(';', base64_decode($row['vars']));
			if (is_array($sessData)) {
				foreach ($sessData as $val) {
					$data = explode('|', $val);
					if (isset($data[0]) && $data[0] === 'user_id') {
						$param = explode(':', $data[1]);
						if ($param !== false && isset($param[2]) && in_array((int) str_replace('"', '', $param[2]), $roundCubeUsers)) {
							$arraySess[] = $row['sess_id'];
						}
					}
				}
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
		if (count($sessId)) {
			\App\Db::getInstance()->createCommand()
				->delete('roundcube_session', ['sess_id' => $sessId])
				->execute();
		}
	}
}
