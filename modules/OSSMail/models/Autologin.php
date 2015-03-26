<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class OSSMail_Autologin_Model {
	public function getAutologinUsers($user_id) {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$users = [];
		$sql = 'SELECT crmuser_id, username, password FROM roundcube_users_autologin '
				. 'INNER JOIN roundcube_users ON roundcube_users_autologin.rcuser_id = roundcube_users.user_id WHERE rcuser_id = ?;';
		$result = $db->pquery($sql,[$user_id]);
		for($i = 0; $i < $db->num_rows($result); $i++){
			$users[$db->query_result_raw($result, $i, 'rcuser_id')] =  $db->raw_query_result_rowdata($result, $i);
		}
		return $users;
	}
	
}