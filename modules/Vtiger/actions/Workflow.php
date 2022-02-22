<?php

/**
 * Vtiger Workflow action class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Workflow_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('execute');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)
		|| (!$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule()))
		|| !$recordModel->isPermitted('WorkflowTrigger')
		|| !$recordModel->isPermitted('DetailView')
		|| (!$recordModel->isPermitted('EditView') || ($recordModel->isPermitted('EditView') && !$recordModel->isPermitted('WorkflowTriggerWhenRecordIsBlocked') && !$recordModel->isBlocked()))) {
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
		$tasks = $request->getArray('tasks', 'Integer');
		\Vtiger_WorkflowTrigger_Model::execute($moduleName, $record, $user, $tasks);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
