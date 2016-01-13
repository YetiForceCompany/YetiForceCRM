<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Campaigns_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule)
	{
		if ($relatedModule === 'Leads') {
			$specificQuery = 'AND vtiger_leaddetails.converted = 0';
			return $specificQuery;
		}
		return parent::getSpecificRelationQuery($relatedModule);
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery)
	{
		if (in_array($sourceModule, array('Leads', 'Accounts', 'Contacts'))) {
			switch ($sourceModule) {
				case 'Leads' : $tableName = 'vtiger_campaignleadrel';
					$relatedFieldName = 'leadid';
					break;
				case 'Accounts' : $tableName = 'vtiger_campaignaccountrel';
					$relatedFieldName = 'accountid';
					break;
				case 'Contacts' : $tableName = 'vtiger_campaigncontrel';
					$relatedFieldName = 'contactid';
					break;
			}

			$condition = " vtiger_campaign.campaignid NOT IN (SELECT campaignid FROM $tableName WHERE $relatedFieldName = '$record')";
			$pos = stripos($listQuery, 'where');

			if ($pos) {
				$overRideQuery = $listQuery . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule, $relationModel = false)
	{
		if ($functionName === 'get_activities') {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_activity.visibility,
						vtiger_activity.status AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.process = " . $recordId;
			$time = vtlib_purify($_REQUEST['time']);
			if ($time == 'current') {
				$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('current');
				$query .= " AND (vtiger_activity.activitytype NOT IN ('Emails') AND vtiger_activity.status IN ('" . implode("','", $stateActivityLabels) . "'))";
			}
			if ($time == 'history') {
				$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('history');
				$query .= " AND (vtiger_activity.activitytype NOT IN ('Emails') AND vtiger_activity.status IN ('" . implode("','", $stateActivityLabels) . "'))";
			}
			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$instance = CRMEntity::getInstance($relatedModuleName);
			$securityParameter = $instance->getUserAccessConditionsQuerySR($relatedModuleName, false, $recordId);
			if ($securityParameter != '')
				$query .= $securityParameter;
		} elseif ($functionName === 'get_mails' && $relatedModule->getName() == 'OSSMailView') {
			$query = OSSMailView_Record_Model::getMailsQuery($recordId, $relatedModule->getName());
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationModel);
		}

		return $query;
	}

}
