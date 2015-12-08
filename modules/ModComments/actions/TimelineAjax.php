<?php

class ModComments_TimelineAjax_Action extends Vtiger_Action_Controller{
	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'TimelineAjax', $recordId);
		if (!$recordPermission) {
			throw new NoPermittedToRecordException('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}
	function process(Vtiger_Request $request)
	{
		$parentRecordId = $request->get('record');
		$values = ModComments_Record_Model::getAllCommentsJSON($parentRecordId);
		$response = new Vtiger_Response();
		$response->setResult($values);
		$response->emit();
	}
}

