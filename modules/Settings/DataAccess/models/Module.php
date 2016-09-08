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

class Settings_DataAccess_Module_Model extends Vtiger_Module_Model
{

	public static $moduleName = 'Settings:DataAccess';
	public static $supportedModules = false;
	public static $AccesDataDirector = 'modules/Vtiger/data_access';
	public static $separator = '!!';

	public static function getSupportedModules()
	{
		if (empty(self::$supportedModules)) {
			self::$supportedModules = self::getEntityModulesList();
		}
		return self::$supportedModules;
	}

	public static function getEntityModulesList()
	{
		$db = PearDatabase::getInstance();
		self::preModuleInitialize2();

		$presence = [0, 2];
		$restrictedModules = ['Emails', 'Integration', 'Dashboard', 'ModComments', 'PBXManager', 'vtmessages', 'vttwitter'];
		$query = sprintf('SELECT name FROM vtiger_tab WHERE
                    presence IN (%s)
                    && isentitytype = ?
                    && name NOT IN (%s) ', generateQuestionMarks($presence), generateQuestionMarks($restrictedModules));

		$result = $db->pquery($query, [$presence, 1, $restrictedModules]);
		$numOfRows = $db->num_rows($result);

		$modulesList = array('All' => 'All');
		for ($i = 0; $i < $numOfRows; $i++) {
			$moduleName = $db->query_result($result, $i, 'name');
			$modulesList[$moduleName] = $moduleName;
		}
		if (!array_key_exists('Calendar', $modulesList)) {
			unset($modulesList['Events']);
		}
		return $modulesList;
	}

	public static function getDataAccessList($module = NULL)
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_dataaccess ';
		if ($module) {
			$sql .= 'WHERE module_name IN (?, ?)';
			$params = ['All', $module];
		} else {
			$sql .= 'WHERE presence = ?;';
			$params = [1];
		}
		$output = [];
		if (empty($module) || array_key_exists($module, self::getSupportedModules())) {
			$result = $db->pquery($sql, $params);
			while ($row = $db->getRow($result)) {
				$output[] = [
					'module' => $row['module_name'],
					'summary' => $row['summary'],
					'data' => unserialize($row['data']),
					'id' => $row['dataaccessid'],
				];
			}
		}
		return $output;
	}

	public static function getDataAccessInfo($id, $type = true)
	{
		$db = PearDatabase::getInstance();
		$sql = "SELECT vtiger_dataaccess.* ";
		if ($type) {
			$sql .= ",vtiger_dataaccess_cnd.* ";
		}
		$sql .= "FROM vtiger_dataaccess ";
		if ($type) {
			$sql .= "LEFT JOIN vtiger_dataaccess_cnd ON vtiger_dataaccess_cnd.dataaccessid = vtiger_dataaccess.dataaccessid ";
		}
		$sql .= "WHERE vtiger_dataaccess.dataaccessid = ?";
		$result = $db->pquery($sql, array($id), true);
		$row = $db->raw_query_result_rowdata($result, 0);
		$basicInfo = array();
		$basicInfo['module_name'] = $row['module_name'];
		$basicInfo['summary'] = $row['summary'];
		$basicInfo['actions'] = $row['actions'];
		$basicInfo['data'] = unserialize($row['data']);
		$requiredConditions = array();
		$requiredNum = 0;
		$optionalConditions = array();
		$optionalNum = 0;
		if ($type && $row['fieldname'] != '') {
			for ($i = 0; $i < $db->num_rows($result); $i++) {
				$idRequired = $db->query_result_raw($result, $i, 'required');
				if ($idRequired) {
					$requiredConditions[$requiredNum]['fieldname'] = $db->query_result_raw($result, $i, 'fieldname');
					$requiredConditions[$requiredNum]['comparator'] = $db->query_result_raw($result, $i, 'comparator');
					$requiredConditions[$requiredNum]['field_type'] = $db->query_result_raw($result, $i, 'field_type');
					if ($requiredConditions[$requiredNum]['field_type'] == 'multipicklist') {
						$requiredConditions[$requiredNum]['val'] = explode('::', $db->query_result_raw($result, $i, 'val'));
					} else {
						$requiredConditions[$requiredNum]['val'] = $db->query_result_raw($result, $i, 'val');
					}
					$requiredNum++;
				} else {
					$optionalConditions[$optionalNum]['fieldname'] = $db->query_result_raw($result, $i, 'fieldname');
					$optionalConditions[$optionalNum]['comparator'] = $db->query_result_raw($result, $i, 'comparator');
					$optionalConditions[$optionalNum]['field_type'] = $db->query_result_raw($result, $i, 'field_type');
					if ($optionalConditions[$optionalNum]['field_type'] == 'multipicklist') {
						$optionalConditions[$optionalNum]['val'] = explode('::', $db->query_result_raw($result, $i, 'val'));
					} else {
						$optionalConditions[$optionalNum]['val'] = $db->query_result_raw($result, $i, 'val');
					}
					$optionalNum++;
				}
			}
		}
		return array('basic_info' => $basicInfo, 'required_conditions' => $requiredConditions, 'optional_conditions' => $optionalConditions);
	}

	public static function getListBaseModuleField($baseModule)
	{
		if ($baseModule === 'All')
			return $baseModule;
		$baseModuleModel = Vtiger_Module_Model::getInstance($baseModule);
		$list = $baseModuleModel->getFields();
		$output = array();
		if (count($list)) {
			$num = 0;
			foreach ($list as $key => $value) {
				if ($value->isActiveField()) {
					$output[$baseModule][$num]['name'] = $value->get('name');
					$output[$baseModule][$num]['uitype'] = $value->get('uitype');
					$output[$baseModule][$num]['label'] = $value->get('label');
					$output[$baseModule][$num]['info'] = $value->getFieldInfo();
					$num++;
				}
			}
		}

		return $output;
	}

	public static function getConditionByType($type = NULL)
	{
		$list = array(
			'string' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'salutation' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'text' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'url' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'email' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'phone' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'integer' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed'),
			'double' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed'),
			'currency' => array('equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed'),
			'picklist' => array('is', 'is not', 'has changed'),
			'multipicklist' => array('is', 'is not', 'has changed'),
			'datetime' => array('is', 'is not', 'less than hours before', 'less than hours later', 'more than hours before', 'more than hours later', 'has changed'),
			'time' => array('is', 'is not', 'has changed'),
			'date' => array('is', 'is not', 'between', 'before', 'after', 'is today', 'in less than', 'in more than', 'days ago', 'days later', 'has changed'),
			'boolean' => array('is enabled', 'is disabled', 'has changed'),
			'reference' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'owner' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'sharedOwner' => array('is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'),
			'recurrence' => array('is', 'is not'),
			'comment' => array('is added'),
			'rangeTime' => ['is empty', 'is not empty'],
			'tree' => ['is', 'is not', 'has changed', 'has changed to', 'is empty', 'is not empty'],
		);
		if (NULL != $type) {
			return $list[$type];
		} else {
			return $list;
		}
	}

	public function addConditions($conditions, $relId, $mendatory = TRUE)
	{
		$db = PearDatabase::getInstance();
		$conditionObj = json_decode($conditions);
		if (count($conditionObj)) {
			foreach ($conditionObj as $key => $obj) {
				$insertConditionSql = "INSERT INTO vtiger_dataaccess_cnd VALUES(?, ?, ?, ?, ?, ?, ?)";
				if (is_array($obj->val)) {
					$db->pquery($insertConditionSql, array(NULL, $relId, $obj->field, $obj->name, implode('::', $obj->val), $mendatory, $obj->type), TRUE);
				} else {
					$db->pquery($insertConditionSql, array(NULL, $relId, $obj->field, $obj->name, $obj->val, $mendatory, $obj->type), TRUE);
				}
			}
		}
	}

	public function updateConditions($conditions, $relId, $mendatory = TRUE)
	{
		$db = PearDatabase::getInstance();
		if ($mendatory) {
			$deleteOldConditionsSql = "DELETE FROM vtiger_dataaccess_cnd WHERE dataaccessid = ? && required = 1";
		} else {
			$deleteOldConditionsSql = "DELETE FROM vtiger_dataaccess_cnd WHERE dataaccessid = ? && required = 0";
		}
		$db->pquery($deleteOldConditionsSql, array($relId), TRUE);
		$conditionObj = json_decode($conditions);
		if (count($conditionObj)) {
			foreach ($conditionObj as $key => $obj) {
				$insertConditionSql = "INSERT INTO vtiger_dataaccess_cnd VALUES(?, ?, ?, ?, ?, ?, ?)";
				if (is_array($obj->val)) {
					$db->pquery($insertConditionSql, array(NULL, $relId, $obj->field, $obj->name, implode('::', $obj->val), $mendatory, $obj->type), TRUE);
				} else {
					$db->pquery($insertConditionSql, array(NULL, $relId, $obj->field, $obj->name, $obj->val, $mendatory, $obj->type), TRUE);
				}
			}
		}
	}

	public function saveActionConfig($ID, $action, $form_data, $aid = false)
	{
		unset($form_data['__vtrftk']);
		unset($form_data['sid']);
		$DataAccess = self::getDataAccessInfo($ID, false);
		$db = PearDatabase::getInstance();
		$actionArray = explode(self::$separator, $action);
		vimport("~~modules/{$actionArray[0]}/data_access/{$actionArray[1]}.php");
		$class = "DataAccess_" . $actionArray[1];
		$actionObject = new $class();
		$form_data['cf'] = $actionObject->config;
		$form_data['an'] = $action;
		$data = $DataAccess['basic_info']['data'];
		if ($aid === false) {
			$data[] = $form_data;
		} else {
			$data[$aid] = $form_data;
		}
		$db->pquery("UPDATE vtiger_dataaccess SET data = ?  WHERE dataaccessid = ?", array(serialize($data), $ID), true);
	}

	public function deleteAction($ID, $aid)
	{
		$DataAccess = self::getDataAccessInfo($ID, false);
		$db = PearDatabase::getInstance();
		$data = $DataAccess['basic_info']['data'];
		unset($data[$aid]);
		$db->pquery("UPDATE vtiger_dataaccess SET data = ?  WHERE dataaccessid = ?", array(serialize($data), $ID), true);
	}

	public function showConfigDataAccess($tpl_id, $actionsName, $baseModule)
	{
		if (!is_array($actionsName)) {
			$actionsNameA = explode(self::$separator, $actionsName);
		}
		vimport("~~modules/{$actionsNameA[0]}/data_access/{$actionsNameA[1]}.php");
		$class = "DataAccess_" . $actionsNameA[1];
		$actionObject = new $class();
		return $actionObject->getConfig($tpl_id, $actionsName, $baseModule);
	}

	public function parse_data($a, $b)
	{
		$resp = false;
		if ($b != '' && strstr($b, $a) !== false) {
			$resp = true;
		}
		return $resp;
	}

	public function getActionName($name, $typ)
	{
		$actionsName = explode(self::$separator, $name);
		if ($typ)
			return vtranslate('Action_' . $actionsName[1], 'DataAccess');
		else
			return vtranslate('Action_Desc_' . $actionsName[1], 'DataAccess');
	}

	public function listAccesDataDirector($module = false)
	{
		$dirMain = self::$AccesDataDirector;
		$FolderFiles = array();
		$moduleFolderFiles = array();
		$mainFolderFiles = self::listFolderFiles($dirMain, 'Vtiger');
		if ($module && file_exists("modules/$module/data_access")) {
			$moduleFolderFiles = self::listFolderFiles("modules/$module/data_access", $module);
			foreach ($mainFolderFiles as $key => $main) {
				foreach ($moduleFolderFiles as $module) {
					$a_main = explode(self::$separator, $main);
					$a_module = explode(self::$separator, $module);
					if ($a_main[1] == $a_module[1]) {
						unset($mainFolderFiles[$key]);
					}
				}
			}
		}
		return array_merge($mainFolderFiles, $moduleFolderFiles);
	}

	public function listFolderFiles($dir, $prefix)
	{
		$ffs = scandir($dir);
		$Files = array();
		foreach ($ffs as $ff) {
			if ($ff != '.' && $ff != '..') {
				if (is_dir($dir . '/' . $ff)) {
					$Files["$ff"] = self::listFolderFiles($dir . '/' . $ff);
				} else {
					$Files[] = $prefix . self::$separator . str_replace('.php', "", $ff);
				}
			}
		}
		return $Files;
	}

	public static function executeAjaxHandlers($module, $param)
	{
		vimport('~~modules/Settings/DataAccess/helpers/DataAccess_Conditions.php');
		$conditions = new DataAccess_Conditions();
		$DataAccessList = self::getDataAccessList($module);
		$success = true;
		$output = [];

		foreach ($DataAccessList as $DataAccess) {
			$condition_result = $conditions->checkConditions($DataAccess['id'], $param);
			if ($condition_result['test'] == true) {
				$action_result = self::executeAction($module, $param, $DataAccess['data']);
				$output = array_merge($output, $action_result['data']);
				if ($action_result['success'] == false) {
					$success = false;
				}
			}
		}
		return array('success' => $success, 'data' => $output);
	}

	public static function executeAction($module, $param, $data)
	{
		$save_record = true;
		$output = [];
		$recordId = isset($param['record']) ? $param['record'] : false;
		if ($data) {
			foreach ($data as $row) {
				$action = explode(self::$separator, $row['an']);
				$file = "modules/{$action[0]}/data_access/{$action[1]}.php";
				if (file_exists($file)) {
					vimport("~~$file");
					$class = "DataAccess_" . $action[1];
					$actionObject = new $class();
					$output[] = $resp = $actionObject->process($module, $recordId, $param, $row);
					if ($resp['save_record'] == false) {
						$save_record = false;
					}
				}
			}
		}
		return array('success' => $save_record, 'data' => $output);
	}

	public static function compare_vale($actions, $item)
	{
		if (strpos($actions, ',')) {
			$actionsTab = explode(",", $actions);
			if (in_array($item, $actionsTab)) {
				$return = true;
			} else {
				$return = false;
			}
		} else {
			$return = $actions == $item ? true : false;
		}
		return $return;
	}

	protected static $colorListCache = [];

	public static function executeColorListHandlers($moduleName, $record, Vtiger_Record_Model $recordModel)
	{
		if (key_exists($record, self::$colorListCache)) {
			return self::$colorListCache[$record];
		}
		vimport('~~modules/Settings/DataAccess/helpers/DataAccess_Conditions.php');

		$colorList = Vtiger_Cache::get('DataAccess::colorList', $moduleName);
		if ($colorList === false) {
			$db = PearDatabase::getInstance();
			$sql = "SELECT dataaccessid,data FROM vtiger_dataaccess WHERE module_name = ? && data LIKE '%colorList%'";
			$result = $db->pquery($sql, [$moduleName]);
			$colorList = [];
			while ($row = $db->getRow($result)) {
				$colorList[] = $row;
			}
			Vtiger_Cache::set('DataAccess::colorList', $moduleName, $colorList);
		}

		$return = [];
		$recordData = $recordModel->getRawData();
		if (empty($recordData)) {
			$recordData = $recordModel->getData();
		}
		$conditions = new DataAccess_Conditions();
		foreach ($colorList as $row) {
			$conditionResult = $conditions->checkConditions($row['dataaccessid'], $recordData, $recordModel);
			if ($conditionResult['test'] == true) {
				$data = reset(unserialize($row['data']));
				$return = [
					'text' => $data['text'],
					'background' => $data['bg']
				];
			}
		}
		self::$colorListCache[$record] = $return;
		return $return;
	}
}
