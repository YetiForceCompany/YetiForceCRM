<?php

/**
 * Settings SupportProcesses module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_SupportProcesses_Module_Model extends Settings_Vtiger_Module_Model
{
	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Gets ticket status for support processes.
	 *
	 * @return array - array of ticket status
	 */
	public static function getTicketStatus()
	{
		\App\Log::trace('Entering Settings_SupportProcesses_Module_Model::getTicketStatus() method ...');
		$return = App\Fields\Picklist::getValuesName('ticketstatus');
		\App\Log::trace('Exiting Settings_SupportProcesses_Module_Model::getTicketStatus() method ...');

		return $return;
	}

	protected static $ticketStatusNotModify;

	/**
	 * Gets ticket status for support processes from support_processes.
	 *
	 * @return array - array of ticket status
	 */
	public static function getTicketStatusNotModify()
	{
		if (self::$ticketStatusNotModify) {
			return self::$ticketStatusNotModify;
		}
		$ticketStatus = (new App\Db\Query())->select(['ticket_status_indicate_closing'])
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
	 * Update ticket status for support processes from support_processes.
	 *
	 * @param mixed $data
	 *
	 * @return array - array of ticket status
	 */
	public function updateTicketStatusNotModify($data)
	{
		\App\Log::trace('Entering Settings_SupportProcesses_Module_Model::updateTicketStatusNotModify() method ...');
		\App\Db::getInstance()->createCommand()->update('vtiger_support_processes', [
			'ticket_status_indicate_closing' => '',
		], ['id' => 1])->execute();
		if (!empty($data['val'])) {
			$data = implode(',', \is_array($data['val']) ? $data['val'] : [$data['val']]);
			\App\Db::getInstance()->createCommand()->update('vtiger_support_processes', [
				'ticket_status_indicate_closing' => $data,
			], ['id' => 1])->execute();
		}
		\App\Log::trace('Exiting Settings_SupportProcesses_Module_Model::updateTicketStatusNotModify() method ...');

		return true;
	}

	public static function getAllTicketStatus()
	{
		\App\Log::trace(__METHOD__);

		return App\Fields\Picklist::getValuesName('ticketstatus');
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
					if ($closedStatus == $status) {
						unset($getAllTicketStatus[$key]);
					}
				}
			}
			$result = $getAllTicketStatus;
		}
		return $result;
	}
}
