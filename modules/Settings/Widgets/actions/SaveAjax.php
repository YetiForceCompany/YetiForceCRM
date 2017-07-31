<?php

/**
 * Settings widgets SaveAjax action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Widgets_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveWidget');
		$this->exposeMethod('removeWidget');
		$this->exposeMethod('updateSequence');
	}

	public function saveWidget(\App\Request $request)
	{
		$params = $request->get('params');
		Settings_Widgets_Module_Model::saveWidget($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => 1,
			'message' => \App\Language::translate('Saved changes', $request->getModule(false))
		));
		$response->emit();
	}

	public function removeWidget(\App\Request $request)
	{
		$params = $request->get('params');
		Settings_Widgets_Module_Model::removeWidget($params['wid']);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => 1,
			'message' => \App\Language::translate('Removed widget', $request->getModule(false))
		));
		$response->emit();
	}

	public function updateSequence(\App\Request $request)
	{
		$params = $request->get('params');
		Settings_Widgets_Module_Model::updateSequence($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => 1,
			'message' => \App\Language::translate('Update has been completed', $request->getModule(false))
		));
		$response->emit();
	}
}
