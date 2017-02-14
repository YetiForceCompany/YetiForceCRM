<?php

/**
 * Products TreeView View Class
 * @package YetiForce.TreeView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Products_TreeRecords_View extends Vtiger_TreeRecords_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTABLE_CATEGORY', AppConfig::relation('SELECTABLE_CATEGORY') ? 1 : 0);
	}

	public function process(Vtiger_Request $request)
	{
		$branches = $request->get('branches');
		$filter = $request->get('filter');
		$category = $request->get('category');
		if (empty($branches) && empty($category)) {
			return;
		}
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$baseModuleName = 'Accounts';

		$multiReferenceFirld = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($baseModuleName, $moduleName);
		$multiReferenceFirld = reset($multiReferenceFirld);
		if (count($multiReferenceFirld) === 0) {
			return;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$listViewModel = Vtiger_ListView_Model::getInstance($baseModuleName, $filter);
		$queryGenerator = $listViewModel->get('query_generator');
		if (!empty($branches)) {
			$queryGenerator->addCondition($multiReferenceFirld['columnname'], implode(',', $branches), 'c');
		}
		if (!empty($category)) {
			$query = (new \App\Db\Query())
				->select(['crmid'])
				->from('u_#__crmentity_rel_tree')
				->where(['module' => App\Module::getModuleId($baseModuleName), 'relmodule' => App\Module::getModuleId($moduleName), 'tree' => $category]);
			$queryGenerator->addNativeCondition(['in', 'crmid', $query], false);
		}
		$listViewModel->set('query_generator', $queryGenerator);
		$listEntries = $listViewModel->getListViewEntries($pagingModel);
		if (count($listEntries) === 0) {
			return;
		}
		$listHeaders = $listViewModel->getListViewHeaders();

		$viewer->assign('ENTRIES', $listEntries);
		$viewer->assign('HEADERS', $listHeaders);
		$viewer->assign('MODULE', $baseModuleName);
		$viewer->view('TreeRecords.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$baseModuleName = 'Accounts';
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($baseModuleName));
		$viewer->view('TreeRecordsPostProcess.tpl', $request->getModule());
		parent::postProcess($request, false);
	}
}
