<?php

/**
 * System warnings basic action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
	 * @param \App\Request $request
	 */
	public function update(\App\Request $request)
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
	 * @param \App\Request $request
	 */
	public function cancel(\App\Request $request)
	{
		App\Session::set('SystemWarnings', true);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
