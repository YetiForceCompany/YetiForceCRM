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

class PBXManager_Record_Model extends Vtiger_Record_Model
{

	const moduletableName = 'vtiger_pbxmanager';
	const lookuptableName = 'vtiger_pbxmanager_phonelookup';
	const entitytableName = 'vtiger_crmentity';

	public static function getCleanInstance($moduleName)
	{
		return new self;
	}

	/**
	 * Function to get call details(polling)
	 * return <array> calls
	 */
	public function searchIncomingCall()
	{
		$db = PearDatabase::getInstance();
		$query = sprintf('SELECT * FROM %s AS module_table INNER JOIN %s AS entity_table  WHERE module_table.callstatus IN(?,?) && module_table.direction=? && module_table.pbxmanagerid=entity_table.crmid && entity_table.deleted=0', self::moduletableName, self::entitytableName);
		$result = $db->pquery($query, ['ringing', 'in-progress', 'inbound']);
		$recordModels = [];
		$rowCount = $db->num_rows($result);
		for ($i = 0; $i < $rowCount; $i++) {
			$rowData = $db->query_result_rowdata($result, $i);

			$record = new self();
			$record->setData($rowData);
			$recordModels[] = $record;

			//To check if the call status is 'ringing' for >5min
			$starttime = strtotime($rowData['starttime']);
			$currenttime = strtotime(Date('y-m-d H:i:s'));
			$timeDiff = $currenttime - $starttime;
			if ($timeDiff > 300 && $rowData['callstatus'] == 'ringing') {
				$recordIds[] = $rowData['crmid'];
			}
			//END
		}

		if (count($recordIds))
			$this->updateCallStatus($recordIds);

		return $recordModels;
	}

	/**
	 * To update call status from 'ringing' to 'no-response', if status not updated 
	 * for more than 5 minutes
	 * @param type $recordIds
	 */
	public function updateCallStatus($recordIds)
	{
		$db = PearDatabase::getInstance();
		$where = sprintf("pbxmanagerid IN (%s) && callstatus='ringing'", generateQuestionMarks($recordIds));
		$db->update(self::moduletableName, ['callstatus' => 'no-response'], $where, $recordIds);
	}

	/**
	 * Function to save PBXManager record with array of params
	 * @param array $values
	 * return string $recordid
	 */
	public function saveRecordWithArrray($params)
	{
		$moduleModel = Vtiger_Module_Model::getInstance('PBXManager');
		$recordModel = Vtiger_Record_Model::getCleanInstance('PBXManager');
		$details = array_change_key_case($params, CASE_LOWER);
		$fieldModelList = $moduleModel->getFields();
		if (!isset($details["assigned_user_id"]))
			$details["assigned_user_id"] = Users::getActiveAdminId();
		if (!isset($details["created_user_id"]))
			$details["created_user_id"] = Users::getActiveAdminId();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldValue = $details[$fieldName];
			$recordModel->set($fieldName, $fieldValue);
		}
		return $moduleModel->saveRecord($recordModel);
	}

	/**
	 * Function to update call details
	 * @param <array> $details
	 * $param string $callid
	 * return true
	 */
	public function updateCallDetails($details)
	{
		$db = PearDatabase::getInstance();
		$sourceuuid = $this->get('sourceuuid');
		$query = sprintf('UPDATE %s SET ', self::moduletableName);
		foreach ($details as $key => $value) {
			$query .= $key . '=?,';
			$params[] = $value;
		}
		$query = substr_replace($query, "", -1);
		$query .= ' WHERE sourceuuid = ?';
		$params[] = $sourceuuid;
		$db->pquery($query, $params);
		return true;
	}

	/**
	 * To update Assigned to with user who answered the call 
	 */
	public function updateAssignedUser($userid)
	{
		$callid = $this->get('pbxmanagerid');
		$db = PearDatabase::getInstance();
		$db->update(self::entitytableName, ['smownerid' => $userid], 'crmid=?', [$callid]);
		return true;
	}

	public static function getInstanceById($recordId, $module = null)
	{
		$db = PearDatabase::getInstance();
		$record = new self();
		$query = sprintf('SELECT * FROM %s WHERE pbxmanagerid=?', self::moduletableName);
		$params = [$recordId];
		$result = $db->pquery($query, $params);
		$rowCount = $db->num_rows($result);
		if ($rowCount) {
			$rowData = $db->query_result_rowdata($result, 0);
			$record->setData($rowData);
		}
		return $record;
	}

	public static function getInstanceBySourceUUID($sourceuuid)
	{
		$db = PearDatabase::getInstance();
		$record = new self();
		$query = sprintf('SELECT * FROM %s WHERE sourceuuid=?', self::moduletableName);
		$params = [$sourceuuid];
		$result = $db->pquery($query, $params);
		$rowCount = $db->num_rows($result);
		if ($rowCount) {
			$rowData = $db->query_result_rowdata($result, 0);
			$record->setData($rowData);
		}
		return $record;
	}

	/**
	 * Function to save/update contact/account/lead record in Phonelookup table on every save
	 * @param string $fieldName
	 * @param array $details
	 * @param boolean $new
	 * @return int
	 */
	public function receivePhoneLookUpRecord($fieldName, $details, $new)
	{
		$db = \App\Db::getInstance();
		$fnumber = preg_replace('/[-()\s+]/', '', $details[$fieldName]);
		$isExists = (new \App\Db\Query())
			->from(self::lookuptableName)
			->where(['crmid' => $details['crmid'], 'setype' => $details['setype'], 'fieldname' => $fieldName])
			->exists();
		if ($isExists) {
			return $db->createCommand()->update(self::lookuptableName, ['fnumber' => $fnumber, 'rnumber' => strrev($fnumber)], ['crmid' => $details['crmid'], 'setype' => $details['setype'], 'fieldname' => $fieldName])->execute();
		} else {
			return $db->createCommand()
					->insert(self::lookuptableName, [
						'crmid' => $details['crmid'],
						'setype' => $details['setype'],
						'fnumber' => $fnumber,
						'rnumber' => strrev($fnumber),
						'fieldname' => $fieldName
					])->execute();
		}
	}

	/**
	 * Function to delete contact/account/lead record in Phonelookup table on every delete
	 * @param string $recordid
	 */
	public function deletePhoneLookUpRecord($recordid)
	{
		$db = PearDatabase::getInstance();
		$db->delete(self::lookuptableName, 'crmid=?', [$recordid]);
	}

	/**
	 * * Function to check the customer with number in phonelookup table
	 * @param string $from
	 */
	public static function lookUpRelatedWithNumber($from)
	{
		$db = PearDatabase::getInstance();
		$fnumber = preg_replace('/[-()\s+]/', '', $from);
		$rnumber = strrev($fnumber);
		$query = sprintf('SELECT crmid, fieldname FROM %s WHERE fnumber LIKE "%s" || rnumber LIKE "%s" ', self::lookuptableName, "$fnumber%", "$rnumber%");
		$result = $db->query($query);
		if ($db->num_rows($result)) {
			$row = $db->getRow($result);
			$crmid = $row['crmid'];
			$fieldname = $row['fieldname'];
			$contact = $db->pquery('SELECT setype FROM vtiger_crmentity WHERE crmid = ? && deleted=0', [$crmid]);
			if ($db->num_rows($contact)) {
				$rowCrm = $db->getRow($contact);
				$data['id'] = $crmid;
				$data['name'] = \App\Record::getLabel($crmid);
				$data['setype'] = $rowCrm['setype'];
				$data['fieldname'] = $fieldname;
				return $data;
			} else
				return;
		}
		return;
	}

	/**
	 * Function to user details with number
	 * @param string $number
	 */
	public static function getUserInfoWithNumber($number)
	{
		$db = PearDatabase::getInstance();
		if (empty($number)) {
			return false;
		}
		$query = PBXManager_Record_Model::buildSearchQueryWithUIType(11, $number, 'Users');
		$result = $db->pquery($query, array());
		if ($db->num_rows($result) > 0) {
			$user['id'] = $db->query_result($result, 0, 'id');
			$user['name'] = $db->query_result($result, 0, 'name');
			$user['setype'] = 'Users';
			return $user;
		}
		return;
	}

	// Because, User is not related to crmentity 
	public function buildSearchQueryWithUIType($uitype, $value, $module)
	{
		if (empty($value)) {
			return false;
		}

		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		if ($cachedModuleFields === false) {
			getColumnFields($module); // This API will initialize the cache as well
			// We will succeed now due to above function call
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}

		$lookuptables = array();
		$lookupcolumns = array();
		foreach ($cachedModuleFields as $fieldinfo) {
			if (in_array($fieldinfo['uitype'], array($uitype))) {
				$lookuptables[] = $fieldinfo['tablename'];
				$lookupcolumns[] = $fieldinfo['columnname'];
			}
		}

		$entityfields = \vtlib\Functions::getEntityModuleSQLColumnString($module);
		$querycolumnnames = implode(',', $lookupcolumns);
		$entitycolumnnames = $entityfields['fieldname'];

		$query = "select id as id, $querycolumnnames, $entitycolumnnames as name ";
		$query .= " FROM vtiger_users";

		if (!empty($lookupcolumns)) {
			$query .= " WHERE deleted=0 && ";
			$i = 0;
			$columnCount = count($lookupcolumns);
			foreach ($lookupcolumns as $columnname) {
				if (!empty($columnname)) {
					if ($i == 0 || $i == ($columnCount))
						$query .= sprintf("%s = '%s'", $columnname, $value);
					else
						$query .= sprintf(" || %s = '%s'", $columnname, $value);
					$i++;
				}
			}
		}
		return $query;
	}

	public static function getUserNumbers()
	{
		$numbers = null;
		$db = PearDatabase::getInstance();
		$query = 'SELECT id, phone_crm_extension FROM vtiger_users';
		$result = $db->pquery($query, array());
		$count = $db->num_rows($result);
		for ($i = 0; $i < $count; $i++) {
			$number = $db->query_result($result, $i, 'phone_crm_extension');
			$userId = $db->query_result($result, $i, 'id');
			if ($number)
				$numbers[$userId] = $number;
		}
		return $numbers;
	}
}

?>
