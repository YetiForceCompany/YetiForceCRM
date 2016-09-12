<?php

/**
 * System warnings basic action class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Vtiger_SystemWarnings_Action extends Settings_Vtiger_Basic_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setIgnore');
	}

	/**
	 * Update ignore status
	 * @param Vtiger_Request $request
	 */
	public function setIgnore(Vtiger_Request $request)
	{
		$id = $request->get('id');
		$status = $request->get('status');

		$result = \includes\SystemWarnings::setIgnored($id, $status);

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
