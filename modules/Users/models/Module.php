<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
	public function getQueryByModuleField($sourceModule, $field, $record, App\QueryGenerator $queryGenerator)
	{
		if ('Users' === $sourceModule && 'reports_to_id' === $field && !empty($record)) {
			$queryGenerator->addNativeCondition(['<>', 'vtiger_users.id', $record]);
		}
	}

	/** {@inheritdoc} */
	public function isWorkflowSupported()
	{
		return true;
	}

	/** {@inheritdoc} */
	public function isUtilityActionEnabled()
	{
		return true;
	}

	/** {@inheritdoc} */
	public function isCustomViewAdvCondEnabled(): bool
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getQueryForRecords(string $searchValue, int $limit, int $srcRecord = null): App\QueryGenerator
	{
		$searchTableName = 'u_#__users_labels';
		$searchColumnName = "{$searchTableName}.label";

		$queryGenerator = new \App\QueryGenerator($this->getName());
		$queryGenerator->setFields(['id'])
			->setCustomColumn(['search_label' => $searchColumnName])
			->addJoin(['INNER JOIN', $searchTableName, "{$queryGenerator->getColumnName('id')} = {$searchTableName}.id"])
			->addCondition('status', 'Active', 'e')
			->addNativeCondition(['like', $searchColumnName, $searchValue])
			->setLimit($limit);
		if ($srcRecord) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}
		return $queryGenerator;
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
	 * @param string $userName
	 * @param string $status
	 *
	 * @return void
	 */
	public function saveLoginHistory(string $userName, string $status): void
	{
		$userIPAddress = \App\RequestUtil::getRemoteIP();
		$browser = \App\RequestUtil::getBrowserInfo();
		\App\Db::getInstance()->createCommand()
			->insert('vtiger_loginhistory', [
				'user_name' => $userName,
				'user_ip' => empty($userIPAddress) ? '-' : \App\TextUtils::textTruncate($userIPAddress, 252, true),
				'login_time' => date('Y-m-d H:i:s'),
				'logout_time' => null,
				'status' => $status,
				'browser' => $browser->name . ' ' . $browser->ver,
				'userid' => \App\User::getUserIdByName($userName),
				'agent' => \App\TextUtils::textTruncate(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 500, false),
			])->execute();
	}

	/**
	 * Function to store the logout history.
	 */
	public function saveLogoutHistory()
	{
		if (!empty(App\User::getCurrentUserRealId())) {
			$userRecordModel = Users_Record_Model::getInstanceById(App\User::getCurrentUserRealId(), 'Users');
		} else {
			$userRecordModel = Users_Record_Model::getCurrentUserModel();
		}
		$loginId = (new \App\Db\Query())
			->select(['login_id'])
			->from('vtiger_loginhistory')
			->where(['user_name' => $userRecordModel->get('user_name'), 'user_ip' => \App\RequestUtil::getRemoteIP()])
			->limit(1)->orderBy('login_id DESC')->scalar();
		if (false !== $loginId) {
			\App\Db::getInstance()->createCommand()
				->update('vtiger_loginhistory', [
					'logout_time' => date('Y-m-d H:i:s'),
					'status' => 'Signed off',
				], ['login_id' => $loginId])
				->execute();
		}
	}

	/**
	 * Get user login history.
	 *
	 * @param int|null $limit
	 *
	 * @return array
	 */
	public static function getLoginHistory($limit = null): array
	{
		return (new \App\Db\Query())->from('vtiger_loginhistory')
			->where(['or', ['user_name' => \App\Session::get('user_name')], ['userid' => \App\Session::get('authenticated_user_id')]])
			->orderBy(['login_id' => SORT_DESC])
			->limit($limit ?: App\Config::performance('LOGIN_HISTORY_VIEW_LIMIT'))
			->all();
	}

	/**
	 * Check mail exist.
	 *
	 * @param string    $email
	 * @param false|int $userId
	 *
	 * @return bool
	 */
	public static function checkMailExist($email, $userId = false)
	{
		$cacheKey = "$email|$userId";
		if (App\Cache::staticHas('Users_Module_Model::checkMailExist', $cacheKey)) {
			return App\Cache::staticGet('Users_Module_Model::checkMailExist', $cacheKey);
		}
		$query = (new \App\Db\Query())->from('vtiger_users')->where(['email1' => $email]);
		if ($userId) {
			$query->andWhere(['<>', 'id', $userId]);
		}
		$exists = $query->exists();
		App\Cache::staticSave('Users_Module_Model::checkMailExist', $cacheKey, $exists);
		return $exists;
	}

	/**
	 * Validation of user name.
	 *
	 * @param string    $userName
	 * @param false|int $userId
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
		if ($userId && App\Config::module('Users', 'CHECK_LAST_USERNAME') && (new \App\Db\Query())->from('l_#__username_history')->where(['or', ['user_name' => $userName, 'user_name' => strtolower($userName)]])->exists(\App\Db::getInstance('log'))) {
			return \App\Language::translate('LBL_USER_NAME_HAS_ALREADY_BEEN_USED', 'Users');
		}
		$blacklist = require 'config/username_blacklist.php';
		if (\in_array(strtolower($userName), $blacklist)) {
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
	public static function getSwitchUsers($showRole = false): array
	{
		require ROOT_DIRECTORY . '/user_privileges/switchUsers.php';
		$baseUserId = \App\User::getCurrentUserRealId();
		$users = [];
		if (isset($switchUsers[$baseUserId])) {
			foreach ($switchUsers[$baseUserId] as $userId) {
				$userModel = \App\User::getUserModel($userId);
				if (empty($userModel->getId()) || !$userModel->isActive()) {
					continue;
				}
				$users[$userId] = $userId;
			}
			if ($showRole) {
				foreach ($users as $userId => &$row) {
					$userModel = \App\User::getUserModel($userId);
					$row = [
						'userName' => $userModel->getName(),
						'roleName' => $userModel->getRoleName(),
						'isAdmin' => $userModel->isAdmin(),
					];
				}
			}
		}
		return $users;
	}

	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getListViewName() . '&parent=Settings';
	}

	/**
	 * Function gives list fields for save.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return string[]
	 */
	public function getFieldsForSave(Vtiger_Record_Model $recordModel)
	{
		$editFields = [];
		foreach (App\Field::getFieldsPermissions($this->getId(), false) as $field) {
			$editFields[] = $field['fieldname'];
		}
		return $editFields;
	}
}
