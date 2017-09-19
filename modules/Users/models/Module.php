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
	 * Function to get list view query for popup window
	 * @param string $sourceModule Parent module
	 * @param string $field parent fieldname
	 * @param string $record parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		if ($sourceModule == 'Users' && $field == 'reports_to_id') {
			if (!empty($record)) {
				$queryGenerator->addNativeCondition(['<>', 'vtiger_users.id', $record]);
			}
		}
	}

	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param string $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param string $parentModule - parent module name
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
			return $matchingRecords;
		}
	}

	/**
	 * Function returns the default column for Alphabetic search
	 * @return string columnname
	 */
	public function getAlphabetSearchField()
	{
		return 'last_name';
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&parent=Settings&view=' . $this->getEditViewName();
	}

	/**
	 * Function to delete a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function deleteRecord($recordModel)
	{
		$db = PearDatabase::getInstance();
		$query = 'UPDATE vtiger_users SET status=?, date_modified=?, modified_user_id=? WHERE id=?';
		$db->pquery($query, array('Inactive', date('Y-m-d H:i:s'), $recordModel->getId(), $recordModel->getId()), true, 'Error marking record deleted: ');
	}

	/**
	 * Function to get the url for list view of the module
	 * @return string - url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&parent=Settings&view=' . $this->getListViewName();
	}

	/**
	 * Function to update Base Currency of Product
	 * @param- $currencyName array
	 */
	public function updateBaseCurrency($currencyName)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT currency_code, currency_symbol FROM vtiger_currencies WHERE currency_name = ?', array($currencyName));
		$numRows = $db->numRows($result);
		if ($numRows > 0) {
			$currency_code = App\Purifier::decodeHtml($db->queryResult($result, 0, 'currency_code'));
			$currency_symbol = App\Purifier::decodeHtml($db->queryResult($result, 0, 'currency_symbol'));
		}

		//Updating Database
		$query = 'UPDATE vtiger_currency_info SET currency_name = ?, currency_code = ?, currency_symbol = ? WHERE id = ?';
		$params = array($currencyName, $currency_code, $currency_symbol, '1');
		$db->pquery($query, $params);

		$this->updateConfigFile($currencyName);
	}

	/**
	 * Function to update Config file
	 * @param- $currencyName array
	 */
	public function updateConfigFile($currencyName)
	{
		$currencyName = '$currency_name = \'' . $currencyName . '\'';

		//Updating in config inc file
		$filename = 'config/config.php';
		if (file_exists($filename)) {
			$contents = file_get_contents($filename);
			$contents = str_replace('$currency_name = \'USA, Dollars\'', $currencyName, $contents);
			file_put_contents($filename, $contents);
		}
	}

	/**
	 * Function to store the login history
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
				'browser' => $browser->name . ' ' . $browser->ver
			])->execute();
	}

	/**
	 * Function to store the logout history
	 */
	public function saveLogoutHistory()
	{
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		$userIPAddress = \App\RequestUtil::getRemoteIP();
		$outtime = date('Y-m-d H:i:s');

		$loginId = (new \App\Db\Query())
				->select('login_id')
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
	 * @return an array with the list of currencies which are available in source
	 */
	public function getCurrenciesList()
	{
		$adb = PearDatabase::getInstance();

		$currency_query = 'SELECT currency_name, currency_code, currency_symbol FROM vtiger_currencies ORDER BY currency_name';
		$result = $adb->pquery($currency_query, []);
		$numRows = $adb->numRows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$currencyname = App\Purifier::decodeHtml($adb->queryResult($result, $i, 'currency_name'));
			$currencycode = App\Purifier::decodeHtml($adb->queryResult($result, $i, 'currency_code'));
			$currencysymbol = App\Purifier::decodeHtml($adb->queryResult($result, $i, 'currency_symbol'));
			$currencies[$currencyname] = array($currencycode, $currencysymbol);
		}
		return $currencies;
	}

	/**
	 * @return an array with the list of time zones which are availables in source
	 */
	public function getTimeZonesList()
	{
		$adb = PearDatabase::getInstance();

		$timezone_query = 'SELECT time_zone FROM vtiger_time_zone';
		$result = $adb->pquery($timezone_query, []);
		$numRows = $adb->numRows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$time_zone = App\Purifier::decodeHtml($adb->queryResult($result, $i, 'time_zone'));
			$time_zones_list[$time_zone] = $time_zone;
		}
		return $time_zones_list;
	}

	/**
	 * Check mail exist
	 * @param string $email
	 * @param int $userId
	 * @return boolean
	 */
	public static function checkMailExist($email, $userId)
	{
		$query = (new \App\Db\Query())->from('vtiger_users')->where(['email1' => $email]);
		if ($userId) {
			$query->andWhere(['<>', 'id', $userId]);
		}
		return $query->exists();
	}

	/**
	 * Validation of user name
	 * @param string $userName
	 * @param int $userId
	 * @return boolean
	 */
	public static function checkUserName($userName, $userId)
	{
		$query = (new \App\Db\Query())->from('vtiger_users')->where(['user_name' => $userName]);
		if ($userId) {
			$query->andWhere(['<>', 'id', $userId]);
		}
		if ($query->exists()) {
			return \App\Language::translate('LBL_USER_NAME_EXISTS', 'Users');
		}
		if ($userId && AppConfig::module('Users', 'CHECK_LAST_USERNAME') && (new \App\Db\Query())->from('l_#__username_history')->where(['user_name' => $userName])->exists()) {
			return \App\Language::translate('LBL_USER_NAME_HAS_ALREADY_BEEN_USED', 'Users');
		}
		$blacklist = require 'config/username_blacklist.php';
		if (in_array($userName, $blacklist)) {
			return \App\Language::translate('LBL_FORBIDDEN_USERNAMES', 'Users');
		}
		return false;
	}

	/**
	 * @return an array with the list of languages which are available in source
	 */
	public static function getLanguagesList()
	{
		$adb = PearDatabase::getInstance();

		$language_query = 'SELECT prefix, label FROM vtiger_language';
		$result = $adb->query($language_query);
		$numRows = $adb->numRows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$lang_prefix = App\Purifier::decodeHtml($adb->queryResult($result, $i, 'prefix'));
			$label = App\Purifier::decodeHtml($adb->queryResult($result, $i, 'label'));
			$languages[$lang_prefix] = $label;
		}
		asort($languages);
		return $languages;
	}

	/**
	 * Get switch users
	 * @param boolean $showRole
	 * @return array
	 */
	public static function getSwitchUsers($showRole = false)
	{
		require('user_privileges/switchUsers.php');
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
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 * @copyright Modyfikowane przez PWC
	 */
	public function saveRecord(\Vtiger_Record_Model $recordModel)
	{
		$moduleName = $this->get('name');
		if (!$recordModel->isNew() && empty($recordModel->getPreviousValue())) {
			App\Log::info('ERR_NO_DATA');
			return $recordModel;
		}
		$recordModel->validate();
		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($recordModel);
		$eventHandler->setModuleName($moduleName);
		if ($recordModel->getHandlerExceptions()) {
			$eventHandler->setExceptions($recordModel->getHandlerExceptions());
		}
		$recordModel->saveToDb();
		if ($recordModel->getPreviousValue('language') !== false && App\User::getCurrentUserRealId() === $recordModel->getId()) {
			App\Session::set('language', $recordModel->get('language'));
		}
		if ($_FILES) {
			foreach ($_FILES as $fileindex => $files) {
				if ($files['name'] !== '' && $files['size'] > 0) {
					$files['original_name'] = \App\Request::_get($fileindex . '_hidden');
					$recordModel->getEntity()->uploadAndSaveFile($recordModel->getId(), $moduleName, $files);
				}
			}
		}
		Vtiger_Loader::includeOnce('~/modules/Users/CreateUserPrivilegeFile.php');
		createUserPrivilegesfile($recordModel->getId());
		createUserSharingPrivilegesfile($recordModel->getId());

		if (AppConfig::performance('ENABLE_CACHING_USERS')) {
			\App\PrivilegeFile::createUsersFile();
		}
		return $recordModel;
	}

	/**
	 * Function gives list fields for save
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
