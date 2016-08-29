<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_PublicHoliday_Module_Model extends Settings_Vtiger_Module_Model
{

	/**
	 * Gets list of holidays
	 * @param <String> $dateStart - beginning date
	 * @param <String> $dateTo - ending date
	 * @return - array of holidays success, false on failure
	 */
	public static function getHolidays($date)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_PublicHoliday_Module_Model::getHolidays(" . print_r($date, true) . ") method ...");

		$db = PearDatabase::getInstance();
		$sql = 'SELECT `publicholidayid`, `holidaydate`, `holidayname`, `holidaytype` FROM `vtiger_publicholiday`';
		$params = array();
		$date[0] = DateTimeField::convertToDBFormat($date[0]);
		$date[1] = DateTimeField::convertToDBFormat($date[1]);
		if (is_array($date)) {
			$sql .= ' WHERE holidaydate BETWEEN ? AND ?';
			$params[] = $date[0];
			$params[] = $date[1];
		}
		$sql .= ' ORDER BY `holidaydate` ASC;';

		$result = $db->pquery($sql, $params);
		$num = $db->num_rows($result);

		$holidays = array();
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++) {
				$id = $db->query_result($result, $i, 'publicholidayid');
				$date = $db->query_result($result, $i, 'holidaydate');
				$name = $db->query_result($result, $i, 'holidayname');
				$type = $db->query_result($result, $i, 'holidaytype');
				$holidays[$id]['id'] = $id;
				$holidays[$id]['date'] = $date;
				$holidays[$id]['name'] = $name;
				$holidays[$id]['type'] = $type;
				$holidays[$id]['day'] = vtranslate(date('l', strtotime($date)), 'PublicHoliday');
			}
		}

		$log->debug("Exiting Settings_PublicHoliday_Module_Model::getHolidays() method ...");
		return $holidays;
	}

	/**
	 * Delete holiday
	 * @param <Int> $id - id of holiday
	 * @return - true on success, false on failure
	 */
	public static function delete($id)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_PublicHoliday_Module_Model::delete(" . $id . ") method ...");

		$db = PearDatabase::getInstance();
		$sql = 'DELETE FROM `vtiger_publicholiday` WHERE `publicholidayid` = ? LIMIT 1;';
		$params = array($id);

		$result = $db->pquery($sql, $params);
		$deleted = $db->getAffectedRowCount($result);

		$log->debug("Exiting Settings_PublicHoliday_Module_Model::delete() method ...");

		if ($deleted == 1)
			return true;
		else
			return false;
	}

	/**
	 * Add new holiday
	 * @param <String> $date - date of the holiday
	 * @param <String> $name - name of the holiday
	 * @param <String> $type - type of the holiday
	 * @return - true on success, false on failure
	 */
	public static function save($date, $name, $type)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_PublicHoliday_Module_Model::save(" . $date . ', ' . $name . ', ' . $type . ") method ...");

		$db = PearDatabase::getInstance();
		$sql = 'INSERT INTO `vtiger_publicholiday` (`holidaydate`, `holidayname`, `holidaytype`) VALUES (?, ?, ?);';
		$params = array($date, $name, $type);

		$result = $db->pquery($sql, $params);
		$saved = $db->getAffectedRowCount($result);

		$log->debug("Exiting Settings_PublicHoliday_Module_Model::save() method ...");

		if ($saved == 1)
			return true;
		else
			return false;
	}

	/**
	 * Edit holiday
	 * @param <Int> $id - id of the holiday
	 * @param <String> $date - date of the holiday
	 * @param <String> $name - name of the holiday
	 * @param <String> $type - name of the holiday
	 * @return - true on success, false on failure
	 */
	public static function edit($id, $date, $name, $type)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_PublicHoliday_Module_Model::edit(" . $id . ', ' . $date . ', ' . $name . ', ' . $type . ") method ...");

		$db = PearDatabase::getInstance();
		$sql = 'UPDATE `vtiger_publicholiday` SET `holidaydate` = ?, `holidayname` = ?, `holidaytype` = ? WHERE `publicholidayid` = ? LIMIT 1;';
		$params = array($date, $name, $type, $id);

		$result = $db->pquery($sql, $params);
		$saved = $db->getAffectedRowCount($result);

		$log->debug("Exiting Settings_PublicHoliday_Module_Model::edit() method ...");

		if ($saved == 1)
			return true;
		else
			return false;
	}

	/**
	 * Check if it is public holiday
	 * @param <String> $date - date to be checked
	 * @return - true if public holiday exists, false on failure
	 */
	public static function checkIfHoliday($date)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_PublicHoliday_Module_Model::checkIfHoliday(" . $date . ") method ...");

		$db = PearDatabase::getInstance();
		$sql = 'SELECT COUNT(1) as num FROM `vtiger_publicholiday` WHERE `holidaydate` = ?;';
		$params = array($date);

		$result = $db->pquery($sql, $params);
		$num = $db->query_result($result, 0, 'num');

		$log->debug("Exiting Settings_PublicHoliday_Module_Model::checkIfHoliday() method ...");

		if ($num > 0)
			return true;

		return false;
	}

	/**
	 * @param <array> $date - start and end date to get holidays
	 * @return - holidays count group by type if exist or false
	 */
	public static function getHolidayGroupType($date = FALSE)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_PublicHoliday_Module_Model::getHolidayGroupType method ...");
		$db = PearDatabase::getInstance();
		$params = [];
		$sql = 'SELECT COUNT(`publicholidayid`) AS count, `holidaytype` FROM `vtiger_publicholiday` ';
		if ($date) {
			$sql .= ' WHERE holidaydate BETWEEN ? AND ?';
			$params[] = $date[0];
			$params[] = $date[1];
		}
		$sql .= ' GROUP BY `holidaytype`';
		$result = $db->pquery($sql, $params);
		$numRows = $db->num_rows($result);
		if (0 == $numRows)
			$return = FALSE;
		else {
			for ($i = 0; $i < $numRows; $i++) {
				$count = $db->query_result($result, $i, 'count');
				$type = $db->query_result($result, $i, 'holidaytype');
				$return[$type] = $count;
			}
		}
		$log->debug("Exiting Settings_PublicHoliday_Module_Model::getHolidayGroupType() method ...");
		return $return;
	}
}
