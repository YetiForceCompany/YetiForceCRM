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
		$presence = [0, 2];
		$restrictedModules = ['Integration', 'Dashboard', 'ModComments', 'PBXManager'];

		$dataReader = (new \App\Db\Query())->select('name')
				->from('vtiger_tab')
				->where(['presence' => $presence, 'isentitytype' => 1])
				->andWhere(['NOT IN', 'name', $restrictedModules])
				->createCommand()->query();
		$modulesList = ['All' => 'All'];
		while ($moduleName = $dataReader->readColumn(0)) {
			$modulesList[$moduleName] = $moduleName;
		}
		if (!array_key_exists('Calendar', $modulesList)) {
			unset($modulesList['Events']);
		}
		return $modulesList;
	}

	public static function getDataAccessList($module = NULL)
	{
		if (\App\Cache::has('DataAccessListInModule', $module)) {
			return \App\Cache::get('DataAccessListInModule', $module);
		}
		$output = [];
		if (empty($module) || array_key_exists($module, self::getSupportedModules())) {
			$query = (new \App\Db\Query())->from('vtiger_dataaccess');
			if ($module) {
				$query->where(['module_name' => ['All', $module]]);
			} else {
				$query->where(['presence' => 1]);
			}
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$output[] = [
					'module' => $row['module_name'],
					'summary' => $row['summary'],
					'data' => unserialize($row['data']),
					'id' => $row['dataaccessid'],
				];
			}
		}
		\App\Cache::save('DataAccessListInModule', $module, $output);
		return $output;
	}

	public static function getDataAccessInfo($id, $type = true)
	{
		$query = (new \App\Db\Query())->select('vtiger_dataaccess.*');
		if ($type) {
			$query->addSelect('vtiger_dataaccess_cnd.*');
		}
		$query->from('vtiger_dataaccess');
		if ($type) {
			$query->leftJoin('vtiger_dataaccess_cnd', 'vtiger_dataaccess_cnd.dataaccessid = vtiger_dataaccess.dataaccessid');
		}
		$query->where(['vtiger_dataaccess.dataaccessid' => $id]);
		$dataReader = $query->createCommand()->query();
		$rows = $dataReader->readAll();
		$row = $rows[0];
		$basicInfo = [];
		$basicInfo['module_name'] = $row['module_name'];
		$basicInfo['summary'] = $row['summary'];
		$basicInfo['actions'] = $row['actions'];
		$basicInfo['data'] = unserialize($row['data']);
		$requiredConditions = [];
		$requiredNum = 0;
		$optionalConditions = [];
		$optionalNum = 0;
		if ($type && $row['fieldname'] != '') {
			foreach ($rows as $row) {
				$idRequired = $row['required'];
				if ($idRequired) {
					$requiredConditions[$requiredNum]['fieldname'] = $row['fieldname'];
					$requiredConditions[$requiredNum]['comparator'] = $row['comparator'];
					$requiredConditions[$requiredNum]['field_type'] = $row['field_type'];
					if ($requiredConditions[$requiredNum]['field_type'] == 'multipicklist') {
						$requiredConditions[$requiredNum]['val'] = explode('::', $row['val']);
					} else {
						$requiredConditions[$requiredNum]['val'] = $row['val'];
					}
					$requiredNum++;
				} else {
					$optionalConditions[$optionalNum]['fieldname'] = $row['fieldname'];
					$optionalConditions[$optionalNum]['comparator'] = $row['comparator'];
					$optionalConditions[$optionalNum]['field_type'] = $row['field_type'];
					if ($optionalConditions[$optionalNum]['field_type'] == 'multipicklist') {
						$optionalConditions[$optionalNum]['val'] = explode('::', $row['val']);
					} else {
						$optionalConditions[$optionalNum]['val'] = $row['val'];
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
			'documentsFileUpload' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'],
		);
		if (NULL != $type) {
			return $list[$type];
		} else {
			return $list;
		}
	}

	public static function addConditions($conditions, $relId, $mendatory = true)
	{
		$conditionObj = json_decode($conditions);
		if (count($conditionObj)) {
			foreach ($conditionObj as $key => $obj) {
				$val = $obj->val;
				if (is_array($obj->val)) {
					$val = implode('::', $val);
				}
				\App\Db::getInstance()->createCommand()->insert('vtiger_dataaccess_cnd', [
					'dataaccessid' => $relId,
					'fieldname' => $obj->field,
					'comparator' => $obj->name,
					'val' => $val,
					'required' => $mendatory,
					'field_type' => $obj->type
				])->execute();
			}
		}
	}

	public static function updateConditions($conditions, $relId, $mendatory = true)
	{
		\App\Db::getInstance()->createCommand()
			->delete('vtiger_dataaccess_cnd', ['dataaccessid' => $relId, 'required' => $mendatory ? 1 : 0])
			->execute();
		$conditionObj = json_decode($conditions);
		if (count($conditionObj)) {
			foreach ($conditionObj as $key => $obj) {
				$val = $obj->val;
				if (is_array($obj->val)) {
					$val = implode('::', $val);
				}
				\App\Db::getInstance()->createCommand()->insert('vtiger_dataaccess_cnd', [
					'dataaccessid' => $relId,
					'fieldname' => $obj->field,
					'comparator' => $obj->name,
					'val' => $val,
					'required' => $mendatory,
					'field_type' => $obj->type
				])->execute();
			}
		}
	}

	public static function saveActionConfig($ID, $action, $form_data, $aid = false)
	{
		unset($form_data['__vtrftk']);
		unset($form_data['sid']);
		$dataAccess = self::getDataAccessInfo($ID, false);
		$actionArray = explode(self::$separator, $action);
		vimport("~~modules/{$actionArray[0]}/data_access/{$actionArray[1]}.php");
		$class = "DataAccess_" . $actionArray[1];
		$actionObject = new $class();
		$form_data['cf'] = $actionObject->config;
		$form_data['an'] = $action;
		$data = $dataAccess['basic_info']['data'];
		if ($aid === false) {
			$data[] = $form_data;
		} else {
			$data[$aid] = $form_data;
		}
		\App\Db::getInstance()->createCommand()
			->update('vtiger_dataaccess', ['data' => serialize($data)], ['dataaccessid' => $ID])
			->execute();
	}

	public function deleteAction($ID, $aid)
	{
		$dataAccess = self::getDataAccessInfo($ID, false);
		$data = $dataAccess['basic_info']['data'];
		unset($data[$aid]);
		\App\Db::getInstance()->createCommand()->update('vtiger_dataaccess', ['data' => serialize($data)], ['dataaccessid' => $ID])
			->execute();
	}

	public static function showConfigDataAccess($tpl_id, $actionsName, $baseModule)
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

	public static function getActionName($name, $typ)
	{
		$actionsName = explode(self::$separator, $name);
		if ($typ)
			return vtranslate('Action_' . $actionsName[1], 'DataAccess');
		else
			return vtranslate('Action_Desc_' . $actionsName[1], 'DataAccess');
	}

	public static function listAccesDataDirector($module = false)
	{
		$dirMain = self::$AccesDataDirector;
		$FolderFiles = [];
		$moduleFolderFiles = [];
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

	public static function listFolderFiles($dir, $prefix)
	{
		$ffs = scandir($dir);
		$Files = [];
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
			if ($condition_result['test'] === true) {
				$action_result = self::executeAction($module, $param, $DataAccess['data']);
				$output = array_merge($output, $action_result['data']);
				if ($action_result['success'] === false) {
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
					if ($resp['save_record'] === false) {
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

		if (\App\Cache::has('DataAccess:colorList', $moduleName)) {
			$colorList = \App\Cache::get('DataAccess:colorList', $moduleName);
		} else {
			$colorList = (new \App\Db\Query())->select(['dataaccessid', 'data'])
				->from('vtiger_dataaccess')
				->where(['module_name' => $moduleName])
				->andWhere(['like', 'data', 'colorList'])
				->all();
			\App\Cache::save('DataAccess:colorList', $moduleName, $colorList);
		}
		$return = [];
		$recordData = $recordModel->getRawData();
		if (empty($recordData)) {
			$recordData = $recordModel->getData();
		}
		$conditions = new DataAccess_Conditions();
		foreach ($colorList as $row) {
			$conditionResult = $conditions->checkConditions($row['dataaccessid'], $recordData, $recordModel);
			if ($conditionResult['test'] === true) {
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
