<?php

/**
 * System warnings basic action class.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Vtiger_SystemWarnings_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('update');
		$this->exposeMethod('cancel');
	}

	/**
	 * Update ignore status.
	 *
	 * @param \App\Request $request
	 */
	public function update(App\Request $request)
	{
		$className = $request->get('id');
		if (!is_subclass_of($className, '\App\SystemWarnings\Template')) {
			$result = false;
		} else {
			$result = (new $className())->update($request->get('params'));
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Update ignore status.
	 *
	 * @param \App\Request $request
	 */
	public function cancel(App\Request $request)
	{
		App\Session::set('SystemWarnings', true);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
