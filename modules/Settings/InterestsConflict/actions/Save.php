<?php

/**
 * Settings conflict of interests save action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings conflict of interests save action class.
 */
class Settings_InterestsConflict_Save_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod, \App\Controller\Traits\SettingsPermission;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('config');
		$this->exposeMethod('modules');
	}

	/**
	 * Save config data.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function config(App\Request $request): void
	{
		$confirmationTimeInterval = $request->getInteger('confirmationTimeInterval');
		if (($list = $request->getByType('confirmationTimeIntervalList', 'Standard')) && '-' !== $list) {
			$confirmationTimeInterval .= ' ' . $list;
		}
		$configFile = new \App\ConfigFile('component', 'InterestsConflict');
		$configFile->set('isActive', $request->getBoolean('isActive'));
		$configFile->set('sendMailAccessRequest', $request->getBoolean('sendMailAccessRequest'));
		$configFile->set('sendMailAccessResponse', $request->getBoolean('sendMailAccessResponse'));
		$configFile->set('notificationsEmails', $request->getByType('notificationsEmails', 'Text'));
		$configFile->set('confirmationTimeInterval', $confirmationTimeInterval);
		$configFile->set('unlockUsersAccess', $request->getArray('unlockUsersAccess', 'Integer'));
		$configFile->set('confirmUsersAccess', $request->getArray('confirmUsersAccess', 'Integer'));
		$configFile->create();
		$response = new Vtiger_Response();
		$response->emit();
	}

	/**
	 * Save modules configuration.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function modules(App\Request $request): void
	{
		$config = [];
		foreach ($request->getArray('modules') as $key => $module) {
			$data = [
				'key' => $key
			];
			foreach (\App\Json::decode($module) as $jsonKey => $jsonValue) {
				$data[\App\Purifier::purifyByType($jsonKey, 'Alnum')] = \App\Purifier::purifyByType($jsonValue, 'AlnumExtended');
			}
			$config[$data['related']][] = $data;
		}
		$configFile = new \App\ConfigFile('component', 'InterestsConflict');
		$configFile->set('modules', $config);
		$configFile->create();
		$response = new Vtiger_Response();
		$response->emit();
	}
}
