<?php

/**
 * UpdateStatus SQuoteEnquiries Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SQuoteEnquiries_UpdateStatus_Action extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . print_r($request, true) . ') method ...');
		$moduleName = $request->getModule();
		$recordId = $request->get('id');
		$state = $request->get('state');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordModel->set('id', $recordId);
		$recordModel->set('squoteenquiries_status', $state);
		$recordModel->set('mode', 'edit');
		if (in_array($state, ['LBL_DISCARDED', 'LBL_ACCEPTED'])) {
			$currentTime = date('Y-m-d H:i:s');
			$responseTime = strtotime($currentTime) - strtotime($recordModel->get('createdtime'));
			$recordModel->set('response_time', $responseTime / 60 / 60);
		}
		$recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}
}
