<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

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
