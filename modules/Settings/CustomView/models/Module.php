<?php

class Settings_CustomView_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getCustomViews($tabid = false)
	{
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT *, (SELECT COUNT( * ) FROM vtiger_customview c1 WHERE c1.entitytype = c2.entitytype) AS cn from vtiger_customview c2';
		$params = array();
		if ($tabid) {
			$sql .= ' WHERE entitytype = ?';
			$params[] = $tabid;
		}
		$sql .= ' ORDER BY entitytype ASC';
		$result = $adb->pquery($sql, $params, true);
		$moduleEntity = array();
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$moduleEntity[$row['cvid']] = $row;
		}
		return $moduleEntity;
	}

	public function Delete($params)
	{
		$adb = PearDatabase::getInstance();
		$cvid = $params['cvid'];
		if (is_numeric($cvid)) {
			$adb->pquery("DELETE FROM vtiger_customview WHERE cvid = ?", array($cvid));
		}
	}

	public function UpdateField($params)
	{
		$authorized_actions = array('setdefault', 'privileges');
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

	public function GetUrlToEdit($module, $record)
	{
		return "module=CustomView&view=EditAjax&source_module=$module&record=$record";
	}
}
