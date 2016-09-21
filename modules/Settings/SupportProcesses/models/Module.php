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

class Settings_SupportProcesses_Module_Model extends Settings_Vtiger_Module_Model
{

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	/**
	 * Gets ticket status for support processes
	 * @return - array of ticket status
	 */
	public static function getTicketStatus()
	{
		$log = LoggerManager::getInstance();
		$adb = PearDatabase::getInstance();
		$log->debug("Entering Settings_SupportProcesses_Module_Model::getTicketStatus() method ...");
		$sql = 'SELECT * FROM `vtiger_ticketstatus`;';
		$result = $adb->query($sql);
		$rowsNum = $adb->num_rows($result);

		for ($i = 0; $i < $rowsNum; $i++) {
			$return[$i]['id'] = $adb->query_result($result, $i, 'ticketstatus_id');
			$return[$i]['statusTranslate'] = vtranslate($adb->query_result($result, $i, 'ticketstatus'), 'HelpDesk');
			$return[$i]['status'] = $adb->query_result($result, $i, 'ticketstatus');
		}
		$log->debug("Exiting Settings_SupportProcesses_Module_Model::getTicketStatus() method ...");
		return $return;
	}

	protected static $ticketStatusNotModify;

	/**
	 * Gets ticket status for support processes from support_processes
	 * @return - array of ticket status
	 */
	public static function getTicketStatusNotModify()
	{
		if (self::$ticketStatusNotModify) {
			return self::$ticketStatusNotModify;
		}
		$log = LoggerManager::getInstance();
		$db = PearDatabase::getInstance();
		$log->debug('Entering Settings_SupportProcesses_Module_Model::getTicketStatusNotModify() method ...');
		$result = $db->query('SELECT ticket_status_indicate_closing FROM `vtiger_support_processes`');

		$return = [];
		$ticketStatus = $db->getSingleValue($result);
		if (!empty($ticketStatus)) {
			$return = explode(',', $ticketStatus);
		}
		self::$ticketStatusNotModify = $return;
		$log->debug('Exiting Settings_SupportProcesses_Module_Model::getTicketStatusNotModify() method ...');
		return array_flip($return);
	}

	/**
	 * Update ticket status for support processes from support_processes
	 * @return - array of ticket status
	 */
	public function updateTicketStatusNotModify($data)
	{
		$log = LoggerManager::getInstance();
		$adb = PearDatabase::getInstance();
		$log->debug("Entering Settings_SupportProcesses_Module_Model::updateTicketStatusNotModify() method ...");
		$deleteQuery = "UPDATE `vtiger_support_processes` SET `ticket_status_indicate_closing` = NULL WHERE `id` = 1";
		$adb->query($deleteQuery);
		if ('null' != $data['val']) {
			$insertQuery = "UPDATE `vtiger_support_processes` SET `ticket_status_indicate_closing` = ? WHERE `id` = 1";
			$data = implode(',', $data['val']);
			$adb->pquery($insertQuery, [$data]);
		}
		$log->debug("Exiting Settings_SupportProcesses_Module_Model::updateTicketStatusNotModify() method ...");
		return TRUE;
	}

	public function getAllTicketStatus()
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug("Entering Settings_SupportProcesses_Module_Model::getAllTicketStatus() method ...");
		$sql = 'SELECT `ticketstatus` FROM `vtiger_ticketstatus`';
		$result = $adb->query($sql);
		$rowsNum = $adb->num_rows($result);
		for ($i = 0; $i < $rowsNum; $i++) {
			$ticketStatus[] = $adb->query_result($result, $i, 'ticketstatus');
		}
		return $ticketStatus;
	}

	public static function getOpenTicketStatus()
	{
		$log = LoggerManager::getInstance();
		$getTicketStatusClosed = self::getTicketStatusNotModify();
		$log->debug("Entering Settings_SupportProcesses_Module_Model::getOpenTicketStatus() method ...");
		if (empty($getTicketStatusClosed)) {
			$result = FALSE;
		} else {
			$getAllTicketStatus = self::getAllTicketStatus();
			foreach ($getTicketStatusClosed as $key => $closedStatus) {
				foreach ($getAllTicketStatus as $key => $status) {
					if ($closedStatus == $status)
						unset($getAllTicketStatus[$key]);
				}
			}
			$result = $getAllTicketStatus;
		}
		return $result;
	}
}
