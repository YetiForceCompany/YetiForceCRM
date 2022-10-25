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
		$rcUser = \App\Session::has('AutoLoginUser') ? (int) \App\Session::get('AutoLoginUser') : 0;

		$queryGenerator = (new \App\QueryGenerator('MailAccount'));
		$queryGenerator->setFields(['id']);
		$queryGenerator->addCondition('mailaccount_status', 'PLL_ACTIVE', 'e');
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($recordId = $dataReader->readColumn(0)) {
			$users[$recordId] = [
				'id' => $recordId,
				'active' => $rcUser === (int) $recordId,
				'name' => \App\Record::getLabel($recordId, true)
			];
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
