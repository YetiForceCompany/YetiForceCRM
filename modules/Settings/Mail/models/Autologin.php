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
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$param = $users = [];
		$sql = "SELECT * FROM roundcube_users WHERE password <> '';";
		$result = $db->query($sql);

		while ($row = $db->fetch_array($result)) {
			$users[] = $row;
		}
		return $users;
	}

	public function getAutologinUsers($user_id)
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$users = [];
		$sql = 'SELECT crmuser_id FROM roundcube_users_autologin WHERE rcuser_id = ?;';
		$result = $db->pquery($sql, [$user_id]);
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$users[] = $db->query_result_raw($result, $i, 'crmuser_id');
		}
		return $users;
	}

	public function updateUsersAutologin($id, $users)
	{
		$db = PearDatabase::getInstance();
		if (!$users)
			$users = [];
		$db->pquery('DELETE FROM roundcube_users_autologin WHERE rcuser_id = ?', [$id]);
		foreach ($users as $user) {
			$db->pquery('INSERT INTO roundcube_users_autologin (`rcuser_id`,`crmuser_id`) VALUES (?, ?);', [$id, $user]);
		}
	}

	/**
	 * Function to get instance
	 * @param <Boolean> true/false
	 * @return <Settings_Leads_Mapping_Model>
	 */
	public static function getInstance()
	{
		$instance = new self();
		return $instance;
	}
}
