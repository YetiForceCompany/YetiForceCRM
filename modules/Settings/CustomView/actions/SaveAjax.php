<?php

/**
 * CustomView save class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_CustomView_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('Delete');
		$this->exposeMethod('UpdateField');
	}

	public function Delete(Vtiger_Request $request)
	{
		$params = $request->get('params');
		Settings_CustomView_Module_Model::Delete($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => vtranslate('Delete CustomView', $request->getModule(false))
		));
		$response->emit();
	}

	public function UpdateField(Vtiger_Request $request)
	{
		$params = $request->get('params');
		Settings_CustomView_Module_Model::UpdateField($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => vtranslate('Saving CustomView', $request->getModule(false))
		));
		$response->emit();
	}
}
