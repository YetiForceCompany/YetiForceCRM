<?php

/**
 * OpenStreetMap CRMEntity Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap
{
	/**
	 * Handler.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if ($eventType === 'module.postinstall') {
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			App\EventHandler::registerHandler('EntityAfterSave', 'OpenStreetMap_OpenStreetMapHandler_Handler', 'Accounts,Leads,Partners,Vendors,Competition,Contacts', '', 3);
			\vtlib\Cron::register('LBL_UPDATER_COORDINATES', 'modules/OpenStreetMap/cron/UpdaterCoordinates.php', 60, 'OpenStreetMap', 1);
			\vtlib\Cron::register('LBL_UPDATER_RECORDS_COORDINATES', 'modules/OpenStreetMap/cron/UpdaterRecordsCoordinates.php', 300, 'OpenStreetMap', 1);
		} elseif ($eventType === 'module.disabled') {
			App\EventHandler::setInActive('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::getInstance('LBL_UPDATER_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
			\vtlib\Cron::getInstance('LBL_UPDATER_RECORDS_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
		} elseif ($eventType === 'module.enabled') {
			App\EventHandler::setActive('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::getInstance('LBL_UPDATER_RECORDS_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
		} elseif ($eventType === 'module.preuninstall') {
			App\EventHandler::deleteHandler('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::deregister('LBL_UPDATER_RECORDS_COORDINATES');
			\vtlib\Cron::deregister('LBL_UPDATER_COORDINATES');
		}
	}
}
