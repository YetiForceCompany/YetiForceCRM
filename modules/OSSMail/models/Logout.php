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
}
