<?php

/**
 * OpenStreetMap CRMEntity Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap
{

	/**
	 * Handler
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$db = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			$db->update('vtiger_tab', ['customized' => 0], 'name = ?', [$moduleName]);
			App\EventHandler::registerHandler('EntityAfterSave', 'OpenStreetMap_OpenStreetMapHandler_Handler', 'Accounts,Leads,Partners,Vendors,Competition,Contacts', '', 3);
			\vtlib\Cron::register('LBL_UPDATER_COORDINATES', 'modules/OpenStreetMap/cron/UpdaterCoordinates.php', 60, 'OpenStreetMap', 1);
			\vtlib\Cron::register('LBL_UPDATER_RECORDS_COORDINATES', 'modules/OpenStreetMap/cron/UpdaterRecordsCoordinates.php', 300, 'OpenStreetMap', 1);
		} else if ($eventType == 'module.disabled') {
			App\EventHandler::setInActive('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::getInstance('LBL_UPDATER_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
			\vtlib\Cron::getInstance('LBL_UPDATER_RECORDS_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
		} else if ($eventType == 'module.enabled') {
			App\EventHandler::setActive('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::getInstance('LBL_UPDATER_RECORDS_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
		} else if ($eventType == 'module.preuninstall') {
			App\EventHandler::deleteHandler('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::deregister('LBL_UPDATER_RECORDS_COORDINATES');
			\vtlib\Cron::deregister('LBL_UPDATER_COORDINATES');
		}
	}
}
