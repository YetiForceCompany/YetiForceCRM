<?php

/**
 * Settings search SaveAjax action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Search_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('Save');
		$this->exposeMethod('UpdateLabels');
		$this->exposeMethod('SaveSequenceNumber');
	}

	public function Save(\App\Request $request)
	{
		$params = $request->get('params');
		Settings_Search_Module_Model::save($params);
		$message = 'LBL_SAVE_CHANGES_LABLE';
		if ($params['name'] === 'turn_off')
			$message = 'LBL_SAVE_CHANGES_SEARCHING';
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => \App\Language::translate($message, $request->getModule(false))
		));
		$response->emit();
	}

	public function UpdateLabels(\App\Request $request)
	{
		$params = $request->get('params');
		Settings_Search_Module_Model::updateLabels($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => \App\Language::translate('Update has been completed', $request->getModule(false))
		));
		$response->emit();
	}

	public function SaveSequenceNumber(\App\Request $request)
	{
		$updatedFieldsList = $request->get('updatedFields');

		//This will update the modules sequence 
		Settings_Search_Module_Model::updateSequenceNumber($updatedFieldsList);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
}
