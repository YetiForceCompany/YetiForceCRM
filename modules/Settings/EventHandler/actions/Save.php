<?php

/**
 * Settings EventHandler save action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings EventHandler save action class.
 */
class Settings_EventHandler_Save_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('set');
	}

	/**
	 * Set EditViewPreSave event.
	 *
	 * @param App\Request $request
	 */
	public function set(App\Request $request): void
	{
		$className = $request->getByType('name', 'AlnumExtended');
		$eventName = $request->getByType('tab', 'AlnumExtended');
		if ($request->getBoolean('val')) {
			App\EventHandler::setActive($className, $eventName);
		} else {
			App\EventHandler::setInActive($className, $eventName);
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_CHANGES_SAVED')]);
		$response->emit();
	}
}
