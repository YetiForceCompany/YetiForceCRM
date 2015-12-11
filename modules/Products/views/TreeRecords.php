<?php

/**
 * Products TreeView View Class
 * @package YetiForce.TreeView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Products_TreeRecords_View extends Vtiger_TreeRecords_View
{

	function process(Vtiger_Request $request)
	{
		$branches = $request->get('branches');
		$filter = $request->get('filter');
		if (empty($branches)) {
			return;
		}
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$baseModuleName = 'Accounts';

		$multiReferenceFirld = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($baseModuleName, $moduleName);
		if (count($multiReferenceFirld) === 0) {
			return;
		}
		$multiReferenceFirld = reset($multiReferenceFirld);
		$searchParams = [
			['columns' => [[
					'columnname' => $multiReferenceFirld['tablename'] . ':' . $multiReferenceFirld['columnname'] . ':' . $multiReferenceFirld['fieldname'],
					'value' => implode(',', $branches),
					'column_condition' => '',
					'comparator' => 'c',
					]]],
		];

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$listViewModel = Vtiger_ListView_Model::getInstance($baseModuleName, $filter);
		$listViewModel->set('search_key', $multiReferenceFirld['fieldname']);
		$listViewModel->set('search_params', $searchParams);

		$listEntries = $listViewModel->getListViewEntries($pagingModel, true);
		if (count($listEntries) === 0) {
			return;
		}
		$listHeaders = $listViewModel->getListViewHeaders();

		$viewer->assign('ENTRIES', $listEntries);
		$viewer->assign('HEADERS', $listHeaders);
		$viewer->assign('MODULE', $baseModuleName);
		$viewer->view('TreeRecords.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$baseModuleName = 'Accounts';

		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($baseModuleName));
		$viewer->view('TreeRecordsPostProcess.tpl', $moduleName);

		parent::postProcess($request);
	}
}
