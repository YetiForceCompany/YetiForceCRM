<?php

/**
 * Settings calendar SaveAjax action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Calendar_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('UpdateModuleColor');
		$this->exposeMethod('UpdateModuleActiveType');
		$this->exposeMethod('UpdateCalendarConfig');
		$this->exposeMethod('updateNotWorkingDays');
		$this->exposeMethod('generateColor');
	}

	public function generateColor(\App\Request $request)
	{
		$params = $request->get('param');
		$color = Settings_Calendar_Module_Model::generateColor();
		$params['color'] = $color;
		if (isset($params['viewtypesid']) && $params['viewtypesid']) {
			Settings_Calendar_Module_Model::updateModuleColor($params);
		} else {
			Settings_Calendar_Module_Model::updateCalendarConfig($params);
		}
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_GENERATED_COLOR', $request->getModule(false))
		));
		$response->emit();
	}

	public function UpdateModuleColor(\App\Request $request)
	{
		$params = $request->get('params');
		Settings_Calendar_Module_Model::updateModuleColor($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		));
		$response->emit();
	}

	public function UpdateModuleActiveType(\App\Request $request)
	{
		$params = $request->get('params');
		Settings_Calendar_Module_Model::updateModuleActiveType($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_ACTIVE_TYPE', $request->getModule(false))
		));
		$response->emit();
	}

	public function UpdateCalendarConfig(\App\Request $request)
	{
		$params = $request->get('params');
		Settings_Calendar_Module_Model::updateCalendarConfig($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_CHANGES', $request->getModule(false))
		));
		$response->emit();
	}

	public function updateNotWorkingDays(\App\Request $request)
	{
		$params = $request->get('param');
		Settings_Calendar_Module_Model::updateNotWorkingDays($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_ACTIVE_TYPE', $request->getModule(false))
		));
		$response->emit();
	}
}
