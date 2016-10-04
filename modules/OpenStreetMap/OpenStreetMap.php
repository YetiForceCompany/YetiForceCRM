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
		if ($eventType == 'module.postinstall') {
			$db = PearDatabase::getInstance();
			$db->update('vtiger_tab', ['customized' => 0], 'name = ?', [$moduleName]);
			$eventsManager = new \VTEventsManager($db);
			$eventsManager->registerHandler('vtiger.entity.aftersave.final', 'modules/OpenStreetMap/handlers/OpenStreetMapHandler.php', 'OpenStreetMapHandler', "moduleName in ['Accounts', 'Leads', 'Partners', 'Vendors', 'Competition', 'Contacts']"
			);
			\vtlib\Cron::register('UpdaterCoordinates', 'modules/OpenStreetMap/cron/UpdaterCoordinates.php', 60, 'OpenStreetMap', 1);
		} else if ($eventType == 'module.disabled') {
			$db->update('vtiger_eventhandlers', ['is_active' => 0], 'handler_class = ?', ['OpenStreetMapHandler']);
		} else if ($eventType == 'module.enabled') {
			$db->update('vtiger_eventhandlers', ['is_active' => 1], 'handler_class = ?', ['OpenStreetMapHandler']);
		} else if ($eventType == 'module.preuninstall') {
			$db->delete('vtiger_eventhandlers', 'handler_class = ?', ['OpenStreetMapHandler']);
		}
	}
}
