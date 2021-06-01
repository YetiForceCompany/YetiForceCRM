<?php

/**
 * Products TreeView View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Products_TreeRecords_View extends Vtiger_TreeRecords_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$baseModuleName = 'Accounts';
		$viewer = $this->getViewer($request);
		$filter = $request->has('filter') ? $request->getByType('filter', 'Alnum') : \App\CustomView::getInstance($baseModuleName)->getViewId();
		$viewer->assign('VIEWID', $filter);
		if ($request->isEmpty('branches', true) && $request->isEmpty('category', true)) {
			return;
		}
		$branches = $request->getArray('branches', 'Text');
		$category = $request->getArray('category', 'Alnum');

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$multiReferenceFields = \Vtiger_MultiReferenceValue_UIType::getFieldsByModules($baseModuleName, $moduleName);
		$multiReferenceFieldId = reset($multiReferenceFields);
		if (!$multiReferenceFieldId || !($fieldInfo = \App\Field::getFieldInfo($multiReferenceFieldId))) {
			return;
		}
		$listViewModel = Vtiger_ListView_Model::getInstance($baseModuleName, $filter);
		$queryGenerator = $listViewModel->getQueryGenerator();
		if (!empty($branches)) {
			$queryGenerator->addCondition($fieldInfo['fieldname'], implode('##', $branches), 'e');
		}
		if (!empty($category)) {
			$query = (new \App\Db\Query())
				->select(['crmid'])
				->from('u_#__crmentity_rel_tree')
				->where(['module' => App\Module::getModuleId($baseModuleName), 'relmodule' => App\Module::getModuleId($moduleName), 'tree' => $category]);
			$queryGenerator->addNativeCondition(['in', 'vtiger_crmentity.crmid', $query], false);
		}
		$listEntries = $listViewModel->getAllEntries();
		if (0 === \count($listEntries)) {
			return;
		}
		$listHeaders = $listViewModel->getListViewHeaders();

		$viewer->assign('ENTRIES', $listEntries);
		$viewer->assign('HEADERS', $listHeaders);
		$viewer->assign('MODULE', $baseModuleName);
		$viewer->view('TreeRecords.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$baseModuleName = 'Accounts';
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($baseModuleName));
		$viewer->view('TreeRecordsPostProcess.tpl', $request->getModule());
		parent::postProcess($request, false);
	}
}
