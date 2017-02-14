<?php

/**
 * Advanced Filter Class
 * @package YetiForce.Helpers
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_AdvancedFilter_Helper
{

	public static function getMetaVariables()
	{
		return [
			'Current Date' => '(general : (__VtigerMeta__) date) ($_DATE_FORMAT_)',
			'Current Time' => '(general : (__VtigerMeta__) time)',
			'System Timezone' => '(general : (__VtigerMeta__) dbtimezone)',
			'User Timezone' => '(general : (__VtigerMeta__) usertimezone)',
			'CRM Detail View URL' => '(general : (__VtigerMeta__) crmdetailviewurl)',
			'Portal Detail View URL' => '(general : (__VtigerMeta__) portaldetailviewurl)',
			'Site Url' => '(general : (__VtigerMeta__) siteurl)',
			'Portal Url' => '(general : (__VtigerMeta__) portalurl)',
			'Record Id' => '(general : (__VtigerMeta__) recordId)',
		];
	}

	/**
	 * Function to get all the supported advanced filter operations
	 * @return <Array>
	 */
	public static function getAdvancedFilterOptions()
	{
		return array(
			'is' => 'is',
			'is not' => 'is not',
			'contains' => 'contains',
			'does not contain' => 'does not contain',
			'starts with' => 'starts with',
			'ends with' => 'ends with',
			'has changed' => 'has changed',
			'has changed to' => 'has changed to',
			'is empty' => 'is empty',
			'is not empty' => 'is not empty',
			'less than' => 'less than',
			'greater than' => 'greater than',
			'does not equal' => 'does not equal',
			'less than or equal to' => 'less than or equal to',
			'greater than or equal to' => 'greater than or equal to',
			'before' => 'before',
			'after' => 'after',
			'between' => 'between',
			'is added' => 'is added',
			'is today' => 'is today',
			'less than hours before' => 'less than hours before',
			'less than hours later' => 'less than hours later',
			'more than hours before' => 'more than hours before',
			'more than hours later' => 'more than hours later',
			'less than days ago' => 'less than days ago',
			'more than days ago' => 'more than days ago',
			'in less than' => 'in less than',
			'in more than' => 'in more than',
			'days ago' => 'days ago',
			'days later' => 'days later',
			'equal to' => 'equal to',
			'None' => 'None',
			'is Watching Record' => 'is Watching Record',
			'is Not Watching Record' => 'is Not Watching Record',
		);
	}

	/**
	 * Function to get the advanced filter option names by Field type
	 * @return array
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		return [
			'string' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'],
			'salutation' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'],
			'text' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'],
			'url' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'],
			'email' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'],
			'phone' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'],
			'integer' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed'],
			'double' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed'],
			'currency' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'is not empty'],
			'picklist' => ['is', 'is not', 'has changed', 'has changed to', 'is empty', 'is not empty'],
			'multipicklist' => ['is', 'is not', 'has changed', 'has changed to'],
			'datetime' => ['is', 'is not', 'has changed', 'less than hours before', 'less than hours later', 'more than hours before', 'more than hours later', 'is not empty'],
			'time' => ['is', 'is not', 'has changed', 'is not empty'],
			'date' => ['is', 'is not', 'has changed', 'between', 'before', 'after', 'is today', 'less than days ago', 'more than days ago', 'in less than', 'in more than',
				'days ago', 'days later', 'is not empty'],
			'boolean' => ['is', 'is not', 'has changed'],
			'reference' => ['has changed', 'is empty', 'is not empty'],
			'owner' => ['has changed', 'is', 'is not', 'is Watching Record', 'is Not Watching Record'],
			'sharedOwner' => ['has changed', 'is', 'is not'],
			'recurrence' => ['is', 'is not', 'has changed'],
			'comment' => ['is added'],
			'image' => ['is', 'is not', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'],
			'percentage' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'is not empty'],
			'multiReferenceValue' => ['contains', 'does not contain', 'has changed', 'is empty', 'is not empty'],
			'tree' => ['is', 'is not', 'has changed', 'has changed to', 'is empty', 'is not empty'],
			'rangeTime' => ['is empty', 'is not empty'],
			'documentsFileUpload' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed'],
		];
	}

	public static function getExpressions()
	{
		return ['concat' => 'concat(a,b)', 'time_diffdays(a,b)' => 'time_diffdays(a,b)', 'time_diffdays(a)' => 'time_diffdays(a)', 'time_diff(a,b)' => 'time_diff(a,b)', 'time_diff(a)' => 'time_diff(a)',
			'add_days' => 'add_days(datefield, noofdays)', 'sub_days' => 'sub_days(datefield, noofdays)', 'add_time(timefield, minutes)' => 'add_time(timefield, minutes)', 'sub_time(timefield, minutes)' => 'sub_time(timefield, minutes)',
			'today' => "get_date('today')", 'tomorrow' => "get_date('tomorrow')", 'yesterday' => "get_date('yesterday')"];
	}

	/**
	 * Functions transforms workflow filter to advanced filter
	 * @return <Array>
	 */
	public static function transformToAdvancedFilterCondition($conditions = false)
	{
		$transformedConditions = [];
		if (!empty($conditions)) {
			foreach ($conditions as $index => $info) {
				if (!($info['groupid'])) {
					$firstGroup[] = array('columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				} else {
					$secondGroup[] = array('columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				}
			}
		}
		$transformedConditions[1] = array('columns' => $firstGroup);
		$transformedConditions[2] = array('columns' => $secondGroup);
		return $transformedConditions;
	}

	public static function transformToSave($conditions = false)
	{
		$wfCondition = [];
		if (!empty($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if ($index == '1' && empty($columns)) {
					$wfCondition[] = array('fieldname' => '', 'operation' => '', 'value' => '', 'valuetype' => '',
						'joincondition' => '', 'groupid' => '0');
				}
				if (!empty($columns) && is_array($columns)) {
					foreach ($columns as $column) {
						$wfCondition[] = array('fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'], 'valuetype' => $column['valuetype'], 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'], 'groupid' => $column['groupid']);
					}
				}
			}
		}
		return $wfCondition;
	}

	public static function getDateFilter($moduleName)
	{
		foreach (\App\CustomView::getDateFilterTypes() as $comparatorKey => $comparatorInfo) {
			$comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
			$comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
			$comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $moduleName);
			$dateFilters[$comparatorKey] = $comparatorInfo;
		}
		return $dateFilters;
	}

	protected static $recordStructure = false;

	public static function getRecordStructure($recordModel)
	{
		$recordId = $recordModel->getId();
		if (isset(self::$recordStructure[$recordId])) {
			return self::$recordStructure[$recordId];
		}
		$values = [];
		$baseModuleModel = $moduleModel = $recordModel->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isViewable()) {
						if (in_array($moduleModel->getName(), array('Calendar', 'Events')) && $fieldName != 'modifiedby' && $fieldModel->getDisplayType() == 3) {
							/* Restricting the following fields(Event module fields) for "Calendar" module
							 * time_start, time_end, eventstatus, activitytype,	visibility, duration_hours,
							 * duration_minutes, reminder_time, notime
							 */
							continue;
						}
						if (!empty($recordId)) {
							//Set the fieldModel with the valuetype for the client side.
							$fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
							$fieldInfo = $fieldModel->getFieldInfo();
							$fieldInfo['workflow_valuetype'] = $fieldValueType;
							$fieldModel->setFieldInfo($fieldInfo);
						}
						// This will be used during editing task like email, sms etc
						$fieldModel->set('workflow_columnname', "$(record : $fieldName)$");
						$values[$blockLabel][$fieldName] = clone $fieldModel;
					}
				}
			}
		}
		//All the reference fields should also be sent
		$fields = $moduleModel->getFieldsByType(array('reference', 'owner', 'multireference'));
		foreach ($fields as $parentFieldName => $field) {
			$type = $field->getFieldDataType();
			$referenceModules = $field->getReferenceList();
			if ($type === 'owner') {
				$referenceModules = array('Users');
			}
			foreach ($referenceModules as $refModule) {
				$moduleModel = Vtiger_Module_Model::getInstance($refModule);
				$blockModelList = $moduleModel->getBlocks();
				foreach ($blockModelList as $blockLabel => $blockModel) {
					$fieldModelList = $blockModel->getFields();
					if (!empty($fieldModelList)) {
						foreach ($fieldModelList as $fieldName => $fieldModel) {
							if ($fieldModel->isViewable()) {
								$name = "$(reletedRecord : $parentFieldName|$fieldName|$refModule)$";
								$label = vtranslate($field->get('label'), $baseModuleModel->getName()) . ' : (' . vtranslate($refModule, $refModule) . ') ' . vtranslate($fieldModel->get('label'), $refModule);
								$fieldModel->set('workflow_columnname', $name);
								if (!empty($recordId)) {
									$fieldValueType = $recordModel->getFieldFilterValueType($name);
									$fieldInfo = $fieldModel->getFieldInfo();
									$fieldInfo['workflow_valuetype'] = $fieldValueType;
									$fieldModel->setFieldInfo($fieldInfo);
								}
								$values[$field->get('label')][$name] = clone $fieldModel;
							}
						}
					}
				}
			}
		}
		self::$recordStructure[$recordId] = $values;
		return $values;
	}
}
