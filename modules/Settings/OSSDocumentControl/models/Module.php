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
		self::preModuleInitialize2();
		$presence = [0, 2];
		$restrictedModules = ['Emails', 'Integration', 'Dashboard', 'ModComments', 'PBXManager', 'vtmessages', 'vttwitter'];
		$module = ['Project', 'HelpDesk'];
		$dataReader = (new \App\Db\Query())->select(['name'])
				->from('vtiger_tab')
				->where(['presence' => $presence, 'isentitytype' => 1, 'name' => $module])
				->andWhere(['NOT IN', 'name', $restrictedModules])
				->createCommand()->query();
		$modulesList = [];
		while ($moduleName = $dataReader->readColumn(0)) {
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
		$query = (new \App\Db\Query())->from('vtiger_ossdocumentcontrol');
		if ($module) {
			$query->where(['module_name' => $module]);
		}
		$query->orderBy(['doc_order' => SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		$output = [];
		while ($row = $dataReader->read()) {
			$output [] = [
				'module' => $row['module_name'],
				'summary' => $row['summary'],
				'id' => $row['ossdocumentcontrolid']
			];
		}
		return $output;
	}

	public static function getDocInfo($id)
	{
		$rows = (new \App\Db\Query())->select([
				'module_name' => 'vtiger_ossdocumentcontrol.module_name',
				'summary' => 'vtiger_ossdocumentcontrol.summary',
				'doc_folder' => 'vtiger_ossdocumentcontrol.doc_folder',
				'doc_name' => 'vtiger_ossdocumentcontrol.doc_name',
				'doc_order' => 'vtiger_ossdocumentcontrol.doc_order',
				'fieldname' => 'vtiger_ossdocumentcontrol_cnd.fieldname',
				'comparator' => 'vtiger_ossdocumentcontrol_cnd.comparator',
				'val' => 'vtiger_ossdocumentcontrol_cnd.val',
				'field_type' => 'vtiger_ossdocumentcontrol_cnd.field_type',
				'required' => 'vtiger_ossdocumentcontrol_cnd.required'
			])->from('vtiger_ossdocumentcontrol')
			->leftJoin('vtiger_ossdocumentcontrol_cnd', 'vtiger_ossdocumentcontrol_cnd.ossdocumentcontrolid = vtiger_ossdocumentcontrol.ossdocumentcontrolid')
			->where(['vtiger_ossdocumentcontrol.ossdocumentcontrolid' => $id])
			->all();
		$firstRow = $rows[0];
		$basicInfo = [];
		$basicInfo['module_name'] = $firstRow['module_name'];
		$basicInfo['summary'] = $firstRow['summary'];
		$basicInfo['doc_folder'] = $firstRow['doc_folder'];
		$basicInfo['doc_name'] = $firstRow['doc_name'];
		$basicInfo['doc_request'] = $firstRow['doc_request'];
		$basicInfo['doc_order'] = $firstRow['doc_order'];
		$requiredConditions = [];
		$requiredNum = 0;
		$optionalConditions = [];
		$optionalNum = 0;
		foreach ($rows as $row) {
			$idRequired = $row['required'];
			if (NULL !== $idRequired) {
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
			"sharedOwner" => array('has changed', 'is', 'is not'),
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
