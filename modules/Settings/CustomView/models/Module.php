<?php

/**
 * CustomView module model class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getCustomViews($tabId)
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT vtiger_customview.* FROM vtiger_customview LEFT JOIN vtiger_tab ON  vtiger_tab.`name` = vtiger_customview.`entitytype` WHERE vtiger_tab.`tabid` = ? ORDER BY vtiger_customview.sequence ASC';
		$result = $db->pquery($sql, [$tabId]);
		$moduleEntity = [];
		while ($row = $db->getRow($result)) {
			$moduleEntity[$row['cvid']] = $row;
		}
		return $moduleEntity;
	}
	
	public function getFilterPermissionsView($cvId, $action)
	{
		$db = PearDatabase::getInstance();
		if($action == 'default'){
			$sql = 'SELECT `userid` FROM vtiger_user_module_preferences WHERE `default_cvid` = ? ORDER BY userid';
		}elseif($action == 'featured'){
			$sql = 'SELECT `user` FROM a_yf_featured_filter WHERE `cvid` = ? ORDER BY user';
		}
		$result = $db->pquery($sql, [$cvId]);
		$users = [];
		while ($user = $db->getSingleValue($result)) {
			$members = explode(':', $user);
			$users[$members[0]][] = $user;
		}
		return $users;
	}

	public function setDefaultUsersFilterView($tabid, $cvId, $user, $action)
	{
		$db = PearDatabase::getInstance();
		if ($action == 'add') {
			$result = $db->pquery('SELECT vtiger_customview.`viewname` FROM `vtiger_user_module_preferences` LEFT JOIN `vtiger_customview` ON vtiger_user_module_preferences.`default_cvid` = vtiger_customview.`cvid` WHERE vtiger_user_module_preferences.tabid = ? AND vtiger_user_module_preferences.userid = ?;', [$tabid, $user]);
			if ($result->rowCount()) {
				return $db->getSingleValue($result);
			}
			$db->insert('vtiger_user_module_preferences', [
				'userid' => $user,
				'tabid' => $tabid,
				'default_cvid' => $cvId
			]);
		} elseif ($action == 'remove') {
			$db->delete('vtiger_user_module_preferences', 'userid = ? AND tabid = ? AND default_cvid = ?', [$user, $tabid, $cvId]);
		}
		return false;
	}

	public static function setFeaturedFilterView($cvId, $user, $action)
	{
		$db = PearDatabase::getInstance();
		if ($action == 'add') {
			$db->insert('a_yf_featured_filter', [
				'user' => $user,
				'cvid' => $cvId
			]);
		} elseif ($action == 'remove') {
			$db->delete('a_yf_featured_filter', 'user = ? AND cvid = ?', [$user, $cvId]);
		}
		return false;
	}

	public function delete($params)
	{
		$db = PearDatabase::getInstance();
		$cvId = $params['cvid'];
		if (is_numeric($cvId)) {
			$db->pquery("DELETE FROM vtiger_customview WHERE cvid = ?", [$cvId]);
			$db->delete('vtiger_user_module_preferences', 'default_cvid = ?', [$cvId]);
			// To Delete the mini list widget associated with the filter 
			$db->pquery('DELETE FROM vtiger_module_dashboard_widgets WHERE filterid = ?', [$cvId]);
		}
	}

	public function UpdateField($params)
	{
		$authorized_actions = ['setdefault', 'privileges', 'featured'];
		$adb = PearDatabase::getInstance();
		$cvid = $params['cvid'];
		$name = $params['name'];
		$mod = $params['mod'];
		$checked = $params['checked'] == 'true' ? 1 : 0;
		if (is_numeric($cvid) && in_array($name, $authorized_actions)) {
			if ($name == 'setdefault' && $checked == 1)
				$adb->pquery("UPDATE vtiger_customview SET `setdefault` = ? WHERE entitytype = ?", array(0, $mod));
			$adb->pquery("UPDATE vtiger_customview SET `$name` = ? WHERE cvid = ?", array($checked, $cvid));
			return true;
		}else {
			return false;
		}
	}
	
	public static function upadteSequences($params)
	{
		$db = PearDatabase::getInstance();
		$sql = 'UPDATE vtiger_customview SET `sequence` = CASE ';
		foreach ($params as $sequence => $cvId) {
			$sql .= " WHEN `cvid` = $cvId THEN $sequence";
		}
		$sql .= ' END WHERE `cvid` IN (' . implode(',', $params) . ')';
		return $db->query($sql);
	}

	public function GetUrlToEdit($module, $record)
	{
		return "module=CustomView&view=EditAjax&source_module=$module&record=$record";
	}

	public function getCreateFilterUrl($module)
	{
		return 'index.php?module=CustomView&view=EditAjax&source_module=' . $module;
	}

	public function getUrlDefaultUsers($module, $cvid, $isDefault)
	{
		return 'index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=default&sourceModule=' . $module . '&cvid=' . $cvid . '&isDefault=' . $isDefault;
	}

	public function getFeaturedFilterUrl($module, $cvid)
	{
		return 'index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=featured&sourceModule=' . $module . '&cvid=' . $cvid;
	}

	public static function getSupportedModules()
	{
		$db = PearDatabase::getInstance();
		$modulesList = [];
		$result = $db->query('SELECT DISTINCT vtiger_tab.`tabid`, vtiger_customview.`entitytype` FROM `vtiger_customview` LEFT JOIN vtiger_tab ON vtiger_tab.`name` = vtiger_customview.`entitytype`;');
		while ($row = $db->getRow($result)) {
			$modulesList[$row['tabid']] = $row['entitytype'];
		}
		return $modulesList;
	}
}
