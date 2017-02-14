<?php

/**
 *
 * @package YetiForce.views
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_ActivityStateModal_View extends Vtiger_BasicModal_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$id = $request->get('record');
		$recordInstance = Vtiger_Record_Model::getInstanceById($id, $moduleName);
		$permissionToSendEmail = \App\Module::isModuleActive('OSSMail') && Users_Privileges_Model::isPermitted('OSSMail');

		$viewer = $this->getViewer($request);
		$viewer->assign('PERMISSION_TO_SENDE_MAIL', $permissionToSendEmail);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordInstance);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SCRIPTS', $this->getScripts($request));
		$viewer->view('ActivityStateModal.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getScripts(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = array(
			"modules.$moduleName.resources.ActivityStateModal",
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}
}
