<?php
/**
 * CardDAV Cron Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_CardDav_Cron class.
 */
class Contacts_CardDav_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		\App\Log::trace('Start cron CardDAV');
		$dav = new API_DAV_Model();
		$davUsers = API_DAV_Model::getAllUser(1);
		foreach (Users_Record_Model::getAll() as $id => $user) {
			if (isset($davUsers[$id])) {
				$user->set('david', $davUsers[$id]['david']);
				$user->set('addressbooksid', $davUsers[$id]['addressbooksid']);
				$user->set('groups', \App\User::getUserModel($id)->getGroups());
				$dav->davUsers[$id] = $user;
				\App\Log::trace(__METHOD__ . ' | User is active ' . $user->getName());
			} else { // User is inactive
				\App\Log::info(__METHOD__ . ' | User is inactive ' . $user->getName());
			}
		}
		$cardDav = new API_CardDAV_Model();
		$cardDav->davUsers = $dav->davUsers;
		$cardDav->cardDavCrm2Dav();
		if ($this->checkTimeout()) {
			return;
		}
		$cardDav->cardDav2Crm();
		\App\Log::trace('End cron CardDAV');
	}
}
