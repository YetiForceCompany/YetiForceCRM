<?php

/**
 * Settings dav SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Dav_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{
	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addKey');
		$this->exposeMethod('deleteKey');
	}

	/**
	 * Action to create key for user.
	 *
	 * @param \App\Request $request
	 */
	public function addKey(\App\Request $request)
	{
		$params = $request->get('params');
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Dav_Module_Model::getInstance($qualifiedModuleName);
		$result = $moduleModel->addKey($params);
		$success = true;
		$message = \App\Language::translate('LBL_SUCCESS_SAVE_KEY', $request->getModule(false));
		if ($result === 0) {
			$success = false;
			$message = \App\Language::translate('LBL_ERROR_SAVE_KEY', $request->getModule(false));
		} elseif ($result === 1) {
			$success = false;
			$message = \App\Language::translate('LBL_DUPLICATE_USER_SERVICES', $request->getModule(false));
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $success,
			'key' => $result,
			'message' => $message,
		]);
		$response->emit();
	}

	/**
	 * Action to remove key.
	 *
	 * @param \App\Request $request
	 */
	public function deleteKey(\App\Request $request)
	{
		$params = $request->get('params');
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Dav_Module_Model::getInstance($qualifiedModuleName);
		$moduleModel->deleteKey($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_KEY_HAS_BEEN_REMOVED', $request->getModule(false)),
		]);
		$response->emit();
	}
}
