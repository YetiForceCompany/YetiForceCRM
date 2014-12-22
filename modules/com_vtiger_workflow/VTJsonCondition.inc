<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once("include/Zend/Json.php");

class VTJsonCondition {

	function __construct() {

	}

	function evaluate($condition, $entityCache, $id) {
		$expr = Zend_Json::decode($condition);
		$finalResult = TRUE;
		if (is_array($expr)) {
			$entityData = $entityCache->forId($id);
			$data = $entityData->getData();

			$groupResults = array();
			$expressionResults = array();
			$i = 0;
			foreach ($expr as $cond) {
				$conditionGroup = $cond['groupid'];
				if (empty($conditionGroup))
					$conditionGroup = 0;
				preg_match('/(\w+) : \((\w+)\) (\w+)/', $cond['fieldname'], $matches);
				if (count($matches) == 0) {
					$expressionResults[$conditionGroup][$i]['result'] = $this->checkCondition($entityData, $cond);
				} else {
					list($full, $referenceField, $referenceModule, $fieldname) = $matches;
					$referenceFieldId = $data[$referenceField];
					if ($referenceFieldId != 0) {
						$entity = $entityCache->forId($data[$referenceField]);
						if ($entity->getModuleName() == $referenceModule) {
							$cond['fieldname'] = $fieldname;
							$expressionResults[$conditionGroup][$i]['result'] = $this->checkCondition($entity, $cond, $entityData);
						} else {
							$expressionResults[$conditionGroup][$i]['result'] = FALSE;
						}
					} else {
						$expressionResults[$conditionGroup][$i]['result'] = FALSE;
					}
				}
				$expressionResults[$conditionGroup][$i + 1]['logicaloperator'] = (!empty($cond['joincondition'])) ? $cond['joincondition'] : 'and';
				$groupResults[$conditionGroup]['logicaloperator'] = (!empty($cond['groupjoin'])) ? $cond['groupjoin'] : 'and';
				$i++;
			}

			foreach ($expressionResults as $groupId => $groupExprResultSet) {
				$groupResult = TRUE;
				foreach ($groupExprResultSet as $exprResult) {
					$result = $exprResult['result'];
					$logicalOperator = $exprResult['logicaloperator'];
					if (isset($result)) { // Condition to skip last condition
						if (!empty($logicalOperator)) {
							switch ($logicalOperator) {
								case 'and' : $groupResult = ($groupResult && $result);
									break;
								case 'or' : $groupResult = ($groupResult || $result);
									break;
							}
						} else { // Case for the first condition
							$groupResult = $result;
						}
					}
				}
				$groupResults[$groupId]['result'] = $groupResult;
			}

			foreach ($groupResults as $groupId => $groupResult) {
				$result = $groupResult['result'];
				$logicalOperator = $groupResult['logicaloperator'];
				if (isset($result)) { // Condition to skip last condition
					if (!empty($logicalOperator)) {
						switch ($logicalOperator) {
							case 'and' : $finalResult = ($finalResult && $result);
								break;
							case 'or' : $finalResult = ($finalResult || $result);
								break;
						}
					} else { // Case for the first condition
						$finalResult = $result;
					}
				}
			}
		}
		return $finalResult;
	}

	function startsWith($str, $subStr) {
		$sl = strlen($str);
		$ssl = strlen($subStr);
		if ($sl >= $ssl) {
			return substr_compare($str, $subStr, 0, $ssl) == 0;
		} else {
			return FALSE;
		}
	}

	function endsWith($str, $subStr) {
		$sl = strlen($str);
		$ssl = strlen($subStr);
		if ($sl >= $ssl) {
			return substr_compare($str, $subStr, $sl - $ssl, $ssl) == 0;
		} else {
			return FALSE;
		}
	}

	function checkCondition($entityData, $cond, $referredEntityData=null) {
		$data = $entityData->getData();

		$condition = $cond['operation'];
		if(empty($condition)) return false;

		$fieldValue = $data[$cond['fieldname']];
		$value = trim(html_entity_decode($cond['value']));
		$expressionType = $cond['valuetype'];

		if ($expressionType == 'fieldname') {
			if ($referredEntityData != null) {
				$referredData = $referredEntityData->getData();
			} else {
				$referredData = $data;
			}
			$value = $referredData[$value];
		} elseif ($expressionType == 'expression') {
			require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

			$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($value)));
			$expression = $parser->expression();
			$exprEvaluater = new VTFieldExpressionEvaluater($expression);
			if ($referredEntityData != null) {
				$value = $exprEvaluater->evaluate($referredEntityData);
			} else {
				$value = $exprEvaluater->evaluate($entityData);
			}
		}

		global $current_user;
		$handler = vtws_getModuleHandlerFromName($entityData->getModuleName(), $current_user);
		$moduleFields = $handler->getMeta()->getModuleFields();
		$fieldInstance = $moduleFields[$cond['fieldname']];

		if($fieldInstance && $fieldInstance->getFieldDataType() == 'datetime') {
			//Convert the DB Date Time Format to User Date Time Format
			$date = new DateTimeField($fieldValue);
			$fieldValue = $date->getDisplayDateTimeValue();
			$valueArray = explode(' ', $value);
			if(count($valueArray) == 1) {
				$fieldValueArray = explode(' ', $fieldValue);
				$fieldValue = $fieldValueArray[0];
			}
		}
		//strtotime condition is added for days before, days after where we give integer values, so strtotime will return 0 for such cases.
		if($fieldInstance && $fieldInstance->getFieldDataType() == 'date' && $condition != 'between' && strtotime($value)) {
			//Convert User Date Format filter value to DB date format
			$value = getValidDBInsertDateValue($value);
		}

		if($fieldInstance && $fieldInstance->getFieldDataType() == 'time') {
			$value = $value.':00';	// time fields will not have seconds appended to it, so we are adding 
		}
        
        if($fieldInstance && $fieldInstance->getFieldDataType() == 'owner' ) { 
            if($condition == 'is' || $condition == 'is not') { 
                //To avoid again checking whether it is user or not 
                $idList = array();
                $idList[] = vtws_getWebserviceEntityId('Users',$value); 
                $idList[] = vtws_getWebserviceEntityId('Groups',$value);
                $value = $idList;
                $condition = ($condition == 'is') ? 'contains' : 'does not contain';
            } 
        }

		switch ($condition) {
			case "equal to":
				return $fieldValue == $value;
			case "less than":
				return $fieldValue < $value;
			case "greater than":
				return $fieldValue > $value;
			case "does not equal":
				return $fieldValue != $value;
			case "less than or equal to":
				return $fieldValue <= $value;
			case "greater than or equal to":
				return $fieldValue >= $value;
			case "is":
				if (preg_match('/([^:]+):boolean$/', $value, $match)) {
					$value = $match[1];
					if ($value == 'true') {
						return $fieldValue === 'on' || $fieldValue === 1 || $fieldValue === '1';
					} else {
						return $fieldValue === 'off' || $fieldValue === 0 || $fieldValue === '0' || $fieldValue === '';
					}
				} else {
					return $fieldValue == $value;
				}
			case "is not":
				if (preg_match('/([^:]+):boolean$/', $value, $match)) {
					$value = $match[1];
					if ($value == 'true') {
						return $fieldValue === 'off' || $fieldValue === 0 || $fieldValue === '0' || $fieldValue === '';
					} else {
						return $fieldValue === 'on' || $fieldValue === 1 || $fieldValue === '1';
					}
				} else {
					return $fieldValue != $value;
				}
			case "contains":
                if(is_array($value)){ 
                    return in_array($fieldValue, $value); 
                }
				return strpos($fieldValue, $value) !== FALSE;
			case "does not contain":
				if(empty($value)) unset($value);
                if(is_array($value)){ 
                    return !in_array($fieldValue, $value); 
                } 
				return strpos($fieldValue, $value) === FALSE;
			case "starts with":
				return $this->startsWith($fieldValue, $value);
			case "ends with":
				return $this->endsWith($fieldValue, $value);
			case "matches":
				return preg_match($value, $fieldValue);

			case "has changed" :
				$entityDelta = new VTEntityDelta();
				$idParts = vtws_getIdComponents($entityData->getId());
				$hasChanged = $entityDelta->hasChanged($entityData->getModuleName(), $idParts[1], $cond['fieldname']);
				if (empty($value)) {
					return $hasChanged;
				} else {
					return $hasChanged && $fieldValue == $value;
				}
			case "is empty":
				if(empty($fieldValue)) {
					return true;
				}
				return false;
			case "is not empty":
				if(empty($fieldValue)) {
					return false;
				}
				return true;
			case "before":
				if(empty($fieldValue)) {
					return false;
				}
				if($fieldValue < $value) {
					return true;
				}
				return false;
			case "after":
				if(empty($fieldValue)) {
					return false;
				}
				if($fieldValue > $value) {
					return true;
				}
				return false;
			case "between":
				if(empty($fieldValue)) {
					return false;
				}
				$values = explode(',', $value);
				$values = array_map('getValidDBInsertDateValue',$values);
				if($fieldValue > $values[0] && $fieldValue < $values[1]) {
					return true;
				}
				return false;
			case 'is today':
				$today = date('Y-m-d');

				if($fieldValue == $today) {
					return true;
				}
				return false;
			case 'less than days ago':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$today = date('Y-m-d');
				$olderDate = date('Y-m-d', strtotime('-'.$value.' days'));
				if($olderDate <= $fieldValue && $fieldValue <= $today) {
					return true;
				}
				return false;
			case 'more than days ago':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$olderDate = date('Y-m-d', strtotime('-'.$value.' days'));
				if($fieldValue <= $olderDate) {
					return true;
				}
				return false;
			case 'in less than':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$today = date('Y-m-d');
				$futureDate = date('Y-m-d', strtotime('+'.$value.' days'));
				if($today <= $fieldValue && $fieldValue <= $futureDate) {
					return true;
				}
				return false;
			case 'in more than':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$futureDate = date('Y-m-d', strtotime('+'.$value.' days'));
				if($fieldValue >= $futureDate) {
					return true;
				}
				return false;
			case 'days ago':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$olderDate = date('Y-m-d', strtotime('-'.$value.' days'));
				if($fieldValue == $olderDate) {
					return true;
				}
				return false;
			case 'days later':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$futureDate = date('Y-m-d', strtotime('+'.$value.' days'));
				if($fieldValue == $futureDate) {
					return true;
				}
				return false;

			case 'less than hours before':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$currentTime = date('Y-m-d H:i:s');
				$olderDateTime = date('Y-m-d H:i:s', strtotime('-'.$value.' hours'));
				if($olderDateTime <= $fieldValue && $fieldValue <= $currentTime) {
					return true;
				}
				return false;

			case 'less than hours later':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$currentTime = date('Y-m-d H:i:s');
				$futureDateTime = date('Y-m-d H:i:s', strtotime('+'.$value.' hours'));
				if($currentTime <= $fieldValue && $fieldValue <= $futureDateTime) {
					return true;
				}
				return false;

			case 'more than hours before':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$olderDateTime = date('Y-m-d H:i:s', strtotime('-'.$value.' hours'));
				if($fieldValue <= $olderDateTime) {
					return true;
				}
				return false;
			case 'more than hours later':
				if(empty($fieldValue) || empty($value)) {
					return false;
				}
				$futureDateTime = date('Y-m-d H:i:s', strtotime('+'.$value.' hours'));
				if($fieldValue >= $futureDateTime) {
					return true;
				}
				return false;
			case 'has changed to' :
				$entityDelta = new VTEntityDelta();
				$idParts = vtws_getIdComponents($entityData->getId());
				return $entityDelta->hasChanged($entityData->getModuleName(), $idParts[1], $cond['fieldname'], $value);
			case 'is added':
				//This condition was used only for comments. It should not execute from not from workflows, So it was always "FALSE"
				return false;
			default:
				//Unexpected condition
				throw new Exception("Found an unexpected condition: " . $condition);
		}
	}

}

?>
