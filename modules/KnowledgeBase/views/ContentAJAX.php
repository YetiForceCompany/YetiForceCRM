<?php

/**
 * @package YetiForce.Views
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_ContentAJAX_View extends Vtiger_IndexAjax_View
{
	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		if (!empty($recordId)) {
			$previewContent = new KnowledgeBase_PreviewContent_View();
			$previewContent->process($request);
		} else {
			$moduleName = $request->getModule();
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$treeModel = KnowledgeBase_Tree_Model::getInstance($moduleModel);
			$headers = [];
			$documents = $treeModel->getLastDocuments($headers);
			$viewer = $this->getViewer($request);
			$viewer->assign('VIEW', $request->get('view'));
			$viewer->assign('DOCUMENTS', $documents);
			$viewer->assign('HEADERS', $headers);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->view('ContentsDefault.tpl', $moduleName);
		}
	}
}
