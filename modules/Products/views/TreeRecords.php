<?php

/**
 * Products TreeView View Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Products_TreeRecords_View extends Vtiger_TreeRecords_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$baseModuleName = 'Accounts';
		$viewer = $this->getViewer($request);
		$filter = $request->has('filter') ? $request->getByType('filter', 'Alnum') : \App\CustomView::getInstance($baseModuleName)->getViewId();
		$viewer->assign('VIEWID', $filter);
		$moduleName = $request->getModule();
		$listEntries = $listHeaders = [];
		if (!$request->isEmpty('branches', true) || !$request->isEmpty('category', true)) {
			$branches = $request->getArray('branches', 'Text');
			$category = $request->getArray('category', 'Alnum');
			$moduleName = $request->getModule();
			$viewer = $this->getViewer($request);
			$multiReferenceFields = \Vtiger_MultiReferenceValue_UIType::getFieldsByModules($baseModuleName, $moduleName);
			$multiReferenceFieldId = reset($multiReferenceFields);
			if ($multiReferenceFieldId && ($fieldInfo = \App\Field::getFieldInfo($multiReferenceFieldId))) {
				$listViewModel = Vtiger_ListView_Model::getInstance($baseModuleName, $filter);
				$queryGenerator = $listViewModel->getQueryGenerator();
				$conditions = ['or'];
				if (!empty($branches)) {
					$queryField = $queryGenerator->getQueryField($fieldInfo['fieldname']);
					$queryField->setValue(implode('##', $branches));
					$conditions[] = ['or like', $queryField->getColumnName(), $queryField->getValue()];
				}
				if (!empty($category)) {
					$query = (new \App\Db\Query())
						->select(['crmid'])
						->from('u_#__crmentity_rel_tree')
						->where(['module' => App\Module::getModuleId($baseModuleName), 'relmodule' => App\Module::getModuleId($moduleName), 'tree' => $category]);
					$conditions[] = ['in', 'vtiger_crmentity.crmid', $query];
				}
				$queryGenerator->addNativeCondition($conditions);
				$listEntries = $listViewModel->getAllEntries();
				if (0 < \count($listEntries)) {
					$listHeaders = $listViewModel->getListViewHeaders();
				}
			}
		}

		$viewer->assign('ENTRIES', $listEntries);
		$viewer->assign('HEADERS', $listHeaders);
		$viewer->assign('MODULE', $baseModuleName);
		$viewer->view('TreeRecords.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$baseModuleName = 'Accounts';
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($baseModuleName));
		$viewer->view('TreeRecordsPostProcess.tpl', $request->getModule());
		parent::postProcess($request, false);
	}
}
