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
		\App\Log::trace("Entering Settings_SupportProcesses_Module_Model::getTicketStatus() method ...");
		$return = App\Fields\Picklist::getPickListValues('ticketstatus');
		\App\Log::trace("Exiting Settings_SupportProcesses_Module_Model::getTicketStatus() method ...");
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
		$ticketStatus = (new App\Db\Query())->select('ticket_status_indicate_closing')
			->from('vtiger_support_processes')
			->scalar();
		$return = [];
		if (!empty($ticketStatus)) {
			$return = explode(',', $ticketStatus);
		}
		self::$ticketStatusNotModify = $return;
		return $return;
	}

	/**
	 * Update ticket status for support processes from support_processes
	 * @return - array of ticket status
	 */
	public function updateTicketStatusNotModify($data)
	{
		\App\Log::trace("Entering Settings_SupportProcesses_Module_Model::updateTicketStatusNotModify() method ...");
		\App\Db::getInstance()->createCommand()->update('vtiger_support_processes', [
			'ticket_status_indicate_closing' => ''
			], ['id' => 1])->execute();
		if (!empty($data['val'])) {
			$data = implode(',', $data['val']);
			\App\Db::getInstance()->createCommand()->update('vtiger_support_processes', [
				'ticket_status_indicate_closing' => $data
				], ['id' => 1])->execute();
		}
		\App\Log::trace("Exiting Settings_SupportProcesses_Module_Model::updateTicketStatusNotModify() method ...");
		return true;
	}

	public static function getAllTicketStatus()
	{
		\App\Log::trace(__METHOD__);
		return App\Fields\Picklist::getPickListValues('ticketstatus');
	}

	public static function getOpenTicketStatus()
	{

		$getTicketStatusClosed = self::getTicketStatusNotModify();
		\App\Log::trace(__METHOD__);
		if (empty($getTicketStatusClosed)) {
			$result = false;
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
