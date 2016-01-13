<?php
/**
 * 
 * @package YetiForce.Ajax
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class ModComments_TimelineAjax_Action extends Vtiger_Action_Controller{
	function checkPermission(Vtiger_Request $request)
	{
		$srcModuleName = $request->get('srcModule');
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($srcModuleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new NoPermittedToRecordException('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}
	function process(Vtiger_Request $request)
	{
		$parentRecordId = $request->get('record');
		$values = ModComments_Record_Model::getAllCommentsForTimeline($parentRecordId);
		$response = new Vtiger_Response();
		$response->setResult($values);
		$response->emit();
	}
}

