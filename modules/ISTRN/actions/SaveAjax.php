<?php

/**
 * SaveAjax ISTRN Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ISTRN_SaveAjax_Action extends Vtiger_SaveAjax_Action
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateStatus');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		parent::process($request);
	}

	public function updateStatus(Vtiger_Request $request)
	{
		$params = $request->get('param');
		$moduleName = $request->getModule();
		$recordId = $params['record'];
		$state = $params['state'];

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->set('id', $recordId);
		$recordModel->set('istrn_status', $state);
		$recordModel->set('mode', 'edit');
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
