<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
vimport('~~modules/OSSPdf/models/Conditions.php');

class Conditions {
    function saveCondition($recordModel,$request) {
		$db = PearDatabase::getInstance();
		$conditionAll = $request->get('condition_all_json');
		$conditionOption = $request->get('condition_option_json');
		$record = $recordModel->get('id');
		$newModuleId = $request->get('moduleid');
		
        self::saveConditionByType($conditionAll, $record);
        self::saveConditionByType($conditionOption, $record, FALSE);
    }
    function saveConditionByType($conditions, $relId, $mendatory = TRUE) {
        $db = PearDatabase::getInstance();
		$Conditions_Model = new Conditions_Model;
		$tab_constraints = $Conditions_Model->tab_constraints;
		$db->pquery("DELETE FROM $tab_constraints WHERE relid = ? AND required = ?", array($relId,$mendatory), true);
        if (count($conditions)) {
            foreach ($conditions as $key => $condition) {
                $insertConditionSql = "INSERT INTO $tab_constraints VALUES(?, ?, ?, ?, ?, ?, ?)";
                if(is_array($condition['val'])){
                    $db->pquery($insertConditionSql, array(NULL, $relId, $condition['field'], $condition['name'], implode('::', $condition['val']), $mendatory, $condition['type']), TRUE);
                } else {
                    $db->pquery($insertConditionSql, array(NULL, $relId, $condition['field'], $condition['name'], $condition['val'], $mendatory, $condition['type']), TRUE);
                }
            }
        }
    }
    public function getConditionRelToRecord($id){
        $db = PearDatabase::getInstance();
		$Conditions_Model = new Conditions_Model;
		$tab_constraints = $Conditions_Model->tab_constraints;
        $sql = "SELECT * FROM $tab_constraints WHERE relid = ?";
        $result = $db->pquery($sql, array($id), true);
		
        $requiredConditions = array();
        $requiredNum = 0;
        $optionalConditions = array();
        $optionalNum = 0;
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $idRequired = $db->query_result($result, $i, 'required');
            if ($idRequired) {
                //var_dump($db->query_result($result, $i, 'field_type'));
                $requiredConditions[$requiredNum]['fieldname'] = $db->query_result($result, $i, 'fieldname');
                $requiredConditions[$requiredNum]['comparator'] = $db->query_result($result, $i, 'comparator');
                $requiredConditions[$requiredNum]['field_type'] = $db->query_result($result, $i, 'field_type');
				$requiredConditions[$requiredNum]['required'] = $db->query_result($result, $i, 'required');
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
				$optionalConditions[$optionalNum]['required'] = $db->query_result($result, $i, 'required');
                if ($optionalConditions[$optionalNum]['field_type'] == 'multipicklist') {
                    $optionalConditions[$optionalNum]['val'] = explode('::', $db->query_result($result, $i, 'val'));
                } else {
                    $optionalConditions[$optionalNum]['val'] = $db->query_result($result, $i, 'val');
                }
                $optionalNum++;
            }
        }
        return array('required_conditions' => $requiredConditions, 'optional_conditions' =>  $optionalConditions);
    }
	public function getConditionRelToRecordFieldInfo($id,$moduleName){
		$Condition = self::getConditionRelToRecord($id); 
		for ($i = 0; $i < count($Condition['required_conditions']); $i++) {
			$fieldModel = Vtiger_Field_Model::getInstance($Condition['required_conditions'][$i]['fieldname'], Vtiger_Module_Model::getInstance($moduleName));
			$Condition['required_conditions'][$i]['info'] = $fieldModel->getFieldInfo();
		}
		for ($i = 0; $i < count($Condition['optional_conditions']); $i++) {
			$fieldModel = Vtiger_Field_Model::getInstance($Condition['optional_conditions'][$i]['fieldname'], Vtiger_Module_Model::getInstance($moduleName));
			$Condition['optional_conditions'][$i]['info'] = $fieldModel->getFieldInfo();
		}
		return $Condition;
	}
	public function translateType($text,$mod){
		$text = 'LBL_'.strtoupper(str_replace(' ', '_', $text));
		return vtranslate($text,$mod);
	}
    public static function getConditionByType($type = NULL) {
        $list =  array(
            "string" => array("is","contains","does not contain","starts with","ends with","is empty","is not empty"),
            "salutation" => array("is","contains","does not contain","starts with","ends with","is empty","is not empty"),
            "text" => array("is","contains","does not contain","starts with","ends with","is empty","is not empty"),
            "url" => array("is","contains","does not contain","starts with","ends with","is empty","is not empty"),
            "email" => array("is","contains","does not contain","starts with","ends with","is empty","is not empty"),
            "phone" => array("is","contains","does not contain","starts with","ends with","is empty","is not empty"),
            "integer" => array("equal to","less than","greater than","does not equal","less than or equal to","greater than or equal to"),
            "double" => array("equal to","less than","greater than","does not equal","less than or equal to","greater than or equal to"),
            "currency" => array("equal to","less than","greater than","does not equal","less than or equal to","greater than or equal to"),
            "picklist" => array("is","is not"),
            "multipicklist" => array("is","is not"),
            "datetime" => array("is","is not","less than hours before","less than hours later","more than hours before","more than hours later"),
            "time" => array("is","is not"),
            "date" => array("is","is not","between","before","after","is today","in less than","in more than","days ago","days later"),
            "boolean" => array("is enabled", "is disabled"),
            "reference" => array("is","contains","does not contain","starts with","ends with","is empty","is not empty"),
            "owner" => array("is","contains","does not contain","starts with","ends with","is empty","is not empty"),
            "recurrence" => array("is","is not"),
            "comment" => array("is added"),
        );
        
        if (NULL != $type) {
            return $list[$type];
        } else {
            return $list;
        }
    }
	
    public static function getListBaseModuleField($baseModule) {
        $baseModuleModel = Vtiger_Module_Model::getInstance($baseModule);
        $list = $baseModuleModel->getFields();
        $output = array();
        if (count($list)) {
            $num = 0;
            foreach ($list as $key => $value) {
                if(in_array($value->get('displaytype'), array('1', '2'))){
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
   /* function getListValidDoc($moduleName, $record) {
        $listDocAndConditions = self::getListConditions($moduleName);

        $output = array();

        foreach ($listDocAndConditions as $key => $lisConditions) {

            $responeListRequired = array();
            $responeListOptional = array();

            foreach ($lisConditions as $cndKey => $singleCnd) {

                if ('1' == $singleCnd['cnd_required']) {
                    if (NULL != $singleCnd['comparator']) {
                        $responeListRequired[] = self::checkSingleCondition($record, $singleCnd);
                    }
                } else {
                    if (NULL != $singleCnd['comparator']) {
                        $responeListOptional[] = self::checkSingleCondition($record, $singleCnd);
                    }
                }
            }

            $responeListRequiredStatus = true;

            for ($i = 0; $i < count($responeListRequired); $i++) {
                if (TRUE != $responeListRequired[$i]) {
                    $responeListRequiredStatus = false;
                }
            }

            $responeListOptionalStatus = false;

            if (count($responeListOptional)) {
                for ($i = 0; $i < count($responeListOptional); $i++) {
                    if (TRUE == $responeListOptional[$i]) {
                        $responeListOptionalStatus = true;
                    }
                }
            } else {
                $responeListOptionalStatus = TRUE;
            }

            if ($responeListRequiredStatus && $responeListOptionalStatus) {
                $singleDocInfo = array_shift(array_values($listDocAndConditions[$key]));

                $folderModel = Documents_Folder_Model::getInstanceById($singleDocInfo['doc_folder']);
                $singleDocInfo['folder'] = $folderModel->getName();
                $output[] = $singleDocInfo;
            }
        }

        return $output;
    }*/
    public function checkConditionsForRecord($id, $view_id, $request = false) {
        $lisConditions = self::getConditionRelToRecord($id);     
        $responeListRequired = array();
        $responeListOptional = array();
		$record_model = Vtiger_Record_Model::getInstanceById($view_id);
        foreach ($lisConditions as $Conditions) {
            foreach ($Conditions as $singleCnd) {
                if ('1' == $singleCnd['required']) {
                    if (NULL != $singleCnd['comparator']) {
                        $responeListRequired[] = self::checkSingleCondition($record_model, $singleCnd);
                    }
                } else {
                    if (NULL != $singleCnd['comparator']) {
                        $responeListOptional[] = self::checkSingleCondition($record_model, $singleCnd);
                    }
                }
            }
        }
        $responeListRequiredStatus = true;
        for ($i = 0; $i < count($responeListRequired); $i++) {
            if (TRUE != $responeListRequired[$i]) {
                $responeListRequiredStatus = false;
            }
        }
        $responeListOptionalStatus = false;
        if (count($responeListOptional)) {
            for ($i = 0; $i < count($responeListOptional); $i++) {
                if (TRUE == $responeListOptional[$i]) {
                    $responeListOptionalStatus = true;
                }
            }
        } else {
            $responeListOptionalStatus = TRUE;
        }
        if ($responeListRequiredStatus && $responeListOptionalStatus) {
            return array('test' => true);
        } else {
           return array('test' => false);
        }
    }

    private function checkSingleCondition($record_model, $cndArray) {
		vimport('~~modules/OSSPdf/helpers/ConditionsTest.php');
        $methodName = self::createFunctionName($cndArray['comparator']);
        $class = new ReflectionClass('ConditionsTest');
        $methodList = $class->getMethods(ReflectionMethod::IS_STATIC);
        $exist = false;
        for ($i = 0; $i < count($methodList); $i++) {
            if ($methodList[$i]->name == $methodName) {
                $exist = true;
            }
        }
        if ($exist) {
            return ConditionsTest::$methodName($record_model, $cndArray);
        }
    }

    private function createFunctionName($condition) {
        $tabConditionName = explode(' ', $condition);

        for ($i = 0; $i < count($tabConditionName); $i++) {
            if (0 != $i) {
                $tabConditionName[$i] = ucfirst($tabConditionName[$i]);
            }
        }

        return implode('', $tabConditionName);
    }
}