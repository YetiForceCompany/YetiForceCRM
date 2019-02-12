<?php
/**
 * View to create chart with a filter.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * View to create chart with a filter.
 */
class Vtiger_ChartFilter_View extends Vtiger_Index_View
{
	/**
	 * Process request.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIZARD_STEP', $request->getByType('step', 'Alnum'));

		switch ($request->getByType('step', 'Alnum')) {
			case 'step1':
				$modules = vtlib\Functions::getAllModules(true, false, 0);
				$chartTypes = [
					'Pie' => 'LBL_PIE_CHART',
					'Donut' => 'LBL_DONUT_CHART',
					'Bar' => 'LBL_VERTICAL_BAR_CHART',
					'Horizontal' => 'LBL_HORIZONTAL_BAR_CHART',
					'Line' => 'LBL_LINE_CHART',
					'LinePlain' => 'LBL_LINE_CHART_PLAIN',
					'Funnel' => 'LBL_FUNNEL_CHART',
				];
				$viewer->assign('CHART_TYPES', $chartTypes);
				//Since comments is not treated as seperate module
				unset($modules['ModComments']);
				$viewer->assign('MODULES', $modules);
				break;
			case 'step2':
				$selectedModuleName = $request->getByType('selectedModule', 2);
				$viewer->assign('CHART_TYPE', $request->getByType('chartType'));
				$viewer->assign('ALLFILTERS', CustomView_Record_Model::getAllByGroup($selectedModuleName));
				$viewer->assign('SELECTED_MODULE', $selectedModuleName);
				foreach (Vtiger_Module_Model::getInstance($selectedModuleName)->getFields() as $field) {
					if (in_array($field->getFieldDataType(), ['currency', 'double', 'percentage', 'integer'])) {
						$viewer->assign('IS_NUMERAL_VALUE', true);
					}
				}
				break;
			case 'step3':
				$selectedModuleName = $request->getByType('selectedModule', 2);
				$viewer->assign('CHART_TYPE', $request->getByType('chartType'));
				$viewer->assign('MODULE_FIELDS', Vtiger_Module_Model::getInstance($selectedModuleName)->getFieldsByBlocks());
				$viewer->assign('VALUE_TYPE', $request->getByType('valueType'));
				$viewer->assign('SELECTED_MODULE', $selectedModuleName);
				break;
			case 'step4':
				$selectedModuleName = $request->getByType('selectedModule', 2);
				$selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
				$viewer->assign('SELECTED_MODULE', $selectedModuleName);
				$viewer->assign('SELECTED_MODULE_MODEL', $selectedModuleModel);
				$viewer->assign('MODULE_FIELDS', Vtiger_Module_Model::getInstance($selectedModuleName)->getFieldsByBlocks());
				$viewer->assign('CHART_TYPE', $request->getByType('chartType'));
				$viewer->assign('GROUP_FIELD', $request->getByType('groupField', 'Alnum'));
				$viewer->assign('GROUP_FIELD_MODEL', $selectedModuleModel->getFieldByName($request->getByType('groupField', 'Alnum')));
				$filters = $request->getArray('filtersId', 'Integer');
				$viewer->assign('FILTERS', $filters);
				break;
			default:
				break;
		}
		$viewer->view('dashboards/ChartFilter.tpl', $moduleName);
	}
}
