<?php

/**
 * Create Key
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_POS_CreateKey_View extends Settings_Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function getSize()
	{
		return 'modal-lg';
	}

	public function process(Vtiger_Request $request)
	{
		parent::preProcess($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');
		$recordModel = Settings_POS_Record_Model::getInstanceById($recordId);
		$listUsers = Users_Record_Model::getAll();
		$listActions = Settings_POS_Module_Model::getListActions();

		$viewer = $this->getViewer($request);

		$viewer->assign('LIST_USERS', $listUsers);
		$viewer->assign('LIST_ACTIONS', $listActions);
		$viewer->assign('LIST_SERVERS', Settings_WebserviceApps_Module_Model::getActiveServers('POS'));
		$viewer->assign('RECORD_MODEL', $recordModel);

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('CreateKey.tpl', $qualifiedModuleName);
		parent::postProcess($request);
	}
}
