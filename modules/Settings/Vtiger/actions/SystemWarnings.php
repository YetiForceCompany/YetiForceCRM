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
		$this->exposeMethod('update');
		$this->exposeMethod('cancel');
	}

	/**
	 * Update ignore status
	 * @param Vtiger_Request $request
	 */
	public function update(Vtiger_Request $request)
	{
		$className = $request->get('id');
		if (!class_exists($className)) {
			$result = false;
		}
		$instace = new $className;
		$result = $instace->update($request->get('params'));

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Update ignore status
	 * @param Vtiger_Request $request
	 */
	public function cancel(Vtiger_Request $request)
	{
		Vtiger_Session::set('SystemWarnings', true);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
