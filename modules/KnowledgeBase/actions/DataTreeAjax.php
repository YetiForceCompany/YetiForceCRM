<?php

/**
 * Action to get data of tree
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_DataTreeAjax_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);
		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeModel = KnowledgeBase_Tree_Model::getInstance($moduleModel);
		$allFolders = $treeModel->getFolders();
		$documents = $treeModel->getDocuments();
		if (!is_array($documents)) {
			$documents = [];
		}
		$dataOfTree = array_merge($allFolders, $documents);
		$response = new Vtiger_Response();
		$response->setResult($dataOfTree);
		$response->emit();
	}
}
