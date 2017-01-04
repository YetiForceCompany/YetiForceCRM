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

class OSSMail_Autologin_Model
{

	public static function getAutologinUsers()
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$user_id = $currentUserModel->getId();
		$users = [];
		$sql = 'SELECT rcuser_id, crmuser_id, username, password FROM roundcube_users_autologin '
			. 'INNER JOIN roundcube_users ON roundcube_users_autologin.rcuser_id = roundcube_users.user_id WHERE crmuser_id = ?;';
		$result = $db->pquery($sql, [$user_id]);
		$rcUser = isset($_SESSION['AutoLoginUser']) ? $_SESSION['AutoLoginUser'] : false;
		$numRowsResult = $db->num_rows($result);
		for ($i = 0; $i < $numRowsResult; $i++) {
			$account = $db->raw_query_result_rowdata($result, $i);
			$account['active'] = ($rcUser && $rcUser == $account['rcuser_id']) ? true : false;
			$users[$account['rcuser_id']] = $account;
		}
		return $users;
	}
}
