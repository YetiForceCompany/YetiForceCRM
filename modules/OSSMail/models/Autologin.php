<?php
/**
 * OSSMail autologin model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * OSSMail autologin model class
 */
class OSSMail_Autologin_Model
{

	/**
	 * Get autologin users
	 * @return array
	 */
	public static function getAutologinUsers()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$user_id = $currentUserModel->getId();
		$users = [];
		$query = (new \App\Db\Query())->select(['rcuser_id', 'crmuser_id', 'username', 'password'])->from('roundcube_users_autologin')->innerJoin('roundcube_users', 'roundcube_users_autologin.rcuser_id = roundcube_users.user_id')->where(['roundcube_users_autologin.crmuser_id' => $user_id]);
		$rcUser = isset($_SESSION['AutoLoginUser']) ? $_SESSION['AutoLoginUser'] : false;
		$dataReader = $query->createCommand()->query();
		while ($account = $dataReader->read()) {
			$account['active'] = ($rcUser && $rcUser == $account['rcuser_id']) ? true : false;
			$users[$account['rcuser_id']] = $account;
		}
		$dataReader->close();
		return $users;
	}
}
