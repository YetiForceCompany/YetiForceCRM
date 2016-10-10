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

class Settings_OSSDocumentControl_Module_Model extends Vtiger_Module_Model
{

	public static $supportedModules = false;

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
		$module = ['Project', 'HelpDesk'];

		$query = 'SELECT name FROM vtiger_tab WHERE
                    presence IN (%s)
                    && isentitytype = ?
                    && name NOT IN (%s) && name IN (%s)';
		$query = sprintf($query, generateQuestionMarks($presence), generateQuestionMarks($restrictedModules), generateQuestionMarks($module));
		$result = $db->pquery($query, [$presence, 1, $restrictedModules, $module]);
		$numOfRows = $db->num_rows($result);

		$modulesList = array();
		for ($i = 0; $i < $numOfRows; $i++) {
			$moduleName = $db->query_result($result, $i, 'name');
			$modulesList[$moduleName] = $moduleName;
		}
		// If calendar is disabled we should not show events module too
		// in layout editor
		if (!array_key_exists('Calendar', $modulesList)) {
			unset($modulesList['Events']);
		}
		return $modulesList;
	}

	public static function getDocList($module = NULL)
	{
		$db = PearDatabase::getInstance();

		$sql = "SELECT * FROM vtiger_ossdocumentcontrol ";

		if ($module) {
			$sql .= " WHERE module_name = ? ORDER BY vtiger_ossdocumentcontrol.doc_order ASC";
			$result = $db->pquery($sql, array($module), true);
		} else {
			$sql .= " ORDER BY vtiger_ossdocumentcontrol.doc_order ASC";
			$result = $db->pquery($sql, array(), true);
		}


		$output = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$output[$i]['module'] = $db->query_result($result, $i, 'module_name');
			$output[$i]['summary'] = $db->query_result($result, $i, 'summary');
			$output[$i]['id'] = $db->query_result($result, $i, 'ossdocumentcontrolid');
		}

		return $output;
	}

	public static function getDocInfo($id)
	{
		$db = PearDatabase::getInstance();

		$sql = "SELECT "
			. "vtiger_ossdocumentcontrol.module_name as module_name, "
			. "vtiger_ossdocumentcontrol.summary as summary, "
			. "vtiger_ossdocumentcontrol.doc_folder as doc_folder, "
			. "vtiger_ossdocumentcontrol.doc_name as doc_name, "
			. "vtiger_ossdocumentcontrol.doc_order as doc_order, "
			. "vtiger_ossdocumentcontrol.doc_request as doc_request, "
			. "vtiger_ossdocumentcontrol_cnd.fieldname as fieldname, "
			. "vtiger_ossdocumentcontrol_cnd.comparator as comparator, "
			. "vtiger_ossdocumentcontrol_cnd.val as val, "
			. "vtiger_ossdocumentcontrol_cnd.field_type as field_type, "
			. "vtiger_ossdocumentcontrol_cnd.required as required "
			. "FROM vtiger_ossdocumentcontrol "
			. "LEFT JOIN vtiger_ossdocumentcontrol_cnd ON vtiger_ossdocumentcontrol_cnd.ossdocumentcontrolid = vtiger_ossdocumentcontrol.ossdocumentcontrolid "
			. "WHERE vtiger_ossdocumentcontrol.ossdocumentcontrolid = ?";

		$result = $db->pquery($sql, array($id), true);
		$basicInfo = array();

		$basicInfo['module_name'] = $db->query_result($result, 0, 'module_name');
		$basicInfo['summary'] = $db->query_result($result, 0, 'summary');
		$basicInfo['doc_folder'] = $db->query_result($result, 0, 'doc_folder');
		$basicInfo['doc_name'] = $db->query_result($result, 0, 'doc_name');
		$basicInfo['doc_request'] = $db->query_result($result, 0, 'doc_request');
		$basicInfo['doc_order'] = $db->query_result($result, 0, 'doc_order');

		$requiredConditions = array();
		$requiredNum = 0;
		$optionalConditions = array();
		$optionalNum = 0;

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$idRequired = $db->query_result($result, $i, 'required');
			if (NULL !== $idRequired) {
				if ($idRequired) {
					//var_dump($db->query_result($result, $i, 'field_type'));
					$requiredConditions[$requiredNum]['fieldname'] = $db->query_result($result, $i, 'fieldname');
					$requiredConditions[$requiredNum]['comparator'] = $db->query_result($result, $i, 'comparator');

					$requiredConditions[$requiredNum]['field_type'] = $db->query_result($result, $i, 'field_type');

					if ($requiredConditions[$requiredNum]['field_type'] == 'multipicklist') {
						$requiredConditions[$requiredNum]['val'] = explode('::', $db->query_result($result, $i, 'val'));
					} else {
						$requiredConditions[$requiredNum]['val'] = $db->query_result($result, $i, 'val');
					}

					$requiredNum++;
				} else {
					$optionalConditions[$optionalNum]['fieldname'] = $db->query_result($result, $i, 'fieldname');
					$optionalConditions[$optionalNum]['comparator'] = $db->query_result($result, $i, 'comparator');

					$optionalConditions[$optionalNum]['field_type'] = $db->query_result($result, $i, 'field_type');

					if ($optionalConditions[$optionalNum]['field_type'] == 'multipicklist') {
						$optionalConditions[$optionalNum]['val'] = explode('::', $db->query_result($result, $i, 'val'));
					} else {
						$optionalConditions[$optionalNum]['val'] = $db->query_result($result, $i, 'val');
					}
					$optionalNum++;
				}
			}
		}

		return array('basic_info' => $basicInfo, 'required_conditions' => $requiredConditions, 'optional_conditions' => $optionalConditions);
	}

	public static function getListBaseModuleField($baseModule)
	{
		$baseModuleModel = Vtiger_Module_Model::getInstance($baseModule);
		$list = $baseModuleModel->getFields();
		$output = array();
		if (count($list)) {
			$num = 0;
			foreach ($list as $key => $value) {
				if (in_array($value->get('displaytype'), array('1', '2'))) {
					$output[$baseModule][$num]['name'] = $value->get('name');
					$output[$baseModule][$num]['uitype'] = $value->get('uitype');
					$output[$baseModule][$num]['label'] = $value->get('label');

					$fieldModel = Vtiger_Field_Model::getInstance($value->get('name'), $baseModuleModel);
					$output[$baseModule][$num]['info'] = $fieldModel->getFieldInfo();
					$num++;
				}
			}
		}

		return $output;
	}

	public static function getConditionByType($type = NULL)
	{
		$list = array(
			"string" => array("is", "contains", "does not contain", "starts with", "ends with", "is empty", "is not empty"),
			"salutation" => array("is", "contains", "does not contain", "starts with", "ends with", "is empty", "is not empty"),
			"text" => array("is", "contains", "does not contain", "starts with", "ends with", "is empty", "is not empty"),
			"url" => array("is", "contains", "does not contain", "starts with", "ends with", "is empty", "is not empty"),
			"email" => array("is", "contains", "does not contain", "starts with", "ends with", "is empty", "is not empty"),
			"phone" => array("is", "contains", "does not contain", "starts with", "ends with", "is empty", "is not empty"),
			"integer" => array("equal to", "less than", "greater than", "does not equal", "less than or equal to", "greater than or equal to"),
			"double" => array("equal to", "less than", "greater than", "does not equal", "less than or equal to", "greater than or equal to"),
			"currency" => array("equal to", "less than", "greater than", "does not equal", "less than or equal to", "greater than or equal to"),
			"picklist" => array("is", "is not"),
			"multipicklist" => array("is", "is not"),
			"datetime" => array("is", "is not", "less than hours before", "less than hours later", "more than hours before", "more than hours later"),
			"time" => array("is", "is not"),
			"date" => array("is", "is not", "between", "before", "after", "is today", "in less than", "in more than", "days ago", "days later"),
			"boolean" => array("is enabled", "is disabled"),
			"reference" => array("is", "contains", "does not contain", "starts with", "ends with", "is empty", "is not empty"),
			"owner" => array("is", "contains", "does not contain", "starts with", "ends with", "is empty", "is not empty"),
			"recurrence" => array("is", "is not"),
			"comment" => array("is added"),
		);

		if (NULL != $type) {
			return $list[$type];
		} else {
			return $list;
		}
	}
}
