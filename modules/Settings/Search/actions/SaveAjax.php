<?php

/**
 * Settings search SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Search_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('updateLabels');
		$this->exposeMethod('saveSequenceNumber');
	}

	public function save(App\Request $request)
	{
		$params = $request->getArray('params', 'Alnum');
		$success = Settings_Search_Module_Model::save($params);
		$message = 'LBL_SAVE_CHANGES_LABLE';
		if ('turn_off' === $params['name']) {
			$message = 'LBL_SAVE_CHANGES_SEARCHING';
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $success,
			'message' => \App\Language::translate($message, $request->getModule(false)),
		]);
		$response->emit();
	}

	public function updateLabels(App\Request $request)
	{
		$params = $request->getArray('params', 'Integer');
		Settings_Search_Module_Model::updateLabels($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('Update has been completed', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function saveSequenceNumber(App\Request $request)
	{
		$updatedFieldsList = $request->getArray('updatedFields', 'Integer');
		//This will update the modules sequence
		Settings_Search_Module_Model::updateSequenceNumber($updatedFieldsList);
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
