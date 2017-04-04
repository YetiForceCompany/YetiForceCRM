<?php

/**
 * Quick detail modal view class
 * @package YetiForce.Modal
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_QuickDetailModal_View extends Vtiger_BasicModal_View
{

	/**
	 * Checking permissions
	 * @param Vtiger_Request $request
	 * @throws \Exception\AppException
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		if (!is_numeric($recordId)) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		$recordPermission = Users_Privileges_Model::isPermitted($request->getModule(), 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function getSize(Vtiger_Request $request)
	{
		return 'modalRightSiteBar';
	}

	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $moduleName);


		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->view('QuickDetailModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
