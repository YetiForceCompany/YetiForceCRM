<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Calendar_ActivityStateModal_View extends Vtiger_BasicModal_View
{

	function process(Vtiger_Request $request)
	{

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = $request->getModule();
		$id = $request->get('record');
		$recordInstance = Vtiger_Record_Model::getInstanceById($id, $moduleName);
		$permissionToSendEmail = vtlib_isModuleActive('OSSMail') && Users_Privileges_Model::isPermitted('OSSMail', 'compose');

		$viewer = $this->getViewer($request);
		$viewer->assign('PERMISSION_TO_SENDE_MAIL', $permissionToSendEmail);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordInstance);
		$viewer->assign('CURRENT_USER_MODEL', $currentUser);
		$viewer->assign('SCRIPTS', $this->getScripts($request));
		$viewer->view('ActivityStateModal.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getScripts(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = array(
			"modules.$moduleName.resources.ActivityStateModal",
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}
}
