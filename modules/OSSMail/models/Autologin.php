<?php
/**
 * OSSMail autologin model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * OSSMail autologin model class.
 */
class OSSMail_Autologin_Model
{
	/**
	 * Get autologin users.
	 *
	 * @return array
	 */
	public static function getAutologinUsers()
	{
		$users = [];
		$query = (new \App\Db\Query())->select(['rcuser_id', 'crmuser_id', 'username', 'password'])
			->from('roundcube_users_autologin')
			->innerJoin('roundcube_users', 'roundcube_users_autologin.rcuser_id = roundcube_users.user_id')
			->where(['roundcube_users_autologin.crmuser_id' => \App\User::getCurrentUserId()])
			->orderBy(['active' => SORT_DESC]);
		$rcUser = \App\Session::has('AutoLoginUser') ? (int) \App\Session::get('AutoLoginUser') : 0;
		$dataReader = $query->createCommand()->query();
		while ($account = $dataReader->read()) {
			$account['active'] = $rcUser === (int) $account['rcuser_id'];
			$users[$account['rcuser_id']] = $account;
		}
		$dataReader->close();
		return $users;
	}

	/**
	 * Update last active mail.
	 *
	 * @param int $user
	 *
	 * @return void
	 */
	public static function updateActive(int $user)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->update('roundcube_users_autologin', ['active' => 0], ['crmuser_id' => App\User::getCurrentUserId()])->execute();
		$dbCommand->update('roundcube_users_autologin', ['active' => 1], ['rcuser_id' => $user, 'crmuser_id' => App\User::getCurrentUserId()])->execute();
	}
}
