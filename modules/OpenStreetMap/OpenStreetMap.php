<?php

/**
 * OpenStreetMap CRMEntity Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap
{

	public function vtlib_handler($moduleName, $eventType)
	{
		$db = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			$db->update('vtiger_tab', ['customized' => 0], 'name = ?', [$moduleName]);
			App\EventHandler::registerHandler('EntityAfterSave', 'OpenStreetMap_OpenStreetMapHandler_Handler', 'Accounts,Leads,Partners,Vendors,Competition,Contacts', '', 3);
			\vtlib\Cron::register('UpdaterCoordinates', 'modules/OpenStreetMap/cron/UpdaterCoordinates.php', 60, 'OpenStreetMap', 1);
			\vtlib\Cron::register('UpdaterRecordsCoordinates', 'modules/OpenStreetMap/cron/UpdaterRecordsCoordinates.php', 300, 'OpenStreetMap', 1);
		} else if ($eventType == 'module.disabled') {
			App\EventHandler::setInActive('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::getInstance('UpdaterCoordinates')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
			\vtlib\Cron::getInstance('UpdaterRecordsCoordinates')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
		} else if ($eventType == 'module.enabled') {
			App\EventHandler::setActive('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::getInstance('UpdaterRecordsCoordinates')->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
		} else if ($eventType == 'module.preuninstall') {
			App\EventHandler::deleteHandler('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::deregister('UpdaterRecordsCoordinates');
			\vtlib\Cron::deregister('UpdaterCoordinates');
		}
	}
}
