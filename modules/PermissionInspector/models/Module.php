<?php

/**
 * PermissionInspector class module model.
 *
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PermissionInspector_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Get users permissions.
	 *
	 * @return array
	 */
	public function getUsersPermission()
	{
		$list = [];
		$fieldOwner = \App\Fields\Owner::getInstance($this->get('sourceModule'));
		foreach ($fieldOwner->getAccessibleUsers() as $userId => $userName) {
			$list[$userId] = [
				'userName' => $userName,
				'privileges' => $this->getPrivileges($userId),
				'watchdog' => $this->getWatchdog($userId),
			];
		}
		return $list;
	}

	/**
	 * Get privileges by user id.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public function getPrivileges($userId)
	{
		$recordId = false;
		if (!empty($this->get('sourceRecord'))) {
			$recordId = $this->get('sourceRecord');
		}
		$userModel = \App\User::getUserModel($userId);
		$profileNames = [];
		if ($userModel && ($profiles = $userModel->getProfiles())) {
			foreach ($profiles as $profileId) {
				$profile = Settings_Profiles_Record_Model::getInstanceById($profileId);
				$profileNames[] = \App\Language::translate($profile->getName(), 'Settings::Profiles');
			}
		}
		$permissions = [];
		foreach ($this->get('actions') as $action) {
			$permissions[$action] = $this->getParameters([
				'isPermitted' => \App\Privilege::isPermitted($this->get('sourceModule'), $action, $recordId, $userId),
				'accessLog' => \App\Privilege::$isPermittedLevel,
				'profiles' => implode(',', $profileNames),
			]);
		}
		return $permissions;
	}

	/**
	 * Get additional parameters.
	 *
	 * @param array $privileges
	 *
	 * @return array
	 */
	public function getParameters($privileges)
	{
		switch ($privileges['accessLog']) {
			case 'SEC_RECORD_OWNER_CURRENT_GROUP':
				$metadata = \vtlib\Functions::getCRMRecordMetadata($this->get('sourceRecord'));
				$privileges['param'] = \App\Language::translate(\App\Fields\Owner::getLabel($metadata['smownerid']));
				break;
			case 'SEC_RECORD_OWNER_CURRENT_USER':
				$metadata = \vtlib\Functions::getCRMRecordMetadata($this->get('sourceRecord'));
				$privileges['param'] = \App\Language::translate(\App\Fields\Owner::getLabel($metadata['smownerid']));
				break;
			default:
				break;
		}
		return $privileges;
	}

	/**
	 * Get watchdog by user id.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public function getWatchdog($userId)
	{
		if (empty($this->get('sourceRecord'))) {
			$watchdog = Vtiger_Watchdog_Model::getInstance($this->get('sourceModule'), $userId);
			$watchdog->remove('isWatchingModule');
			$active = $watchdog->isWatchingModule();
		} else {
			$watchdog = Vtiger_Watchdog_Model::getInstanceById($this->get('sourceRecord'), $this->get('sourceModule'), $userId);
			$watchdog->remove('isWatchingRecord');
			$watchdog->remove('isWatchingModule');
			$active = $watchdog->isWatchingRecord();
		}
		return ['active' => $active];
	}
}
