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

class Leads_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function returns deleted records condition
	 */
	public function getDeletedRecordCondition()
	{
		return 'vtiger_crmentity.deleted = 0 AND vtiger_leaddetails.converted = 0';
	}

	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public function getRecentRecords($limit = 10)
	{
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$deletedCondition = $this->getDeletedRecordCondition();
		$query = 'SELECT * FROM vtiger_crmentity ' .
			' INNER JOIN vtiger_leaddetails ON
                vtiger_leaddetails.leadid = vtiger_crmentity.crmid
                WHERE setype=? AND ' . $deletedCondition . ' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->get('name'), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$recentRecords = array();
		for ($i = 0; $i < $noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function returns the Number of Leads created per week
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsCreated($owner, $dateFilter)
	{
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}

		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$result = $db->pquery('SELECT COUNT(*) AS count, date(createdtime) AS time FROM vtiger_leaddetails
						INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
						AND deleted=0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql . ' AND converted = 0 GROUP BY week(createdtime)', $params);

		$response = array();
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$response[$i][1] = $row['time'];
		}
		return $response;
	}

	/**
	 * Function returns Leads grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsByStatusConverted($owner, $dateFilter)
	{
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}

		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_leadstatus.leadstatus IS NULL OR vtiger_leadstatus.leadstatus = "" THEN "" ELSE vtiger_leadstatus.leadstatus END AS leadstatusvalue 
		FROM vtiger_leaddetails 
		INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
		AND deleted=0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
			'INNER JOIN vtiger_leadstatus ON vtiger_leaddetails.leadstatus = vtiger_leadstatus.leadstatus 
		GROUP BY leadstatusvalue ORDER BY vtiger_leadstatus.sortorderid', $params);

		$response = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$leadStatusVal = $row['leadstatusvalue'];
			if ($leadStatusVal == '') {
				$leadStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($leadStatusVal, $this->getName());
			$response[$i][2] = $leadStatusVal;
		}
		return $response;
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
						WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.link = " . $recordId;
			$time = vtlib_purify($_REQUEST['time']);
			if ($time == 'current') {
				$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('current');
				$query .= " AND (vtiger_activity.activitytype NOT IN ('Emails') AND vtiger_activity.status IN ('" . implode("','", $stateActivityLabels) . "'))";
			}
			if ($time == 'history') {
				$query .= " AND ((vtiger_activity.activitytype='Task' and vtiger_activity.status in ('Completed','Deferred'))
				OR (vtiger_activity.activitytype not in ('Emails','Task') and  vtiger_activity.status in ('Completed','Deferred')))";
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

	/**
	 * Function to get Converted Information for selected records
	 * @param <array> $recordIdsList
	 * @return <array> converted Info
	 */
	public static function getConvertedInfo($recordIdsList = array())
	{
		$convertedInfo = array();
		if ($recordIdsList) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT converted FROM vtiger_leaddetails WHERE leadid IN (" . generateQuestionMarks($recordIdsList) . ")", $recordIdsList);
			$numOfRows = $db->num_rows($result);

			for ($i = 0; $i < $numOfRows; $i++) {
				$convertedInfo[$recordIdsList[$i]] = $db->query_result($result, $i, 'converted');
			}
		}
		return $convertedInfo;
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
		if (in_array($sourceModule, array('Campaigns', 'Products', 'Services', 'Emails'))) {
			switch ($sourceModule) {
				case 'Campaigns' : $tableName = 'vtiger_campaignleadrel';
					$fieldName = 'leadid';
					$relatedFieldName = 'campaignid';
					break;
				case 'Products' : $tableName = 'vtiger_seproductsrel';
					$fieldName = 'crmid';
					$relatedFieldName = 'productid';
					break;
			}

			if ($sourceModule === 'Services') {
				$condition = " vtiger_leaddetails.leadid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' vtiger_leaddetails.emailoptout = 0';
			} else {
				$condition = " vtiger_leaddetails.leadid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = '$record')";
			}

			$position = stripos($listQuery, 'where');
			if ($position) {
				$overRideQuery = $listQuery . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	public function getDefaultSearchField()
	{
		return "company";
	}

	public function searchAccountsToConvert($recordModel)
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		if ($recordModel) {
			$params = [];
			$db = PearDatabase::getInstance();
			$mappingFields = Vtiger_Processes_Model::getConfig('marketing', 'conversion', 'mapping');
			$mappingFields = Zend_Json::decode($mappingFields);
			$sql = "SELECT vtiger_account.accountid FROM vtiger_account "
				. "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_account.accountid "
				. "INNER JOIN `vtiger_accountaddress` ON vtiger_accountaddress.accountaddressid=vtiger_account.accountid "
				. "INNER JOIN `vtiger_accountscf` ON vtiger_accountscf.accountid=vtiger_account.accountid "
				. "WHERE vtiger_crmentity.deleted=0";
			foreach ($mappingFields as $fields) {
				$sql .= ' AND `' . current($fields) . '` = ?';
				$params[] = $recordModel->get(key($fields));
			}
			$result = $db->pquery($sql, $params);
			$num = $db->num_rows($result);
			if ($num > 1) {
				$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
				return false;
			} elseif ($num == 1) {
				$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
				return (int) $db->query_result($result, 0, 'accountid');
			}
		}
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
		return true;
	}

	/**
	 * Function that returns status that allow to convert Lead
	 * @return <Array> array of statuses
	 */
	public static function getConversionAvaibleStatuses()
	{
		$leadConfig = Settings_MarketingProcesses_Module_Model::getConfig('lead');

		return $leadConfig['convert_status'];
	}

	/**
	 * Function that checks if lead record can be converted
	 * @param <String> $status - lead status
	 * @return <boolean> if or not allowed to convert
	 */
	public static function checkIfAllowedToConvert($status)
	{
		$leadConfig = Settings_MarketingProcesses_Module_Model::getConfig('lead');

		if (empty($leadConfig['convert_status'])) {
			return true;
		} else {
			return in_array($status, $leadConfig['convert_status']);
		}
	}
}
