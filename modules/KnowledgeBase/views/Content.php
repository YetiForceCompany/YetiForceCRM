<?php

/**
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_Content_View extends Vtiger_IndexAjax_View
{

	public function process(\App\Request $request)
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
