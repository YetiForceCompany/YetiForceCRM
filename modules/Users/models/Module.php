<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */

class Users_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		if ($sourceModule === 'Users' && $field === 'reports_to_id' && !empty($record)) {
			$queryGenerator->addNativeCondition(['<>', 'vtiger_users.id', $record]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function isWorkflowSupported()
	{
		return true;
	}

	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 *
	 * @param string    $searchValue  - Search value
	 * @param <Integer> $parentId     - parent recordId
	 * @param string    $parentModule - parent module name
	 *
	 * @return <Array of Users_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId = false, $parentModule = false, $relatedModule = false)
	{
		if (!empty($searchValue)) {
			$dataReader = (new App\Db\Query())->from('vtiger_users')
				->where(['and', ['or', ['like', 'first_name', $searchValue], ['like', 'last_name', $searchValue]], ['status' => 'Active']])
				->createCommand()->query();
			$matchingRecords = [];
			while ($row = $dataReader->read()) {
				$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', 'Users');
				$recordInstance = new $modelClassName();
				$matchingRecords['Users'][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($this);
			}
			$dataReader->close();

			return $matchingRecords;
		}
	}

	/**
	 * Function returns the default column for Alphabetic search.
	 *
	 * @return string columnname
	 */
	public function getAlphabetSearchField()
	{
		return 'last_name';
	}

	/**
	 * Function to get the url for the Create Record view of the module.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&parent=Settings&view=' . $this->getEditViewName();
	}

	/**
	 * Function to get the url for list view of the module.
	 *
	 * @return string - url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&parent=Settings&view=' . $this->getListViewName();
	}

	/**
	 * Function to store the login history.
	 *
	 * @param type $userName
	 */
	public function saveLoginHistory($userName, $status = 'Signed in')
	{
		$userIPAddress = \App\RequestUtil::getRemoteIP();
		$browser = \App\RequestUtil::getBrowserInfo();
		\App\Db::getInstance()->createCommand()
			->insert('vtiger_loginhistory', [
				'user_name' => $userName,
				'user_ip' => empty($userIPAddress) ? '-' : $userIPAddress,
				'login_time' => date('Y-m-d H:i:s'),
				'logout_time' => null,
				'status' => $status,
				'browser' => $browser->name . ' ' . $browser->ver,
			])->execute();
	}

	/**
	 * Function to store the logout history.
	 */
	public function saveLogoutHistory()
	{
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		$userIPAddress = \App\RequestUtil::getRemoteIP();
		$outtime = date('Y-m-d H:i:s');

		$loginId = (new \App\Db\Query())
			->select(['login_id'])
			->from('vtiger_loginhistory')
			->where(['user_name' => $userRecordModel->get('user_name'), 'user_ip' => $userIPAddress])
			->limit(1)->orderBy('login_id DESC')->scalar();
		if ($loginId !== false) {
			\App\Db::getInstance()->createCommand()
				->update('vtiger_loginhistory', [
					'logout_time' => $outtime,
					'status' => 'Signed off',
				], ['login_id' => $loginId])
				->execute();
		}
	}

	/**
	 * Check mail exist.
	 *
	 * @param string    $email
	 * @param int|false $userId
	 *
	 * @return bool
	 */
	public static function checkMailExist($email, $userId = false)
	{
		$query = (new \App\Db\Query())->from('vtiger_users')->where(['email1' => $email]);
		if ($userId) {
			$query->andWhere(['<>', 'id', $userId]);
		}
		return $query->exists();
	}

	/**
	 * Validation of user name.
	 *
	 * @param string    $userName
	 * @param int|false $userId
	 *
	 * @return bool
	 */
	public static function checkUserName($userName, $userId = false)
	{
		$query = (new \App\Db\Query())->from('vtiger_users')->where(['or', ['user_name' => $userName, 'user_name' => strtolower($userName)]]);
		if ($userId) {
			$query->andWhere(['<>', 'id', $userId]);
		}
		if ($query->exists()) {
			return \App\Language::translate('LBL_USER_NAME_EXISTS', 'Users');
		}
		if ($userId && AppConfig::module('Users', 'CHECK_LAST_USERNAME') && (new \App\Db\Query())->from('l_#__username_history')->where(['or', ['user_name' => $userName, 'user_name' => strtolower($userName)]])->exists()) {
			return \App\Language::translate('LBL_USER_NAME_HAS_ALREADY_BEEN_USED', 'Users');
		}
		$blacklist = require 'config/username_blacklist.php';
		if (in_array(strtolower($userName), $blacklist)) {
			return \App\Language::translate('LBL_FORBIDDEN_USERNAMES', 'Users');
		}
		return false;
	}

	/**
	 * Get switch users.
	 *
	 * @param bool $showRole
	 *
	 * @return array
	 */
	public static function getSwitchUsers($showRole = false)
	{
		require 'user_privileges/switchUsers.php';
		$baseUserId = \App\User::getCurrentUserRealId();
		$users = $userIds = [];
		if (isset($switchUsers[$baseUserId])) {
			foreach ($switchUsers[$baseUserId] as $userId => &$userName) {
				$users[$userId] = ['userName' => $userName];
				$userIds[] = $userId;
			}
			if ($showRole) {
				$dataReader = (new \App\Db\Query())->select(['vtiger_role.rolename', 'vtiger_user2role.userid'])->from('vtiger_role')
					->leftJoin('vtiger_user2role', 'vtiger_role.roleid = vtiger_user2role.roleid')
					->where(['vtiger_user2role.userid' => $userIds])
					->createCommand()->query();
				while ($row = $dataReader->read()) {
					$users[$row['userid']]['roleName'] = $row['rolename'];
				}
				$dataReader->close();
			}
			if ($users) {
				return $users;
			}
		}
		return [];
	}

	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getListViewName() . '&parent=Settings';
	}

	/**
	 * Function gives list fields for save.
	 *
	 * @return string[]
	 */
	public function getFieldsForSave(\Vtiger_Record_Model $recordModel)
	{
		$editFields = [];
		foreach (App\Field::getFieldsPermissions($this->getId(), false) as $field) {
			$editFields[] = $field['fieldname'];
		}
		return $editFields;
	}
}
