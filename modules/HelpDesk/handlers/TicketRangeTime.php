<?php

/**
 * HelpDesk Handler Class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class HelpDesk_TicketRangeTime_Handler
{

	/**
	 * EntityAfterLink handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		if (in_array($params['destinationModule'], ['Calendar', 'Events', 'Activity', 'ModComments'])) {
			$recordModel = Vtiger_Record_Model::getInstanceById($params['destinationRecordId'], $params['destinationModule']);
			HelpDesk_Record_Model::updateTicketRangeTimeField($recordModel, true);
		}
	}

	/**
	 * EntityAfterSave handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		\App\Db::getInstance()->createCommand()->update('vtiger_troubletickets', ['from_portal' => 0], ['ticketid' => $recordModel->getId()])->execute();
		HelpDesk_Record_Model::updateTicketRangeTimeField($recordModel);
	}
}
