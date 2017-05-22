<?php

/**
 * Settings mail autologin model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Mail_Autologin_Model
{

	public function getAccountsList()
	{
		return (new \App\Db\Query())->from('roundcube_users')
				->where(['<>', 'password', ''])
				->all();
	}

	public function getAutologinUsers($userId)
	{
		return (new \App\Db\Query())->select('crmuser_id')
				->from('roundcube_users_autologin')
				->where(['rcuser_id' => $userId])
				->createCommand()->queryColumn();
	}

	public function updateUsersAutologin($id, $users)
	{
		if (!$users) {
			$users = [];
		}
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('roundcube_users_autologin', ['rcuser_id' => $id])
			->execute();
		if (!empty($users)) {
			$insertData = [];
			foreach ($users as $user) {
				$insertData [] = [$id, $user];
			}
			$db->createCommand()->batchInsert('roundcube_users_autologin', ['rcuser_id', 'crmuser_id'], $insertData)
				->execute();
		}
	}

	/**
	 * Function to get instance
	 * @param boolean true/false
	 * @return <Settings_Mail_Autologin_Model>
	 */
	public static function getInstance()
	{
		return new self();
	}
}
