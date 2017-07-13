<?php

/**
 * OSSMail autologin model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
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
