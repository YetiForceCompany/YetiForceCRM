<?php

/**
 * Vtiger Workflow action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Workflow_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('execute');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Execute workflow.
	 *
	 * @param App\Request $request
	 */
	public function execute(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$user = $request->getInteger('user');
		$tasks = $request->getMultiDimensionArray('tasks', ['Integer' => ['Integer']]);
		\Vtiger_WorkflowTrigger_Model::execute($moduleName, $record, $user, $tasks);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
