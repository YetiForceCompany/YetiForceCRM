<?php

/**
 * List notifications
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_CreateNotification_View extends Settings_Vtiger_BasicModal_View
{

	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = false;
		if ($request->has('id')) {
			$recordModel = Settings_Notifications_Record_Model::getInstanceById($request->get('id'));
		} else {
			$recordModel = new Settings_Notifications_Record_Model();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->view('CreateNotification.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
