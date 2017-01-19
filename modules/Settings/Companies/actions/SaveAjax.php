<?php

/**
 * Companies SaveAjax action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Companies_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateCompany');
	}

	/**
	 * Function to save company info
	 * @param Vtiger_Request $request
	 * @return array
	 */
	public function updateCompany(Vtiger_Request $request)
	{
		$result = Settings_Companies_Record_Model::updateCompany($request);

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Validate Request
	 * @param Vtiger_Request $request
	 */
	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
