<?php

/**
 * @package YetiForce.Views
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_Content_View extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		if (!empty($recordId)) {
			$previewContent = new KnowledgeBase_PreviewContent_View();
			$previewContent->process($request);
		} else {
			$moduleName = $request->getModule();
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('limit', 'no_limit');
			$listViewModel = Vtiger_ListView_Model::getInstance($moduleName);
			$listEntries = $listViewModel->getListViewEntries($pagingModel);
			$headers = $listViewModel->getListViewHeaders();

			$viewer = $this->getViewer($request);
			$viewer->assign('VIEW', $request->get('view'));
			$viewer->assign('ENTRIES', $listEntries);
			$viewer->assign('HEADERS', $headers);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->view('ContentsDefault.tpl', $moduleName);
		}
	}
}
