<?php

/**
 * Settings calendar SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Calendar_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateCalendarConfig');
		$this->exposeMethod('updateNotWorkingDays');
	}

	/**
	 * Action to update calendar configuration.
	 *
	 * @param \App\Request $request
	 */
	public function updateCalendarConfig(App\Request $request)
	{
		Settings_Calendar_Module_Model::updateCalendarConfig($request->getMultiDimensionArray('params', [
			'color' => 'Integer',
			'id' => 'Standard'
		]));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_CHANGES', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Action to change not working days.
	 *
	 * @param \App\Request $request
	 */
	public function updateNotWorkingDays(App\Request $request)
	{
		Settings_Calendar_Module_Model::updateNotWorkingDays($request->getMultiDimensionArray('param', [
			'param' => 'Standard',
			'val' => ['Integer']
		]));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_ACTIVE_TYPE', $request->getModule(false)),
		]);
		$response->emit();
	}
}
