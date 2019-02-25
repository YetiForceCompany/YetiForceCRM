<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class VTJsonCondition
{
	/**
	 * Evaluate.
	 *
	 * @param array               $condition
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return string
	 */
	public function evaluate($condition, Vtiger_Record_Model $recordModel)
	{
		$expr = \App\Json::decode($condition);
		$finalResult = true;
		if (is_array($expr)) {
			$groupResults = [];
			$expressionResults = [];
			$i = 0;
			foreach ($expr as $cond) {
				$conditionGroup = $cond['groupid'];
				if (empty($conditionGroup)) {
					$conditionGroup = 0;
				}
				preg_match('/(\w+) : \((\w+)\) (\w+)/', $cond['fieldname'], $matches);
				if (count($matches) == 0) {
					$expressionResults[$conditionGroup][$i]['result'] = $this->checkCondition($recordModel, $cond);
				} else {
					$referenceField = $matches[1];
					$referenceModule = $matches[2];
					$fieldname = $matches[3];

					$referenceFieldId = $recordModel->get($referenceField);
					if (!empty($referenceFieldId)) {
						if ($referenceModule === 'Users') {
							$referenceRecordModel = Vtiger_Record_Model::getInstanceById($referenceFieldId, $referenceModule);
						} else {
							$referenceRecordModel = Vtiger_Record_Model::getInstanceById($referenceFieldId);
						}
						$cond['fieldname'] = $fieldname;
						$expressionResults[$conditionGroup][$i]['result'] = $this->checkCondition($referenceRecordModel, $cond, $recordModel);
					} else {
						$expressionResults[$conditionGroup][$i]['result'] = false;
					}
				}
				$expressionResults[$conditionGroup][$i + 1]['logicaloperator'] = (!empty($cond['joincondition'])) ? $cond['joincondition'] : 'and';
				$groupResults[$conditionGroup]['logicaloperator'] = (!empty($cond['groupjoin'])) ? $cond['groupjoin'] : 'and';
				++$i;
			}
			foreach ($expressionResults as $groupId => &$groupExprResultSet) {
				$groupResult = true;
				foreach ($groupExprResultSet as &$exprResult) {
					if (isset($exprResult['result'])) {
						$result = $exprResult['result'];
					}
					if (isset($exprResult['logicaloperator'])) {
						$logicalOperator = $exprResult['logicaloperator'];
					}
					if (isset($result)) { // Condition to skip last condition
						if (isset($logicalOperator)) {
							switch ($logicalOperator) {
								case 'and':
									$groupResult = ($groupResult && $result);
									break;
								case 'or':
									$groupResult = ($groupResult || $result);
									break;
								default:
									break;
							}
						} else { // Case for the first condition
							$groupResult = $result;
						}
					}
				}
				$groupResults[$groupId]['result'] = $groupResult;
			}
			foreach ($groupResults as $groupId => &$groupResult) {
				$result = $groupResult['result'];
				$logicalOperator = $groupResult['logicaloperator'];
				if (isset($result)) { // Condition to skip last condition
					if (!empty($logicalOperator)) {
						switch ($logicalOperator) {
							case 'and':
								$finalResult = ($finalResult && $result);
								break;
							case 'or':
								$finalResult = ($finalResult || $result);
								break;
							default:
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

	public function startsWith($str, $subStr)
	{
		$sl = strlen($str);
		$ssl = strlen($subStr);
		if ($sl >= $ssl) {
			return substr_compare($str, $subStr, 0, $ssl) == 0;
		} else {
			return false;
		}
	}

	public function endsWith($str, $subStr)
	{
		$sl = strlen($str);
		$ssl = strlen($subStr);
		if ($sl >= $ssl) {
			return substr_compare($str, $subStr, $sl - $ssl, $ssl) == 0;
		} else {
			return false;
		}
	}

	/**
	 * Check condition.
	 *
	 * @param Vtiger_Record_Model      $recordModel
	 * @param array                    $cond
	 * @param null|Vtiger_Record_Model $referredRecordModel
	 *
	 * @throws Exception
	 *
	 * @return bool
	 */
	public function checkCondition(Vtiger_Record_Model $recordModel, $cond, Vtiger_Record_Model $referredRecordModel = null)
	{
		$condition = $cond['operation'];
		$fieldInstance = $recordModel->getModule()->getFieldByName($cond['fieldname']);
		if (empty($condition) || $fieldInstance === false) {
			return false;
		}
		$dataType = $fieldInstance->getFieldDataType();
		if ($dataType === 'datetime' || $dataType === 'date') {
			$fieldName = $cond['fieldname'];
			$dateTimePair = ['date_start' => 'time_start', 'due_date' => 'time_end'];
			if (!$recordModel->isEmpty($dateTimePair[$fieldName])) {
				$fieldValue = $recordModel->get($fieldName) . ' ' . $recordModel->get($dateTimePair[$fieldName]);
			} else {
				$fieldValue = $recordModel->get($fieldName);
			}
			$rawFieldValue = $fieldValue;
		} else {
			$fieldValue = $recordModel->get($cond['fieldname']);
		}
		$value = trim(html_entity_decode($cond['value']));
		$expressionType = $cond['valuetype'];
		if ($expressionType === 'fieldname') {
			if ($referredRecordModel !== null) {
				$value = $referredRecordModel->get($value);
			} else {
				$value = $recordModel->get($value);
			}
		} elseif ($expressionType === 'expression') {
			require_once 'modules/com_vtiger_workflow/expression_engine/include.php';
			$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($value)));
			$expression = $parser->expression();
			$exprEvaluater = new VTFieldExpressionEvaluater($expression);
			if ($referredRecordModel !== null) {
				$value = $exprEvaluater->evaluate($referredRecordModel);
			} else {
				$value = $exprEvaluater->evaluate($recordModel);
			}
		}
		switch ($dataType) {
				case 'datetime':
					$fieldValue = $recordModel->get($fieldInstance->getName());
					break;
				case 'date':
					if ($condition !== 'between' && strtotime($value)) {
						//strtotime condition is added for days before, days after where we give integer values, so strtotime will return 0 for such cases.
						$value = \App\Fields\Date::formatToDb($value, true);
					}
					break;
				case 'time':
					$value = $value . ':00'; // time fields will not have seconds appended to it, so we are adding
					break;
				case 'multiReferenceValue':
					$value = Vtiger_MultiReferenceValue_UIType::COMMA . $value . Vtiger_MultiReferenceValue_UIType::COMMA;
					break;
				case 'sharedOwner':
					if ($condition === 'is' || $condition === 'is not') {
						$fieldValueTemp = $value;
						$value = explode(',', $fieldValue);
						$fieldValue = $fieldValueTemp;
						$condition = ($condition == 'is') ? 'contains' : 'does not contain';
					}
					break;
				case 'owner':
					if ($condition === 'is' || $condition === 'is not') {
						//To avoid again checking whether it is user or not
						if (strpos($value, ',') !== false) {
							$value = explode(',', $value);
						} elseif ($value) {
							$value = [$value];
						} else {
							$value = [];
						}
						$condition = ($condition == 'is') ? 'contains' : 'does not contain';
					}
					break;
				default:
					break;
			}
		switch ($condition) {
			case 'equal to':
				return $fieldValue == $value;
			case 'less than':
				return $fieldValue < $value;
			case 'greater than':
				return $fieldValue > $value;
			case 'does not equal':
				return $fieldValue != $value;
			case 'less than or equal to':
				return $fieldValue <= $value;
			case 'greater than or equal to':
				return $fieldValue >= $value;
			case 'is':
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
			// no break
			case 'is not':
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
			// no break
			case 'contains':
				if (is_array($value)) {
					return in_array($fieldValue, $value);
				}

				return strpos($fieldValue, $value) !== false;
			case 'does not contain':
				if (empty($value)) {
					unset($value);
				}
				if (is_array($value)) {
					return !in_array($fieldValue, $value);
				}

				return strpos($fieldValue, $value) === false;
			case 'starts with':
				return $this->startsWith($fieldValue, $value);
			case 'ends with':
				return $this->endsWith($fieldValue, $value);
			case 'matches':
				return preg_match($value, $fieldValue);
			case 'has changed':
				$hasChanged = $recordModel->getPreviousValue($cond['fieldname']);
				if ($hasChanged === false) {
					return false;
				} else {
					return $fieldValue != $hasChanged;
				}
			// no break
			case 'is empty':
				if (empty($fieldValue)) {
					return true;
				}

				return false;
			case 'is not empty':
				if (empty($fieldValue)) {
					return false;
				}

				return true;
			case 'before':
				if (empty($fieldValue)) {
					return false;
				}
				if ($fieldValue < $value) {
					return true;
				}

				return false;
			case 'after':
				if (empty($fieldValue)) {
					return false;
				}
				if ($fieldValue > $value) {
					return true;
				}

				return false;
			case 'between':
				if (empty($fieldValue)) {
					return false;
				}
				$values = explode(',', $value);
				$values = array_map('\App\Fields\Date::formatToDb', $values);
				if ($fieldValue > $values[0] && $fieldValue < $values[1]) {
					return true;
				}

				return false;
			case 'is today':
				if ($fieldValue == date('Y-m-d')) {
					return true;
				}

				return false;
			case 'less than days ago':
				if (empty($fieldValue) || empty($value)) {
					return false;
				}
				$olderDate = date('Y-m-d', strtotime('-' . $value . ' days'));
				if ($olderDate <= $fieldValue && $fieldValue <= date('Y-m-d')) {
					return true;
				}

				return false;
			case 'more than days ago':
				if (empty($fieldValue) || empty($value)) {
					return false;
				}
				$olderDate = date('Y-m-d', strtotime('-' . $value . ' days'));
				if ($fieldValue <= $olderDate) {
					return true;
				}

				return false;
			case 'in less than':
				if (empty($fieldValue) || empty($value)) {
					return false;
				}
				$today = date('Y-m-d');
				$futureDate = date('Y-m-d', strtotime('+' . $value . ' days'));
				if ($today <= $fieldValue && $fieldValue <= $futureDate) {
					return true;
				}

				return false;
			case 'in more than':
				if (empty($fieldValue) || empty($value)) {
					return false;
				}
				$futureDate = date('Y-m-d', strtotime('+' . $value . ' days'));
				if ($fieldValue >= $futureDate) {
					return true;
				}

				return false;
			case 'days ago':
				if (empty($fieldValue) || empty($value)) {
					return false;
				}
				$olderDate = date('Y-m-d', strtotime('-' . $value . ' days'));
				if ($fieldValue == $olderDate) {
					return true;
				}

				return false;
			case 'days later':
				if (empty($fieldValue) || empty($value)) {
					return false;
				}
				$futureDate = date('Y-m-d', strtotime('+' . $value . ' days'));
				if ($fieldValue == $futureDate) {
					return true;
				}

				return false;
			case 'less than hours before':
				if (empty($rawFieldValue) || empty($value)) {
					return false;
				}
				$currentTime = date('Y-m-d H:i:s');
				$olderDateTime = date('Y-m-d H:i:s', strtotime('-' . $value . ' hours'));
				if ($olderDateTime <= $rawFieldValue && $rawFieldValue <= $currentTime) {
					return true;
				}

				return false;
			case 'less than hours later':
				if (empty($rawFieldValue) || empty($value)) {
					return false;
				}
				$currentTime = date('Y-m-d H:i:s');
				$futureDateTime = date('Y-m-d H:i:s', strtotime('+' . $value . ' hours'));
				if ($currentTime <= $rawFieldValue && $rawFieldValue <= $futureDateTime) {
					return true;
				}

				return false;
			case 'more than hours before':
				if (empty($rawFieldValue) || empty($value)) {
					return false;
				}
				$olderDateTime = date('Y-m-d H:i:s', strtotime('-' . $value . ' hours'));
				if ($rawFieldValue <= $olderDateTime) {
					return true;
				}

				return false;
			case 'more than hours later':
				if (empty($rawFieldValue) || empty($value)) {
					return false;
				}
				$futureDateTime = date('Y-m-d H:i:s', strtotime('+' . $value . ' hours'));
				if ($rawFieldValue >= $futureDateTime) {
					return true;
				}

				return false;
			case 'has changed to':
				$oldValue = $recordModel->getPreviousValue($cond['fieldname']);

				return $oldValue !== false && $recordModel->get($cond['fieldname']) == $value;
			case 'is added':
				//This condition was used only for comments. It should not execute from not from workflows, So it was always "FALSE"
				return false;
			case 'is Watching Record':
				$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordModel->getId(), $recordModel->getModuleName());
				if ($watchdog->isWatchingRecord()) {
					return true;
				}

				return false;
			case 'is Not Watching Record':
				$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordModel->getId(), $recordModel->getModuleName());
				if ($watchdog->isWatchingRecord()) {
					return false;
				}

				return true;
			default:
				//Unexpected condition
				throw new \App\Exceptions\AppException('Found an unexpected condition: ' . $condition);
		}
	}
}
