<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */
require_once 'modules/Webforms/model/WebformsModel.php';
require_once 'include/Webservices/DescribeObject.php';

class Webforms
{

	var $LBL_WEBFORMS = 'Webforms';
	// Cache to speed up describe information store
	protected static $moduleDescribeCache = array();

	public function vtlib_handler($moduleName, $eventType)
	{

		require_once('include/utils/utils.php');
		$adb = PearDatabase::getInstance();


		if ($eventType == 'module.postinstall') {
			// Mark the module as Standard module
			// Mark the module as Standard module
			$this->updateSettings();
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($this->LBL_WEBFORMS));
		} else if ($eventType == 'module.disabled') {

			$adb = PearDatabase::getInstance();
			$log = vglobal('log');
			$adb->pquery('UPDATE vtiger_settings_field SET active= 1  WHERE  name= ?', array($this->LBL_WEBFORMS));
		} else if ($eventType == 'module.enabled') {

			$adb = PearDatabase::getInstance();
			$log = vglobal('log');
			$adb->pquery('UPDATE vtiger_settings_field SET active= 0  WHERE  name= ?', array($this->LBL_WEBFORMS));
		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {
			$this->updateSettings();
		}
	}

	public function updateSettings()
	{
		$adb = PearDatabase::getInstance();

		$fieldid = $adb->getUniqueID('vtiger_settings_field');
		$blockid = \vtlib\Deprecated::getSettingsBlockId('LBL_OTHER_SETTINGS');
		$seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid = ?", array($blockid));
		if ($adb->num_rows($seq_res) > 0) {
			$cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
			if ($cur_seq != null)
				$seq = $cur_seq + 1;
		}

		$result = $adb->pquery('SELECT 1 FROM vtiger_settings_field WHERE name=?', array($this->LBL_WEBFORMS));
		if (!$adb->num_rows($result)) {
			$adb->pquery('INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence)
					VALUES (?,?,?,?,?,?,?)', array($fieldid, $blockid, $this->LBL_WEBFORMS, 'modules/Webforms/img/Webform.png', 'Allows you to manage Webforms', 'index.php?module=Webforms&action=index&parenttab=Settings', $seq));
		}
	}

	static function checkAdminAccess($user)
	{
		if (\vtlib\Functions::userIsAdministrator($user))
			return;

		echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
		echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>

		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
		<tbody><tr>
		<td rowspan='2' width='11%'><img src= " . vtiger_imageurl('denied.gif', $theme) . " ></td>
		<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'>
			<span class='genHeaderSmall'>$app_strings[LBL_PERMISSION]</span></td>
		</tr>
		<tr>
		<td class='small' align='right' nowrap='nowrap'>
		<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br>
		</td>
		</tr>
		</tbody></table>
		</div>";
		echo "</td></tr></table>";
		exit;
	}

	static function getModuleDescribe($module)
	{
		if (!isset(self::$moduleDescribeCache[$module])) {
			$adb = PearDatabase::getInstance();
			$current_user = vglobal('current_user');
			$log = vglobal('log');
			self::$moduleDescribeCache[$module] = vtws_describe($module, $current_user);
		}
		return self::$moduleDescribeCache[$module];
	}

	static function getFieldInfo($module, $fieldname)
	{
		$describe = self::getModuleDescribe($module);
		foreach ($describe['fields'] as $index => $fieldInfo) {
			if ($fieldInfo['name'] == $fieldname) {
				return $fieldInfo;
			}
		}
		return false;
	}

	static function getFieldInfos($module)
	{
		$describe = self::getModuleDescribe($module);
		foreach ($describe['fields'] as $index => $fieldInfo) {
			if ($fieldInfo['name'] == 'id') {

				unset($describe['fields'][$index]);
			}
		}
		return $describe['fields'];
	}
}

?>
