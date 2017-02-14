<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class RecycleBin_Pagination_View extends Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPagination');
	}

	public function getPagination(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');

		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if ($sortOrder == 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'glyphicon glyphicon-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'glyphicon glyphicon-chevron-up';
		}

		if (empty($pageNumber)) {
			$pageNumber = '1';
		}

		$moduleModel = RecycleBin_Module_Model::getInstance($moduleName);
		//If sourceModule is empty, pick the first module name from the list
		if (empty($sourceModule)) {
			foreach ($moduleModel->getAllModuleList() as $model) {
				$sourceModule = $model->get('name');
				break;
			}
		}
		$listViewModel = RecycleBin_ListView_Model::getInstance($moduleName, $sourceModule);

		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
		$linkModels = $moduleModel->getListViewMassActions($linkParams);

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (empty($orderBy) && empty($sortOrder)) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$orderBy = $moduleInstance->default_order_by;
			$sortOrder = $moduleInstance->default_sort_order;
		}
		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}

		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		$noOfEntries = count($this->listViewEntries);

		$viewer->assign('MODULE', $moduleName);

		$viewer->assign('LISTVIEW_LINKS', $moduleModel->getListViewLinks(false));
		$viewer->assign('LISTVIEW_MASSACTIONS', $linkModels);

		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pageNumber);

		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);

		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('MODULE_LIST', $moduleModel->getAllModuleList());
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('DELETED_RECORDS_TOTAL_COUNT', $moduleModel->getDeletedRecordsTotalCount());


		if (!$this->listViewCount) {
			$this->listViewCount = $listViewModel->getListViewCount();
		}
		$totalCount = $this->listViewCount;
		$pagingModel->set('totalCount', (int) $totalCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('IS_MODULE_DELETABLE', $listViewModel->getModule()->isPermitted('Delete'));
		echo $viewer->view('Pagination.tpl', $moduleName, true);
	}

}
