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
			$db = PearDatabase::getInstance();

			$query = 'SELECT * FROM vtiger_users WHERE (first_name LIKE ? || last_name LIKE ?) && status = ?';
			$params = array("%$searchValue%", "%$searchValue%", 'Active');

			$result = $db->pquery($query, $params);
			$noOfRows = $db->num_rows($result);

			$matchingRecords = [];
			for ($i = 0; $i < $noOfRows; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
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

	public function checkDuplicateUser($userName)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT user_name FROM vtiger_users WHERE user_name = ?';
		$result = $db->pquery($query, array($userName));
		if ($db->num_rows($result) > 0) {
			return true;
		}
		return false;
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
		$num_rows = $db->num_rows($result);
		if ($num_rows > 0) {
			$currency_code = decode_html($db->query_result($result, 0, 'currency_code'));
			$currency_symbol = decode_html($db->query_result($result, 0, 'currency_symbol'));
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
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$currencyname = decode_html($adb->query_result($result, $i, 'currency_name'));
			$currencycode = decode_html($adb->query_result($result, $i, 'currency_code'));
			$currencysymbol = decode_html($adb->query_result($result, $i, 'currency_symbol'));
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
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$time_zone = decode_html($adb->query_result($result, $i, 'time_zone'));
			$time_zones_list[$time_zone] = $time_zone;
		}
		return $time_zones_list;
	}

	public function checkMailExist($email, $id)
	{
		$query = (new \App\Db\Query())->from('vtiger_users')->where(['email1' => $email]);
		if ($id) {
			$query->andWhere(['<>', 'id', $id]);
		}
		return $query->exists();
	}

	/**
	 * @return an array with the list of languages which are available in source
	 */
	public static function getLanguagesList()
	{
		$adb = PearDatabase::getInstance();

		$language_query = 'SELECT prefix, label FROM vtiger_language';
		$result = $adb->query($language_query);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$lang_prefix = decode_html($adb->query_result($result, $i, 'prefix'));
			$label = decode_html($adb->query_result($result, $i, 'label'));
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
		//After adding new user, set the default activity types for new user
		Vtiger_Util_Helper::setCalendarDefaultActivityTypesForUser($recordModel->getId());
		if ($recordModel->getPreviousValue('language') !== false && App\User::getCurrentUserRealId() === $recordModel->getId()) {
			App\Session::set('language', $recordModel->get('language'));
		}
		foreach ($_FILES as $fileindex => $files) {
			if ($files['name'] !== '' && $files['size'] > 0) {
				$files['original_name'] = \App\Request::_get($fileindex . '_hidden');
				$recordModel->getEntity()->uploadAndSaveFile($recordModel->getId(), $moduleName, $files);
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
