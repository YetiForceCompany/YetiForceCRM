<?php

/**
 * OpenStreetMap CRMEntity Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if ('module.postinstall' === $eventType) {
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			App\EventHandler::registerHandler('EntityAfterSave', 'OpenStreetMap_OpenStreetMapHandler_Handler', 'Accounts,Leads,Partners,Vendors,Competition,Contacts', '', 3);
			\vtlib\Cron::register('LBL_UPDATER_COORDINATES', 'OpenStreetMap_UpdaterCoordinates_Cron', 60, 'OpenStreetMap', 1);
			\vtlib\Cron::register('LBL_UPDATER_RECORDS_COORDINATES', 'OpenStreetMap_UpdaterCoordinates_Cron', 300, 'OpenStreetMap', 1);
		} elseif ('module.disabled' === $eventType) {
			App\EventHandler::setInActive('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::getInstance('LBL_UPDATER_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
			\vtlib\Cron::getInstance('LBL_UPDATER_RECORDS_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
		} elseif ('module.enabled' === $eventType) {
			App\EventHandler::setActive('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::getInstance('LBL_UPDATER_RECORDS_COORDINATES')->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
		} elseif ('module.preuninstall' === $eventType) {
			App\EventHandler::deleteHandler('OpenStreetMap_OpenStreetMapHandler_Handler');
			\vtlib\Cron::deregister('LBL_UPDATER_RECORDS_COORDINATES');
			\vtlib\Cron::deregister('LBL_UPDATER_COORDINATES');
		}
	}
}
