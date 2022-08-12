<?php

/**
 * Advanced Filter Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * Function to get all the supported advanced filter operations.
	 *
	 * @return <Array>
	 */
	public static function getAdvancedFilterOptions()
	{
		return [
			'is' => 'is',
			'is not' => 'is not',
			'contains' => 'contains',
			'does not contain' => 'does not contain',
			'starts with' => 'starts with',
			'ends with' => 'ends with',
			'has changed' => 'has changed',
			'has changed to' => 'has changed to',
			'not has changed' => 'not has changed',
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
			'om' => 'currently logged in user',
			'nom' => 'user currently not logged',
			'None' => 'None',
			'is Watching Record' => 'is Watching Record',
			'is Not Watching Record' => 'is Not Watching Record',
			'is record open' => 'Record is open',
			'is record closed' => 'Record is closed',
			'not created by owner' => 'LBL_NOT_CREATED_BY_OWNER',
		];
	}

	/**
	 * Function to get the advanced filter option names by Field type.
	 *
	 * @return array
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		return [
			'accountName' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'is empty', 'is not empty'],
			'string' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'text' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'url' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'email' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'phone' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'integer' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'not has changed'],
			'double' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'not has changed'],
			'advPercentage' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'not has changed'],
			'currency' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'not has changed', 'is not empty'],
			'picklist' => ['is', 'is not', 'has changed', 'not has changed', 'has changed to', 'is empty', 'is not empty', 'is record open', 'is record closed'],
			'multipicklist' => ['is', 'is not', 'has changed', 'not has changed', 'has changed to', 'is empty', 'is not empty'],
			'datetime' => ['is', 'is not', 'has changed', 'not has changed', 'less than hours before', 'less than hours later', 'more than hours before', 'more than hours later', 'is not empty', 'smallerthannow', 'greaterthannow'],
			'time' => ['is', 'is not', 'has changed', 'not has changed', 'is not empty'],
			'date' => ['is', 'is not', 'has changed', 'not has changed', 'between', 'before', 'after', 'is today', 'less than days ago', 'more than days ago', 'in less than', 'in more than',
				'days ago', 'days later', 'is not empty', ],
			'boolean' => ['is', 'is not', 'has changed', 'not has changed', 'has changed to'],
			'reference' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'owner' => ['has changed', 'not has changed', 'is', 'is not', 'is Watching Record', 'is Not Watching Record', 'not created by owner'],
			'sharedOwner' => ['has changed', 'not has changed', 'is', 'is not', 'is not empty', 'is empty'],
			'userCreator' => ['is', 'is not', 'is not empty', 'is empty', 'om', 'nom'],
			'recurrence' => ['is', 'is not', 'has changed', 'not has changed'],
			'image' => ['is', 'is not', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'],
			'percentage' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'not has changed', 'is not empty'],
			'multiReferenceValue' => ['contains', 'does not contain', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'tree' => ['is', 'is not', 'has changed', 'not has changed', 'has changed to', 'is empty', 'is not empty'],
			'categoryMultipicklist' => ['contains', 'contains hierarchy', 'does not contain', 'does not contain hierarchy', 'is', 'is not', 'has changed', 'not has changed', 'has changed to', 'is empty', 'is not empty'],
			'rangeTime' => ['is empty', 'is not empty'],
			'documentsFileUpload' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty', 'has changed', 'not has changed'],
			'multiImage' => ['is', 'is not', 'contains', 'does not contain', 'is empty', 'is not empty'],
			'twitter' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'multiEmail' => ['contains', 'does not contain', 'starts with', 'ends with', 'is empty', 'is not empty'],
			'serverAccess' => ['is', 'is not', 'has changed', 'not has changed'],
			'multiDomain' => ['is', 'contains', 'does not contain', 'starts with', 'ends with', 'has changed', 'not has changed', 'is empty', 'is not empty'],
			'currencyInventory' => ['equal to', 'less than', 'greater than', 'does not equal', 'less than or equal to', 'greater than or equal to', 'has changed', 'not has changed'],
			'country' => ['is', 'is not', 'is empty', 'is not empty']
		];
	}

	public static function getExpressions()
	{
		return ['concat' => 'concat(a,b)', 'time_diffdays(a,b)' => 'time_diffdays(a,b)', 'time_diffdays(a)' => 'time_diffdays(a)', 'time_diff(a,b)' => 'time_diff(a,b)', 'time_diff(a)' => 'time_diff(a)',
			'add_days' => 'add_days(datefield, noofdays)', 'sub_days' => 'sub_days(datefield, noofdays)', 'add_time(timefield, minutes)' => 'add_time(timefield, minutes)', 'sub_time(timefield, minutes)' => 'sub_time(timefield, minutes)',
			'today' => "get_date('today')", 'tomorrow' => "get_date('tomorrow')", 'yesterday' => "get_date('yesterday')", ];
	}

	/**
	 * Functions transforms workflow filter to advanced filter.
	 *
	 * @param mixed $conditions
	 *
	 * @return <Array>
	 */
	public static function transformToAdvancedFilterCondition($conditions = false)
	{
		$transformedConditions = [];
		$firstGroup = [];
		$secondGroup = [];
		if (!empty($conditions)) {
			foreach ($conditions as $info) {
				if (!($info['groupid'])) {
					$firstGroup[] = ['columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid'], ];
				} else {
					$secondGroup[] = ['columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid'], ];
				}
			}
		}
		$transformedConditions[1] = ['columns' => $firstGroup];
		$transformedConditions[2] = ['columns' => $secondGroup];

		return $transformedConditions;
	}

	public static function transformToSave($conditions = false)
	{
		$wfCondition = [];
		if (!empty($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if ('1' == $index && empty($columns)) {
					$wfCondition[] = ['fieldname' => '', 'operation' => '', 'value' => '', 'valuetype' => '',
						'joincondition' => '', 'groupid' => '0', ];
				}
				if (!empty($columns) && \is_array($columns)) {
					foreach ($columns as $column) {
						$wfCondition[] = ['fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'], 'valuetype' => $column['valuetype'], 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'] ?? '', 'groupid' => $column['groupid'], ];
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
			$comparatorInfo['label'] = \App\Language::translate($comparatorInfo['label'], $moduleName);
			$dateFilters[$comparatorKey] = $comparatorInfo;
		}
		return $dateFilters;
	}

	protected static $recordStructure = false;
}
