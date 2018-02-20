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
	const MODULE_TABLE_NAME = 'vtiger_pbxmanager';
	const LOOKUP_TABLE_NAME = 'vtiger_pbxmanager_phonelookup';
	const ENTITY_TABLE_NAME = 'vtiger_crmentity';

	public static function getCleanInstance($moduleName)
	{
		return new self();
	}

	/**
	 * Function to get call details(polling)
	 * return <array> calls.
	 */
	public function searchIncomingCall()
	{
		$db = PearDatabase::getInstance();
		$query = sprintf('SELECT * FROM %s AS module_table INNER JOIN %s AS entity_table  WHERE module_table.callstatus IN(?,?) && module_table.direction=? && module_table.pbxmanagerid=entity_table.crmid && entity_table.deleted=0', self::MODULE_TABLE_NAME, self::ENTITY_TABLE_NAME);
		$result = $db->pquery($query, ['ringing', 'in-progress', 'inbound']);
		$recordModels = [];
		$rowCount = $db->numRows($result);
		for ($i = 0; $i < $rowCount; ++$i) {
			$rowData = $db->queryResultRowData($result, $i);

			$record = new self();
			$record->setData($rowData);
			$recordModels[] = $record;

			//To check if the call status is 'ringing' for >5min
			$starttime = strtotime($rowData['starttime']);
			$currenttime = strtotime(date('y-m-d H:i:s'));
			$timeDiff = $currenttime - $starttime;
			if ($timeDiff > 300 && $rowData['callstatus'] == 'ringing') {
				$recordIds[] = $rowData['crmid'];
			}
			//END
		}

		if (count($recordIds)) {
			$this->updateCallStatus($recordIds);
		}

		return $recordModels;
	}

	/**
	 * To update call status from 'ringing' to 'no-response', if status not updated
	 * for more than 5 minutes.
	 *
	 * @param type $recordIds
	 */
	public function updateCallStatus($recordIds)
	{
		\App\Db::getInstance()->createCommand()->update(self::MODULE_TABLE_NAME, ['callstatus' => 'no-response'], ['pbxmanagerid' => $recordIds, 'callstatus' => 'ringing'])->execute();
	}

	/**
	 * Function to save PBXManager record with array of params.
	 *
	 * @param array $params
	 *                      return string $recordid
	 */
	public function saveRecordWithArrray($params)
	{
		$recordModel = Vtiger_Record_Model::getCleanInstance('PBXManager');
		$details = array_change_key_case($params, CASE_LOWER);
		if (!isset($details['assigned_user_id'])) {
			$details['assigned_user_id'] = Users::getActiveAdminId();
		}
		if (!isset($details['created_user_id'])) {
			$details['created_user_id'] = Users::getActiveAdminId();
		}
		foreach (Vtiger_Module_Model::getInstance('PBXManager')->getFields() as $fieldName => $fieldModel) {
			$fieldValue = $details[$fieldName];
			$recordModel->set($fieldName, $fieldValue);
		}

		return $recordModel->save();
	}

	/**
	 * Function to update call details.
	 *
	 * @param <array> $details
	 *                         $param string $callid
	 *                         return true
	 */
	public function updateCallDetails($details)
	{
		\App\Db::getInstance()->createCommand()->update(self::MODULE_TABLE_NAME, $details, ['sourceuuid' => $this->get('sourceuuid')])->execute();

		return true;
	}

	/**
	 * To update Assigned to with user who answered the call.
	 *
	 * @param int $userId
	 */
	public function updateAssignedUser($userId)
	{
		\App\Db::getInstance()->createCommand()->update(self::ENTITY_TABLE_NAME, ['smownerid' => $userId], ['crmid' => $this->get('pbxmanagerid')])->execute();

		return true;
	}

	public static function getInstanceById($recordId, $module = null)
	{
		$record = new self();
		$rowData = (new App\Db\Query())->from(self::MODULE_TABLE_NAME)->where(['pbxmanagerid' => $recordId])->one();
		if ($rowData) {
			$record->setData($rowData);
		}

		return $record;
	}

	public static function getInstanceBySourceUUID($sourceuuid)
	{
		$record = new self();
		$rowData = (new App\Db\Query())->from(self::MODULE_TABLE_NAME)->where(['sourceuuid' => $sourceuuid])->one();
		if ($rowData) {
			$record->setData($rowData);
		}

		return $record;
	}

	/**
	 * Function to save/update contact/account/lead record in Phonelookup table on every save.
	 *
	 * @param string $fieldName
	 * @param array  $details
	 * @param bool   $new
	 *
	 * @return int
	 */
	public function receivePhoneLookUpRecord($fieldName, $details, $new)
	{
		$db = \App\Db::getInstance();
		$fnumber = preg_replace('/[-()\s+]/', '', $details[$fieldName]);
		$isExists = (new \App\Db\Query())
			->from(self::LOOKUP_TABLE_NAME)
			->where(['crmid' => $details['crmid'], 'setype' => $details['setype'], 'fieldname' => $fieldName])
			->exists();
		if ($isExists) {
			return $db->createCommand()->update(self::LOOKUP_TABLE_NAME, ['fnumber' => $fnumber, 'rnumber' => strrev($fnumber)], ['crmid' => $details['crmid'], 'setype' => $details['setype'], 'fieldname' => $fieldName])->execute();
		} else {
			return $db->createCommand()
				->insert(self::LOOKUP_TABLE_NAME, [
						'crmid' => $details['crmid'],
						'setype' => $details['setype'],
						'fnumber' => $fnumber,
						'rnumber' => strrev($fnumber),
						'fieldname' => $fieldName,
					])->execute();
		}
	}

	/**
	 * Function to delete contact/account/lead record in Phonelookup table on every delete.
	 *
	 * @param string $recordId
	 */
	public function deletePhoneLookUpRecord($recordId)
	{
		\App\Db::getInstance()->createCommand()->delete(self::LOOKUP_TABLE_NAME, ['crmid' => $recordId])->execute();
	}

	/**
	 * * Function to check the customer with number in phonelookup table.
	 *
	 * @param string $from
	 */
	public static function lookUpRelatedWithNumber($from)
	{
		$db = PearDatabase::getInstance();
		$fnumber = preg_replace('/[-()\s+]/', '', $from);
		$rnumber = strrev($fnumber);
		$row = (new App\Db\Query())->select(['crmid', 'fieldname'])
			->from(self::LOOKUP_TABLE_NAME)
			->where(['or', ['like', 'fnumber', "$fnumber%", false], ['like', 'rnumber', "$rnumber%", false]])
			->one();
		if ($row) {
			$crmid = $row['crmid'];
			$fieldname = $row['fieldname'];
			$contact = $db->pquery('SELECT setype FROM vtiger_crmentity WHERE crmid = ? && deleted=0', [$crmid]);
			if ($db->numRows($contact)) {
				$rowCrm = $db->getRow($contact);
				$data['id'] = $crmid;
				$data['name'] = \App\Record::getLabel($crmid);
				$data['setype'] = $rowCrm['setype'];
				$data['fieldname'] = $fieldname;

				return $data;
			} else {
				return;
			}
		}
	}

	/**
	 * Function to user details with number.
	 *
	 * @param string $number
	 */
	public static function getUserInfoWithNumber($number)
	{
		$db = PearDatabase::getInstance();
		if (empty($number)) {
			return false;
		}
		$query = self::buildSearchQueryWithUIType(11, $number, 'Users');
		$result = $db->pquery($query, []);
		if ($db->numRows($result) > 0) {
			$user['id'] = $db->queryResult($result, 0, 'id');
			$user['name'] = $db->queryResult($result, 0, 'name');
			$user['setype'] = 'Users';

			return $user;
		}
	}

	// Because, User is not related to crmentity
	public function buildSearchQueryWithUIType($uitype, $value, $module)
	{
		if (empty($value)) {
			return false;
		}

		$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);
		if ($cachedModuleFields === false) {
			vtlib\Deprecated::getColumnFields($module); // This API will initialize the cache as well
			// We will succeed now due to above function call
			$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);
		}

		$lookupcolumns = [];
		foreach ($cachedModuleFields as $fieldinfo) {
			if (in_array($fieldinfo['uitype'], [$uitype])) {
				$lookupcolumns[] = $fieldinfo['columnname'];
			}
		}

		$entityfields = \vtlib\Functions::getEntityModuleSQLColumnString($module);
		$querycolumnnames = implode(',', $lookupcolumns);
		$entitycolumnnames = $entityfields['fieldname'];

		$query = "select id as id, $querycolumnnames, $entitycolumnnames as name ";
		$query .= ' FROM vtiger_users';

		if (!empty($lookupcolumns)) {
			$query .= ' WHERE deleted=0 && ';
			$i = 0;
			$columnCount = count($lookupcolumns);
			foreach ($lookupcolumns as $columnname) {
				if (!empty($columnname)) {
					if ($i == 0 || $i == ($columnCount)) {
						$query .= sprintf("%s = '%s'", $columnname, $value);
					} else {
						$query .= sprintf(" || %s = '%s'", $columnname, $value);
					}
					++$i;
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
		$result = $db->pquery($query, []);
		$count = $db->numRows($result);
		for ($i = 0; $i < $count; ++$i) {
			$number = $db->queryResult($result, $i, 'phone_crm_extension');
			$userId = $db->queryResult($result, $i, 'id');
			if ($number) {
				$numbers[$userId] = $number;
			}
		}

		return $numbers;
	}
}
