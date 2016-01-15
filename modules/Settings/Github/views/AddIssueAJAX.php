<?php

/**
 * Show modal to add issue 
 * @package YetiForce.Github
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_AddIssueAJAX_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		true;
	}


	public function process(Vtiger_Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$clientModel = Settings_Github_Client_Model::getInstance();
		$clientModel->authorization();
		$viewer->assign('GITHUB_CLIENT_MODEL', $clientModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('AddIssueModal.tpl', $qualifiedModule);
	}
}
