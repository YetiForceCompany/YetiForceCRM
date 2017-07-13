<?php

/**
 * Vtiger Workflow action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Vtiger_Workflow_Action extends Vtiger_Action_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('execute');
	}

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function execute(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$ids = $request->get('ids');
		$user = $request->get('user');
		Vtiger_WorkflowTrigger_Model::execute($moduleName, $record, $ids, $user);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
