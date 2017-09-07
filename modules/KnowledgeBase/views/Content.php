<?php

/**
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_Content_View extends Vtiger_IndexAjax_View
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		$recordId = $request->getInteger('record');
		if ($recordId && !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
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
			$viewer->assign('VIEW', $request->getByType('view', 1));
			$viewer->assign('ENTRIES', $listEntries);
			$viewer->assign('HEADERS', $headers);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->view('ContentsDefault.tpl', $moduleName);
		}
	}
}
