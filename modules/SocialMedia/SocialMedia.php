<?php

/**
 * SocialMedia CRMEntity Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class SocialMedia
{
	/**
	 * Handler.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			\vtlib\Cron::register('LBL_ARCHIVE_OLD_RECORDS', "modules/$moduleName/cron/ArchiveOldRecords.php", 3600 * 24, $moduleName, 1);
		} elseif ($eventType === 'module.disabled') {
			\vtlib\Cron::getInstance('LBL_ARCHIVE_OLD_RECORDS')->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
		} elseif ($eventType === 'module.enabled') {
			\vtlib\Cron::getInstance('LBL_ARCHIVE_OLD_RECORDS')->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
		} elseif ($eventType === 'module.preuninstall') {
			\vtlib\Cron::deregister('LBL_ARCHIVE_OLD_RECORDS');
		}
	}
}
