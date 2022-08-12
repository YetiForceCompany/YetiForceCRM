<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
		if (\is_array($expr)) {
			$groupResults = [];
			$expressionResults = [];
			$i = 0;
			foreach ($expr as $cond) {
				$conditionGroup = $cond['groupid'];
				if (empty($conditionGroup)) {
					$conditionGroup = 0;
				}
				preg_match('/(\w+) : \((\w+)\) (\w+)/', $cond['fieldname'], $matches);
				if (0 == \count($matches)) {
					$expressionResults[$conditionGroup][$i]['result'] = $this->checkCondition($recordModel, $cond);
				} else {
					$referenceField = $matches[1];
					$referenceModule = $matches[2];
					$fieldname = $matches[3];
					$result = false;
					$referenceFieldId = $recordModel->get($referenceField);
					if (!empty($referenceFieldId)) {
						$cond['fieldname'] = $fieldname;
						if ('Users' !== $referenceModule) {
							if (\App\Record::isExists($referenceFieldId)) {
								$referenceRecordModel = Vtiger_Record_Model::getInstanceById($referenceFieldId);
								$result = $this->checkCondition($referenceRecordModel, $cond, $recordModel);
							}
						} elseif ('Users' === \App\Fields\Owner::getType($referenceFieldId) && \App\User::getUserModel($referenceFieldId)->isActive()) {
							$referenceRecordModel = Vtiger_Record_Model::getInstanceById($referenceFieldId, $referenceModule);
							$result = $this->checkCondition($referenceRecordModel, $cond, $recordModel);
						}
					}
					$expressionResults[$conditionGroup][$i]['result'] = $result;
				}
				$expressionResults[$conditionGroup][$i + 1]['logicaloperator'] = (!empty($cond['joincondition'])) ? $cond['joincondition'] : 'and';
				$groupResults[$conditionGroup]['logicaloperator'] = (!empty($cond['groupjoin'])) ? $cond['groupjoin'] : 'and';
				++$i;
			}
			foreach ($expressionResults as $groupId => &$groupExprResultSet) {
				$groupResult = true;
				foreach ($groupExprResultSet as &$exprResult) {
					$result = null;
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
		$sl = \strlen($str);
		$ssl = \strlen($subStr);
		if ($sl >= $ssl) {
			return 0 == substr_compare($str, $subStr, 0, $ssl);
		}
		return false;
	}

	public function endsWith($str, $subStr)
	{
		$sl = \strlen($str);
		$ssl = \strlen($subStr);
		if ($sl >= $ssl) {
			return 0 == substr_compare($str, $subStr, $sl - $ssl, $ssl);
		}
		return false;
	}

	/**
	 * Check condition.
	 *
	 * @param Vtiger_Record_Model      $recordModel
	 * @param array                    $cond
	 * @param Vtiger_Record_Model|null $referredRecordModel
	 *
	 * @throws Exception
	 *
	 * @return bool
	 */
	public function checkCondition(Vtiger_Record_Model $recordModel, $cond, Vtiger_Record_Model $referredRecordModel = null)
	{
		$condition = $cond['operation'];
		$fieldInstance = $recordModel->getModule()->getFieldByName($cond['fieldname']);
		if (empty($condition) || false === $fieldInstance) {
			return false;
		}
		$dataType = $fieldInstance->getFieldDataType();
		if ('datetime' === $dataType || 'date' === $dataType) {
			$fieldName = $cond['fieldname'];
			$dateTimePair = ['date_start' => 'time_start', 'due_date' => 'time_end'];
			if (isset($dateTimePair[$fieldName]) && !$recordModel->isEmpty($dateTimePair[$fieldName])) {
				$fieldValue = $recordModel->get($fieldName) . ' ' . $recordModel->get($dateTimePair[$fieldName]);
			} else {
				$fieldValue = $recordModel->get($fieldName);
			}
			$rawFieldValue = $fieldValue;
		} else {
			$fieldValue = $recordModel->get($cond['fieldname']);
		}
		$value = trim(html_entity_decode($cond['value'] ?? ''));
		$expressionType = $cond['valuetype'];
		if ('fieldname' === $expressionType) {
			if (null !== $referredRecordModel) {
				$value = $referredRecordModel->get($value);
			} else {
				$value = $recordModel->get($value);
			}
		} elseif ('expression' === $expressionType) {
			require_once 'modules/com_vtiger_workflow/expression_engine/include.php';
			$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($value)));
			$expression = $parser->expression();
			$exprEvaluater = new VTFieldExpressionEvaluater($expression);
			if (null !== $referredRecordModel) {
				$value = $exprEvaluater->evaluate($referredRecordModel);
			} else {
				$value = $exprEvaluater->evaluate($recordModel);
			}
		}
		switch ($dataType) {
				case 'accountName':
					$fieldValue = $recordModel->get($fieldInstance->getName());
					$recordData = explode('|##|', $fieldValue);
					if (\count($recordData) > 1) {
						$fieldValue = trim("$recordData[0] $recordData[1]");
					}
					return $value === $fieldValue;
					break;
				case 'datetime':
					$fieldValue = $recordModel->get($fieldInstance->getName());
					break;
				case 'date':
					if ('between' !== $condition && strtotime($value)) {
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
				case 'categoryMultipicklist':
					if (\in_array($condition, ['contains', 'contains hierarchy', 'does not contain', 'does not contain hierarchy', 'is', 'is not'])) {
						$value = array_filter(explode(',', $value));
						$fieldValue = array_filter(explode(',', $fieldValue));
						sort($value);
						sort($fieldValue);
						switch ($condition) {
							case 'is':
								return $value === $fieldValue;
							case 'is not':
								return $value !== $fieldValue;
							case 'contains':
								return empty(array_diff($value, $fieldValue));
							case 'contains hierarchy':
								$result = true;
								$value = \Settings_TreesManager_Record_Model::getChildren(implode('##', $value), $fieldInstance->getColumnName(), \Vtiger_Module_Model::getInstance($recordModel->getModule()->getName()));
								if (!empty($value)) {
									$value = explode('##', $value);
									sort($value);
								}
								foreach ($fieldValue as $val) {
									if (!\in_array($val, $value)) {
										$result = false;
									}
								}
								return $result;
							case 'does not contain':
								return !empty(array_diff($value, $fieldValue));
							case 'does not contain hierarchy':
								$result = true;
								$value = \Settings_TreesManager_Record_Model::getChildren(implode('##', $value), $fieldInstance->getColumnName(), \Vtiger_Module_Model::getInstance($recordModel->getModule()->getName()));
								if (!empty($value)) {
									sort($value);
									$value = explode('##', $value);
								}
								foreach ($fieldValue as $val) {
									if (\in_array($val, $value)) {
										$result = false;
									}
								}
								return $result;
							default:
								break;
						}
					}
					$value = ",{$value},";
					break;
				case 'sharedOwner':
					if ('is' === $condition || 'is not' === $condition) {
						$fieldValueTemp = $value;
						$value = explode(',', $fieldValue);
						$fieldValue = $fieldValueTemp;
						$condition = ('is' == $condition) ? 'contains' : 'does not contain';
					}
					break;
				case 'owner':
					if ('is' === $condition || 'is not' === $condition) {
						//To avoid again checking whether it is user or not
						if (false !== strpos($value, ',')) {
							$value = explode(',', $value);
						} elseif ($value) {
							$value = [$value];
						} else {
							$value = [];
						}
						$condition = ('is' == $condition) ? 'contains' : 'does not contain';
					}
					break;
				case 'reference':
					$fieldValue = $recordModel->getDisplayValue($fieldInstance->getName(), false, true);
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
			case 'greaterthannow':
				$value = date('Y-m-d');
				if ('datetime' === $dataType) {
					$value = date('Y-m-d H:i:s');
				}
				return $fieldValue > $value;
			case 'smallerthannow':
				$value = date('Y-m-d');
				if ('datetime' === $dataType) {
					$value = date('Y-m-d H:i:s');
				}
				return $fieldValue < $value;
			case 'does not equal':
				return $fieldValue != $value;
			case 'less than or equal to':
				return $fieldValue <= $value;
			case 'greater than or equal to':
				return $fieldValue >= $value;
			case 'is':
				if (preg_match('/([^:]+):boolean$/', $value, $match)) {
					$value = $match[1];
					if ('true' == $value) {
						return 'on' === $fieldValue || 1 === $fieldValue || '1' === $fieldValue;
					}
					return 'off' === $fieldValue || 0 === $fieldValue || '0' === $fieldValue || '' === $fieldValue;
				}
					return $fieldValue == $value;
			case 'is not':
				if (preg_match('/([^:]+):boolean$/', $value, $match)) {
					$value = $match[1];
					if ('true' == $value) {
						return 'off' === $fieldValue || 0 === $fieldValue || '0' === $fieldValue || '' === $fieldValue;
					}
					return 'on' === $fieldValue || 1 === $fieldValue || '1' === $fieldValue;
				}
					return $fieldValue != $value;
			case 'contains':
				if (\is_array($value)) {
					return \in_array($fieldValue, $value);
				}
				return false !== strpos($fieldValue, $value);
			case 'does not contain':
				if (empty($value)) {
					unset($value);
				}
				if (\is_array($value)) {
					return !\in_array($fieldValue, $value);
				}
				return false === strpos($fieldValue, $value);
			case 'starts with':
				return $this->startsWith($fieldValue, $value);
			case 'ends with':
				return $this->endsWith($fieldValue, $value);
			case 'matches':
				return preg_match($value, $fieldValue);
			case 'has changed':
				$hasChanged = $recordModel->getPreviousValue($cond['fieldname']);
				if (false === $hasChanged) {
					return false;
				}
				return $fieldValue != $hasChanged;
			case 'not has changed':
				return false === $recordModel->getPreviousValue($cond['fieldname']);
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
				return ($recordModel->isNew() || false !== $oldValue) && $recordModel->get($cond['fieldname']) == $value;
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
			case 'om':
				return (int) $value !== \App\User::getCurrentUserId() ? false : true;
			case 'nom':
				return (int) $value === \App\User::getCurrentUserId() ? false : true;
			case 'is record open':
				if (
					($fieldName = App\RecordStatus::getFieldName($recordModel->getModule()->getName()))
				&& \in_array($recordModel->get($fieldName), App\RecordStatus::getStates($recordModel->getModule()->getName(), \App\RecordStatus::RECORD_STATE_OPEN))
				) {
					return true;
				}
				return false;
			case 'is record closed':
				if (
					($fieldName = App\RecordStatus::getFieldName($recordModel->getModule()->getName()))
				&& \in_array($recordModel->get($fieldName), App\RecordStatus::getStates($recordModel->getModule()->getName(), \App\RecordStatus::RECORD_STATE_CLOSED))
				) {
					return false;
				}
				return true;
			case 'not created by owner':
				return $recordModel->get($fieldInstance->getName()) !== $recordModel->get('created_user_id');
			default:
				//Unexpected condition
				throw new \App\Exceptions\AppException('Found an unexpected condition: ' . $condition);
		}
	}
}
