<?php

/**
 * Brute force save action class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author YetiForce.com
 */
class Settings_BruteForce_SaveAjax_Action extends Settings_Vtiger_Index_View
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveConfig');
		$this->exposeMethod('unBlock');
	}

	/**
	 * Function updates module configuration 
	 * @param Vtiger_Request $request
	 */
	public function saveConfig(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		$data = $request->get('param');
		Settings_BruteForce_Module_Model::updateConfig($data);

		$response = new Vtiger_Response();
		$response->setResult(['message' => vtranslate('LBL_SAVE_SUCCESS', $moduleName)]);
		$response->emit();
	}

	/**
	 * Function unblocks user
	 * @param Vtiger_Request $request
	 */
	public function unBlock(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		$id = $request->get('param');
		$status = Settings_BruteForce_Module_Model::unBlock($id);

		if (!$status) {
			$return = ['success' => false, 'message' => vtranslate('LBL_UNBLOCK_FAIL', $moduleName)];
		} else {
			$return = ['success' => true, 'message' => vtranslate('LBL_UNBLOCK_SUCCESS', $moduleName)];
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}
