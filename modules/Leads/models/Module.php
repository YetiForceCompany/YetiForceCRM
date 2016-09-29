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
		return 'vtiger_crmentity.deleted = 0 && vtiger_leaddetails.converted = 0';
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
                WHERE setype=? && ' . $deletedCondition . ' && modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->get('name'), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$recentRecords = [];
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
		$module = $this->getName();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);

		if (!empty($owner)) {
			$ownerSql = ' && smownerid = ' . $owner;
		}

		$params = [];
		if (!empty($dateFilter)) {
			$dateFilterSql = ' && createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$sql = sprintf('SELECT COUNT(*) AS count, date(createdtime) AS time FROM vtiger_leaddetails
		INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
		WHERE deleted = 0 %s %s %s', $ownerSql, $dateFilterSql, $securityParameter);
		$sql .= ' && converted = 0 GROUP BY week(createdtime)';
		$result = $db->pquery($sql, $params);

		$response = [];
		while ($row = $db->getRow($result)) {
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

		$module = $this->getName();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);

		if (!empty($owner)) {
			$ownerSql = ' && smownerid = ' . $owner;
		}

		$params = [];
		if (!empty($dateFilter)) {
			$dateFilterSql = ' && createdtime BETWEEN ? AND ?';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$sql = sprintf('SELECT COUNT(*) as count, CASE WHEN vtiger_leadstatus.leadstatus IS NULL || vtiger_leadstatus.leadstatus = "" THEN "" ELSE vtiger_leadstatus.leadstatus END AS leadstatusvalue 
		FROM vtiger_leaddetails 
		INNER JOIN vtiger_crmentity ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid
		INNER JOIN vtiger_leadstatus ON vtiger_leaddetails.leadstatus = vtiger_leadstatus.leadstatus
		WHERE deleted = 0 %s %s %s', $ownerSql, $dateFilterSql, $securityParameter);
		$sql .= ' GROUP BY leadstatusvalue ORDER BY vtiger_leadstatus.sortorderid';
		$result = $db->pquery($sql, $params);

		$response = [];
		while ($row = $db->getRow($result)) {
			$response[$i][0] = $row['count'];
			$leadStatusVal = $row['leadstatusvalue'];
			if ($leadStatusVal == '') {
				$leadStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($leadStatusVal, $module);
			$response[$i][2] = $leadStatusVal;
		}
		return $response;
	}

	/**
	 * Function to get Converted Information for selected records
	 * @param <array> $recordIdsList
	 * @return <array> converted Info
	 */
	public static function getConvertedInfo($recordIdsList = [])
	{
		$convertedInfo = [];
		if ($recordIdsList) {
			$db = PearDatabase::getInstance();
			$query = sprintf('SELECT leadid,converted FROM vtiger_leaddetails WHERE leadid IN (%s)', implode(',', $recordIdsList));
			$result = $db->query($query);
			while ($row = $db->getRow($result)) {
				$convertedInfo[$row['leadid']] = $row['converted'];
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
				case 'Campaigns' : $tableName = 'vtiger_campaign_records';
					$fieldName = 'crmid';
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
				$overRideQuery = $listQuery . ' && ' . $condition;
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
			$mappingFields = \includes\utils\Json::decode($mappingFields);
			$sql = "SELECT vtiger_account.accountid FROM vtiger_account "
				. "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_account.accountid "
				. "INNER JOIN `vtiger_accountaddress` ON vtiger_accountaddress.accountaddressid=vtiger_account.accountid "
				. "INNER JOIN `vtiger_accountscf` ON vtiger_accountscf.accountid=vtiger_account.accountid "
				. "WHERE vtiger_crmentity.deleted=0";
			foreach ($mappingFields as $fields) {
				$sql .= ' && `' . current($fields) . '` = ?';
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
