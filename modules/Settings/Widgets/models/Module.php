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

class Settings_Widgets_Module_Model extends Settings_Vtiger_Module_Model
{

	public static function getWidgets($module = false, $record = false)
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_widgets';
		$params = [];
		if ($module) {
			if (!is_numeric($module)) {
				$module = Vtiger_Functions::getModuleId($module);
			}
			$sql .= ' WHERE tabid = ? ';
			$params[] = $module;
		}
		$sql .= ' ORDER BY tabid,sequence ASC';
		$result = $db->pquery($sql, $params, true);
		$widgets = array(1 => array(), 2 => array(), 3 => array());
		while ($row = $db->getRow($result)) {
			$row['data'] = Zend_Json::decode($row['data']);
			$widgets[$row["wcol"]][$row["id"]] = $row;
		}
		return $widgets;
	}

	public function getModulesList()
	{
		$adb = PearDatabase::getInstance();
		$restrictedModules = array('Emails', 'Integration', 'Dashboard', 'ModComments', 'SMSNotifier');
		$sql = 'SELECT * FROM vtiger_tab WHERE isentitytype = ? AND name NOT IN (' . generateQuestionMarks($restrictedModules) . ')';
		$params = array(1, $restrictedModules);
		$result = $adb->pquery($sql, $params);
		$modules = array();
		while ($row = $adb->fetch_array($result)) {
			$modules[$row['tabid']] = $row;
		}
		return $modules;
	}

	public function getSize()
	{
		return [1, 2, 3];
	}

	public function getType($module = false)
	{
		$moduleName = Vtiger_Functions::getModuleName($module);

		$dir = 'modules/Vtiger/widgets/';
		$ffs = scandir($dir);
		foreach ($ffs as $ff) {
			$action = str_replace('.php', "", $ff);
			if ($ff != '.' && $ff != '..' && !is_dir($dir . '/' . $ff) && $action != 'Basic') {
				$folderFiles[$action] = $action;
				vimport('~~' . $dir . $ff);
				$modelClassName = Vtiger_Loader::getComponentClassName('Widget', $action, 'Vtiger');
				$instance = new $modelClassName();
				if ($instance->allowedModules && !in_array($moduleName, $instance->allowedModules)) {
					unset($folderFiles[$action]);
				}
			}
		}
		return $folderFiles;
	}

	public function getColumns()
	{
		return [1, 2, 3, 4, 5, 6];
	}

	public function getRelatedModule($tabid)
	{
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT vtiger_relatedlists.*,vtiger_tab.name FROM vtiger_relatedlists
				LEFT JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_relatedlists.related_tabid WHERE vtiger_relatedlists.tabid = ? AND vtiger_relatedlists.related_tabid != 0';
		$result = $adb->pquery($sql, array($tabid));
		$relation = array();
		while ($row = $adb->fetch_array($result)) {
			$relation[$row['relation_id']] = $row;
		}
		return $relation;
	}

	public function getFiletrs($modules)
	{
		$adb = PearDatabase::getInstance();
		$filetrs = [];
		$tabid = [];
		foreach ($modules as $key => $value) {
			if (!in_array($value['related_tabid'], $tabid)) {
				$sql = "SELECT columnname,tablename,fieldlabel,fieldname FROM vtiger_field WHERE tabid = ? AND uitype in ('15','16');";
				$result = $adb->pquery($sql, [$value['related_tabid']]);
				while ($row = $adb->getRow($result)) {
					$filetrs[$value['related_tabid']][$row['fieldname']] = vtranslate($row['fieldlabel'], $value['name']);
				}
				$tabid[] = $value['related_tabid'];
			}
		}
		return $filetrs;
	}

	public function getCheckboxs($modules)
	{
		$db = PearDatabase::getInstance();
		$checkboxs = [];
		$tabid = [];
		foreach ($modules as $key => $value) {
			if (!in_array($value['related_tabid'], $tabid)) {
				$sql = "SELECT columnname,tablename,fieldlabel,fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ? AND columnname NOT IN ('was_read');";
				$result = $db->pquery($sql, [$value['related_tabid'], 56]);
				while ($row = $db->getRow($result)) {
					$checkboxs[$value['related_tabid']][$row['tablename'] . '.' . $row['fieldname']] = vtranslate($row['fieldlabel'], $value['name']);
				}
				$tabid[] = $value['related_tabid'];
			}
		}
		return $checkboxs;
	}

	public function getFields($tabid, $uitype = false)
	{
		$adb = PearDatabase::getInstance();
		$fieldlabel = $fieldsList = array();
		$params = array($tabid);
		$sql = "SELECT fieldid,columnname,tablename,fieldlabel,fieldname FROM vtiger_field WHERE tabid = ? AND displaytype <> '2' AND vtiger_field.presence in (0,2)";
		if ($uitype) {
			$uitype = implode("','", $uitype);
			$sql .= " AND uitype in ('$uitype')";
		}
		$result = $adb->pquery($sql, $params, true);
		$Num = $adb->num_rows($result);
		while ($row = $adb->fetch_array($result)) {
			$fieldlabel[$row['fieldid']] = vtranslate($row['fieldlabel'], $value['name']);
			$fieldsList[$value['related_tabid']][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = vtranslate($row['fieldlabel'], $value['name']);
		}
		return array('labels' => $fieldlabel, 'table' => $fieldsList);
	}

	public static function saveWidget($params)
	{
		$adb = PearDatabase::getInstance();
		$tabid = $params['tabid'];
		$data = $params['data'];
		$wid = $data['wid'];
		$widgetName = 'Vtiger_' . $data['type'] . '_Widget';
		if (class_exists($widgetName)) {
			$widgetInstance = new $widgetName();
			$dbParams = $widgetInstance->dbParams;
		}
		$data = array_merge($dbParams, $data);
		$label = $data['label'];
		unset($data['label']);
		$type = $data['type'];
		unset($data['type']);
		if (isset($data['FastEdit'])) {
			$FastEdit = array();
			if (!is_array($data['FastEdit'])) {
				$FastEdit[] = $data['FastEdit'];
				$data['FastEdit'] = $FastEdit;
			}
		}
		unset($data['filter_selected']);
		unset($data['wid']);
		$nomargin = isset($data['nomargin']) ? $data['nomargin'] : 0;
		unset($data['nomargin']);
		$serializeData = Zend_Json::encode($data);
		$sequence = self::getLastSequence($tabid) + 1;
		if ($wid) {
			$sql = "UPDATE vtiger_widgets SET label = ?, nomargin = ?, `data` = ? WHERE id = ?;";
			$adb->pquery($sql, [$label, $nomargin, $serializeData, $wid]);
		} else {
			$sql = "INSERT INTO vtiger_widgets (tabid, type, label, nomargin, sequence ,data) VALUES (?, ?, ?, ?, ?, ?);";
			$adb->pquery($sql, [$tabid, $type, $label, $nomargin, $sequence, $serializeData]);
			if ('Comments' == $type) {
				$moduleName = Vtiger_Functions::getModuleName($tabid);
				$params = [
					'fieldid' => 601,
					'module' => 'ModComments',
					'relmodule' => $moduleName
				];
				$adb->insert('vtiger_fieldmodulerel', $params);
			}
		}
	}

	public static function removeWidget($wid)
	{
		$adb = PearDatabase::getInstance();
		$query = "SELECT tabid, type FROM vtiger_widgets WHERE id =?";
		$result = $adb->pquery($query,[$wid]);
		$row = $adb->getRow($result);
		if('Comments' == $row['type']){
			$moduleName = Vtiger_Functions::getModuleName($row['tabid']);
			$adb->delete('vtiger_fieldmodulerel', 'fieldid = ? AND module = ? AND relmodule =? ', [601,'ModComments', $moduleName ]);
		}
		$adb->delete('vtiger_widgets', 'id = ?', [$wid]);
	}

	public function getWidgetInfo($wid)
	{
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_widgets WHERE id = ?';
		$result = $adb->pquery($sql, array($wid));
		$resultrow = $adb->raw_query_result_rowdata($result);
		$resultrow['data'] = Zend_Json::decode($resultrow['data']);
		return $resultrow;
	}

	public static function getLastSequence($tabid)
	{
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT MAX(sequence) as max FROM vtiger_widgets WHERE tabid = ?';
		$result = $adb->pquery($sql, array($tabid));
		return $adb->query_result($result, 0, 'max');
	}

	public static function updateSequence($params)
	{
		$adb = PearDatabase::getInstance();
		$tabid = $params['tabid'];
		$data = $params['data'];
		foreach ($data as $key => $value) {
			$sql = 'UPDATE vtiger_widgets SET sequence = ?, wcol = ? WHERE tabid = ? AND id = ?;';
			$adb->pquery($sql, array($value['index'], $value['column'], $tabid, $key));
		}
	}

	public function getWYSIWYGFields($tabid, $module)
	{
		$field = array();
		$adb = PearDatabase::getInstance();
		$sql = "SELECT fieldlabel,fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ?;";
		$result = $adb->pquery($sql, array($tabid, '300'));
		while ($row = $adb->fetch_array($result)) {
			$field[$row['fieldname']] = vtranslate($row['fieldlabel'], $module);
		}
		return $field;
	}

	public static function getHeaderSwitch($index = [])
	{
		// type: 1-field, TODO 2-label
		$data = [
			getTabid('SSalesProcesses') => [ 0 =>
				[
					'type' => 1,
					'label' => vtranslate('LBL_HEADERSWITCH_OPEN_CLOSED', 'SSalesProcesses'), // used only in configuration
					'value' => ['ssalesprocesses_status' => ['PLL_SALE_COMPLETED', 'PLL_SALE_FAILED', 'PLL_SALE_CANCELLED']]
				]
			]
		];
		if (empty($index)) {
			return $data;
		} elseif ($data[$index[0]]) {
			return $data[$index[0]][$index[1]];
		} else {
			return [];
		}
	}
}
